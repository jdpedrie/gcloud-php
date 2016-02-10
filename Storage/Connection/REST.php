<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Storage\Connection;

use Google\Cloud\HttpRequestWrapper;
use Google\Cloud\Storage\Connection\ConnectionInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use Rize\UriTemplate;

class REST implements ConnectionInterface
{
    // @todo more granularity over scopes
    const SCOPE = 'https://www.googleapis.com/auth/devstorage.full_control';
    const BASE_URI = 'https://www.googleapis.com/storage/v1/';
    const UPLOAD_URI = 'https://www.googleapis.com/upload/storage/v1/b/{bucket}/o';
    const DOWNLOAD_URI = 'https://storage.googleapis.com/{bucket}/{object}';

    private $service;
    private $httpWrapper;

    public function __construct(array $config)
    {
        $this->service = $this->loadServiceDefinition();
        $this->httpWrapper = new HttpRequestWrapper($config + ['scope' => self::SCOPE]);
    }

    // @todo use __call to handle the methods below?
    // @todo createBucket should be insertBucket - review names
    public function deleteAcl(array $options = [])
    {
        return $this->sendRequest($options['type'], 'delete', $options);
    }

    public function getAcl(array $options = [])
    {
        return $this->sendRequest($options['type'], 'get', $options);
    }

    public function listAcl(array $options = [])
    {
        return $this->sendRequest($options['type'], 'list', $options);
    }

    public function insertAcl(array $options = [])
    {
        return $this->sendRequest($options['type'], 'insert', $options);
    }

    public function patchAcl(array $options = [])
    {
        return $this->sendRequest($options['type'], 'patch', $options);
    }

    public function deleteBucket(array $options = [])
    {
        return $this->sendRequest('buckets', 'delete', $options);
    }

    public function getBucket(array $options = [])
    {
        return $this->sendRequest('buckets', 'get', $options);
    }

    public function listBuckets(array $options = [])
    {
        return $this->sendRequest('buckets', 'list', $options);
    }

    public function createBucket(array $options = [])
    {
        return $this->sendRequest('buckets', 'insert', $options);
    }

    public function patchBucket(array $options = [])
    {
        return $this->sendRequest('buckets', 'patch', $options);
    }

    public function deleteObject(array $options = [])
    {
        return $this->sendRequest('objects', 'delete', $options);
    }

    public function getObject(array $options = [])
    {
        return $this->sendRequest('objects', 'get', $options);
    }

    public function listObjects(array $options = [])
    {
        return $this->sendRequest('objects', 'list', $options);
    }

    public function patchObject(array $options = [])
    {
        return $this->sendRequest('objects', 'patch', $options);
    }

    public function downloadObject(array $options = [])
    {
        // @todo investigate using mediaLink to download versions
        $template = new UriTemplate();
        $uri = $template->expand(self::DOWNLOAD_URI, [
            'bucket' => $options['bucket'],
            'object' => $options['object']
        ]);

        return $this->httpWrapper->send(
            new Request(
                'GET',
                Psr7\uri_for($uri)
            )
        )->getBody();
    }

    // @todo finish upload
    public function uploadObject(array $options = [])
    {

    }

    private function loadServiceDefinition()
    {
        return json_decode(
            file_get_contents('ServiceDefinition/storage-v1.json', true),
            true
        );
    }

    private function sendRequest($resource, $method, $options)
    {
        // @todo quick POC. need to tighten this up
        $action = $this->service[$resource]['methods'][$method];
        $template = new UriTemplate();
        $path = [];
        $query = [];
        $body = [];

        foreach ($action['parameters'] as $parameter => $parameterOptions) {
            if ($parameterOptions['location'] === 'path' && array_key_exists($parameter, $options)) {
                $path[$parameter] = $options[$parameter];
            }

            if ($parameterOptions['location'] === 'query' && array_key_exists($parameter, $options)) {
                $query[$parameter] = $options[$parameter];
            }

            if ($parameterOptions['location'] === 'body' && array_key_exists($parameter, $options)) {
                $body[$parameter] = $options[$parameter];
            }
        }

        $uri = $this->buildUri(
            $template->expand($action['path'], $path),
            $query
        );

        return json_decode(
            $this->httpWrapper->send(
                new Request(
                    $action['httpMethod'],
                    $uri,
                    ['Content-Type' => 'application/json'],
                    $body ? json_encode($body) : null
                )
            )->getBody(),
            true
        );
    }

    private function buildUri($path, array $query = [])
    {
        $uri = self::BASE_URI . $path;

        // @todo fix this hack. when using build_query booleans are converted to
        // 1 or 0 which the API does not accept. this casts bools to their
        // string representation

        $query = array_filter($query);
        foreach ($query as $k => &$v) {
            if (is_bool($v)) {
                $v = $v ? 'true' : 'false';
            }
        }

        return Psr7\uri_for($uri)->withQuery(Psr7\build_query($query));
    }
}
