<?php
/*
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * GENERATED CODE WARNING
 * This file was generated from the file
 * https://github.com/google/googleapis/blob/master/google/cloud/recommendationengine/v1beta1/prediction_apikey_registry_service.proto
 * and updates to that file get reflected here through a refresh process.
 *
 * @experimental
 */

namespace Google\Cloud\Recommendationengine\V1beta1\Gapic;

use Google\ApiCore\ApiException;
use Google\ApiCore\CredentialsWrapper;
use Google\ApiCore\GapicClientTrait;
use Google\ApiCore\PathTemplate;
use Google\ApiCore\RequestParamsHeaderDescriptor;
use Google\ApiCore\RetrySettings;
use Google\ApiCore\Transport\TransportInterface;
use Google\ApiCore\ValidationException;
use Google\Auth\FetchAuthTokenInterface;
use Google\Cloud\Recommendationengine\V1beta1\CreatePredictionApiKeyRegistrationRequest;
use Google\Cloud\Recommendationengine\V1beta1\DeletePredictionApiKeyRegistrationRequest;
use Google\Cloud\Recommendationengine\V1beta1\ListPredictionApiKeyRegistrationsRequest;
use Google\Cloud\Recommendationengine\V1beta1\ListPredictionApiKeyRegistrationsResponse;
use Google\Cloud\Recommendationengine\V1beta1\PredictionApiKeyRegistration;
use Google\Protobuf\GPBEmpty;

/**
 * Service Description: Service for registering API keys for use with the `predict` method. If you
 * use an API key to request predictions, you must first register the API key.
 * Otherwise, your prediction request is rejected. If you use OAuth to
 * authenticate your `predict` method call, you do not need to register an API
 * key. You can register up to 20 API keys per project.
 *
 * This class provides the ability to make remote calls to the backing service through method
 * calls that map to API methods. Sample code to get started:
 *
 * ```
 * $predictionApiKeyRegistryClient = new PredictionApiKeyRegistryClient();
 * try {
 *     $formattedParent = $predictionApiKeyRegistryClient->eventStoreName('[PROJECT]', '[LOCATION]', '[CATALOG]', '[EVENT_STORE]');
 *     $predictionApiKeyRegistration = new PredictionApiKeyRegistration();
 *     $response = $predictionApiKeyRegistryClient->createPredictionApiKeyRegistration($formattedParent, $predictionApiKeyRegistration);
 * } finally {
 *     $predictionApiKeyRegistryClient->close();
 * }
 * ```
 *
 * Many parameters require resource names to be formatted in a particular way. To assist
 * with these names, this class includes a format method for each type of name, and additionally
 * a parseName method to extract the individual identifiers contained within formatted names
 * that are returned by the API.
 *
 * @experimental
 */
class PredictionApiKeyRegistryGapicClient
{
    use GapicClientTrait;

    /**
     * The name of the service.
     */
    const SERVICE_NAME = 'google.cloud.recommendationengine.v1beta1.PredictionApiKeyRegistry';

    /**
     * The default address of the service.
     */
    const SERVICE_ADDRESS = 'recommendationengine.googleapis.com';

    /**
     * The default port of the service.
     */
    const DEFAULT_SERVICE_PORT = 443;

    /**
     * The name of the code generator, to be included in the agent header.
     */
    const CODEGEN_NAME = 'gapic';

    /**
     * The default scopes required by the service.
     */
    public static $serviceScopes = [
        'https://www.googleapis.com/auth/cloud-platform',
    ];
    private static $eventStoreNameTemplate;
    private static $predictionApiKeyRegistrationNameTemplate;
    private static $pathTemplateMap;

    private static function getClientDefaults()
    {
        return [
            'serviceName' => self::SERVICE_NAME,
            'apiEndpoint' => self::SERVICE_ADDRESS.':'.self::DEFAULT_SERVICE_PORT,
            'clientConfig' => __DIR__.'/../resources/prediction_api_key_registry_client_config.json',
            'descriptorsConfigPath' => __DIR__.'/../resources/prediction_api_key_registry_descriptor_config.php',
            'gcpApiConfigPath' => __DIR__.'/../resources/prediction_api_key_registry_grpc_config.json',
            'credentialsConfig' => [
                'scopes' => self::$serviceScopes,
            ],
            'transportConfig' => [
                'rest' => [
                    'restClientConfigPath' => __DIR__.'/../resources/prediction_api_key_registry_rest_client_config.php',
                ],
            ],
        ];
    }

