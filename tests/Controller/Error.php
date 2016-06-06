<?php
/**
 * Error.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Controller;


class ErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetException()
    {
        /** @var \FMUP\Controller\Error $error */
        $error = $this->getMockBuilder(\FMUP\Controller\Error::class)->setMethods(array('render'))->getMock();
        $reflection = new \ReflectionMethod(\FMUP\Controller\Error::class, 'getException');
        $reflection->setAccessible(true);
        $this->assertNull($reflection->invoke($error, 'getException'));
        $exception = new \Exception(uniqid());
        $this->assertSame($error, $error->setException($exception));
        $this->assertSame($exception, $reflection->invoke($error, 'getException'));
    }

    public function testErrorStatusWhenNormalException()
    {
        $response = $this->getMockBuilder(\FMUP\Response::class)->setMethods(null)->getMock();
        $error = $this->getMockBuilder(\FMUP\Controller\Error::class)->setMethods(array('render'))->getMock();
        $error->method('render')->will($this->returnCallback(function () use ($error) {
            /** @var \FMUP\Controller\Error $error */
            $this->assertEquals(array(), $error->getResponse()->getHeaders());
        }));
        /**
         * @var \FMUP\Controller\Error $error
         * @var \FMUP\Response $response
         */
        $exception = new \Exception(uniqid());
        $this->assertSame($error, $error->setException($exception));
        $error->setResponse($response);
        $error->indexAction();
    }

    public function testErrorStatusWhen404()
    {
        $response = $this->getMockBuilder(\FMUP\Response::class)->setMethods(null)->getMock();
        $error = $this->getMockBuilder(\FMUP\Controller\Error::class)->setMethods(array('render'))->getMock();
        $error->method('render')->will($this->returnCallback(function () use ($error) {
            /** @var \FMUP\Controller\Error $error */
            $this->assertEquals(
                array(
                    'Status' => array(
                        new \FMUP\Response\Header\Status(\FMUP\Response\Header\Status::VALUE_NOT_FOUND)
                    ),
                ),
                $error->getResponse()->getHeaders()
            );
        }));
        $exception = $this->getMockBuilder(\FMUP\Exception\Status\NotFound::class)->setMethods(null)->getMock();
        /**
         * @var \FMUP\Controller\Error $error
         * @var \FMUP\Response $response
         * @var \FMUP\Exception\Status\NotFound $exception
         */
        $this->assertSame($error, $error->setException($exception));
        $error->setResponse($response);
        $error->indexAction();
    }
}
