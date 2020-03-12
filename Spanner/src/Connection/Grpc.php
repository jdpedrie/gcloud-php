<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Spanner\Connection;

use Google\ApiCore\Call;
use Google\ApiCore\CredentialsWrapper;
use Google\ApiCore\Serializer;
use Google\Cloud\Core\EmulatorTrait;
use Google\Cloud\Core\GrpcRequestWrapper;
use Google\Cloud\Core\GrpcTrait;
use Google\Cloud\Core\LongRunning\OperationResponseTrait;
use Google\Cloud\Spanner\Admin\Database\V1\Database;
use Google\Cloud\Spanner\Admin\Database\V1\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\Instance;
use Google\Cloud\Spanner\Admin\Instance\V1\InstanceAdminClient;
use Google\Cloud\Spanner\Operation;
use Google\Cloud\Spanner\SpannerClient as ManualSpannerClient;
use Google\Cloud\Spanner\V1\CreateSessionRequest;
use Google\Cloud\Spanner\V1\DeleteSessionRequest;
use Google\Cloud\Spanner\V1\ExecuteBatchDmlRequest\Statement;
use Google\Cloud\Spanner\V1\ExecuteSqlRequest\QueryOptions;
use Google\Cloud\Spanner\V1\KeySet;
use Google\Cloud\Spanner\V1\Mutation;
use Google\Cloud\Spanner\V1\Mutation\Delete;
use Google\Cloud\Spanner\V1\Mutation\Write;
use Google\Cloud\Spanner\V1\PartitionOptions;
use Google\Cloud\Spanner\V1\Session;
use Google\Cloud\Spanner\V1\SpannerClient;
use Google\Cloud\Spanner\V1\TransactionOptions;
use Google\Cloud\Spanner\V1\TransactionOptions\PartitionedDml;
use Google\Cloud\Spanner\V1\TransactionOptions\ReadOnly;
use Google\Cloud\Spanner\V1\TransactionOptions\ReadWrite;
use Google\Cloud\Spanner\V1\TransactionSelector;
use Google\Cloud\Spanner\V1\Type;
use Google\Protobuf;
use Google\Protobuf\FieldMask;
use Google\Protobuf\GPBEmpty;
use Google\Protobuf\ListValue;
use Google\Protobuf\Struct;
use Google\Protobuf\Value;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Connection to Cloud Spanner over gRPC
 */
class Grpc implements ConnectionInterface
{
    use EmulatorTrait;
    use GrpcTrait;
    use OperationResponseTrait;

    /**
     * @var InstanceAdminClient|null
     */
    private $instanceAdminClient;

    /**
     * @var DatabaseAdminClient|null
     */
    private $databaseAdminClient;

    /**
     * @var SpannerClient
     */
    private $spannerClient;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $defaultQueryOptions;

    /**
     * @var array
     */
    private $grpcConfig;

    /**
     * @var array
     */
    private $mutationSetters = [
        'insert' => 'setInsert',
        'update' => 'setUpdate',
        'insertOrUpdate' => 'setInsertOrUpdate',
        'replace' => 'setReplace',
        'delete' => 'setDelete'
    ];

    /**
     * @var array
     */
    private $lroResponseMappers = [
        [
            'method' => 'updateDatabaseDdl',
            'typeUrl' => 'type.googleapis.com/google.spanner.admin.database.v1.UpdateDatabaseDdlMetadata',
            'message' => GPBEmpty::class
        ], [
            'method' => 'createDatabase',
            'typeUrl' => 'type.googleapis.com/google.spanner.admin.database.v1.CreateDatabaseMetadata',
            'message' => Database::class
        ], [
            'method' => 'createInstance',
            'typeUrl' => 'type.googleapis.com/google.spanner.admin.instance.v1.CreateInstanceMetadata',
            'message' => Instance::class
        ], [
            'method' => 'updateInstance',
            'typeUrl' => 'type.googleapis.com/google.spanner.admin.instance.v1.UpdateInstanceMetadata',
            'message' => Instance::class
        ]
    ];

