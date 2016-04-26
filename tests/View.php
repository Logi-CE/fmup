<?php

namespace Tests;

use FMUP\Exception;
use FMUP\Exception\UnexpectedValue as ExceptionUnexpectedValue;
use FMUP\View;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    const PARAM_KEY = 'key';
    const PARAM_VALUE = 'value';
    const WRONG_EXCEPTION_CODE = 'Wrong exception code.';
    const ERROR_NOT_INSTANCE_OF = 'Not an instance of %s';

    public function testConstruct()
    {
        // check without optional param
        $view = new View();
        $this->assertInstanceOf(\FMUP\View::class, $view, sprintf(self::ERROR_NOT_INSTANCE_OF, \FMUP\View::class));

        // check with empty array of params
        $view2 = new View(array());
        $this->assertInstanceOf(\FMUP\View::class, $view2, sprintf(self::ERROR_NOT_INSTANCE_OF, \FMUP\View::class));

        // check with array of params with 1 element
        $view3 = new View(array(self::PARAM_KEY => self::PARAM_VALUE));
        $this->assertInstanceOf(\FMUP\View::class, $view3, sprintf(self::ERROR_NOT_INSTANCE_OF, \FMUP\View::class));

        // check with object as value in array
        $view4 = new View(array(self::PARAM_KEY, new \stdClass()));
        $this->assertInstanceOf(\FMUP\View::class, $view4, sprintf(self::ERROR_NOT_INSTANCE_OF, \FMUP\View::class));

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
        $this->assertFalse(count($view2->getParams()) != 0, 'Error : params not empty');

        // check params is an array
        $view3 = clone $view;
        $this->assertFalse(!is_array($view3->getParams()), 'The params must be an array');

        // check param not exists
        $view4 = clone $view;
        $this->assertArrayNotHasKey(self::PARAM_KEY, $view4->getParams(), 'The param ' . self::PARAM_KEY . ' exists');

        // check param value not exists
        $view5 = clone $view;
        $this->assertNotContains(self::PARAM_VALUE, $view5->getParams(), 'The value' . self::PARAM_VALUE . '  exists in params');

        // check with empty array of params
        $view6 = clone $view;
        $view6->addParams(array());
        $this->assertFalse(count($view6->getParams()) != 0, 'Error : params not empty');

        // check with array of params with 1 element & check param exists and its value
        $view7 = clone $view;
        $view7->addParams(array(self::PARAM_KEY => self::PARAM_VALUE));
        $this->assertArrayHasKey(self::PARAM_KEY, $view7->getParams(), 'The param ' . self::PARAM_KEY . ' doesn\'t exist');
        $this->assertContains(self::PARAM_VALUE, $view7->getParams(), 'The value' . self::PARAM_VALUE . ' doesn\'t exist in params');

        // check with object as value in array
        $view8 = clone $view;
        $view8->addParams(array(self::PARAM_KEY => new \stdClass()));
        $this->assertArrayHasKey(self::PARAM_KEY, $view8->getParams(), 'The value' . self::PARAM_VALUE . ' doesn\'t exist in params');

        return $view;
    }

    /**
     * @depends testConstruct
     * @param View $view
     * @return View
     */
    public function testSetGetParam($view)
    {
        // check param not exists & value returned is null
        $view2 = clone $view;
        $paramKey = self::PARAM_KEY;
        $view2->getParam('test');
        $this->assertArrayNotHasKey(self::PARAM_KEY, $view2->getParams(), 'The param ' . self::PARAM_KEY . ' exists');
        $this->assertEquals(null, $view2->getParam($paramKey), 'The value returned \'' . $view2->getParam('test') . '\' is not the expected value \'null\' for the param \'' . $paramKey . '\'');

        // check string param value
        $view3 = clone $view;
        $paramKey = self::PARAM_KEY;
        $paramValue = self::PARAM_VALUE;
        $view3->setParam($paramKey, $paramValue);
        $this->assertEquals($paramValue, $view3->getParam($paramKey), 'The value returned \'' . $view3->getParam($paramKey) . '\' is not the expected value \'' . $paramValue . '\' for the param \'' . $paramKey . '\'');

        // check null param value
        $view4 = clone $view;
        $paramKey = self::PARAM_KEY;
        $paramValue = null;
        $view4->setParam($paramKey, $paramValue);
        $this->assertEquals($paramValue, $view4->getParam($paramKey), 'The value returned \'' . $view4->getParam($paramKey) . '\' is not the expected value \'' . $paramValue . '\' for the param \'' . $paramKey . '\'');

        // check boolean param value
        $view5 = clone $view;
        $paramKey = self::PARAM_KEY;
        $paramValue = true;
        $view5->setParam($paramKey, $paramValue);
        $this->assertEquals($paramValue, $view5->getParam($paramKey), 'The value returned \'' . $view5->getParam($paramKey) . '\' is not the expected value \'' . $paramValue . '\' for the param \'' . $paramKey . '\'');

        // check object param value
        $view6 = clone $view;
        $paramKey = self::PARAM_KEY;
        $paramValue = new \stdClass();
        $view6->setParam($paramKey, $paramValue);
        $this->assertEquals($paramValue, $view6->getParam($paramKey), 'The value returned is not the expected value for the param \'' . $paramKey . '\'');

        $view7 = clone $view;
        $view7->test = self::PARAM_VALUE;
        $this->assertEquals(self::PARAM_VALUE, $view7->test, 'The value returned is not the expected value for the param test');

        return $view;
    }

    /**
     * @depends testConstruct
     * @param View $view
     * @return View
     */
    public function testSetParamExceptions($view)
    {
        // check set param with null
        try {
            $view2 = clone $view;
            $view2->setParam(null, self::PARAM_VALUE);
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check set param with boolean
        try {
            $view3 = clone $view;
            $view3->setParam(true, self::PARAM_VALUE);
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check set param with string containing whitespace
        try {
            $view4 = clone $view;
            $view4->setParam(new \stdClass(), self::PARAM_VALUE);
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check set param with empty string
        try {
            $view5 = clone $view;
            $view5->setParam('', self::PARAM_VALUE);
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_VALUE_EMPTY, $e->getCode(), $e->getMessage());
        }

        // check set param with string containing whitespace
        try {
            $view6 = clone $view;
            $view6->setParam(' ', self::PARAM_VALUE);
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_VALUE_EMPTY, $e->getCode(), $e->getMessage());
        }
        return $view;
    }

    /**
     * @depends testConstruct
     * @param View $view
     * @return View
     */
    public function testGetParamExceptions($view)
    {

        // check get param with null
        try {
            $view2 = clone $view;
            $view2->getParam(null);
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check get param with boolean
        try {
            $view3 = clone $view;
            $view3->getParam(true);
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check get param with string containing whitespace
        try {
            $view4 = clone $view;
            $view4->getParam(new \stdClass());
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check get param with empty string
        try {
            $view5 = clone $view;
            $view5->getParam('');
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_VALUE_EMPTY, $e->getCode(), $e->getMessage());
        }

        // check get param with string containing whitespace
        try {
            $view6 = clone $view;
            $view6->getParam(' ');
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_VALUE_EMPTY, $e->getCode(), $e->getMessage());
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
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_VALUE_NULL, $e->getCode(), $e->getMessage());
        }

        // check case ViewPath file not exists
        try {
            $view3 = clone $view;
            $view3->setViewPath('test.php');
            $view3->render();
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_VALUE_INVALID_FILE_PATH, $e->getCode(), $e->getMessage());
        }

        // check view result (expected and not expected)
        $view4 = clone $view;
        $view4->setViewPath(__DIR__ . '/.files/testView.phtml');
        $view4->addParams(array('test' => 'View'));
        $viewResult = $view4->render();
        $expectedResult = 'The value of the param test is View';
        $this->assertEquals($expectedResult, $viewResult, 'Error : invalid view result');
        $notExpectedResult = 'The value of the param test is NULL';
        $this->assertNotEquals($notExpectedResult, $viewResult, 'Error : the view result is the result expected');
    }

    /**
     * @depends testConstruct
     * @param View $view
     * @return View
     */
    public function testSetGetViewPath($view)
    {

        // check with string
        $view2 = clone $view;
        $viewPath = 'views/test.php';
        $view2->setViewPath($viewPath);
        $this->assertEquals($viewPath, $view2->getViewPath(), 'View path is not set correctly');

        // check set with null
        try {
            $view3 = clone $view;
            $view3->setViewPath(null);
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check set with boolean
        try {
            $view4 = clone $view;
            $view4->setViewPath(true);
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check get with object
        try {
            $view5 = clone $view;
            $view5->setViewPath(new \stdClass());
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check get with array
        try {
            $view6 = clone $view;
            $view6->setViewPath(array('test'));
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_TYPE_NOT_STRING, $e->getCode(), $e->getMessage());
        }

        // check get with empty string
        try {
            $view7 = clone $view;
            $view7->setViewPath('');
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_VALUE_EMPTY, $e->getCode(), $e->getMessage());
        }

        // check get with string containing whitespace
        try {
            $view8 = clone $view;
            $view8->setViewPath(' ');
        } catch (ExceptionUnexpectedValue $e) {
            $this->assertEquals(ExceptionUnexpectedValue::CODE_VALUE_EMPTY, $e->getCode(), $e->getMessage());
        }

        return $view;
    }
}
