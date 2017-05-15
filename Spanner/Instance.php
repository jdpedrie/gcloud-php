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

namespace Google\Cloud\Spanner;

use Google\Cloud\Core\ArrayTrait;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Core\Iam\Iam;
use Google\Cloud\Core\Iterator\ItemIterator;
use Google\Cloud\Core\Iterator\PageIterator;
use Google\Cloud\Core\LongRunning\LongRunningOperation;
use Google\Cloud\Core\LongRunning\LROTrait;
use Google\Cloud\Core\LongRunning\LongRunningConnectionInterface;
use Google\Cloud\Spanner\Admin\Database\V1\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\InstanceAdminClient;
use Google\Cloud\Spanner\Connection\ConnectionInterface;
use Google\Cloud\Spanner\Connection\IamInstance;
use Google\Cloud\Spanner\Session\SessionPoolInterface;
use google\spanner\admin\instance\v1\Instance\State;

/**
 * Represents a Google Cloud Spanner instance
 *
 * Example:
 * ```
 * use Google\Cloud\ServiceBuilder;
 *
 * $cloud = new ServiceBuilder();
 * $spanner = $cloud->spanner();
 *
 * $instance = $spanner->instance('my-instance');
 * ```
 */
class Instance
{
    use ArrayTrait;
    use LROTrait;

    const STATE_READY = State::READY;
    const STATE_CREATING = State::CREATING;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var SessionPool;
     */
    private $sessionPool;

    /**
     * @var LongRunningConnectionInterface
     */
    private $lroConnection;

    /**
     * @var array
     */
    private $lroCallables;

    /**
     * @var string
     */
    private $projectId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $returnInt64AsObject;

    /**
     * @var array
     */
    private $info;

    /**
     * @var Iam
     */
    private $iam;

    /**
     * Create an object representing a Google Cloud Spanner instance.
     *
     * @param ConnectionInterface $connection The connection to the
     *        Google Cloud Spanner Admin API.
     * @param SessionPoolInterface $sessionPool The session pool implementation.
     * @param LongRunningConnectionInterface $lroConnection An implementation
     *        mapping to methods which handle LRO resolution in the service.
     * @param array $lroCallables
     * @param string $projectId The project ID.
     * @param string $name The instance name.
     * @param bool $returnInt64AsObject If true, 64 bit integers will be
     *        returned as a {@see Google\Cloud\Core\Int64} object for 32 bit platform
     *        compatibility. **Defaults to** false.
     * @param array $info [optional] A representation of the instance object.
     */
    public function __construct(
        ConnectionInterface $connection,
        SessionPoolInterface $sessionPool,
        LongRunningConnectionInterface $lroConnection,
        array $lroCallables,
        $projectId,
        $name,
        $returnInt64AsObject = false,
        array $info = []
    ) {
        $this->connection = $connection;
        $this->sessionPool = $sessionPool;
        $this->lroConnection = $lroConnection;
        $this->lroCallables = $lroCallables;
        $this->projectId = $projectId;
        $this->name = $name;
        $this->returnInt64AsObject = $returnInt64AsObject;
        $this->info = $info;
    }

    /**
     * Return the instance name.
     *
     * Example:
     * ```
     * $name = $instance->name();
     * ```
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Return the service representation of the instance.
     *
     * This method may require a service call.
     *
     * Example:
     * ```
     * $info = $instance->info();
     * echo $info['nodeCount'];
     * ```
     *
     * @param array $options [optional] Configuration options.
     * @return array
     */
    public function info(array $options = [])
    {
        if (!$this->info) {
            $this->reload($options);
        }

        return $this->info;
    }

