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

use Google\Cloud\Core\Int64;
use Google\Cloud\Spanner\Bytes;
use Google\Cloud\Spanner\ValueMapper;

/**
 * @group spanner
 * @group spanner-read
 */
class ReadTest extends SpannerTestCase
{
    /**
     * covers 21
     */
    public function testQueryReturnsArrayStruct()
    {
        $db = self::$database;

        $res = $db->execute('SELECT ARRAY(SELECT STRUCT(1, 2))');
        $row = $res->rows()->current();
        $this->assertEquals($row[0][0], [1,2]);
    }

    /**
     * covers 23
     */
    public function testBindBoolParameter()
    {
        $db = self::$database;

        $res = $db->execute('SELECT @param as foo', [
            'parameters' => [
                'param' => true
            ]
        ]);

        $row = $res->rows()->current();
        $this->assertTrue($row['foo']);
    }

    /**
     * covers 25
     */
    public function testBindInt64Parameter()
    {
        $db = self::$database;

        $res = $db->execute('SELECT @param as foo', [
            'parameters' => [
                'param' => 1337
            ]
        ]);

        $row = $res->rows()->current();
        $this->assertEquals(1337, $row['foo']);
    }

    /**
     * covers 25
     */
    public function testBindInt64ParameterWithInt64Class()
    {
        $db = self::$database;

        $res = $db->execute('SELECT @param as foo', [
            'parameters' => [
                'param' => new Int64('1337')
            ]
        ]);

        $row = $res->rows()->current();
        $this->assertEquals(1337, $row['foo']);
    }

    /**
     * covers 26
     */
    public function testBindNullIntParameter()
    {
        $db = self::$database;

        $res = $db->execute('SELECT @param as foo', [
            'parameters' => [
                'param' => null
            ],
            'types' => [
                'param' => ValueMapper::TYPE_INT64
            ]
        ]);

        $row = $res->rows()->current();
        $this->assertNull($row['foo']);
    }

    /**
     * covers 27
     */
    public function testBindFloat64Parameter()
    {
        $db = self::$database;

        $pi = 3.1415;
        $res = $db->execute('SELECT @param as foo', [
            'parameters' => [
                'param' => $pi
            ]
        ]);

        $row = $res->rows()->current();
        $this->assertEquals($pi, $row['foo']);
    }

    /**
     * covers 29
     */
    public function testBindStringParameter()
    {
        $db = self::$database;

        $str = 'hello world';
        $res = $db->execute('SELECT @param as foo', [
            'parameters' => [
                'param' => $str
            ]
        ]);

        $row = $res->rows()->current();
        $this->assertEquals($str, $row['foo']);
    }

    /**
     * covers 31
     */
    public function testBindBytesParameter()
    {
        $db = self::$database;

        $str = 'hello world';
        $bytes = new Bytes($str);
        $res = $db->execute('SELECT @param as foo', [
            'parameters' => [
                'param' => $bytes
            ]
        ]);

        $row = $res->rows()->current();
        $this->assertInstanceOf(Bytes::class, $row['foo']);
        $this->assertEquals($str, base64_decode($bytes->formatAsString()));
    }

    /**
     * covers 40
     */
    public function testBindInt64ArrayParameter()
    {
        $db = self::$database;

        $arr = [5,4,3,2,1];
        $res = $db->execute('SELECT @param as foo', [
            'parameters' => [
                'param' => $arr
            ]
        ]);

        $row = $res->rows()->current();
        $this->assertEquals($arr, $row['foo']);
    }
}
