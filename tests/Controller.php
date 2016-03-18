<?php
/**
 * Created by PhpStorm.
 * User: abizac
 * Date: 29/10/2015
 * Time: 16:31
 */

/**
 * Actions dans git bash
 *      abizac@WK114 /c/wamp/www/FMUP (master)
 *          vendor/bin/phing phpunit                => test dans la console
 *          vendor/bin/phpunit tests/Config.php     => résultats à voir dans FMUP\build\coverage\report\index.html
 */

namespace Tests;

use FMUP\Bootstrap;
use FMUP\Controller as FMUPController;
use FMUP\Request;
use FMUP\Session;
use FMUP\View;

class ControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $controller = $this->getMockBuilder('\FMUP\Controller')
            ->setMethods(null)
            ->getMockForAbstractClass();

        $this->assertInstanceOf('\FMUP\Controller', $controller, 'Not an instance of FMUP\Controller');

        return $controller;
    }

    /**
     * @depends testConstruct
     * @param $controller FMUPController
     * @return FMUPController
     */
    public function testClone(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $this->assertEquals($controller, $controller2, "assert equals");
        $this->assertNotSame($controller, $controller2, "assert not same");

        return $controller;
    }

    /**
     * La méthode prefilter retourne $this tout le temps
     * @depends testConstruct
     * @return FMUPController
     */
    public function testPreFiltre(FMUPController $controller)
    {
        $this->assertEquals($controller->preFilter(null), $controller, 'Problem if called action is NULL');
        $this->assertEquals($controller->preFilter(''), $controller, 'Problem if called action is empty string');
        $this->assertEquals($controller->preFilter('calleddAction'), $controller, 'Problem if called action is a string');
        $this->assertEquals($controller->preFilter(array('foo')), $controller, 'Problem if called action is an array');
        $this->assertEquals($controller->preFilter($this->getMockObjectGenerator()), $controller, 'Problem if called action is an object');

        return $controller;
    }

    /**
     * La méthode postfilter retourne $this tout le temps
     * @depends testConstruct
     * @return FMUPController
     */
    public function testPostFiltre(FMUPController $controller)
    {
        $this->assertEquals($controller->postFilter(null), $controller, 'Problem if called action is NULL');
        $this->assertEquals($controller->postFilter(''), $controller, 'Problem if called action is empty string');
        $this->assertEquals($controller->postFilter('calleddAction'), $controller, 'Problem if called action is a string');
        $this->assertEquals($controller->postFilter(array('foo')), $controller, 'Problem if called action is an array');
        $this->assertEquals($controller->postFilter($this->getMockObjectGenerator()), $controller, 'Problem if called action is an object');

        return $controller;
    }

    /**
     * @depends testConstruct
     * @return FMUPController
     */
    public function testSetRequest(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $controller2->setRequest(new Request\Http());

        $this->assertNotEquals($controller, $controller2, "Controllers should be different : set a request doesn't affect the controller.");

        try {
            $controller3 = clone $controller;
            $controller3->setRequest('test');
            $this->assertFalse(true, 'Exception expected : type error (string instead of Request)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        try {
            $controller4 = clone $controller;
            $controller4->setRequest($this->getMockObjectGenerator());
            $this->assertFalse(true, 'Exception expected : type error (object instead of Request)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        try {
            $controller5 = clone $controller;
            $controller5->setRequest(null);
            $this->assertFalse(true, 'Exception expected : type error (null instead of Request)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        return $controller;
    }

    /**
     * @depends testSetRequest
     * @return FMUPController
     */
    public function testGetRequest(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $request = new Request\Http();
        $controller2->setRequest($request);
        $this->assertSame($request, $controller2->getRequest(), "Request given to controller is not the same that getRequest give.");
        $this->assertNotNull($controller2->getRequest(), "Request should be not null");

        try {
            $controller3 = clone $controller;
            $controller3->getRequest();
            $this->assertFalse(true, 'Exception expected : request null');
        } catch (\LogicException $e) {
            $this->assertEquals($e->getMessage(), 'Request must be set', 'Wrong exception message.');
        }

        return $controller;
    }

    /**
     * @depends testConstruct
     * @return FMUPController
     */
    public function testSetResponse(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $controller2->setResponse(new \FMUP\Response());
        $this->assertNotEquals($controller, $controller2, "Controllers should be different : set a Response doesn't affect the controller.");

        try {
            $controller3 = clone $controller;
            $controller3->setResponse('test');
            $this->assertFalse(true, 'Exception expected : type error (string instead of Response)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        try {
            $controller4 = clone $controller;
            $controller4->setResponse($this->getMockObjectGenerator());
            $this->assertFalse(true, 'Exception expected : type error (object instead of Response)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        try {
            $controller5 = clone $controller;
            $controller5->setResponse(null);
            $this->assertFalse(true, 'Exception expected : type error (null instead of Response)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        return $controller;
    }

    /**
     * @depends testSetResponse
     * @return FMUPController
     */
    public function testGetResponse(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $response = new \FMUP\Response();
        $controller2->setResponse($response);
        $this->assertSame($response, $controller2->getResponse(), "Response given to controller is not the same that getResponse give.");
        $this->assertNotNull($controller2->getResponse(), "Response should be not null");

        try {
            $controller3 = clone $controller;
            $controller3->getResponse();
            $this->assertFalse(true, 'Exception expected : response null');
        } catch (\LogicException $e) {
            $this->assertEquals($e->getMessage(), 'Response must be set', 'Wrong exception message.');
        }

        return $controller;
    }

    /**
     * @depends testConstruct
     * @return FMUPController
     */
    public function testSetView(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $controller2->setView(new View());
        $this->assertNotEquals($controller, $controller2, "Controllers should be different : set a View doesn't affect the controller.");

        try {
            $controller3 = clone $controller;
            $controller3->setView('test');
            $this->assertFalse(true, 'Exception expected : type error (string instead of View)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        try {
            $controller4 = clone $controller;
            $controller4->setView($this->getMockObjectGenerator());
            $this->assertFalse(true, 'Exception expected : type error (object instead of View)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        try {
            $controller5 = clone $controller;
            $controller5->setView(null);
            $this->assertFalse(true, 'Exception expected : type error (null instead of View)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        return $controller;
    }

    /**
     * @depends testSetView
     * @return FMUPController
     */
    public function testGetView(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $view = new View();
        $controller2->setView($view);
        $this->assertSame($view, $controller2->getView(), "View given to controller is not the same that getView give.");
        $this->assertNotNull($controller2->getView(), "View should be not null");

        $controller3 = clone $controller;
        $this->assertInstanceOf('\FMUP\View', $controller3->getView(), "Issue with Lazy Loading initialization.");

        return $controller;
    }

    /**
     * @depends testConstruct
     * @return FMUPController
     */
    public function testSetBootstrap(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $controller2->setBootstrap(new Bootstrap());
        $this->assertNotEquals($controller, $controller2, "Controllers should be different : set a Bootstrap doesn't affect the controller.");

        try {
            $controller3 = clone $controller;
            $controller3->setBootstrap('test');
            $this->assertFalse(true, 'Exception expected : type error (string instead of Bootstrap)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        try {
            $controller4 = clone $controller;
            $controller4->setBootstrap($this->getMockObjectGenerator());
            $this->assertFalse(true, 'Exception expected : type error (object instead of Bootstrap)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        try {
            $controller5 = clone $controller;
            $controller5->setBootstrap(null);
            $this->assertFalse(true, 'Exception expected : type error (null instead of Bootstrap)');
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        return $controller;
    }

    /**
     * @depends testSetBootstrap
     * @return FMUPController
     */
    public function testGetBootstrap(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $bootstrap = new Bootstrap();
        $controller2->setBootstrap($bootstrap);
        $this->assertSame($bootstrap, $this->invokeMethod($controller2, 'getBootstrap'), "Bootstrap given to controller is not the same that getBootstrap give.");

        try {
            $controller3 = clone $controller;
            $this->invokeMethod($controller3, 'getBootstrap');
            $this->assertFalse(true, 'Exception expected : response null');
        } catch (\Exception $e) {
            $this->assertEquals($e->getMessage(), 'Bootstrap must be defined', 'Wrong exception message.');
        }

        return $controller;
    }

    /**
     * @depends testConstruct
     * @return FMUPController
     */
    public function testGetSession(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $bootstrap = new Bootstrap();
        $controller2->setBootstrap($bootstrap);
        $session = Session::getInstance();
        $bootstrap->setSession($session);

        $this->assertSame($session, $this->invokeMethod($controller2, 'getSession'), "Session given to controller is not the same that getSession give.");
    }

    /**
     * @depends testConstruct
     * @return FMUPController
     */
    public function testGetActionMethod(FMUPController $controller)
    {
        $controller2 = clone $controller;
        $suffix = FMUPController::ACTION_SUFFIX;

        $action = null;
        $this->assertSame($action . $suffix, $controller2->getActionMethod($action), "Action Method return something different when action is null.");

        $action = 'foo';
        $this->assertSame($action . $suffix, $controller2->getActionMethod($action), "Action Method return something different when action is a string.");

        try {
            $action = $this->getMockObjectGenerator();
            $controller2->getActionMethod($action);
        } catch (\Exception $e) {
            $this->assertEquals($e->getCode(), '4096', 'Wrong exception code.');
        }

        return $controller;
    }

    /**
     * @depends testConstruct
     * @param FMUPController $controller
     * @return FMUPController
     */
    public function getHasResponse(FMUPController $controller)
    {
        $this->assertFalse($controller->hasResponse(), 'Controller must not have reponse at creation');
        $controller2 = clone $controller;
        $controller2->setResponse($this->getMock('\FMUP\Response'));
        $this->assertTrue($controller2->hasResponse(), 'Controller must not have reponse at creation');
        return $controller;
    }

    /**
     * @param $obj
     * @param $method
     * @param array $args
     * @return mixed
     */
    private function invokeMethod($obj, $method, $args = array())
    {
        $method = new \ReflectionMethod(get_class($obj), $method);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}
