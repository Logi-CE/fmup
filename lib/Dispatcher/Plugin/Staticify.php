<?php
/**
 * Staticify.php
 * @author: jmoulin@castelis.com
 */

namespace FMUP\Dispatcher\Plugin;

/**
 * Class Staticify
 * this component is a POST dispatcher
 * it will replace all statics url to attack statics ones
 * This will allow cookie free domain + improve parallels asset download
 *
 * @package FMUP\Dispatcher\Plugin
 */
class Staticify extends \FMUP\Dispatcher\Plugin
{
    const PROTOCOL = '://';
    /**
     * Number of static instances
     * @var int
     */
    protected $staticNumber = 3;

    /**
     * Prefix of static instances
     * @var string
     */
    protected $staticPrefix = 'static';
    
    /**
     * Suffix of static instances
     * @var string
     */
    protected $staticSuffix = '';
    /**
     * SubDomain to replace
     * @var string
     */
    protected $subDomain = 'www';
    /**
     * Domain to replace
     * @var string
     */
    protected $domain = null;

    /**
     * Current asset
     * @var int
     */
    private $currentAsset = 1;

    /**
     * Will catch all resources URL
     *
     * @see self::computeAsset
     */
    public function handle()
    {
        $isJson = false;
        $response = $this->getResponse()->getBody();
        $newResponse = $response;
        foreach ($this->getResponse()->getHeaders() as $type => $items) {
            if ($type == \FMUP\Response\Header\ContentType::TYPE) {
                foreach ($items as $item) {
                    /** @var \FMUP\Response\Header $item */
                    if ($item->getValue() == \FMUP\Response\Header\ContentType::MIME_APPLICATION_JSON) {
                        $isJson = true;
                        break;
                    }
                }
            }
        }
        if ($isJson) {
            $response = stripslashes($response);
        }
        $regexps = array(
            '~src="?\'?([^"\']+)"?\'?~',
            '~<link .*href="?\'?([^"\']+)"?\'?~'
        );
        foreach ($regexps as $exp) {
            preg_match_all($exp, $response, $glob);
            $values = array();
            foreach ($glob[1] as $key => $string) {
                $crc = crc32($string);
                if (!isset($values[$crc])) {
                    $newResponse = str_replace($string, $this->computeAsset($string, $isJson), $newResponse);
                    $values[$crc] = 1;
                }
            }
        }
        $this->getResponse()->setBody($newResponse);
    }

    /**
     * Compute wich asset for a path and return the full path
     *
     * @param string $path
     * @param bool $isJson Check if url should be encoded
     *
     * @return string
     */
    protected function computeAsset($path, $isJson = false)
    {
        if (strpos(strtolower($path), self::PROTOCOL) === false) {
            $path = $this->getDomain() . $path;
        } else {
            return $path;
        }
        $path = str_replace(
            self::PROTOCOL . $this->getSubDomain(),
            self::PROTOCOL . $this->getStaticPrefix() . $this->currentAsset++ . $this->getStaticSuffix(),
            $path
        );
        if ($this->currentAsset > $this->getStaticNumber()) {
            $this->currentAsset = 1;
        }
        if ($isJson) {
            $path = str_replace('/', '\/', $path);
        }
        return $path;
    }

    /**
     * @param int $number
     * @return $this
     */
    public function setStaticNumber($number = 3)
    {
        $this->staticNumber = (int) $number;
        return $this;
    }

    /**
     * @return int
     */
    public function getStaticNumber()
    {
        return $this->staticNumber;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setStaticPrefix($prefix = '')
    {
        $this->staticPrefix = (string)$prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getStaticPrefix()
    {
        return $this->staticPrefix;
    }

    /**
     * @param string $suffix
     * @return $this
     */
    public function setStaticSuffix($suffix = '')
    {
        $this->staticSuffix = (string)$suffix;
        return $this;
    }

    /**
     * @return string
     */
    public function getStaticSuffix()
    {
        return $this->staticSuffix;
    }

    /**
     * @param string $subDomain
     * @return $this
     */
    public function setSubDomain($subDomain = 'www')
    {
        $this->subDomain = (string)$subDomain;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubDomain()
    {
        return $this->subDomain;
    }

    /**
     * @return string
     * @throws \FMUP\Exception
     */
    protected function getDomain()
    {
        /** @var \FMUP\Request\Http $request */
        $request = $this->getRequest();
        if ($this->domain === null) {
            $this->domain = $request->getServer(\FMUP\Request\Http::REQUEST_SCHEME)
                . '://' . $request->getServer(\FMUP\Request\Http::HTTP_HOST)
            ;
        }
        return $this->domain;
    }
}
