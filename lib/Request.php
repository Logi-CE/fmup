<?php
namespace FMUP;

/**
 * Class Request - handle HTTP request
 * @package FMUP
 */
class Request
{
    const HTTP_X_REQUESTED_WITH = 'HTTP_X_REQUESTED_WITH';
    const HTTP_X_REQUESTED_WITH_AJAX = 'XMLHttpRequest';

    const REQUEST_URI = 'REQUEST_URI';
    const QUERY_STRING = 'QUERY_STRING';

    const REQUEST_METHOD = 'REQUEST_METHOD';
    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_POST = 'POST';

    const SERVER_NAME = 'SERVER_NAME';

    protected $get = array();
    protected $post = array();
    protected $server = array();
    protected $files = array();

    /**
     * mapped to HTTP values
     */
    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
    }

    /**
     * Get a value in the request depending HTTP method
     * @param string $name Name to retrieve
     * @param mixed $defaultValue Value returned if name is not defined in query
     * @return mixed
     */
    public function get($name, $defaultValue = null)
    {
        return ($this->getMethod() == self::REQUEST_METHOD_POST)
            ? $this->getPost($name, $defaultValue)
            : $this->getGet($name, $defaultValue);
    }

    public function has($name)
    {
        return ($this->getMethod() == self::REQUEST_METHOD_POST)
            ? $this->hasPost($name)
            : $this->hasGet($name);
    }

    /**
     * Return requested value in GET method
     * @param string $name Name to retrieve
     * @param mixed $defaultValue Value returned if name is not defined in query
     * @return mixed
     */
    public function getGet($name, $defaultValue = null)
    {
        return $this->hasGet($name) ? $this->get[$name] : $defaultValue;
    }

    /**
     * Return requested value in POST method
     * @param string $name Name to retrieve
     * @param mixed $defaultValue Value returned if name is not defined in query
     * @return mixed
     */
    public function getPost($name, $defaultValue = null)
    {
        return $this->hasPost($name) ? $this->post[$name] : $defaultValue;
    }

    /**
     * Retrieve a value defined on server
     * @param string $name Name to retrieve
     * @param mixed $defaultValue Value returned if name is not defined in query
     * @return mixed
     */
    public function getServer($name, $defaultValue = null)
    {
        return $this->hasServer($name) ? $this->server[$name] : $defaultValue;
    }
    
    /**
     * Retrieve a value defined on files
     * @param int $name
     * @param mixed $defaultValue Value returned if name is not defined in query
     * @return mixed
     */
    public function getFiles($name, $defaultValue = null)
    {
        return $this->hasFiles($name) ? $this->files[$name] : $defaultValue;
    }

    /**
     * Check whether request is an Ajax request
     * @return bool
     */
    public function isAjax()
    {
        $requestClient = $this->getServer(self::HTTP_X_REQUESTED_WITH);
        return (strtolower($requestClient) == strtolower(self::HTTP_X_REQUESTED_WITH_AJAX));
    }

    /**
     * Get HTTP method (GET/POST/DELETE...)
     * @return string
     */
    public function getMethod()
    {
        return $this->getServer(self::REQUEST_METHOD);
    }

    /**
     * Retrieve called URI
     * @param bool $withQuerySting must return query string in the request
     * @return string
     */
    public function getRequestUri($withQuerySting = false)
    {
        $requestUri = $this->getServer(self::REQUEST_URI);
        return $withQuerySting
            ? $requestUri
            : str_replace('?' . $this->getServer(self::QUERY_STRING), '', $requestUri);
    }

    /**
     * Define a POST value
     * @param string $name Name to define
     * @param mixed $value Value to define
     * @return $this
     */
    public function setPostValue($name, $value)
    {
        $this->post[$name] = $value;
        //for compliance purpose only - do not use
        $_POST[$name] = $value;
        $_REQUEST[$name] = $value;
        return $this;
    }

    /**
     * Define a GET value
     * @param string $name Name to define
     * @param mixed $value Value to define
     * @return $this
     */
    public function setGetValue($name, $value)
    {
        $this->get[$name] = $value;
        //for compliance purpose only - do not use
        $_GET[$name] = $value;
        $_REQUEST[$name] = $value;
        return $this;
    }

    /**
     * Check whether selected post exists
     * @param string $name
     * @return bool
     */
    public function hasPost($name)
    {
        return array_key_exists($name, $this->post);
    }

    /**
     * Check whether selected get exists
     * @param string $name
     * @return bool
     */
    public function hasGet($name)
    {
        return array_key_exists($name, $this->get);
    }

    /**
     * Check whether selected server value exists
     * @param string $name
     * @return bool
     */
    public function hasServer($name)
    {
        return array_key_exists($name, $this->server);
    }
    
    /**
     * Check whether selected files value exists
     * @param string $name
     * @return bool
     */
    public function hasFiles($name)
    {
        return array_key_exists($name, $this->files);
    }
}
