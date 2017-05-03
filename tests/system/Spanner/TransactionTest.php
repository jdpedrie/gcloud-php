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

namespace Google\Cloud\Tests\System\Spanner;

use Google\Cloud\Spanner\Date;
use Google\Cloud\Spanner\KeySet;
use Google\Cloud\Spanner\Timestamp;

/**
 * @group spannerz
 */
class TransactionTest extends SpannerTestCase
{
    private static $row = [];

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$row = [
            'id' => rand(1000,9999),
            'name' => uniqid(self::TESTING_PREFIX),
            'birthday' => new Date(new \DateTime('2000-01-01'))
        ];

        self::$database->insert(self::TEST_TABLE_NAME, self::$row);
    }

    public function testRunTransaction()
    {
        $db = self::$database;

        $db->runTransaction(function ($t) {
            $id = rand(1,346464);
            $t->insert(self::TEST_TABLE_NAME, [
                'id' => $id,
                'name' => uniqid(self::TESTING_PREFIX),
                'birthday' => new Date(new \DateTime)
            ]);

            $t->commit();
        });

        $db->runTransaction(function ($t) {
            $t->rollback();
        });
    }

    public function testStrongRead()
    {
        $db = self::$database;

        $snapshot = $db->snapshot([
            'strong' => true,
            'returnReadTimestamp' => true
        ]);

        $res = $snapshot->execute('SELECT * FROM '. self::TEST_TABLE_NAME .' WHERE id=@id', [
            'parameters' => [
                'id' => (int)self::$row['id']
            ]
        ]);

        $row = $res->rows()->current();

        $this->assertEquals(self::$row, $row);
        $this->assertInstanceOf(Timestamp::class, $snapshot->readTimestamp());
    }

    public function testExactTimestampRead()
    {
        $db = self::$database;

        $ts = new Timestamp(new \DateTimeImmutable);

        usleep(500);

        $row = self::$row;
        $row['name'] = uniqid(self::TESTING_PREFIX);

        $db->update(self::TEST_TABLE_NAME, $row);

        $snapshot = $db->snapshot([
            'returnReadTimestamp' => true,
            'readTimestamp' => $ts
        ]);

        $keySet = new KeySet([
            'keys' => [self::$row['id']]
        ]);

        $cols = array_keys(self::$row);

        $res = $snapshot->read(self::TEST_TABLE_NAME, $keySet, $cols)->rows();
        print_r(iterator_to_array($res));exit;
        $row = $res->current();

        $this->assertEquals($ts->get(), $snapshot->readTimestamp()->get());
        $this->assertEquals($row, self::$row);
    }
}
