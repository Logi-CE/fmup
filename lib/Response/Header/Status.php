<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

/**
 * Class Status
 * @package FMUP\Response\Header
 * @todo must implement http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
 */
class Status extends Header
{
    const TYPE = 'Status';

    const VALUE_OK = '200 OK';
    const VALUE_BAD_REQUEST= '400 Bad Request';
    const VALUE_UNAUTHORIZED = '401 Unauthorized';
    const VALUE_PAYMENT_REQUIRED = '402 Payment Required';
    const VALUE_FORBIDDEN = '403 Forbidden';
    const VALUE_NOT_FOUND = '404 Not Found';
    const VALUE_METHOD_NOT_ALLOWED = '405 Method Not Allowed';
    const VALUE_NOT_ACCEPTABLE = '406 Not Acceptable';
    const VALUE_PROXY_AUTH_REQUIRED = '407 Proxy Authentication Required';
    const VALUE_REQUEST_TIMEOUT = '408 Request Timeout';
    const VALUE_CONFLICT = '409 Conflict';
    const VALUE_GONE = '410 Gone';
    const VALUE_LENGTH_REQUIRED = '411 Length Required';
    const VALUE_PRECONDITION_FAILED = '412 Precondition Failed';
    const VALUE_PAYLOAD_TOO_LARGE = '413 Payload Too Large';
    const VALUE_REQUEST_URI_TOO_LONG = '414 Request-URI Too Long';
    const VALUE_UNSUPPORTED_MEDIA_TYPE = '415 Unsupported Media Type';
    const VALUE_REQUESTED_RANGE_NOT_SATISFIABLE = '416 Requested Range Not Satisfiable';
    const VALUE_EXPECTATION_FAILED = '417 Expectation Failed';
    const VALUE_I_AM_TEAPOT = '418 I\'m a teapot';

    const VALUE_INTERNAL_SERVER_ERROR = '500 Internal Server Error';

    /**
     * @param string $value
     */
    public function __construct($value = self::VALUE_OK)
    {
        $this->setValue($value);
    }

    /**
     * @return $this
     */
    public function render()
    {
        header('HTTP/1.1 ' . $this->getValue());
        return $this;
    }

    /**
     * Type for the header. Can be used to determine header to send
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }
}