    /**
     * @var CredentialsWrapper
     */
    private $credentialsWrapper;

    /**
     * @param array $config [optional]
     */
    public function __construct(array $config = [])
    {
        //@codeCoverageIgnoreStart
        $this->serializer = new Serializer([
            'commit_timestamp' => function ($v) {
                return $this->formatTimestampFromApi($v);
            },
            'read_timestamp' => function ($v) {
                return $this->formatTimestampFromApi($v);
            }
        ], [
            'google.protobuf.Value' => function ($v) {
                return $this->flattenValue($v);
            },
            'google.protobuf.ListValue' => function ($v) {
                return $this->flattenListValue($v);
            },
            'google.protobuf.Struct' => function ($v) {
                return $this->flattenStruct($v);
            },
        ]);
        //@codeCoverageIgnoreEnd

        $config['serializer'] = $this->serializer;
        $this->setRequestWrapper(new GrpcRequestWrapper($config));
        $grpcConfig = $this->getGaxConfig(
            ManualSpannerClient::VERSION,
            isset($config['authHttpHandler'])
                ? $config['authHttpHandler']
                : null
        );

        $config += [
            'emulatorHost' => null,
            'queryOptions' => []
        ];
        if ((bool) $config['emulatorHost']) {
            $grpcConfig += $this->emulatorGapicConfig($config['emulatorHost']);
        } else {
            $this->credentialsWrapper = $grpcConfig['credentials'];
            if (isset($config['apiEndpoint'])) {
                $grpcConfig['apiEndpoint'] = $config['apiEndpoint'];
            }
        }

        // Environment-level query options have a higher precedence than client-level query options.
        $envQueryOptimizerVersion = getenv('SPANNER_OPTIMIZER_VERSION');
        if (!empty($envQueryOptimizerVersion)) {
            $config['queryOptions']['optimizerVersion'] = $envQueryOptimizerVersion;
        }
        $this->defaultQueryOptions = $config['queryOptions'];

        $this->spannerClient = isset($config['gapicSpannerClient'])
            ? $config['gapicSpannerClient']
            : $this->constructGapic(SpannerClient::class, $grpcConfig);

        //@codeCoverageIgnoreStart
        if (isset($config['gapicSpannerInstanceAdminClient'])) {
            $this->instanceAdminClient = $config['gapicSpannerInstanceAdminClient'];
        }

        if (isset($config['gapicSpannerDatabaseAdminClient'])) {
            $this->databaseAdminClient = $config['gapicSpannerDatabaseAdminClient'];
        }
        //@codeCoverageIgnoreEnd

        $this->grpcConfig = $grpcConfig;
    }

    /**
     * @param array $args
     */
    public function listInstanceConfigs(array $args)
    {
        $projectId = $this->pluck('projectId', $args);
        return $this->send([$this->getInstanceAdminClient(), 'listInstanceConfigs'], [
            $projectId,
            $this->addResourcePrefixHeader($args, $projectId)
        ]);
    }

    /**
     * @param array $args
     */
    public function getInstanceConfig(array $args)
    {
        $projectId = $this->pluck('projectId', $args);
        return $this->send([$this->getInstanceAdminClient(), 'getInstanceConfig'], [
            $this->pluck('name', $args),
            $this->addResourcePrefixHeader($args, $projectId)
        ]);
    }

    /**
     * @param array $args
     */
    public function listInstances(array $args)
    {
        $projectId = $this->pluck('projectId', $args);
        return $this->send([$this->getInstanceAdminClient(), 'listInstances'], [
            $projectId,
            $this->addResourcePrefixHeader($args, $projectId)
        ]);
    }

