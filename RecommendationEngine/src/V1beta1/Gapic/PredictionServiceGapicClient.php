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
 * https://github.com/google/googleapis/blob/master/google/cloud/recommendationengine/v1beta1/prediction_service.proto
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
use Google\Cloud\Recommendationengine\V1beta1\PredictRequest;
use Google\Cloud\Recommendationengine\V1beta1\PredictResponse;
use Google\Cloud\Recommendationengine\V1beta1\UserEvent;

/**
 * Service Description: Service for making recommendation prediction.
 *
 * This class provides the ability to make remote calls to the backing service through method
 * calls that map to API methods. Sample code to get started:
 *
 * ```
 * $predictionServiceClient = new PredictionServiceClient();
 * try {
 *     $formattedName = $predictionServiceClient->placementName('[PROJECT]', '[LOCATION]', '[CATALOG]', '[EVENT_STORE]', '[PLACEMENT]');
 *     $userEvent = new UserEvent();
 *     // Iterate over pages of elements
 *     $pagedResponse = $predictionServiceClient->predict($formattedName, $userEvent);
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
 *     $pagedResponse = $predictionServiceClient->predict($formattedName, $userEvent);
 *     foreach ($pagedResponse->iterateAllElements() as $element) {
 *         // doSomethingWith($element);
 *     }
 * } finally {
 *     $predictionServiceClient->close();
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
class PredictionServiceGapicClient
{
    use GapicClientTrait;

    /**
     * The name of the service.
     */
    const SERVICE_NAME = 'google.cloud.recommendationengine.v1beta1.PredictionService';

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
    private static $placementNameTemplate;
    private static $pathTemplateMap;

    private static function getClientDefaults()
    {
        return [
            'serviceName' => self::SERVICE_NAME,
            'apiEndpoint' => self::SERVICE_ADDRESS.':'.self::DEFAULT_SERVICE_PORT,
            'clientConfig' => __DIR__.'/../resources/prediction_service_client_config.json',
            'descriptorsConfigPath' => __DIR__.'/../resources/prediction_service_descriptor_config.php',
            'gcpApiConfigPath' => __DIR__.'/../resources/prediction_service_grpc_config.json',
            'credentialsConfig' => [
                'scopes' => self::$serviceScopes,
            ],
            'transportConfig' => [
                'rest' => [
                    'restClientConfigPath' => __DIR__.'/../resources/prediction_service_rest_client_config.php',
                ],
            ],
        ];
    }

    private static function getPlacementNameTemplate()
    {
        if (null == self::$placementNameTemplate) {
            self::$placementNameTemplate = new PathTemplate('projects/{project}/locations/{location}/catalogs/{catalog}/eventStores/{event_store}/placements/{placement}');
        }

        return self::$placementNameTemplate;
    }

    private static function getPathTemplateMap()
    {
        if (null == self::$pathTemplateMap) {
            self::$pathTemplateMap = [
                'placement' => self::getPlacementNameTemplate(),
            ];
        }

        return self::$pathTemplateMap;
    }

    /**
     * Formats a string containing the fully-qualified path to represent
     * a placement resource.
     *
     * @param string $project
     * @param string $location
     * @param string $catalog
     * @param string $eventStore
     * @param string $placement
     *
     * @return string The formatted placement resource.
     * @experimental
     */
    public static function placementName($project, $location, $catalog, $eventStore, $placement)
    {
        return self::getPlacementNameTemplate()->render([
            'project' => $project,
            'location' => $location,
            'catalog' => $catalog,
            'event_store' => $eventStore,
            'placement' => $placement,
        ]);
    }

    /**
     * Parses a formatted name string and returns an associative array of the components in the name.
     * The following name formats are supported:
     * Template: Pattern
     * - placement: projects/{project}/locations/{location}/catalogs/{catalog}/eventStores/{event_store}/placements/{placement}.
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
     * Makes a recommendation prediction. If using API Key based authentication,
     * the API Key must be registered using the
     * [PredictionApiKeyRegistry][google.cloud.recommendationengine.v1beta1.PredictionApiKeyRegistry]
     * service. [Learn more](https://cloud.google.com/recommendations-ai/docs/setting-up#register-key).
     *
     * Sample code:
     * ```
     * $predictionServiceClient = new PredictionServiceClient();
     * try {
     *     $formattedName = $predictionServiceClient->placementName('[PROJECT]', '[LOCATION]', '[CATALOG]', '[EVENT_STORE]', '[PLACEMENT]');
     *     $userEvent = new UserEvent();
     *     // Iterate over pages of elements
     *     $pagedResponse = $predictionServiceClient->predict($formattedName, $userEvent);
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
     *     $pagedResponse = $predictionServiceClient->predict($formattedName, $userEvent);
     *     foreach ($pagedResponse->iterateAllElements() as $element) {
     *         // doSomethingWith($element);
     *     }
     * } finally {
     *     $predictionServiceClient->close();
     * }
     * ```
     *
     * @param string $name Required. Full resource name of the format:
     *                     {name=projects/&#42;/locations/global/catalogs/default_catalog/eventStores/default_event_store/placements/*}
     *                     The id of the recommendation engine placement. This id is used to identify
     *                     the set of models that will be used to make the prediction.
     *
     * We currently support three placements with the following IDs by default:
     *
     * * `shopping_cart`: Predicts items frequently bought together with one or
     *   more catalog items in the same shopping session. Commonly displayed after
     *   `add-to-cart` events, on product detail pages, or on the shopping cart
     *   page.
     *
     * * `home_page`: Predicts the next product that a user will most likely
     *   engage with or purchase based on the shopping or viewing history of the
     *   specified `userId` or `visitorId`. For example - Recommendations for you.
     *
     * * `product_detail`: Predicts the next product that a user will most likely
     *   engage with or purchase. The prediction is based on the shopping or
     *   viewing history of the specified `userId` or `visitorId` and its
     *   relevance to a specified `CatalogItem`. Typically used on product detail
     *   pages. For example - More items like this.
     *
     * * `recently_viewed_default`: Returns up to 75 items recently viewed by the
     *   specified `userId` or `visitorId`, most recent ones first. Returns
     *   nothing if neither of them has viewed any items yet. For example -
     *   Recently viewed.
     *
     * The full list of available placements can be seen at
     * https://console.cloud.google.com/recommendation/datafeeds/default_catalog/dashboard
     * @param UserEvent $userEvent    Required. Context about the user, what they are looking at and what action
     *                                they took to trigger the predict request. Note that this user event detail
     *                                won't be ingested to userEvent logs. Thus, a separate userEvent write
     *                                request is required for event logging.
     * @param array     $optionalArgs {
     *                                Optional.
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
     *     @type string $filter
     *          Optional. Filter for restricting prediction results. Accepts values for
     *          tags and the `filterOutOfStockItems` flag.
     *
     *           * Tag expressions. Restricts predictions to items that match all of the
     *             specified tags. Boolean operators `OR` and `NOT` are supported if the
     *             expression is enclosed in parentheses, and must be separated from the
     *             tag values by a space. `-"tagA"` is also supported and is equivalent to
     *             `NOT "tagA"`. Tag values must be double quoted UTF-8 encoded strings
     *             with a size limit of 1 KiB.
     *
     *           * filterOutOfStockItems. Restricts predictions to items that do not have a
     *             stockState value of OUT_OF_STOCK.
     *
     *          Examples:
     *
     *           * tag=("Red" OR "Blue") tag="New-Arrival" tag=(NOT "promotional")
     *           * filterOutOfStockItems  tag=(-"promotional")
     *           * filterOutOfStockItems
     *     @type bool $dryRun
     *          Optional. Use dryRun mode for this prediction query. If set to true, a
     *          dummy model will be used that returns arbitrary catalog items.
     *          Note that the dryRun mode should only be used for testing the API, or if
     *          the model is not ready.
     *     @type array $params
     *          Optional. Additional domain specific parameters for the predictions.
     *
     *          Allowed values:
     *
     *          * `returnCatalogItem`: Boolean. If set to true, the associated catalogItem
     *             object will be returned in the
     *            `PredictResponse.PredictionResult.itemMetadata` object in the method
     *             response.
     *          * `returnItemScore`: Boolean. If set to true, the prediction 'score'
     *             corresponding to each returned item will be set in the `metadata`
     *             field in the prediction response. The given 'score' indicates the
     *             probability of an item being clicked/purchased given the user's context
     *             and history.
     *     @type array $labels
     *          Optional. The labels for the predict request.
     *
     *           * Label keys can contain lowercase letters, digits and hyphens, must start
     *             with a letter, and must end with a letter or digit.
     *           * Non-zero label values can contain lowercase letters, digits and hyphens,
     *             must start with a letter, and must end with a letter or digit.
     *           * No more than 64 labels can be associated with a given request.
     *
     *          See https://goo.gl/xmQnxf for more information on and examples of labels.
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
    public function predict($name, $userEvent, array $optionalArgs = [])
    {
        $request = new PredictRequest();
        $request->setName($name);
        $request->setUserEvent($userEvent);
        if (isset($optionalArgs['pageSize'])) {
            $request->setPageSize($optionalArgs['pageSize']);
        }
        if (isset($optionalArgs['pageToken'])) {
            $request->setPageToken($optionalArgs['pageToken']);
        }
        if (isset($optionalArgs['filter'])) {
            $request->setFilter($optionalArgs['filter']);
        }
        if (isset($optionalArgs['dryRun'])) {
            $request->setDryRun($optionalArgs['dryRun']);
        }
        if (isset($optionalArgs['params'])) {
            $request->setParams($optionalArgs['params']);
        }
        if (isset($optionalArgs['labels'])) {
            $request->setLabels($optionalArgs['labels']);
        }

        $requestParams = new RequestParamsHeaderDescriptor([
          'name' => $request->getName(),
        ]);
        $optionalArgs['headers'] = isset($optionalArgs['headers'])
            ? array_merge($requestParams->getHeader(), $optionalArgs['headers'])
            : $requestParams->getHeader();

        return $this->getPagedListResponse(
            'Predict',
            $optionalArgs,
            PredictResponse::class,
            $request
        );
    }
}
