<?php
namespace FMUP;

/**
 * Class Response
 * @package FMUP
 */
class Response
{
    use Sapi\OptionalTrait;
    /**
     * @var array
     */
    private $headers = array();
    /**
     * @var string
     */
    private $body;
    /**
     * @var int
     */
    private $returnCode = 0;

    /**
     * Add a header to send in response
     *
     * @param Response\Header $header
     * @return $this
     */
    public function addHeader(Response\Header $header)
    {
        if (!array_key_exists($header->getType(), $this->headers)) {
            $this->setHeader($header);
        } else {
            array_push($this->headers[$header->getType()], $header);
        }
        return $this;
    }

    /**
     * Get all headers defined
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Define a specific header
     * @param Response\Header $header
     * @return $this
     */
    public function setHeader(Response\Header $header)
    {
        $this->headers[$header->getType()] = array($header);
        return $this;
    }

    /**
     * Clear headers or a specific one
     * @param string|null $name
     * @return $this
     */
    public function clearHeader($name = null)
    {
        if (!is_null($name)) {
            unset($this->headers[$name]);
        } else {
            $this->headers = array();
        }
        return $this;
    }

    /**
     * Define the body of the Response
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = (string)$body;
        return $this;
    }

    /**
     * Retrieve defined body
     * @return string
     */
    public function getBody()
    {
        return (string)$this->body;
    }

    /**
     * Sends header and response
     */
    public function send()
    {
        if ($this->getSapi()->get() != Sapi::CLI) {
            $strLen = $this->phpStrLen($this->getBody());
            if ($strLen) {
                $this->setHeader(new Response\Header\ContentLength($strLen));
            }
            foreach ($this->getHeaders() as $headers) {
                foreach ($headers as $header) {
                    /* @var $header Response\Header */
                    $header->render();
                }
            }
        }
        echo $this->getBody();
        if ($this->getReturnCode()) {
            $this->exitPhp($this->getReturnCode());
        }
    }

    /**
     * @param string $string
     * @return int
     * @codeCoverageIgnore
     */
    protected function phpStrLen($string)
    {
        return strlen($string);
    }

    /**
     * @param int $returnCode
     * @codeCoverageIgnore
     */
    protected function exitPhp($returnCode = 0)
    {
        exit((int)$returnCode);
    }

    /**
     * Define a PHP Cli return code - 0 (default) is success, another error code > 0 for whatever
     * @param int $returnCode
     * @return $this
     */
    public function setReturnCode($returnCode = 0)
    {
        $this->returnCode = (int)$returnCode;
        return $this;
    }

    /**
     * Get defined PHP Cli return code - 0 (default) is success, another error code > 0 for whatever
     * @return int
     */
    public function getReturnCode()
    {
        return (int)$this->returnCode;
    }
}
