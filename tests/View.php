<?php

namespace Tests;

use FMUP\View;
use InvalidArgumentException;
use OutOfBoundsException;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    const PARAM_KEY  = 'key';
    const PARAM_VALUE  = 'value';

    const WRONG_EXCEPTION_CODE = 'Wrong exception code.';
    const ERROR_NOT_INSTANCE_OF = 'Not an instance of %s';
    // const EXCEPTION_EXPECTED_TYPE_ERROR_STRING_INSTEAD_OF = 'Exception expected : type error (string instead of %s)';
    // const EXCEPTION_EXPECTED_TYPE_ERROR_NULL_INSTEAD_OF = 'Exception expected : type error (null instead of %s)';
    // const EXCEPTION_EXPECTED_TYPE_ERROR_BOOLEAN_INSTEAD_OF = 'Exception expected : type error (boolean instead of %s)';
    const EXCEPTION_EXPECTED_TYPE_ERROR_OBJECT_INSTEAD_OF = 'Exception expected : type error (object instead of %s)';

    public function testConstruct()
    {
        // check without optional param
        $view = new View();
        $this->assertInstanceOf('\FMUP\View', $view, sprintf(self::ERROR_NOT_INSTANCE_OF, 'FMUP\View'));

        // check with empty array of params
        $view2 = new View(array());
        $this->assertInstanceOf('\FMUP\View', $view2, sprintf(self::ERROR_NOT_INSTANCE_OF, 'FMUP\View'));

        // check with array of params with 1 element
        $view3 = new View(array(self::PARAM_KEY => self::PARAM_VALUE));
        $this->assertInstanceOf('\FMUP\View', $view3, sprintf(self::ERROR_NOT_INSTANCE_OF, 'FMUP\View'));

        // check with object as value in array
        $view4 = new View(array(self::PARAM_KEY, new \stdClass()));
        $this->assertInstanceOf('\FMUP\View', $view4, sprintf(self::ERROR_NOT_INSTANCE_OF, 'FMUP\View'));

/*
        // check with object as key in array
        try {
            $view5 = new View(array(new \stdClass() => self::PARAM_VALUE));
            $this->assertInstanceOf('\FMUP\View', $view5, 'Exception expected : an object can\'t be used as key in an array');
        } catch (\Exception $e) {
            $this->assertEquals('2', $e->getCode(), self::WRONG_EXCEPTION_CODE);
        }
*/
        return $view;
    }

    /**
     * @depends testConstruct
     * @param View $view
     * @return View
     */
    public function testAddGetParams($view)
    {
        // check without params
        $view2 = clone $view;
        $view2->addParams();
        $this->assertFalse(count($view2->getParams())!=0, 'Error : params not empty');

        // check params is an array
        $view3 = clone $view;
        $this->assertFalse(!is_array($view3->getParams()), 'The params must be an array');

        // check param not exists
        $view4 = clone $view;
        $this->assertArrayNotHasKey(self::PARAM_KEY,$view4->getParams(),'The param ' . self::PARAM_KEY . ' exists');

        // check param value not exists
        $view5 = clone $view;
        $this->assertNotContains(self::PARAM_VALUE,$view5->getParams(),'The value' . self::PARAM_VALUE . '  exists in params');

        // check with empty array of params
        $view6 = clone $view;
        $view6->addParams(array());
        $this->assertFalse(count($view6->getParams())!=0, 'Error : params not empty');

        // check with array of params with 1 element & check param exists
        $view7 = clone $view;
        $view7->addParams(array(self::PARAM_KEY => self::PARAM_VALUE));
        $this->assertArrayHasKey(self::PARAM_KEY,$view7->getParams(), 'The param ' . self::PARAM_KEY . ' doesn\'t exist');

        // check with array of params with 1 element & check param value exists
        $view8 = clone $view;
        $view8->addParams(array(self::PARAM_KEY => self::PARAM_VALUE));
        $this->assertContains(self::PARAM_VALUE,$view8->getParams(), 'The value' . self::PARAM_VALUE . ' doesn\'t exist in params');

        // check with object as value in array
        $view9 = clone $view;
        $view9->addParams(array(self::PARAM_KEY => new \stdClass()));
        $this->assertArrayHasKey(self::PARAM_KEY,$view9->getParams(), 'The value' . self::PARAM_VALUE . ' doesn\'t exist in params');

/*
        // check with object as key in array
        try {
            $view10 = clone $view;
            $view10->addParams(array(new \stdClass() => self::PARAM_VALUE));
            $this->assertInstanceOf('\FMUP\View', $view10, 'Exception expected : an object can\'t be used as key in an array');
        } catch (\Exception $e) {
            $this->assertEquals('2', $e->getCode(), self::WRONG_EXCEPTION_CODE);
        }
*/
        return $view;
    }

    /**
     * @depends testConstruct
     * @param View $view
     * @return View
     */
    public function testSetGetParam($view) {

        // check param not exists & value returned is null
        $view2 = clone $view;
        $paramKey = self::PARAM_KEY;
        $view2->getParam('test');
        $this->assertEquals(null, $view2->getParam($paramKey), 'The value returned \''. $view2->getParam('test') .'\' is not the expected value \'null\' for the param \'' .  $paramKey . '\'');

        // check string param value
        $view3 = clone $view;
        $paramKey = self::PARAM_KEY;
        $paramValue = self::PARAM_VALUE;
        $view3->setParam($paramKey,$paramValue);
        $this->assertEquals($paramValue, $view3->getParam($paramKey), 'The value returned \''. $view3->getParam($paramKey) .'\' is not the expected value \''. $paramValue .'\' for the param \'' .  $paramKey . '\'');

        // check null param value
        $view4 = clone $view;
        $paramKey = self::PARAM_KEY;
        $paramValue = null;
        $view4->setParam($paramKey,$paramValue);
        $this->assertEquals($paramValue, $view4->getParam($paramKey), 'The value returned \''. $view4->getParam($paramKey) .'\' is not the expected value \''. $paramValue .'\' for the param \'' .  $paramKey . '\'');

        // check boolean param value
        $view5 = clone $view;
        $paramKey = self::PARAM_KEY;
        $paramValue = true;
        $view5->setParam($paramKey,$paramValue);
        $this->assertEquals($paramValue, $view5->getParam($paramKey), 'The value returned \''. $view5->getParam($paramKey) .'\' is not the expected value \''. $paramValue .'\' for the param \'' .  $paramKey . '\'');

        // check object param value
        $view6 = clone $view;
        $paramKey = self::PARAM_KEY;
        $paramValue = new \stdClass();
        $view6->setParam($paramKey,$paramValue);
        $this->assertEquals($paramValue, $view6->getParam($paramKey), 'The value returned is not the expected value for the param \'' .  $paramKey . '\'');

        // check boolean param key
        try {
            $view7 = clone $view;
            $paramKey = null;
            $paramValue = self::PARAM_VALUE;
            $view7->setParam($paramKey,$paramValue);
            $this->assertEquals($paramValue, $view7->getParam($paramKey), 'The value returned \''. $view7->getParam($paramKey) .'\' is not the expected value \''. $paramValue .'\' for the param \'' .  $paramKey . '\'');
        } catch (\Exception $e) {
            $this->assertEquals('8', $e->getCode(), self::WRONG_EXCEPTION_CODE);
        }

        // check object param key
        try {
            $view8 = clone $view;
            $paramKey = true;
            $paramValue = self::PARAM_VALUE;
            $view8->setParam($paramKey,$paramValue);
            $this->assertEquals($paramValue, $view8->getParam($paramKey), 'The value returned \''. $view8->getParam($paramKey) .'\' is not the expected value \''. $paramValue .'\' for the param \'' .  $paramKey . '\'');
        } catch (\Exception $e) {
            $this->assertEquals('8', $e->getCode(), self::WRONG_EXCEPTION_CODE);
        }

        return $view;
    }

    /**
     * @depends testConstruct
     * @param View $view
     * @return View
     */
    public function testRender($view)
    {
        // check case ViewPath is null
        try {
            $view2 = clone $view;
            $view2->render();
         } catch (\InvalidArgumentException $e) {
            $this->assertEquals('0', $e->getCode(), 'Exception expected : the ViewPath is null');
        }

        // check case ViewPath file not exists
        try {
            $view3 = clone $view;
            $view3->setViewPath('test.php');
            $view3->render();
        } catch (\OutOfBoundsException $e) {
            $this->assertEquals('0', $e->getCode(), 'Exception expected : the ViewPath file not exists');
        }

        // check case ViewPath file exists
        $view4 = clone $view;
        $view4->setViewPath(__DIR__ . '/String.php');
        $view4->addParams(array(self::PARAM_KEY => self::PARAM_VALUE));
        $viewResult = $view4->render();
        $this->assertFalse((is_string($viewResult) && !empty($viewResult)), 'Error : invalid view result');
    }

    /**
     * @depends testConstruct
     * @param View $view
     * @return View
    */
    public function testSetGetViewPath($view) {

        // check with string
        $view2 = clone $view;
        $viewPath = 'views/test.php';
        $view2->setViewPath($viewPath);
        $this->assertEquals($viewPath, $view2->getViewPath(), 'View path is not set correctly');

        // check with null
        $view3 = clone $view;
        $view3->setViewPath(null);
        $this->assertFalse(!is_string($view3->getViewPath()), 'View path is not set correctly');

        // check with boolean
        $view4 = clone $view;
        $view4->setViewPath(true);
        $this->assertFalse(!is_string($view4->getViewPath()), 'View path is not set correctly');

        // check with array
        try {
            $view5 = clone $view;
            $view5->setViewPath(array('test'));
            $this->assertFalse(!is_string($view5->getViewPath()), 'View path is not set correctly');
        } catch (\Exception $e) {
            $this->assertEquals('8', $e->getCode(), self::WRONG_EXCEPTION_CODE);
        }

        // check with object
        try {
            $view6 = clone $view;
            $view6->setViewPath(new \stdClass());
            $this->assertFalse(true, sprintf(self::EXCEPTION_EXPECTED_TYPE_ERROR_OBJECT_INSTEAD_OF, 'string'));
        } catch (\Exception $e) {
            $this->assertEquals('4096', $e->getCode(), self::WRONG_EXCEPTION_CODE);
        }

        return $view;
    }
}