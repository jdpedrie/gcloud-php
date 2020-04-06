<?php

return [
    'interfaces' => [
        'google.cloud.recommendationengine.v1beta1.UserEventService' => [
            'PurgeUserEvents' => [
                'longRunning' => [
                    'operationReturnType' => '\Google\Cloud\Recommendationengine\V1beta1\PurgeUserEventsResponse',
                    'metadataReturnType' => '\Google\Cloud\Recommendationengine\V1beta1\PurgeUserEventsMetadata',
                    'initialPollDelayMillis' => '500',
                    'pollDelayMultiplier' => '1.5',
                    'maxPollDelayMillis' => '5000',
                    'totalPollTimeoutMillis' => '300000',
                ],
            ],
            'ImportUserEvents' => [
                'longRunning' => [
                    'operationReturnType' => '\Google\Cloud\Recommendationengine\V1beta1\ImportUserEventsResponse',
                    'metadataReturnType' => '\Google\Cloud\Recommendationengine\V1beta1\ImportMetadata',
                    'initialPollDelayMillis' => '500',
                    'pollDelayMultiplier' => '1.5',
                    'maxPollDelayMillis' => '5000',
                    'totalPollTimeoutMillis' => '300000',
                ],
            ],
            'ListUserEvents' => [
                'pageStreaming' => [
                    'requestPageTokenGetMethod' => 'getPageToken',
                    'requestPageTokenSetMethod' => 'setPageToken',
                    'requestPageSizeGetMethod' => 'getPageSize',
                    'requestPageSizeSetMethod' => 'setPageSize',
                    'responsePageTokenGetMethod' => 'getNextPageToken',
                    'resourcesGetMethod' => 'getUserEvents',
                ],
            ],
        ],
    ],
];
