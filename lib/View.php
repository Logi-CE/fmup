<?php
namespace FMUP;

/**
 * Class View
 * /!\ Beware this version is not compliant with FMU View since layout are hardcoded.
 * With FMUP\View you'll be able to inject Views to views
 *
 * @package FMUP
 */
class View
{
    private $viewPath;
    private $params = array();

    /**
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->addParams($params);
    }

    /**
     * @param array $params
     * @return $this
     */
    public function addParams($params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function render()
    {
        if (is_null($this->getViewPath())) {
            throw new \InvalidArgumentException('View must be defined');
        }
        if (!file_exists($this->getViewPath())) {
            throw new \OutOfBoundsException("File does not exist");
        }
        ob_start();
        extract($this->getParams()); //for compliance only - @todo remove this line
        require ($this->getViewPath());
        return ob_get_clean();
    }

    /**
     * Define view to use
     * @param string $viewPath Full path to view
     * @return $this
     */
    public function setViewPath($viewPath)
    {
        $this->viewPath = (string)$viewPath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * Implements object use
     * @param string $param
     * @return mixed
     */
    public function __get($param)
    {
        return $this->getParam($param);
    }

    /**
     * Implements object use
     * @param string $param
     * @param mixed $value
     * @return View
     */
    public function __set($param, $value)
    {
        return $this->setParam($param, $param);
    }
}
