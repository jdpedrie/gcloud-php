<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/recommendationengine/v1beta1/import.proto

namespace Google\Cloud\Recommendationengine\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Google Cloud Storage location for input content.
 * format.
 *
 * Generated from protobuf message <code>google.cloud.recommendationengine.v1beta1.GcsSource</code>
 */
class GcsSource extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. Google Cloud Storage URIs to input files. URI can be up to
     * 2000 characters long. URIs can match the full object path (for example,
     * gs://bucket/directory/object.json) or a pattern matching one or more
     * files, such as gs://bucket/directory/&#42;.json. A request can
     * contain at most 100 files, and each file can be up to 2 GB. See
     * [Importing catalog information](https://cloud.google.com/recommendations-ai/docs/upload-catalog)
     * for the expected file format and setup instructions.
     *
     * Generated from protobuf field <code>repeated string input_uris = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $input_uris;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $input_uris
     *           Required. Google Cloud Storage URIs to input files. URI can be up to
     *           2000 characters long. URIs can match the full object path (for example,
     *           gs://bucket/directory/object.json) or a pattern matching one or more
     *           files, such as gs://bucket/directory/&#42;.json. A request can
     *           contain at most 100 files, and each file can be up to 2 GB. See
     *           [Importing catalog information](https://cloud.google.com/recommendations-ai/docs/upload-catalog)
     *           for the expected file format and setup instructions.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Recommendationengine\V1Beta1\Import::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. Google Cloud Storage URIs to input files. URI can be up to
     * 2000 characters long. URIs can match the full object path (for example,
     * gs://bucket/directory/object.json) or a pattern matching one or more
     * files, such as gs://bucket/directory/&#42;.json. A request can
     * contain at most 100 files, and each file can be up to 2 GB. See
     * [Importing catalog information](https://cloud.google.com/recommendations-ai/docs/upload-catalog)
     * for the expected file format and setup instructions.
     *
     * Generated from protobuf field <code>repeated string input_uris = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getInputUris()
    {
        return $this->input_uris;
    }

    /**
     * Required. Google Cloud Storage URIs to input files. URI can be up to
     * 2000 characters long. URIs can match the full object path (for example,
     * gs://bucket/directory/object.json) or a pattern matching one or more
     * files, such as gs://bucket/directory/&#42;.json. A request can
     * contain at most 100 files, and each file can be up to 2 GB. See
     * [Importing catalog information](https://cloud.google.com/recommendations-ai/docs/upload-catalog)
     * for the expected file format and setup instructions.
     *
     * Generated from protobuf field <code>repeated string input_uris = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setInputUris($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->input_uris = $arr;

        return $this;
    }

}