    /**
     * @param array $args
     */
    public function getInstance(array $args)
    {
        $projectId = $this->pluck('projectId', $args);

        if (isset($args['fieldMask'])) {
            $mask = [];
            if (is_array($args['fieldMask'])) {
                foreach (array_values($args['fieldMask']) as $field) {
                    $mask[] = Serializer::toSnakeCase($field);
                }
            } else {
                $mask[] = Serializer::toSnakeCase($args['fieldMask']);
            }
            $fieldMask = $this->serializer->decodeMessage(new FieldMask(), ['paths' => $mask]);
            $args['fieldMask'] = $fieldMask;
        }

        return $this->send([$this->getInstanceAdminClient(), 'getInstance'], [
            $this->pluck('name', $args),
            $this->addResourcePrefixHeader($args, $projectId)
        ]);
    }

    /**
     * @param array $args
     */
    public function createInstance(array $args)
    {
        $instanceName = $args['name'];

        $instance = $this->instanceObject($args, true);
        $res = $this->send([$this->getInstanceAdminClient(), 'createInstance'], [
            $this->pluck('projectId', $args),
            $this->pluck('instanceId', $args),
            $instance,
            $this->addResourcePrefixHeader($args, $instanceName)
        ]);

        return $this->operationToArray($res, $this->serializer, $this->lroResponseMappers);
    }

    /**
     * @param array $args
     */
    public function updateInstance(array $args)
    {
        $instanceName = $args['name'];

        $instanceArray = $this->instanceArray($args);

        $fieldMask = $this->fieldMask($instanceArray);

        $instanceObject = $this->serializer->decodeMessage(new Instance(), $instanceArray);

        $res = $this->send([$this->getInstanceAdminClient(), 'updateInstance'], [
            $instanceObject,
            $fieldMask,
            $this->addResourcePrefixHeader($args, $instanceName)
        ]);

        return $this->operationToArray($res, $this->serializer, $this->lroResponseMappers);
    }

    /**
     * @param array $args
     */
    public function deleteInstance(array $args)
    {
        $instanceName = $this->pluck('name', $args);
        return $this->send([$this->getInstanceAdminClient(), 'deleteInstance'], [
            $instanceName,
            $this->addResourcePrefixHeader($args, $instanceName)
        ]);
    }

    /**
     * @param array $args
     */
    public function getInstanceIamPolicy(array $args)
    {
        $resource = $this->pluck('resource', $args);
        return $this->send([$this->getInstanceAdminClient(), 'getIamPolicy'], [
            $resource,
            $this->addResourcePrefixHeader($args, $resource)
        ]);
    }

    /**
     * @param array $args
     */
    public function setInstanceIamPolicy(array $args)
    {
        $resource = $this->pluck('resource', $args);
        return $this->send([$this->getInstanceAdminClient(), 'setIamPolicy'], [
            $resource,
            $this->pluck('policy', $args),
            $this->addResourcePrefixHeader($args, $resource)
        ]);
    }

    /**
     * @param array $args
     */
    public function testInstanceIamPermissions(array $args)
    {
        $resource = $this->pluck('resource', $args);
        return $this->send([$this->getInstanceAdminClient(), 'testIamPermissions'], [
            $resource,
            $this->pluck('permissions', $args),
            $this->addResourcePrefixHeader($args, $resource)
        ]);
    }

    /**
     * @param array $args
     */
    public function listDatabases(array $args)
    {
        $instanceName = $this->pluck('instance', $args);
        return $this->send([$this->getDatabaseAdminClient(), 'listDatabases'], [
            $instanceName,
            $this->addResourcePrefixHeader($args, $instanceName)
        ]);
    }

    /**
     * @param array $args
     */
    public function createDatabase(array $args)
    {
        $instanceName = $this->pluck('instance', $args);
        $res = $this->send([$this->getDatabaseAdminClient(), 'createDatabase'], [
            $instanceName,
            $this->pluck('createStatement', $args),
            $this->addResourcePrefixHeader($args, $instanceName)
        ]);

        return $this->operationToArray($res, $this->serializer, $this->lroResponseMappers);
    }

