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
        $error = $this->getMock(\FMUP\Controller\Error::class, array('render'));
        $reflection = new \ReflectionMethod(\FMUP\Controller\Error::class, 'getException');
        $reflection->setAccessible(true);
        $this->assertNull($reflection->invoke($error, 'getException'));
        $exception = new \Exception(uniqid());
        $this->assertSame($error, $error->setException($exception));
        $this->assertSame($exception, $reflection->invoke($error, 'getException'));
    }

    public function testErrorStatusWhenNormalException()
    {
        $response = $this->getMock(\FMUP\Response::class, null);
        $error = $this->getMock(\FMUP\Controller\Error::class, array('render'));
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
        $response = $this->getMock(\FMUP\Response::class, null);
        $error = $this->getMock(\FMUP\Controller\Error::class, array('render'));
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
        $exception = $this->getMock(\FMUP\Exception\Status\NotFound::class, null);
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
