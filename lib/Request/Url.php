<?php
namespace FMUP\Request;

/**
 * Class Url
 * intend to create url by replace parameters on the current URL
 * @package FMUP
 * @category Request
 * @example
 * $url = new Url;
 * $url->setParam('page', 2); //will replace page parameter in current url to 2
 */
class Url
{
    private $request;
    private $params = array();

    /**
     * Define a param value
     * @param string $param
     * @param mixed $value
     * @return $this
     */
    public function setParam($param, $value)
    {
        $this->params[$param] = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Check if the param is defined in this url
     * @param string $param
     * @return bool
     */
    public function hasParam($param)
    {
        return isset($this->params[$param]);
    }

    /**
     * @param $param
     * @return null
     */
    public function getParam($param)
    {
        return $this->hasParam($param) ? $this->params[$param] : null;
    }

    /**
     * @return \FMUP\Request\Http
     */
    public function getRequest()
    {
        if (!$this->request) {
            $this->request = new \FMUP\Request\Http();
        }
        return $this->request;
    }

    /**
     * @param \FMUP\Request\Http $request
     * @return $this
     */
    public function setRequest(\FMUP\Request\Http $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Return the url constructed with parameters
     * @return string
     */
    public function build()
    {
        $urlInfo = parse_url($this->getRequest()->getRequestUri(true));
        $url = $urlInfo['path'];
        $params = array();
        if (isset($urlInfo['query'])) {
            parse_str($urlInfo['query'], $params);
        }
        $params = array_merge($params, $this->getParams());
        if (!empty($params)) {
            $url .= '?' . urldecode(http_build_query($params));
        }
        return $url;
    }

    /**
     * Alias for displaying
     * @return string
     */
    public function __toString()
    {
        return $this->build();
    }
}
