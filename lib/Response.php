<?php
namespace FMUP;

/**
 * Class Response
 * @package FMUP
 */
class Response
{
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
            $this->headers[$name] = array();
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
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sends header and response
     */
    public function send()
    {
        foreach ($this->getHeaders() as $headers) {
            foreach ($headers as $header) {
                /* @var $header Response\Header */
                $header->render();
            }
        }
        echo $this->getBody();
        if ($this->getReturnCode()) {
            exit($this->getReturnCode());
        }
    }

    /**
     * @param int $returnCode
     * @return $this
     */
    public function setReturnCode($returnCode = 0)
    {
        $this->returnCode = (int)$returnCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getReturnCode()
    {
        return (int)$this->returnCode;
    }
}
