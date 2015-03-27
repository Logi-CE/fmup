<?php
namespace FMUP\Response\Header;

/**
 * Class Status
 * @package FMUP\Response\Header
 * @todo must implement http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
 */
abstract class ContentType
{
    const TEXT = 'Content-Type: text/html;';


    const UTF_8 = 'charset=utf-8';
}