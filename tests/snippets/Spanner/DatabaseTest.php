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

namespace Google\Cloud\Tests\Snippets\Spanner;

use Google\Cloud\Core\Iam\Iam;
use Google\Cloud\Core\LongRunning\LongRunningConnectionInterface;
use Google\Cloud\Dev\Snippet\SnippetTestCase;
use Google\Cloud\Spanner\Connection\ConnectionInterface;
use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\Instance;
use Google\Cloud\Spanner\KeySet;
use Google\Cloud\Spanner\Operation;
use Google\Cloud\Spanner\Result;
use Google\Cloud\Spanner\Session\Session;
use Google\Cloud\Spanner\Session\SessionPoolInterface;
use Google\Cloud\Spanner\Snapshot;
use Google\Cloud\Spanner\Timestamp;
use Google\Cloud\Spanner\Transaction;
use Google\Cloud\Spanner\ValueMapper;
use Prophecy\Argument;

/**
 * @group spanner
 */
class DatabaseTest extends SnippetTestCase
{
    const PROJECT = 'my-awesome-project';
    const DATABASE = 'my-database';
    const INSTANCE = 'my-instance';
    const TRANSACTION = 'my-transaction';

    private $connection;
    private $database;

    public function setUp()
    {
        $instance = $this->prophesize(Instance::class);
        $instance->name()->willReturn(self::INSTANCE);

        $session = $this->prophesize(Session::class);

        $sessionPool = $this->prophesize(SessionPoolInterface::class);
        $sessionPool->acquire(Argument::any())
            ->willReturn($session->reveal());
        $sessionPool->setDatabase(Argument::any())
            ->willReturn(null);

        $this->connection = $this->prophesize(ConnectionInterface::class);
        $this->database = \Google\Cloud\Dev\stub(Database::class, [
            $this->connection->reveal(),
            $instance->reveal(),
            $this->prophesize(LongRunningConnectionInterface::class)->reveal(),
            [],
            self::PROJECT,
            self::DATABASE,
            $sessionPool->reveal()
        ], ['connection', 'operation']);
    }

    private function stubOperation()
    {
        $operation = \Google\Cloud\Dev\stub(Operation::class, [
            $this->connection->reveal(), false
        ]);

        $this->database->___setProperty('operation', $operation);
    }

    public function testClass()
    {
        if (!extension_loaded('grpc')) {
            $this->markTestSkipped('Must have the grpc extension installed to run this test.');
        }

        $snippet = $this->snippetFromClass(Database::class);
        $res = $snippet->invoke('database');
        $this->assertInstanceOf(Database::class, $res->returnVal());
        $this->assertEquals(self::DATABASE, $res->returnVal()->name());
    }

    public function testClassViaInstance()
    {
        if (!extension_loaded('grpc')) {
            $this->markTestSkipped('Must have the grpc extension installed to run this test.');
        }

        $snippet = $this->snippetFromClass(Database::class, 1);
        $res = $snippet->invoke('database');
        $this->assertInstanceOf(Database::class, $res->returnVal());
        $this->assertEquals(self::DATABASE, $res->returnVal()->name());
    }

    public function testName()
    {
        $snippet = $this->snippetFromMethod(Database::class, 'name');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke('name');
        $this->assertEquals(self::DATABASE, $res->returnVal());
    }

    /**
     * @group spanneradmin
     */
    public function testExists()
    {
        $snippet = $this->snippetFromMethod(Database::class, 'exists');
        $snippet->addLocal('database', $this->database);

        $this->connection->getDatabaseDDL(Argument::any())
            ->shouldBeCalled()
            ->willReturn(['statements' => []]);

        $this->database->___setProperty('connection', $this->connection->reveal());

        $res = $snippet->invoke();
        $this->assertEquals('Database exists!', $res->output());
    }

    /**
     * @group spanneradmin
     */
    public function testUpdateDdl()
    {
        $snippet = $this->snippetFromMethod(Database::class, 'updateDdl');
        $snippet->addLocal('database', $this->database);

        $this->connection->updateDatabase(Argument::any())
            ->shouldBeCalled();

        $this->database->___setProperty('connection', $this->connection->reveal());

        $snippet->invoke();
    }