    private static function getEventStoreNameTemplate()
    {
        if (null == self::$eventStoreNameTemplate) {
            self::$eventStoreNameTemplate = new PathTemplate('projects/{project}/locations/{location}/catalogs/{catalog}/eventStores/{event_store}');
        }

        return self::$eventStoreNameTemplate;
    }

    private static function getPredictionApiKeyRegistrationNameTemplate()
    {
        if (null == self::$predictionApiKeyRegistrationNameTemplate) {
            self::$predictionApiKeyRegistrationNameTemplate = new PathTemplate('projects/{project}/locations/{location}/catalogs/{catalog}/eventStores/{event_store}/predictionApiKeyRegistrations/{prediction_api_key_registration}');
        }

        return self::$predictionApiKeyRegistrationNameTemplate;
    }

    private static function getPathTemplateMap()
    {
        if (null == self::$pathTemplateMap) {
            self::$pathTemplateMap = [
                'eventStore' => self::getEventStoreNameTemplate(),
                'predictionApiKeyRegistration' => self::getPredictionApiKeyRegistrationNameTemplate(),
            ];
        }

        return self::$pathTemplateMap;
    }

    /**
     * Formats a string containing the fully-qualified path to represent
     * a event_store resource.
     *
     * @param string $project
     * @param string $location
     * @param string $catalog
     * @param string $eventStore
     *
     * @return string The formatted event_store resource.
     * @experimental
     */
    public static function eventStoreName($project, $location, $catalog, $eventStore)
    {
        return self::getEventStoreNameTemplate()->render([
            'project' => $project,
            'location' => $location,
            'catalog' => $catalog,
            'event_store' => $eventStore,
        ]);
    }

    /**
     * Formats a string containing the fully-qualified path to represent
     * a prediction_api_key_registration resource.
     *
     * @param string $project
     * @param string $location
     * @param string $catalog
     * @param string $eventStore
     * @param string $predictionApiKeyRegistration
     *
     * @return string The formatted prediction_api_key_registration resource.
     * @experimental
     */
    public static function predictionApiKeyRegistrationName($project, $location, $catalog, $eventStore, $predictionApiKeyRegistration)
    {
        return self::getPredictionApiKeyRegistrationNameTemplate()->render([
            'project' => $project,
            'location' => $location,
            'catalog' => $catalog,
            'event_store' => $eventStore,
            'prediction_api_key_registration' => $predictionApiKeyRegistration,
        ]);
    }

    /**
     * Parses a formatted name string and returns an associative array of the components in the name.
     * The following name formats are supported:
     * Template: Pattern
     * - eventStore: projects/{project}/locations/{location}/catalogs/{catalog}/eventStores/{event_store}
     * - predictionApiKeyRegistration: projects/{project}/locations/{location}/catalogs/{catalog}/eventStores/{event_store}/predictionApiKeyRegistrations/{prediction_api_key_registration}.
     *
     * The optional $template argument can be supplied to specify a particular pattern, and must
     * match one of the templates listed above. If no $template argument is provided, or if the
     * $template argument does not match one of the templates listed, then parseName will check
     * each of the supported templates, and return the first match.
     *
     * @param string $formattedName The formatted name string
     * @param string $template      Optional name of template to match
     *
     * @return array An associative array from name component IDs to component values.
     *
     * @throws ValidationException If $formattedName could not be matched.
     * @experimental
     */
    public static function parseName($formattedName, $template = null)
    {
        $templateMap = self::getPathTemplateMap();

        if ($template) {
            if (!isset($templateMap[$template])) {
                throw new ValidationException("Template name $template does not exist");
            }

            return $templateMap[$template]->match($formattedName);
        }

        foreach ($templateMap as $templateName => $pathTemplate) {
            try {
                return $pathTemplate->match($formattedName);
            } catch (ValidationException $ex) {
                // Swallow the exception to continue trying other path templates
            }
        }
        throw new ValidationException("Input did not match any known format. Input: $formattedName");
    }

