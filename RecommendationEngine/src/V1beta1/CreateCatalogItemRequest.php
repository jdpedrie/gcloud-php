<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/recommendationengine/v1beta1/catalog_service.proto

namespace Google\Cloud\Recommendationengine\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for CreateCatalogItem method.
 *
 * Generated from protobuf message <code>google.cloud.recommendationengine.v1beta1.CreateCatalogItemRequest</code>
 */
class CreateCatalogItemRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The parent catalog resource name, such as
     * "projects/&#42;&#47;locations/global/catalogs/default_catalog".
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $parent = '';
    /**
     * Required. The catalog item to create.
     *
     * Generated from protobuf field <code>.google.cloud.recommendationengine.v1beta1.CatalogItem catalog_item = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $catalog_item = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The parent catalog resource name, such as
     *           "projects/&#42;&#47;locations/global/catalogs/default_catalog".
     *     @type \Google\Cloud\Recommendationengine\V1beta1\CatalogItem $catalog_item
     *           Required. The catalog item to create.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Recommendationengine\V1Beta1\CatalogService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The parent catalog resource name, such as
     * "projects/&#42;&#47;locations/global/catalogs/default_catalog".
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The parent catalog resource name, such as
     * "projects/&#42;&#47;locations/global/catalogs/default_catalog".
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setParent($var)
    {
        GPBUtil::checkString($var, True);
        $this->parent = $var;

        return $this;
    }

    /**
     * Required. The catalog item to create.
     *
     * Generated from protobuf field <code>.google.cloud.recommendationengine.v1beta1.CatalogItem catalog_item = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\Recommendationengine\V1beta1\CatalogItem
     */
    public function getCatalogItem()
    {
        return $this->catalog_item;
    }

    /**
     * Required. The catalog item to create.
     *
     * Generated from protobuf field <code>.google.cloud.recommendationengine.v1beta1.CatalogItem catalog_item = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\Recommendationengine\V1beta1\CatalogItem $var
     * @return $this
     */
    public function setCatalogItem($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Recommendationengine\V1beta1\CatalogItem::class);
        $this->catalog_item = $var;

        return $this;
    }

}