    /**
     * @group spanneradmin
     */
    public function testUpdateDdlBatch()
    {
        $snippet = $this->snippetFromMethod(Database::class, 'updateDdlBatch');
        $snippet->addLocal('database', $this->database);

        $this->connection->updateDatabase(Argument::any())
            ->shouldBeCalled();

        $this->database->___setProperty('connection', $this->connection->reveal());

        $snippet->invoke();
    }

    /**
     * @group spanneradmin
     */
    public function testDrop()
    {
        $snippet = $this->snippetFromMethod(Database::class, 'drop');
        $snippet->addLocal('database', $this->database);

        $this->connection->dropDatabase(Argument::any())
            ->shouldBeCalled();

        $this->database->___setProperty('connection', $this->connection->reveal());

        $snippet->invoke();
    }

    /**
     * @group spanneradmin
     */
    public function testDdl()
    {
        $snippet = $this->snippetFromMethod(Database::class, 'ddl');
        $snippet->addLocal('database', $this->database);

        $stmts = [
            'CREATE TABLE TestSuites',
            'CREATE TABLE TestCases'
        ];

        $this->connection->getDatabaseDDL(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'statements' => $stmts
            ]);

        $this->database->___setProperty('connection', $this->connection->reveal());

        $res = $snippet->invoke('statements');
        $this->assertEquals($stmts, $res->returnVal());
    }

    public function testSnapshot()
    {
        $this->connection->beginTransaction(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'id' => self::TRANSACTION
            ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'snapshot');
        $snippet->addLocal('database', $this->database);

        $res = $snippet->invoke('snapshot');
        $this->assertInstanceOf(Snapshot::class, $res->returnVal());
    }

    public function testSnapshotReadTimestamp()
    {
        $this->connection->beginTransaction(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'id' => self::TRANSACTION,
                'readTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
            ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'snapshot', 1);
        $snippet->addLocal('database', $this->database);

        $res = $snippet->invoke('timestamp');
        $this->assertInstanceOf(Timestamp::class, $res->returnVal());
    }

    public function testRunTransaction()
    {
        $this->connection->beginTransaction(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'id' => self::TRANSACTION
            ]);

        $this->connection->commit(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
            ]);

        $this->connection->rollback(Argument::any())
            ->shouldNotBeCalled();

        $this->connection->executeStreamingSql(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->resultGenerator([
                'metadata' => [
                    'rowType' => [
                        'fields' => [
                            [
                                'name' => 'loginCount',
                                'type' => [
                                    'code' => ValueMapper::TYPE_INT64
                                ]
                            ]
                        ]
                    ]
                ],
                'values' => [0]
            ]));

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'runTransaction');
        $snippet->addUse(Transaction::class);
        $snippet->addLocal('database', $this->database);
        $snippet->addLocal('username', 'foo');
        $snippet->addLocal('password', 'bar');

        $snippet->invoke();
    }

    public function testRunTransactionRollback()
    {
        $this->connection->beginTransaction(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'id' => self::TRANSACTION
            ]);

        $this->connection->commit(Argument::any())
            ->shouldNotBeCalled();

        $this->connection->rollback(Argument::any())
            ->shouldBeCalled();

        $this->connection->executeStreamingSql(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->resultGenerator([
                'metadata' => [
                    'rowType' => [
                        'fields' => []
                    ]
                ],
                'values' => []
            ]));

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'runTransaction');
        $snippet->addUse(Transaction::class);
        $snippet->addLocal('database', $this->database);
        $snippet->addLocal('username', 'foo');
        $snippet->addLocal('password', 'bar');

        $snippet->invoke();
    }

    public function testTransaction()
    {
        $this->connection->beginTransaction(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'id' => self::TRANSACTION
            ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'transaction');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke('transaction');
        $this->assertInstanceOf(Transaction::class, $res->returnVal());
    }

    public function testInsert()
    {
        $this->connection->commit(Argument::that(function ($args) {
            if (!isset($args['mutations'][0]['insert'])) return false;
            return true;
        }))->shouldBeCalled()->willReturn([
            'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
        ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'insert');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke();
    }


    public function testInsertBatch()
    {
        $this->connection->commit(Argument::that(function ($args) {
            if (!isset($args['mutations'][0]['insert'])) return false;
            if (!isset($args['mutations'][1]['insert'])) return false;
            return true;
        }))->shouldBeCalled()->willReturn([
            'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
        ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'insertBatch');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke();
    }

    public function testUpdate()
    {
        $this->connection->commit(Argument::that(function ($args) {
            if (!isset($args['mutations'][0]['update'])) return false;
            return true;
        }))->shouldBeCalled()->willReturn([
            'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
        ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'update');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke();
    }


    public function testUpdateBatch()
    {
        $this->connection->commit(Argument::that(function ($args) {
            if (!isset($args['mutations'][0]['update'])) return false;
            if (!isset($args['mutations'][1]['update'])) return false;
            return true;
        }))->shouldBeCalled()->willReturn([
            'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
        ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'updateBatch');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke();
    }

    public function testInsertOrUpdate()
    {
        $this->connection->commit(Argument::that(function ($args) {
            if (!isset($args['mutations'][0]['insertOrUpdate'])) return false;
            return true;
        }))->shouldBeCalled()->willReturn([
            'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
        ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'insertOrUpdate');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke();
    }


    public function testInsertOrUpdateBatch()
    {
        $this->connection->commit(Argument::that(function ($args) {
            if (!isset($args['mutations'][0]['insertOrUpdate'])) return false;
            if (!isset($args['mutations'][1]['insertOrUpdate'])) return false;
            return true;
        }))->shouldBeCalled()->willReturn([
            'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
        ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'insertOrUpdateBatch');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke();
    }

    public function testReplace()
    {
        $this->connection->commit(Argument::that(function ($args) {
            if (!isset($args['mutations'][0]['replace'])) return false;
            return true;
        }))->shouldBeCalled()->willReturn([
            'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
        ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'replace');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke();
    }


    public function testReplaceBatch()
    {
        $this->connection->commit(Argument::that(function ($args) {
            if (!isset($args['mutations'][0]['replace'])) return false;
            if (!isset($args['mutations'][1]['replace'])) return false;
            return true;
        }))->shouldBeCalled()->willReturn([
            'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
        ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'replaceBatch');
        $snippet->addLocal('database', $this->database);
        $res = $snippet->invoke();
    }

    public function testDelete()
    {
        $this->connection->commit(Argument::that(function ($args) {
            if (!isset($args['mutations'][0]['delete'])) return false;

            return true;
        }))->shouldBeCalled()->willReturn([
            'commitTimestamp' => (new Timestamp(new \DateTime))->formatAsString()
        ]);

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'delete');
        $snippet->addUse(KeySet::class);
        $snippet->addLocal('database', $this->database);
        $snippet->invoke();
    }

    public function testExecute()
    {
        $this->connection->executeStreamingSql(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->resultGenerator([
                'metadata' => [
                    'rowType' => [
                        'fields' => [
                            [
                                'name' => 'loginCount',
                                'type' => [
                                    'code' => ValueMapper::TYPE_INT64
                                ]
                            ]
                        ]
                    ]
                ],
                'values' => [0]
            ]));

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'execute');
        $snippet->addLocal('database', $this->database);

        $res = $snippet->invoke('result');
        $this->assertInstanceOf(Result::class, $res->returnVal());
    }

    public function testExecuteBeginSnapshot()
    {
        $this->connection->executeStreamingSql(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->resultGenerator([
                'metadata' => [
                    'rowType' => [
                        'fields' => [
                            [
                                'name' => 'loginCount',
                                'type' => [
                                    'code' => ValueMapper::TYPE_INT64
                                ]
                            ]
                        ]
                    ],
                    'transaction' => [
                        'id' => self::TRANSACTION
                    ]
                ],
                'values' => [0]
            ]));

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'execute', 1);
        $snippet->addLocal('database', $this->database);

        $res = $snippet->invoke('result');
        $this->assertInstanceOf(Result::class, $res->returnVal());
        $this->assertInstanceOf(Snapshot::class, $res->returnVal()->snapshot());
    }

    public function testExecuteBeginTransaction()
    {
        $this->connection->executeStreamingSql(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->resultGenerator([
                'metadata' => [
                    'rowType' => [
                        'fields' => [
                            [
                                'name' => 'loginCount',
                                'type' => [
                                    'code' => ValueMapper::TYPE_INT64
                                ]
                            ]
                        ]
                    ],
                    'transaction' => [
                        'id' => self::TRANSACTION
                    ]
                ],
                'values' => [0]
            ]));

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'execute', 2);
        $snippet->addLocal('database', $this->database);
        $snippet->addUse(SessionPoolInterface::class);

        $res = $snippet->invoke('result');
        $this->assertInstanceOf(Result::class, $res->returnVal());
        $this->assertInstanceOf(Transaction::class, $res->returnVal()->transaction());
    }

    public function testRead()
    {
        $this->connection->streamingRead(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->resultGenerator([
                'metadata' => [
                    'rowType' => [
                        'fields' => [
                            [
                                'name' => 'loginCount',
                                'type' => [
                                    'code' => ValueMapper::TYPE_INT64
                                ]
                            ]
                        ]
                    ]
                ],
                'rows' => [0]
            ]));

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'read');
        $snippet->addLocal('database', $this->database);
        $snippet->addUse(KeySet::class);

        $res = $snippet->invoke('result');
        $this->assertInstanceOf(Result::class, $res->returnVal());
    }

    public function testReadWithSnapshot()
    {
        $this->connection->streamingRead(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->resultGenerator([
                'metadata' => [
                    'rowType' => [
                        'fields' => [
                            [
                                'name' => 'loginCount',
                                'type' => [
                                    'code' => ValueMapper::TYPE_INT64
                                ]
                            ]
                        ]
                    ],
                    'transaction' => [
                        'id' => self::TRANSACTION
                    ]
                ],
                'values' => [0]
            ]));

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'read', 1);
        $snippet->addLocal('database', $this->database);
        $snippet->addUse(KeySet::class);

        $res = $snippet->invoke('result');
        $this->assertInstanceOf(Result::class, $res->returnVal());
        $this->assertInstanceOf(Snapshot::class, $res->returnVal()->snapshot());
    }

    public function testReadWithTransaction()
    {
        $this->connection->streamingRead(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->resultGenerator([
                'metadata' => [
                    'rowType' => [
                        'fields' => [
                            [
                                'name' => 'loginCount',
                                'type' => [
                                    'code' => ValueMapper::TYPE_INT64
                                ]
                            ]
                        ]
                    ],
                    'transaction' => [
                        'id' => self::TRANSACTION
                    ]
                ],
                'values' => [0]
            ]));

        $this->stubOperation();

        $snippet = $this->snippetFromMethod(Database::class, 'read', 2);
        $snippet->addLocal('database', $this->database);
        $snippet->addUse(KeySet::class);
        $snippet->addUse(SessionPoolInterface::class);

        $res = $snippet->invoke('result');
        $this->assertInstanceOf(Result::class, $res->returnVal());
        $this->assertInstanceOf(Transaction::class, $res->returnVal()->transaction());
    }

    public function testSessionPool()
    {
        $snippet = $this->snippetFromMethod(Database::class, 'sessionPool');
        $snippet->addLocal('database', $this->database);

        $res = $snippet->invoke('pool');
        $this->assertInstanceOf(SessionPoolInterface::class, $res->returnVal());
    }

    public function testClose()
    {
        $snippet = $this->snippetFromMethod(Database::class, 'close');
        $snippet->addLocal('database', $this->database);

        $res = $snippet->invoke();
    }

    public function testIam()
    {
        $snippet = $this->snippetFromMethod(Database::class, 'iam');
        $snippet->addLocal('database', $this->database);

        $res = $snippet->invoke('iam');
        $this->assertInstanceOf(Iam::class, $res->returnVal());
    }

    private function resultGenerator(array $data)
    {
        yield $data;
    }
}
