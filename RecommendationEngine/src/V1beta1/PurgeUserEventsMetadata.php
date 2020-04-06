<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/recommendationengine/v1beta1/user_event_service.proto

namespace Google\Cloud\Recommendationengine\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Metadata related to the progress of the PurgeUserEvents operation.
 * This will be returned by the google.longrunning.Operation.metadata field.
 *
 * Generated from protobuf message <code>google.cloud.recommendationengine.v1beta1.PurgeUserEventsMetadata</code>
 */
class PurgeUserEventsMetadata extends \Google\Protobuf\Internal\Message
{
    /**
     * The ID of the request / operation.
     *
     * Generated from protobuf field <code>string operation_name = 1;</code>
     */
    private $operation_name = '';
    /**
     * Operation create time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 2;</code>
     */
    private $create_time = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $operation_name
     *           The ID of the request / operation.
     *     @type \Google\Protobuf\Timestamp $create_time
     *           Operation create time.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Recommendationengine\V1Beta1\UserEventService::initOnce();
        parent::__construct($data);
    }

    /**
     * The ID of the request / operation.
     *
     * Generated from protobuf field <code>string operation_name = 1;</code>
     * @return string
     */
    public function getOperationName()
    {
        return $this->operation_name;
    }

    /**
     * The ID of the request / operation.
     *
     * Generated from protobuf field <code>string operation_name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setOperationName($var)
    {
        GPBUtil::checkString($var, True);
        $this->operation_name = $var;

        return $this;
    }

    /**
     * Operation create time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 2;</code>
     * @return \Google\Protobuf\Timestamp
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * Operation create time.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp create_time = 2;</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setCreateTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->create_time = $var;

        return $this;
    }

}

