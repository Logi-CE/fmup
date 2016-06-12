<?php
/**
 * Url.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Request;


class UrlTest extends \PHPUnit_Framework_TestCase
{
    public function testHasSetGetParam()
    {
        $url = new \FMUP\Request\Url();
        $this->assertSame(array(), $url->getParams());
        $this->assertFalse($url->hasParam('param'));
        $this->assertSame($url, $url->setParam('param', 1));
        $this->assertTrue($url->hasParam('param'));
        $this->assertSame(1, $url->getParam('param'));
        $this->assertNull($url->getParam('paramNotExisting'));
        $this->assertSame(array('param' => 1), $url->getParams());
    }

    public function testSetGetRequest()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Http')->getMock();
        /** @var $request \FMUP\Request\Http */
        $url = new \FMUP\Request\Url();
        $requestDefault = $url->getRequest();
        $this->assertInstanceOf('\FMUP\Request\Http', $requestDefault);
        $this->assertSame($requestDefault, $url->getRequest());
        $this->assertSame($url, $url->setRequest($request));
        $this->assertSame($request, $url->getRequest());
    }

    public function testBuild()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Http')->setMethods(array('getRequestUri'))->getMock();
        $request->method('getRequestUri')
            ->will(
                $this->onConsecutiveCalls(
                    '/path/to/file',
                    '/path/to/file?param=2',
                    '/path/to/file?param=2',
                    '/path/to/file',
                    '/path/to/file?v=1',
                    '/path/to/file?v=1'
                )
            );
        $url = $this->getMockBuilder('\FMUP\Request\Url')->setMethods(array('getParams'))->getMock();
        $url->method('getParams')
            ->will(
                $this->onConsecutiveCalls(
                    array(),
                    array(),
                    array('param' => 1),
                    array('param' => 3),
                    array('v' => array(1,2)),
                    array('v' => array(1,2))
                )
            );
        /**
         * @var $request \FMUP\Request\Http
         * @var $url \FMUP\Request\Url
         */
        $url->setRequest($request);
        $this->assertSame('/path/to/file', $url->build());
        $this->assertSame('/path/to/file?param=2', $url->build());
        $this->assertSame('/path/to/file?param=1', $url->build());
        $this->assertSame('/path/to/file?param=3', $url->build());
        $this->assertSame('/path/to/file?v[0]=1&v[1]=2', $url->build());
        $this->assertSame('/path/to/file?v[0]=1&v[1]=2', (string)$url);
    }
}