    /**
     * Constructor.
     *
     * @param array $options {
     *                       Optional. Options for configuring the service API wrapper.
     *
     *     @type string $serviceAddress
     *           **Deprecated**. This option will be removed in a future major release. Please
     *           utilize the `$apiEndpoint` option instead.
     *     @type string $apiEndpoint
     *           The address of the API remote host. May optionally include the port, formatted
     *           as "<uri>:<port>". Default 'recommendationengine.googleapis.com:443'.
     *     @type string|array|FetchAuthTokenInterface|CredentialsWrapper $credentials
     *           The credentials to be used by the client to authorize API calls. This option
     *           accepts either a path to a credentials file, or a decoded credentials file as a
     *           PHP array.
     *           *Advanced usage*: In addition, this option can also accept a pre-constructed
     *           {@see \Google\Auth\FetchAuthTokenInterface} object or
     *           {@see \Google\ApiCore\CredentialsWrapper} object. Note that when one of these
     *           objects are provided, any settings in $credentialsConfig will be ignored.
     *     @type array $credentialsConfig
     *           Options used to configure credentials, including auth token caching, for the client.
     *           For a full list of supporting configuration options, see
     *           {@see \Google\ApiCore\CredentialsWrapper::build()}.
     *     @type bool $disableRetries
     *           Determines whether or not retries defined by the client configuration should be
     *           disabled. Defaults to `false`.
     *     @type string|array $clientConfig
     *           Client method configuration, including retry settings. This option can be either a
     *           path to a JSON file, or a PHP array containing the decoded JSON data.
     *           By default this settings points to the default client config file, which is provided
     *           in the resources folder.
     *     @type string|TransportInterface $transport
     *           The transport used for executing network requests. May be either the string `rest`
     *           or `grpc`. Defaults to `grpc` if gRPC support is detected on the system.
     *           *Advanced usage*: Additionally, it is possible to pass in an already instantiated
     *           {@see \Google\ApiCore\Transport\TransportInterface} object. Note that when this
     *           object is provided, any settings in $transportConfig, and any `$apiEndpoint`
     *           setting, will be ignored.
     *     @type array $transportConfig
     *           Configuration options that will be used to construct the transport. Options for
     *           each supported transport type should be passed in a key for that transport. For
     *           example:
     *           $transportConfig = [
     *               'grpc' => [...],
     *               'rest' => [...]
     *           ];
     *           See the {@see \Google\ApiCore\Transport\GrpcTransport::build()} and
     *           {@see \Google\ApiCore\Transport\RestTransport::build()} methods for the
     *           supported options.
     * }
     *
     * @throws ValidationException
     * @experimental
     */
    public function __construct(array $options = [])
    {
        $clientOptions = $this->buildClientOptions($options);
        $this->setClientOptions($clientOptions);
    }

    /**
     * Register an API key for use with predict method.
     *
     * Sample code:
     * ```
     * $predictionApiKeyRegistryClient = new PredictionApiKeyRegistryClient();
     * try {
     *     $formattedParent = $predictionApiKeyRegistryClient->eventStoreName('[PROJECT]', '[LOCATION]', '[CATALOG]', '[EVENT_STORE]');
     *     $predictionApiKeyRegistration = new PredictionApiKeyRegistration();
     *     $response = $predictionApiKeyRegistryClient->createPredictionApiKeyRegistration($formattedParent, $predictionApiKeyRegistration);
     * } finally {
     *     $predictionApiKeyRegistryClient->close();
     * }
     * ```
     *
     * @param string                       $parent                       Required. The parent resource path.
     *                                                                   "projects/&#42;/locations/global/catalogs/default_catalog/eventStores/default_event_store".
     * @param PredictionApiKeyRegistration $predictionApiKeyRegistration Required. The prediction API key registration.
     * @param array                        $optionalArgs                 {
     *                                                                   Optional.
     *
     *     @type RetrySettings|array $retrySettings
     *          Retry settings to use for this call. Can be a
     *          {@see Google\ApiCore\RetrySettings} object, or an associative array
     *          of retry settings parameters. See the documentation on
     *          {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @return \Google\Cloud\Recommendationengine\V1beta1\PredictionApiKeyRegistration
     *
     * @throws ApiException if the remote call fails
     * @experimental
     */
    public function createPredictionApiKeyRegistration($parent, $predictionApiKeyRegistration, array $optionalArgs = [])
    {
        $request = new CreatePredictionApiKeyRegistrationRequest();
        $request->setParent($parent);
        $request->setPredictionApiKeyRegistration($predictionApiKeyRegistration);

        $requestParams = new RequestParamsHeaderDescriptor([
          'parent' => $request->getParent(),
        ]);
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();

        return $this->startCall(
            'CreatePredictionApiKeyRegistration',
            PredictionApiKeyRegistration::class,
            $optionalArgs,
            $request
        )->wait();
    }

