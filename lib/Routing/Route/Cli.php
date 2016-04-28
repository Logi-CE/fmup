<?php
namespace FMUP\Routing\Route;

use FMUP\Controller;
use FMUP\Exception\Status\NotFound;
use FMUP\Routing\Route;
use FMUP\Sapi;

class Cli extends Route
{
    const ERROR_NOT_FOUND = 1;
    const ERROR_LOGIC = 2;

    private $controller;
    private $action;

    public function canHandle()
    {
        return $this->getSapi()->get() == Sapi::CLI &&
        $this->getRequest()->has('route') &&
        count(explode('/', $this->getRequest()->get('route'))) == 2;
    }

    public function handle()
    {
        list ($this->controller, $this->action) = explode('/', $this->getRequest()->get('route'));
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getControllerName()
    {
        if (!class_exists($this->controller)) {
            throw new NotFound('Controller ' . $this->controller . ' does not exist', self::ERROR_NOT_FOUND);
        }
        $controller = new $this->controller;
        if (!$controller instanceof Controller) {
            throw new NotFound('Controller ' . $this->controller . ' does not exist', self::ERROR_LOGIC);
        }
        return $this->controller;
    }
}
