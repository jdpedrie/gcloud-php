<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/recommendationengine/v1beta1/user_event.proto

namespace Google\Cloud\Recommendationengine\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Detailed product information associated with a user event.
 *
 * Generated from protobuf message <code>google.cloud.recommendationengine.v1beta1.ProductDetail</code>
 */
class ProductDetail extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Catalog item ID. UTF-8 encoded string with a length limit of 128
     * characters.
     *
     * Generated from protobuf field <code>string id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $id = '';
    /**
     * Optional. Currency code for price/costs. Use three-character ISO-4217
     * code. Required only if originalPrice or displayPrice is set.
     *
     * Generated from protobuf field <code>string currency_code = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $currency_code = '';
    /**
     * Optional. Original price of the product. If provided, this will override
     * the original price in Catalog for this product.
     *
     * Generated from protobuf field <code>float original_price = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $original_price = 0.0;
    /**
     * Optional. Display price of the product (e.g. discounted price). If
     * provided, this will override the display price in Catalog for this product.
     *
     * Generated from protobuf field <code>float display_price = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $display_price = 0.0;
    /**
     * Optional. Item stock state. If provided, this overrides the stock state
     * in Catalog for items in this event.
     *
     * Generated from protobuf field <code>.google.cloud.recommendationengine.v1beta1.ProductCatalogItem.StockState stock_state = 5 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $stock_state = 0;
    /**
     * Optional. Quantity of the product associated with the user event. For
     * example, this field will be 2 if two products are added to the shopping
     * cart for `add-to-cart` event. Required for `add-to-cart`, `add-to-list`,
     * `remove-from-cart`, `checkout-start`, `purchase-complete`, `refund` event
     * types.
     *
     * Generated from protobuf field <code>int32 quantity = 6 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $quantity = 0;
    /**
     * Optional. Quantity of the products in stock when a user event happens.
     * Optional. If provided, this overrides the available quantity in Catalog for
     * this event. and can only be set if `stock_status` is set to `IN_STOCK`.
     * Note that if an item is out of stock, you must set the `stock_state` field
     * to be `OUT_OF_STOCK`. Leaving this field unspecified / as zero is not
     * sufficient to mark the item out of stock.
     *
     * Generated from protobuf field <code>int32 available_quantity = 7 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $available_quantity = 0;
    /**
     * Optional. Extra features associated with a product in the user event.
     *
     * Generated from protobuf field <code>.google.cloud.recommendationengine.v1beta1.FeatureMap item_attributes = 8 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $item_attributes = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $id
     *           Required. Catalog item ID. UTF-8 encoded string with a length limit of 128
     *           characters.
     *     @type string $currency_code
     *           Optional. Currency code for price/costs. Use three-character ISO-4217
     *           code. Required only if originalPrice or displayPrice is set.
     *     @type float $original_price
     *           Optional. Original price of the product. If provided, this will override
     *           the original price in Catalog for this product.
     *     @type float $display_price
     *           Optional. Display price of the product (e.g. discounted price). If
     *           provided, this will override the display price in Catalog for this product.
     *     @type int $stock_state
     *           Optional. Item stock state. If provided, this overrides the stock state
     *           in Catalog for items in this event.
     *     @type int $quantity
     *           Optional. Quantity of the product associated with the user event. For
     *           example, this field will be 2 if two products are added to the shopping
     *           cart for `add-to-cart` event. Required for `add-to-cart`, `add-to-list`,
     *           `remove-from-cart`, `checkout-start`, `purchase-complete`, `refund` event
     *           types.
     *     @type int $available_quantity
     *           Optional. Quantity of the products in stock when a user event happens.
     *           Optional. If provided, this overrides the available quantity in Catalog for
     *           this event. and can only be set if `stock_status` is set to `IN_STOCK`.
     *           Note that if an item is out of stock, you must set the `stock_state` field
     *           to be `OUT_OF_STOCK`. Leaving this field unspecified / as zero is not
     *           sufficient to mark the item out of stock.
     *     @type \Google\Cloud\Recommendationengine\V1beta1\FeatureMap $item_attributes
     *           Optional. Extra features associated with a product in the user event.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Recommendationengine\V1Beta1\UserEvent::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Catalog item ID. UTF-8 encoded string with a length limit of 128
     * characters.
     *
     * Generated from protobuf field <code>string id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Required. Catalog item ID. UTF-8 encoded string with a length limit of 128
     * characters.
     *
     * Generated from protobuf field <code>string id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setId($var)
    {
        GPBUtil::checkString($var, True);
        $this->id = $var;

        return $this;
    }

    /**
     * Optional. Currency code for price/costs. Use three-character ISO-4217
     * code. Required only if originalPrice or displayPrice is set.
     *
     * Generated from protobuf field <code>string currency_code = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currency_code;
    }

    /**
     * Optional. Currency code for price/costs. Use three-character ISO-4217
     * code. Required only if originalPrice or displayPrice is set.
     *
     * Generated from protobuf field <code>string currency_code = 2 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param string $var
     * @return $this
     */
    public function setCurrencyCode($var)
    {
        GPBUtil::checkString($var, True);
        $this->currency_code = $var;

        return $this;
    }

    /**
     * Optional. Original price of the product. If provided, this will override
     * the original price in Catalog for this product.
     *
     * Generated from protobuf field <code>float original_price = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->original_price;
    }

    /**
     * Optional. Original price of the product. If provided, this will override
     * the original price in Catalog for this product.
     *
     * Generated from protobuf field <code>float original_price = 3 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param float $var
     * @return $this
     */
    public function setOriginalPrice($var)
    {
        GPBUtil::checkFloat($var);
        $this->original_price = $var;

        return $this;
    }

    /**
     * Optional. Display price of the product (e.g. discounted price). If
     * provided, this will override the display price in Catalog for this product.
     *
     * Generated from protobuf field <code>float display_price = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return float
     */
    public function getDisplayPrice()
    {
        return $this->display_price;
    }

    /**
     * Optional. Display price of the product (e.g. discounted price). If
     * provided, this will override the display price in Catalog for this product.
     *
     * Generated from protobuf field <code>float display_price = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param float $var
     * @return $this
     */
    public function setDisplayPrice($var)
    {
        GPBUtil::checkFloat($var);
        $this->display_price = $var;

        return $this;
    }

    /**
     * Optional. Item stock state. If provided, this overrides the stock state
     * in Catalog for items in this event.
     *
     * Generated from protobuf field <code>.google.cloud.recommendationengine.v1beta1.ProductCatalogItem.StockState stock_state = 5 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return int
     */
    public function getStockState()
    {
        return $this->stock_state;
    }

    /**
     * Optional. Item stock state. If provided, this overrides the stock state
     * in Catalog for items in this event.
     *
     * Generated from protobuf field <code>.google.cloud.recommendationengine.v1beta1.ProductCatalogItem.StockState stock_state = 5 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param int $var
     * @return $this
     */
    public function setStockState($var)
    {
        GPBUtil::checkEnum($var, \Google\Cloud\Recommendationengine\V1beta1\ProductCatalogItem_StockState::class);
        $this->stock_state = $var;

        return $this;
    }

    /**
     * Optional. Quantity of the product associated with the user event. For
     * example, this field will be 2 if two products are added to the shopping
     * cart for `add-to-cart` event. Required for `add-to-cart`, `add-to-list`,
     * `remove-from-cart`, `checkout-start`, `purchase-complete`, `refund` event
     * types.
     *
     * Generated from protobuf field <code>int32 quantity = 6 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Optional. Quantity of the product associated with the user event. For
     * example, this field will be 2 if two products are added to the shopping
     * cart for `add-to-cart` event. Required for `add-to-cart`, `add-to-list`,
     * `remove-from-cart`, `checkout-start`, `purchase-complete`, `refund` event
     * types.
     *
     * Generated from protobuf field <code>int32 quantity = 6 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param int $var
     * @return $this
     */
    public function setQuantity($var)
    {
        GPBUtil::checkInt32($var);
        $this->quantity = $var;

        return $this;
    }

    /**
     * Optional. Quantity of the products in stock when a user event happens.
     * Optional. If provided, this overrides the available quantity in Catalog for
     * this event. and can only be set if `stock_status` is set to `IN_STOCK`.
     * Note that if an item is out of stock, you must set the `stock_state` field
     * to be `OUT_OF_STOCK`. Leaving this field unspecified / as zero is not
     * sufficient to mark the item out of stock.
     *
     * Generated from protobuf field <code>int32 available_quantity = 7 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return int
     */
    public function getAvailableQuantity()
    {
        return $this->available_quantity;
    }

    /**
     * Optional. Quantity of the products in stock when a user event happens.
     * Optional. If provided, this overrides the available quantity in Catalog for
     * this event. and can only be set if `stock_status` is set to `IN_STOCK`.
     * Note that if an item is out of stock, you must set the `stock_state` field
     * to be `OUT_OF_STOCK`. Leaving this field unspecified / as zero is not
     * sufficient to mark the item out of stock.
     *
     * Generated from protobuf field <code>int32 available_quantity = 7 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param int $var
     * @return $this
     */
    public function setAvailableQuantity($var)
    {
        GPBUtil::checkInt32($var);
        $this->available_quantity = $var;

        return $this;
    }

    /**
     * Optional. Extra features associated with a product in the user event.
     *
     * Generated from protobuf field <code>.google.cloud.recommendationengine.v1beta1.FeatureMap item_attributes = 8 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return \Google\Cloud\Recommendationengine\V1beta1\FeatureMap
     */
    public function getItemAttributes()
    {
        return $this->item_attributes;
    }

    /**
     * Optional. Extra features associated with a product in the user event.
     *
     * Generated from protobuf field <code>.google.cloud.recommendationengine.v1beta1.FeatureMap item_attributes = 8 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param \Google\Cloud\Recommendationengine\V1beta1\FeatureMap $var
     * @return $this
     */
    public function setItemAttributes($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\Recommendationengine\V1beta1\FeatureMap::class);
        $this->item_attributes = $var;

        return $this;
    }

}