    /**
     * Check if the instance exists.
     *
     * This method requires a service call.
     *
     * Example:
     * ```
     * if ($instance->exists()) {
     *    echo 'Instance exists!';
     * }
     * ```
     *
     * @param array $options [optional] Configuration options.
     * @return array
     */
    public function exists(array $options = [])
    {
        try {
            $this->reload($options = []);
        } catch (NotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * Fetch a fresh representation of the instance from the service.
     *
     * Example:
     * ```
     * $info = $instance->reload();
     * ```
     *
     * @codingStandardsIgnoreStart
     * @see https://cloud.google.com/spanner/reference/rpc/google.spanner.admin.instance.v1#google.spanner.admin.instance.v1.GetInstanceRequest GetInstanceRequest
     * @codingStandardsIgnoreEnd
     *
     * @param array $options [optional] Configuration options.
     * @return array
     */
    public function reload(array $options = [])
    {
        $this->info = $this->connection->getInstance($options + [
            'name' => $this->fullyQualifiedInstanceName()
        ]);

        return $this->info;
    }

    /**
     * Return the instance state.
     *
     * When instances are created or updated, they may take some time before
     * they are ready for use. This method allows for checking whether an
     * instance is ready.
     *
     * Example:
     * ```
     * if ($instance->state() === Instance::STATE_READY) {
     *     echo 'Instance is ready!';
     * }
     * ```
     *
     * @param array $options [optional] Configuration options.
     * @return string
     */
    public function state(array $options = [])
    {
        $info = $this->info($options);

        return (isset($info['state']))
            ? $info['state']
            : null;
    }

    /**
     * Update the instance
     *
     * Example:
     * ```
     * $instance->update([
     *     'displayName' => 'My Instance',
     *     'nodeCount' => 4
     * ]);
     * ```
     *
     * @codingStandardsIgnoreStart
     * @see https://cloud.google.com/spanner/reference/rpc/google.spanner.admin.instance.v1#updateinstancerequest UpdateInstanceRequest
     * @codingStandardsIgnoreEnd
     *
     * @param array $options [optional] {
     *     Configuration options
     *
     *     @type string $displayName The descriptive name for this instance as
     *           it appears in UIs. **Defaults to** the value of $name.
     *     @type int $nodeCount The number of nodes allocated to this instance.
     *           **Defaults to** `1`.
     *     @type array $labels For more information, see
     *           [Using labels to organize Google Cloud Platform resources](https://goo.gl/xmQnxf).
     * }
     * @return LongRunningOperation<void>
     * @throws \InvalidArgumentException
     */
    public function update(array $options = [])
    {
        $info = $this->info($options);

        $options += [
            'displayName' => $info['displayName'],
            'nodeCount' => (isset($info['nodeCount'])) ? $info['nodeCount'] : null,
            'labels' => (isset($info['labels']))
                ? $info['labels']
                : []
        ];

        $operation = $this->connection->updateInstance([
            'name' => $this->fullyQualifiedInstanceName(),
        ] + $options);

        return $this->lro($this->lroConnection, $operation['name'], $this->lroCallables);
    }

    /**
     * Delete the instance, any databases in the instance, and all data.
     *
     * Example:
     * ```
     * $instance->delete();
     * ```
     *
     * @codingStandardsIgnoreStart
     * @see https://cloud.google.com/spanner/reference/rpc/google.spanner.admin.instance.v1#deleteinstancerequest DeleteInstanceRequest
     * @codingStandardsIgnoreEnd
     *
     * @param array $options [optional] Configuration options.
     * @return void
     */
    public function delete(array $options = [])
    {
        return $this->connection->deleteInstance($options + [
            'name' => $this->fullyQualifiedInstanceName()
        ]);
    }

    /**
     * Create a Database
     *
     * Example:
     * ```
     * $database = $instance->createDatabase('my-database');
     * ```
     *
     * @codingStandardsIgnoreStart
     * @see https://cloud.google.com/spanner/reference/rpc/google.spanner.admin.database.v1#createdatabaserequest CreateDatabaseRequest
     * @codingStandardsIgnoreEnd
     *
     * @param string $name The database name.
     * @param array $options [optional] {
     *     Configuration Options
     *
     *     @type array $statements Additional DDL statements.
     * }
     * @return LongRunningOperation<Database>
     */
    public function createDatabase($name, array $options = [])
    {
        $options += [
            'statements' => [],
        ];

        $statement = sprintf('CREATE DATABASE `%s`', $name);

        $operation = $this->connection->createDatabase([
            'instance' => $this->fullyQualifiedInstanceName(),
            'createStatement' => $statement,
            'extraStatements' => $options['statements']
        ]);

        return $this->lro($this->lroConnection, $operation['name'], $this->lroCallables);
    }

    /**
     * Lazily instantiate a database object
     *
     * Example:
     * ```
     * $database = $instance->database('my-database');
     * ```
     *
     * @param string $name The database name
     * @return Database
     */
    public function database($name)
    {
        return new Database(
            $this->connection,
            $this,
            $this->sessionPool,
            $this->lroConnection,
            $this->lroCallables,
            $this->projectId,
            $name,
            $this->returnInt64AsObject
        );
    }

    /**
     * List databases in an instance
     *
     * Example:
     * ```
     * $databases = $instance->databases();
     * ```
     *
     * @codingStandardsIgnoreStart
     * @see https://cloud.google.com/spanner/docs/reference/rpc/google.spanner.admin.database.v1#listdatabasesrequest ListDatabasesRequest
     * @codingStandardsIgnoreEnd
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type int $pageSize Maximum number of results to return per
     *           request.
     *     @type int $resultLimit Limit the number of results returned in total.
     *           **Defaults to** `0` (return all results).
     *     @type string $pageToken A previously-returned page token used to
     *           resume the loading of results from a specific point.
     * }
     * @return ItemIterator<Database>
     */
    public function databases(array $options = [])
    {
        $resultLimit = $this->pluck('resultLimit', $options, false);
        return new ItemIterator(
            new PageIterator(
                function (array $database) {
                    $name = DatabaseAdminClient::parseDatabaseFromDatabaseName($database['name']);
                    return $this->database($name);
                },
                [$this->connection, 'listDatabases'],
                $options + ['instance' => $this->fullyQualifiedInstanceName()],
                [
                    'itemsKey' => 'databases',
                    'resultLimit' => $resultLimit
                ]
            )
        );
    }

    /**
     * Manage the instance IAM policy
     *
     * Example:
     * ```
     * $iam = $instance->iam();
     * ```
     *
     * @return Iam
     */
    public function iam()
    {
        if (!$this->iam) {
            $this->iam = new Iam(
                new IamInstance($this->connection),
                $this->fullyQualifiedInstanceName()
            );
        }

        return $this->iam;
    }

    /**
     * Convert the simple instance name to a fully qualified name.
     *
     * @return string
     */
    private function fullyQualifiedInstanceName()
    {
        return InstanceAdminClient::formatInstanceName($this->projectId, $this->name);
    }

    /**
     * Represent the class in a more readable and digestable fashion.
     *
     * @access private
     * @codeCoverageIgnore
     */
    public function __debugInfo()
    {
        return [
            'connection' => get_class($this->connection),
            'projectId' => $this->projectId,
            'name' => $this->name,
            'info' => $this->info
        ];
    }
}