    /**
     * List the registered apiKeys for use with predict method.
     *
     * Sample code:
     * ```
     * $predictionApiKeyRegistryClient = new PredictionApiKeyRegistryClient();
     * try {
     *     $formattedParent = $predictionApiKeyRegistryClient->eventStoreName('[PROJECT]', '[LOCATION]', '[CATALOG]', '[EVENT_STORE]');
     *     // Iterate over pages of elements
     *     $pagedResponse = $predictionApiKeyRegistryClient->listPredictionApiKeyRegistrations($formattedParent);
     *     foreach ($pagedResponse->iteratePages() as $page) {
     *         foreach ($page as $element) {
     *             // doSomethingWith($element);
     *         }
     *     }
     *
     *
     *     // Alternatively:
     *
     *     // Iterate through all elements
     *     $pagedResponse = $predictionApiKeyRegistryClient->listPredictionApiKeyRegistrations($formattedParent);
     *     foreach ($pagedResponse->iterateAllElements() as $element) {
     *         // doSomethingWith($element);
     *     }
     * } finally {
     *     $predictionApiKeyRegistryClient->close();
     * }
     * ```
     *
     * @param string $parent       Required. The parent placement resource name such as
     *                             "projects/1234/locations/global/catalogs/default_catalog/eventStores/default_event_store"
     * @param array  $optionalArgs {
     *                             Optional.
     *
     *     @type int $pageSize
     *          The maximum number of resources contained in the underlying API
     *          response. The API may return fewer values in a page, even if
     *          there are additional values to be retrieved.
     *     @type string $pageToken
     *          A page token is used to specify a page of values to be returned.
     *          If no page token is specified (the default), the first page
     *          of values will be returned. Any page token used here must have
     *          been generated by a previous call to the API.
     *     @type RetrySettings|array $retrySettings
     *          Retry settings to use for this call. Can be a
     *          {@see Google\ApiCore\RetrySettings} object, or an associative array
     *          of retry settings parameters. See the documentation on
     *          {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @return \Google\ApiCore\PagedListResponse
     *
     * @throws ApiException if the remote call fails
     * @experimental
     */
    public function listPredictionApiKeyRegistrations($parent, array $optionalArgs = [])
    {
        $request = new ListPredictionApiKeyRegistrationsRequest();
        $request->setParent($parent);
        if (isset($optionalArgs['pageSize'])) {
            $request->setPageSize($optionalArgs['pageSize']);
        }
        if (isset($optionalArgs['pageToken'])) {
            $request->setPageToken($optionalArgs['pageToken']);
        }

        $requestParams = new RequestParamsHeaderDescriptor([
          'parent' => $request->getParent(),
        ]);
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();

        return $this->getPagedListResponse(
            'ListPredictionApiKeyRegistrations',
            $optionalArgs,
            ListPredictionApiKeyRegistrationsResponse::class,
            $request
        );
    }

    /**
     * Unregister an apiKey from using for predict method.
     *
     * Sample code:
     * ```
     * $predictionApiKeyRegistryClient = new PredictionApiKeyRegistryClient();
     * try {
     *     $formattedName = $predictionApiKeyRegistryClient->predictionApiKeyRegistrationName('[PROJECT]', '[LOCATION]', '[CATALOG]', '[EVENT_STORE]', '[PREDICTION_API_KEY_REGISTRATION]');
     *     $predictionApiKeyRegistryClient->deletePredictionApiKeyRegistration($formattedName);
     * } finally {
     *     $predictionApiKeyRegistryClient->close();
     * }
     * ```
     *
     * @param string $name         Required. The API key to unregister including full resource path.
     *                             "projects/&#42;/locations/global/catalogs/default_catalog/eventStores/default_event_store/predictionApiKeyRegistrations/<YOUR_API_KEY>"
     * @param array  $optionalArgs {
     *                             Optional.
     *
     *     @type RetrySettings|array $retrySettings
     *          Retry settings to use for this call. Can be a
     *          {@see Google\ApiCore\RetrySettings} object, or an associative array
     *          of retry settings parameters. See the documentation on
     *          {@see Google\ApiCore\RetrySettings} for example usage.
     * }
     *
     * @throws ApiException if the remote call fails
     * @experimental
     */
    public function deletePredictionApiKeyRegistration($name, array $optionalArgs = [])
    {
        $request = new DeletePredictionApiKeyRegistrationRequest();
        $request->setName($name);

        $requestParams = new RequestParamsHeaderDescriptor([
          'name' => $request->getName(),
        ]);
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();

        return $this->startCall(
            'DeletePredictionApiKeyRegistration',
            GPBEmpty::class,
            $optionalArgs,
            $request
        )->wait();
    }
}
