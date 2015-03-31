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
     * Add a header to send in response
     *
     * @param string $name Name of the header
     * @param string $value
     * @return $this
     */
    public function addHeader($name, $value)
    {
        if (!array_key_exists($name, $this->headers)) {
            $this->setHeader($name, $value);
        } else {
            array_push($this->headers[$name], $value);
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
     * @param string $name Name of the header
     * @param string $value
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = array($value);
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
        foreach ($this->getHeaders() as $type => $values) {
            foreach ($values as $value) {
                header("$type $value");
            }
        }
        echo $this->getBody();
    }
}
