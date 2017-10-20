<?php

namespace FMUP\Response\Header;

use FMUP\Response\Header;

class XFrameOptions extends Header
{

    const TYPE = 'X-Frame-Options';
    const OPTIONS_DENY = 'Deny';
    const OPTIONS_SAMEORIGIN = 'Sameorigin';
    const OPTIONS_ALLOW_FROM = 'ALLOW_FROM';
    const OPTIONS_ALLOW_FROM_URI_DEFAULT = '*';

    private $options = self::OPTIONS_DENY;
    private $uri = self::OPTIONS_DENY;


    /**
     * XFrameOptions constructor.
     * @param $options
     * @param array $uri
     */
    public function __construct($options, $uri = array())
    {
        $this->setOptions($options);
        $this->setUri($uri);
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        $return = '';
        $options = $this->getOptions();
        if ($options == self::OPTIONS_ALLOW_FROM) {
            $urls = $this->getUri();
            foreach ($urls as $url) {
                $return .= $options . ' ' . $url . ';';
            }
        } else {
            $return = $options;
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
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getUri()
    {
        if (!isset($this->uri) or empty($this->uri)) {
            $this->uri = array(self::OPTIONS_ALLOW_FROM_URI_DEFAULT);
        }
        return $this->uri;
    }

    /**
     * @param array $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
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