    /**
     * @param array $args
     */
    public function updateDatabaseDdl(array $args)
    {
        $databaseName = $this->pluck('name', $args);
        $res = $this->send([$this->getDatabaseAdminClient(), 'updateDatabaseDdl'], [
            $databaseName,
            $this->pluck('statements', $args),
            $this->addResourcePrefixHeader($args, $databaseName)
        ]);

        return $this->operationToArray($res, $this->serializer, $this->lroResponseMappers);
    }

    /**
     * @param array $args
     */
    public function dropDatabase(array $args)
    {
        $databaseName = $this->pluck('name', $args);
        return $this->send([$this->getDatabaseAdminClient(), 'dropDatabase'], [
            $databaseName,
            $this->addResourcePrefixHeader($args, $databaseName)
        ]);
    }

    /**
     * @param array $args
     */
    public function getDatabase(array $args)
    {
        $databaseName = $this->pluck('name', $args);
        return $this->send([$this->getDatabaseAdminClient(), 'getDatabase'], [
            $databaseName,
            $this->addResourcePrefixHeader($args, $databaseName)
        ]);
    }

    /**
     * @param array $args
     */
    public function getDatabaseDdl(array $args)
    {
        $databaseName = $this->pluck('name', $args);
        return $this->send([$this->getDatabaseAdminClient(), 'getDatabaseDdl'], [
            $databaseName,
            $this->addResourcePrefixHeader($args, $databaseName)
        ]);
    }

    /**
     * @param array $args
     */
    public function getDatabaseIamPolicy(array $args)
    {
        $databaseName = $this->pluck('resource', $args);
        return $this->send([$this->getDatabaseAdminClient(), 'getIamPolicy'], [
            $databaseName,
            $this->addResourcePrefixHeader($args, $databaseName)
        ]);
    }

    /**
     * @param array $args
     */
    public function setDatabaseIamPolicy(array $args)
    {
        $databaseName = $this->pluck('resource', $args);
        return $this->send([$this->getDatabaseAdminClient(), 'setIamPolicy'], [
            $databaseName,
            $this->pluck('policy', $args),
            $this->addResourcePrefixHeader($args, $databaseName)
        ]);
    }

    /**
     * @param array $args
     */
    public function testDatabaseIamPermissions(array $args)
    {
        $databaseName = $this->pluck('resource', $args);
        return $this->send([$this->getDatabaseAdminClient(), 'testIamPermissions'], [
            $databaseName,
            $this->pluck('permissions', $args),
            $this->addResourcePrefixHeader($args, $databaseName)
        ]);
    }

    /**
     * @param array $args
     */
    public function createSession(array $args)
    {
        $databaseName = $this->pluck('database', $args);

        $session = $this->pluck('session', $args, false);
        if ($session) {
            $args['session'] = $this->serializer->decodeMessage(new Session, $session);
        }

        return $this->send([$this->spannerClient, 'createSession'], [
            $databaseName,
            $this->addResourcePrefixHeader($args, $databaseName)
        ]);
    }

    /**
     * Note: This should be removed once GAPIC exposes the ability to execute
     * concurrent requests.
     *
     * @access private
     * @experimental
     * @param array $args
     * @return PromiseInterface
     */
    public function createSessionAsync(array $args)
    {
        $database = $this->pluck('database', $args);
        $opts = $this->addResourcePrefixHeader([], $database);
        $opts['credentialsWrapper'] = $this->credentialsWrapper;
        $transport = $this->spannerClient->getTransport();

        $request = new CreateSessionRequest([
            'database' => $database
        ]);

        $session = $this->pluck('session', $args, false);

        if ($session) {
            $sessionMessage = new Session($session);
            $request->setSession($sessionMessage);
        }

        return $transport->startUnaryCall(
            new Call(
                'google.spanner.v1.Spanner/CreateSession',
                Session::class,
                $request
            ),
            $opts
        );
    }

