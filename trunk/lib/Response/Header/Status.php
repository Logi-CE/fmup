<?php
namespace FMUP\Response\Header;

/**
 * Class Status
 * @package FMUP\Response\Header
 * @todo must implement http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
 */
abstract class Status
{
    const TYPE = 'HTTP/1.1';

    const VALUE_OK = '200 OK';
    const VALUE_FORBIDDEN = '403 Forbidden';
    const VALUE_NOT_FOUND = '404 Not Found';
}