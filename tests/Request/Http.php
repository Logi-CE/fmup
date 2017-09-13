<?php
/**
 * Http.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Request;


class HttpTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $_GET = array(
            'get' => [1, 2],
            'value' => 'value',
            'valueCommon' => 'isGet',
        );
        $_POST = array(
            'post' => [1, 2],
            'valuePost' => 'valuePost',
            'valueCommon' => 'isPost',
        );
        $_SERVER = array();
        $_FILES = array(
            'file' => array(
                array(),
            ),
        );
    }

    public function testHasGetServer()
    {
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->hasServer(\FMUP\Request\Http::REQUEST_METHOD));
        $this->assertNull($request->getServer(\FMUP\Request\Http::REQUEST_METHOD));
        $this->assertSame(
            \FMUP\Request\Http::REQUEST_METHOD_GET,
            $request->getServer(\FMUP\Request\Http::REQUEST_METHOD, \FMUP\Request\Http::REQUEST_METHOD_GET)
        );
        $_SERVER[\FMUP\Request\Http::REQUEST_METHOD] = \FMUP\Request\Http::REQUEST_METHOD_POST;
        $request = new \FMUP\Request\Http();
        $this->assertTrue($request->hasServer(\FMUP\Request\Http::REQUEST_METHOD));
        $this->assertSame(\FMUP\Request\Http::REQUEST_METHOD_POST, $request->getServer(\FMUP\Request\Http::REQUEST_METHOD));
    }

    public function testGetMethod()
    {
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->hasServer(\FMUP\Request\Http::REQUEST_METHOD));
        $this->assertNull($request->getMethod());
        $_SERVER[\FMUP\Request\Http::REQUEST_METHOD] = \FMUP\Request\Http::REQUEST_METHOD_POST;
        $request = new \FMUP\Request\Http();
        $this->assertSame(\FMUP\Request\Http::REQUEST_METHOD_POST, $request->getMethod());
    }

    public function testGetReferer()
    {
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->hasServer(\FMUP\Request\Http::HTTP_REFERER));
        $this->assertNull($request->getReferer());
        $_SERVER[\FMUP\Request\Http::HTTP_REFERER] = 'UnitTest';
        $request = new \FMUP\Request\Http();
        $this->assertSame('UnitTest', $request->getReferer());
    }

    public function testGetRequestUri()
    {
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->hasServer(\FMUP\Request\Http::QUERY_STRING));
        $this->assertFalse($request->hasServer(\FMUP\Request\Http::REQUEST_URI));
        $this->assertSame('', $request->getRequestUri());
        $this->assertSame('', $request->getRequestUri(true));
        $this->assertSame('', $request->getRequestUri(false));

        $_SERVER[\FMUP\Request\Http::QUERY_STRING] = 'value=1&v[]=2&v[]=3';
        $_SERVER[\FMUP\Request\Http::REQUEST_URI] = '/home/test?' . $_SERVER[\FMUP\Request\Http::QUERY_STRING];
        $request = new \FMUP\Request\Http();
        $this->assertTrue($request->hasServer(\FMUP\Request\Http::QUERY_STRING));
        $this->assertTrue($request->hasServer(\FMUP\Request\Http::REQUEST_URI));
        $this->assertSame('/home/test', $request->getRequestUri());
        $this->assertSame($_SERVER[\FMUP\Request\Http::REQUEST_URI], $request->getRequestUri(true));
        $this->assertSame('/home/test', $request->getRequestUri(false));
    }

    public function testIsAjax()
    {
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->hasServer(\FMUP\Request\Http::HTTP_X_REQUESTED_WITH));
        $this->assertFalse($request->isAjax());

        $_SERVER[\FMUP\Request\Http::HTTP_X_REQUESTED_WITH] = 'value=1&v[]=2&v[]=3';
        $request = new \FMUP\Request\Http();
        $this->assertTrue($request->hasServer(\FMUP\Request\Http::HTTP_X_REQUESTED_WITH));
        $this->assertFalse($request->isAjax());

        $_SERVER[\FMUP\Request\Http::HTTP_X_REQUESTED_WITH] = \FMUP\Request\Http::HTTP_X_REQUESTED_WITH_AJAX;
        $request = new \FMUP\Request\Http();
        $this->assertTrue($request->hasServer(\FMUP\Request\Http::HTTP_X_REQUESTED_WITH));
        $this->assertTrue($request->isAjax());
    }

    public function testHasSetGetGet()
    {
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->hasGet('test'));
        $this->assertTrue($request->hasGet('value'));
        $this->assertTrue($request->hasGet('get'));
        $this->assertSame($request, $request->setGetValue('test', '1'));
        $this->assertTrue($request->hasGet('test'));
        $this->assertSame('1', $request->getGet('test'));
        $this->assertSame(array(1, 2), $request->getGet('get'));
        $this->assertSame('value', $request->getGet('value'));
        $this->assertNull($request->getGet('null'));
        $this->assertSame('defaultValue', $request->getGet('null', 'defaultValue'));
    }

    public function testHasSetGetPost()
    {
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->hasPost('test'));
        $this->assertTrue($request->hasPost('valuePost'));
        $this->assertTrue($request->hasPost('post'));
        $this->assertSame($request, $request->setPostValue('test', '1'));
        $this->assertTrue($request->hasPost('test'));
        $this->assertSame('1', $request->getPost('test'));
        $this->assertSame(array(1, 2), $request->getPost('post'));
        $this->assertSame('valuePost', $request->getPost('valuePost'));
        $this->assertNull($request->getPost('null'));
        $this->assertSame('defaultValue', $request->getPost('null', 'defaultValue'));
    }

    public function testHasOnMethodPostGet()
    {
        $_SERVER[\FMUP\Request\Http::REQUEST_METHOD] = \FMUP\Request\Http::REQUEST_METHOD_POST;
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->has('test'));
        $this->assertTrue($request->has('valuePost'));
        $this->assertTrue($request->has('post'));
        $_SERVER[\FMUP\Request\Http::REQUEST_METHOD] = \FMUP\Request\Http::REQUEST_METHOD_GET;
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->has('test'));
        $this->assertFalse($request->has('valuePost'));
        $this->assertFalse($request->has('post'));
    }

    public function testGetOnMethodPostGet()
    {
        $_SERVER[\FMUP\Request\Http::REQUEST_METHOD] = \FMUP\Request\Http::REQUEST_METHOD_POST;
        $request = new \FMUP\Request\Http();
        $this->assertSame('isPost', $request->get('valueCommon'));
        $this->assertNull($request->get('notExisting'));
        $this->assertSame('defaultUnitValue', $request->get('notExisting', 'defaultUnitValue'));
        $this->assertSame('isPost', $request->get('valueCommon', 'defaultUnitValue'));

        $_SERVER[\FMUP\Request\Http::REQUEST_METHOD] = \FMUP\Request\Http::REQUEST_METHOD_GET;
        $request = new \FMUP\Request\Http();
        $this->assertSame('isGet', $request->get('valueCommon'));
        $this->assertNull($request->get('notExisting'));
        $this->assertSame('defaultUnitValue', $request->get('notExisting', 'defaultUnitValue'));
        $this->assertSame('isGet', $request->get('valueCommon', 'defaultUnitValue'));
    }

    public function testHasGetFiles()
    {
        $request = new \FMUP\Request\Http();
        $this->assertFalse($request->hasFiles('notExistingFile'));
        $this->assertTrue($request->hasFiles('file'));
        $this->assertNull($request->getFiles('notExistingFile'));
        $this->assertSame('default', $request->getFiles('notExistingFile', 'default'));
        $this->assertSame(array(array()), $request->getFiles('file'));
        $this->assertSame(array(array()), $request->getFiles('file', 'defaultValue'));
    }

    public function testGetHeader()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Http')->setMethods(array('getHeaders'))->getMock();
        $request->method('getHeaders')->willReturn(['header' => '0123456789']);
        $this->assertSame('0123456789', $request->getHeader('header'));
        $this->assertNull($request->getHeader('notExistingHeader'));
        $this->assertSame('defaultUnitValue', $request->getHeader('notExistingHeader', 'defaultUnitValue'));
        $this->assertSame('0123456789', $request->getHeader('header', 'defaultUnitValue'));
    }

    public function testHasHeader()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Http')->setMethods(array('getHeaders'))->getMock();
        $request->method('getHeaders')->willReturn(['header' => '0123456789']);
        $this->assertTrue($request->hasHeader('header'));
    }
}
