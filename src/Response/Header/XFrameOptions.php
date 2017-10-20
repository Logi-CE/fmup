<?php

namespace FMUP\Response\Header;

use FMUP\Response\Header;

/**
 * Class XFrameOptions
 *
 * @package FMUP\Response\Header
 */
class XFrameOptions extends Header
{
    const TYPE = 'X-Frame-Options';
    const OPTIONS_DENY = 'Deny';
    const OPTIONS_SAME_ORIGIN = 'Sameorigin';
    const OPTIONS_ALLOW_FROM = 'ALLOW_FROM';
    const OPTIONS_ALLOW_FROM_URI_DEFAULT = '*';

    private $options = self::OPTIONS_DENY;
    private $uri = [self::OPTIONS_ALLOW_FROM_URI_DEFAULT];


    /**
     * XFrameOptions constructor.
     * @param $options
     * @param array $uri
     */
    public function __construct($options = self::OPTIONS_DENY, array $uri = [self::OPTIONS_ALLOW_FROM_URI_DEFAULT])
    {
        $this->setOptions($options)->setUri($uri);
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        $options = $this->getOptions();
        $return = $options;
        if ($options == self::OPTIONS_ALLOW_FROM) {
            $return = '';
            foreach ($this->getUri() as $url) {
                $return .= $options . ' ' . $url . ';';
            }
        }
        return $return;
    }

    /**
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $options
     * @return $this
     */
    public function setOptions($options = self::OPTIONS_DENY)
    {
        $this->options = is_string($options) ? $options : self::OPTIONS_DENY;
        return $this;
    }

    /**
     * Returns allowed Uri
     * @return array
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Define list of url to be allowed
     * @param string[] $uri
     * @return $this
     */
    public function setUri(array $uri = [])
    {
        $this->uri = $uri ?: [self::OPTIONS_ALLOW_FROM_URI_DEFAULT];
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