    /**
     * @param array $args
     */
    public function batchCreateSessions(array $args)
    {
        $args['sessionTemplate'] = $this->serializer->decodeMessage(
            new Session,
            $this->pluck('sessionTemplate', $args)
        );

        $database = $this->pluck('database', $args);
        return $this->send([$this->spannerClient, 'batchCreateSessions'], [
            $database,
            $this->pluck('sessionCount', $args),
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     */
    public function getSession(array $args)
    {
        $database = $this->pluck('database', $args);
        return $this->send([$this->spannerClient, 'getSession'], [
            $this->pluck('name', $args),
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     */
    public function deleteSession(array $args)
    {
        $database = $this->pluck('database', $args);
        return $this->send([$this->spannerClient, 'deleteSession'], [
            $this->pluck('name', $args),
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * Note: This should be removed once GAPIC exposes the ability to execute
     * concurrent requests.
     *
     * @access private
     * @param array $args
     * @return PromiseInterface
     * @experimental
     */
    public function deleteSessionAsync(array $args)
    {
        $database = $this->pluck('database', $args);
        $request = new DeleteSessionRequest();
        $request->setName($this->pluck('name', $args));

        $transport = $this->spannerClient->getTransport();
        $opts = $this->addResourcePrefixHeader([], $database);
        $opts['credentialsWrapper'] = $this->credentialsWrapper;

        return $transport->startUnaryCall(
            new Call(
                'google.spanner.v1.Spanner/DeleteSession',
                GPBEmpty::class,
                $request
            ),
            $opts
        );
    }

    /**
     * @param array $args
     * @return \Generator
     */
    public function executeStreamingSql(array $args)
    {
        $args = $this->formatSqlParams($args);
        $args['transaction'] = $this->createTransactionSelector($args);

        $database = $this->pluck('database', $args);

        $args += ['queryOptions' => $this->defaultQueryOptions];
        if (isset($args['queryOptions'])) {
            $args['queryOptions'] = $this->serializer->decodeMessage(
                new QueryOptions,
                $args['queryOptions']
            );
        }

        return $this->send([$this->spannerClient, 'executeStreamingSql'], [
            $this->pluck('session', $args),
            $this->pluck('sql', $args),
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     * @return \Generator
     */
    public function streamingRead(array $args)
    {
        $keySet = $this->pluck('keySet', $args);
        $keySet = $this->serializer->decodeMessage(new KeySet, $this->formatKeySet($keySet));

        $args['transaction'] = $this->createTransactionSelector($args);

        $database = $this->pluck('database', $args);
        return $this->send([$this->spannerClient, 'streamingRead'], [
            $this->pluck('session', $args),
            $this->pluck('table', $args),
            $this->pluck('columns', $args),
            $keySet,
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     */
    public function executeBatchDml(array $args)
    {
        $database = $this->pluck('database', $args);
        $args['transaction'] = $this->createTransactionSelector($args);

        $statements = [];
        foreach ($this->pluck('statements', $args) as $statement) {
            $statement = $this->formatSqlParams($statement);
            $statements[] = $this->serializer->decodeMessage(new Statement, $statement);
        }

        return $this->send([$this->spannerClient, 'executeBatchDml'], [
            $this->pluck('session', $args),
            $this->pluck('transaction', $args),
            $statements,
            $this->pluck('seqno', $args),
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     */
    public function beginTransaction(array $args)
    {
        $options = new TransactionOptions;

        $transactionOptions = $this->formatTransactionOptions($this->pluck('transactionOptions', $args));
        if (isset($transactionOptions['readOnly'])) {
            $readOnly = $this->serializer->decodeMessage(
                new ReadOnly(),
                $transactionOptions['readOnly']
            );
            $options->setReadOnly($readOnly);
        } elseif (isset($transactionOptions['readWrite'])) {
            $readWrite = new ReadWrite();
            $options->setReadWrite($readWrite);
        } elseif (isset($transactionOptions['partitionedDml'])) {
            $pdml = new PartitionedDml();
            $options->setPartitionedDml($pdml);
        }

        $database = $this->pluck('database', $args);
        return $this->send([$this->spannerClient, 'beginTransaction'], [
            $this->pluck('session', $args),
            $options,
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     */
    public function commit(array $args)
    {
        $inputMutations = $this->pluck('mutations', $args);

        $mutations = [];
        if (is_array($inputMutations)) {
            foreach ($inputMutations as $mutation) {
                $type = array_keys($mutation)[0];
                $data = $mutation[$type];

                switch ($type) {
                    case Operation::OP_DELETE:
                        if (isset($data['keySet'])) {
                            $data['keySet'] = $this->formatKeySet($data['keySet']);
                        }

                        $operation = $this->serializer->decodeMessage(
                            new Delete,
                            $data
                        );
                        break;
                    default:
                        $operation = new Write;
                        $operation->setTable($data['table']);
                        $operation->setColumns($data['columns']);

                        $modifiedData = [];
                        foreach ($data['values'] as $key => $param) {
                            $modifiedData[$key] = $this->fieldValue($param);
                        }

                        $list = new ListValue;
                        $list->setValues($modifiedData);
                        $values = [$list];
                        $operation->setValues($values);

                        break;
                }

                $setterName = $this->mutationSetters[$type];
                $mutation = new Mutation;
                $mutation->$setterName($operation);
                $mutations[] = $mutation;
            }
        }

        if (isset($args['singleUseTransaction'])) {
            $readWrite = $this->serializer->decodeMessage(
                new ReadWrite,
                []
            );

            $options = new TransactionOptions;
            $options->setReadWrite($readWrite);
            $args['singleUseTransaction'] = $options;
        }

        $database = $this->pluck('database', $args);
        return $this->send([$this->spannerClient, 'commit'], [
            $this->pluck('session', $args),
            $mutations,
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     */
    public function rollback(array $args)
    {
        $database = $this->pluck('database', $args);
        return $this->send([$this->spannerClient, 'rollback'], [
            $this->pluck('session', $args),
            $this->pluck('transactionId', $args),
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     */
    public function partitionQuery(array $args)
    {
        $args = $this->formatSqlParams($args);
        $args['transaction'] = $this->createTransactionSelector($args);

        $args['partitionOptions'] = $this->serializer->decodeMessage(
            new PartitionOptions,
            $this->pluck('partitionOptions', $args, false) ?: []
        );

        $database = $this->pluck('database', $args);
        return $this->send([$this->spannerClient, 'partitionQuery'], [
            $this->pluck('session', $args),
            $this->pluck('sql', $args),
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     */
    public function partitionRead(array $args)
    {
        $keySet = $this->pluck('keySet', $args);
        $keySet = $this->serializer->decodeMessage(new KeySet, $this->formatKeySet($keySet));

        $args['transaction'] = $this->createTransactionSelector($args);

        $args['partitionOptions'] = $this->serializer->decodeMessage(
            new PartitionOptions,
            $this->pluck('partitionOptions', $args, false) ?: []
        );

        $database = $this->pluck('database', $args);
        return $this->send([$this->spannerClient, 'partitionRead'], [
            $this->pluck('session', $args),
            $this->pluck('table', $args),
            $keySet,
            $this->addResourcePrefixHeader($args, $database)
        ]);
    }

    /**
     * @param array $args
     */
    public function getOperation(array $args)
    {
        $name = $this->pluck('name', $args);

        $operation = $this->getOperationByName($this->databaseAdminClient, $name);

        return $this->operationToArray($operation, $this->serializer, $this->lroResponseMappers);
    }

    /**
     * @param array $args
     */
    public function cancelOperation(array $args)
    {
        $name = $this->pluck('name', $args);
        $method = $this->pluck('method', $args);

        $operation = $this->getOperationByName($this->databaseAdminClient, $name, $method);
        $operation->cancel();

        return $this->operationToArray($operation, $this->serializer, $this->lroResponseMappers);
    }

    /**
     * @param array $args
     */
    public function deleteOperation(array $args)
    {
        $name = $this->pluck('name', $args);
        $method = $this->pluck('method', $args);

        $operation = $this->getOperationByName($this->databaseAdminClient, $name, $method);
        $operation->delete();

        return $this->operationToArray($operation, $this->serializer, $this->lroResponseMappers);
    }

    /**
     * @param array $args
     */
    public function listOperations(array $args)
    {
        $name = $this->pluck('name', $args, false) ?: '';
        $filter = $this->pluck('filter', $args, false) ?: '';

        $client = $this->databaseAdminClient->getOperationsClient();

        return $this->send([$client, 'listOperations'], [
            $name,
            $filter,
            $args
        ]);
    }

    /**
     * @param array $args
     * @return array
     */
    private function formatSqlParams(array $args)
    {
        $params = $this->pluck('params', $args);
        if ($params) {
            $modifiedParams = [];
            foreach ($params as $key => $param) {
                $modifiedParams[$key] = $this->fieldValue($param);
            }
            $args['params'] = new Struct;
            $args['params']->setFields($modifiedParams);
        }

        if (isset($args['paramTypes']) && is_array($args['paramTypes'])) {
            foreach ($args['paramTypes'] as $key => $param) {
                $args['paramTypes'][$key] = $this->serializer->decodeMessage(new Type, $param);
            }
        }

        return $args;
    }

    /**
     * @param array $keySet
     * @return array Formatted keyset
     */
    private function formatKeySet(array $keySet)
    {
        $keys = $this->pluck('keys', $keySet, false);
        if ($keys) {
            $keySet['keys'] = [];

            foreach ($keys as $key) {
                $keySet['keys'][] = $this->formatListForApi((array) $key);
            }
        }

        if (isset($keySet['ranges'])) {
            foreach ($keySet['ranges'] as $index => $rangeItem) {
                foreach ($rangeItem as $key => $val) {
                    $rangeItem[$key] = $this->formatListForApi($val);
                }

                $keySet['ranges'][$index] = $rangeItem;
            }

            if (empty($keySet['ranges'])) {
                unset($keySet['ranges']);
            }
        }

        return $keySet;
    }

    /**
     * @param array $args
     * @return TransactionSelector
     */
    private function createTransactionSelector(array &$args)
    {
        $selector = new TransactionSelector;
        if (isset($args['transaction'])) {
            $transaction = $this->pluck('transaction', $args);

            if (isset($transaction['singleUse'])) {
                $transaction['singleUse'] = $this->formatTransactionOptions($transaction['singleUse']);
            }

            if (isset($transaction['begin'])) {
                $transaction['begin'] = $this->formatTransactionOptions($transaction['begin']);
            }

            $selector = $this->serializer->decodeMessage($selector, $transaction);
        } elseif (isset($args['transactionId'])) {
            $selector = $this->serializer->decodeMessage($selector, ['id' => $this->pluck('transactionId', $args)]);
        }

        return $selector;
    }

    /**
     * @param array $args
     * @param bool $required
     * @return Instance
     */
    private function instanceObject(array &$args, $required = false)
    {
        return $this->serializer->decodeMessage(
            new Instance(),
            $this->instanceArray($args, $required)
        );
    }

    /**
     * @param array $args
     * @param bool $required
     * @return array
     */
    private function instanceArray(array &$args, $required = false)
    {
        $argsCopy = $args;
        return array_intersect_key([
            'name' => $this->pluck('name', $args, $required),
            'config' => $this->pluck('config', $args, $required),
            'displayName' => $this->pluck('displayName', $args, $required),
            'nodeCount' => $this->pluck('nodeCount', $args, $required),
            'state' => $this->pluck('state', $args, $required),
            'labels' => $this->pluck('labels', $args, $required),
        ], $argsCopy);
    }

    /**
     * @param array $instanceArray
     * @return FieldMask
     */
    private function fieldMask($instanceArray)
    {
        $mask = [];
        foreach (array_keys($instanceArray) as $key) {
            $mask[] = Serializer::toSnakeCase($key);
        }
        return $this->serializer->decodeMessage(new FieldMask(), ['paths' => $mask]);
    }

    /**
     * @param mixed $param
     * @return Value
     */
    private function fieldValue($param)
    {
        $field = new Value;
        $value = $this->formatValueForApi($param);

        $setter = null;
        switch (array_keys($value)[0]) {
            case 'string_value':
                $setter = 'setStringValue';
                break;
            case 'number_value':
                $setter = 'setNumberValue';
                break;
            case 'bool_value':
                $setter = 'setBoolValue';
                break;
            case 'null_value':
                $setter = 'setNullValue';
                break;
            case 'struct_value':
                $setter = 'setStructValue';
                $modifiedParams = [];
                foreach ($param as $key => $value) {
                    $modifiedParams[$key] = $this->fieldValue($value);
                }
                $value = new Struct;
                $value->setFields($modifiedParams);

                break;
            case 'list_value':
                $setter = 'setListValue';
                $modifiedParams = [];
                foreach ($param as $item) {
                    $modifiedParams[] = $this->fieldValue($item);
                }
                $list = new ListValue;
                $list->setValues($modifiedParams);
                $value = $list;

                break;
        }

        $value = is_array($value) ? current($value) : $value;
        if ($setter) {
            $field->$setter($value);
        }

        return $field;
    }

    /**
     * @param array $transactionOptions
     * @return array
     */
    private function formatTransactionOptions(array $transactionOptions)
    {
        if (isset($transactionOptions['readOnly'])) {
            $ro = $transactionOptions['readOnly'];
            if (isset($ro['minReadTimestamp'])) {
                $ro['minReadTimestamp'] = $this->formatTimestampForApi($ro['minReadTimestamp']);
            }

            if (isset($ro['readTimestamp'])) {
                $ro['readTimestamp'] = $this->formatTimestampForApi($ro['readTimestamp']);
            }

            $transactionOptions['readOnly'] = $ro;
        }

        return $transactionOptions;
    }

    /**
     * Add the `google-cloud-resource-prefix` header value to the request.
     *
     * @param array $args
     * @param string $value
     * @return array
     */
    private function addResourcePrefixHeader(array $args, $value)
    {
        $args['headers'] = [
            'google-cloud-resource-prefix' => [$value]
        ];

        return $args;
    }

    /**
     * Allow lazy instantiation of the instance admin client.
     *
     * @return InstanceAdminClient
     */
    private function getInstanceAdminClient()
    {
        //@codeCoverageIgnoreStart
        if ($this->instanceAdminClient) {
            return $this->instanceAdminClient;
        }
        //@codeCoverageIgnoreEnd

        $this->instanceAdminClient = $this->constructGapic(InstanceAdminClient::class, $this->grpcConfig);

        return $this->instanceAdminClient;
    }

    /**
     * Allow lazy instantiation of the database admin client.
     *
     * @return DatabaseAdminClient
     */
    private function getDatabaseAdminClient()
    {
        //@codeCoverageIgnoreStart
        if ($this->databaseAdminClient) {
            return $this->databaseAdminClient;
        }
        //@codeCoverageIgnoreEnd

        $this->databaseAdminClient = $this->constructGapic(DatabaseAdminClient::class, $this->grpcConfig);

        return $this->databaseAdminClient;
    }
}
