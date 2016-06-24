<?php
/**
 * Staticify.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Dispatcher\Plugin;

class StaticifyTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetStaticNumber()
    {
        $staticify = new \FMUP\Dispatcher\Plugin\Staticify();
        $this->assertSame(3, $staticify->getStaticNumber());
        $this->assertSame($staticify, $staticify->setStaticNumber(5));
        $this->assertSame(5, $staticify->getStaticNumber());
        $this->assertSame($staticify, $staticify->setStaticNumber());
        $this->assertSame(3, $staticify->getStaticNumber());
    }

    public function testSetGetStaticPrefix()
    {
        $staticify = new \FMUP\Dispatcher\Plugin\Staticify();
        $this->assertSame('static', $staticify->getStaticPrefix());
        $this->assertSame($staticify, $staticify->setStaticPrefix('cdn'));
        $this->assertSame('cdn', $staticify->getStaticPrefix());
        $this->assertSame($staticify, $staticify->setStaticPrefix());
        $this->assertSame('static', $staticify->getStaticPrefix());
    }

    public function testSetGetStaticSuffix()
    {
        $staticify = new \FMUP\Dispatcher\Plugin\Staticify();
        $this->assertSame('', $staticify->getStaticSuffix());
        $this->assertSame($staticify, $staticify->setStaticSuffix('.'));
        $this->assertSame('.', $staticify->getStaticSuffix());
        $this->assertSame($staticify, $staticify->setStaticSuffix());
        $this->assertSame('', $staticify->getStaticSuffix());
    }

    public function testSetGetSubDomain()
    {
        $staticify = new \FMUP\Dispatcher\Plugin\Staticify();
        $this->assertSame('www', $staticify->getSubDomain());
        $this->assertSame($staticify, $staticify->setSubDomain(''));
        $this->assertSame('', $staticify->getSubDomain());
        $this->assertSame($staticify, $staticify->setSubDomain());
        $this->assertSame('www', $staticify->getSubDomain());
    }

    public function testSetGetDomain()
    {
        $request = $this->getMockBuilder(\FMUP\Request\Http::class)->setMethods(array('getServer'))->getMock();
        $request->expects($this->exactly(4))
            ->method('getServer')
            ->withConsecutive(
                array($this->equalTo(\FMUP\Request\Http::REQUEST_SCHEME)),
                array($this->equalTo(\FMUP\Request\Http::HTTP_HOST)),
                array($this->equalTo(\FMUP\Request\Http::REQUEST_SCHEME)),
                array($this->equalTo(\FMUP\Request\Http::HTTP_HOST))
            )->willReturnOnConsecutiveCalls('https', 'www.localhost.com', 'https', 'www.localhost.com');
        $staticify = $this->getMockBuilder(\FMUP\Dispatcher\Plugin\Staticify::class)
            ->setMethods(array('getRequest')
            )->getMock();
        $staticify->method('getRequest')->willReturn($request);
        /** @var $staticify \FMUP\Dispatcher\Plugin\Staticify */
        $this->assertSame('https://www.localhost.com', $staticify->getDomain());
        $this->assertSame($staticify, $staticify->setDomain('http://this.is.my-test.domain'));
        $this->assertSame('http://this.is.my-test.domain', $staticify->getDomain());
        $this->assertSame($staticify, $staticify->setDomain());
        $this->assertSame('https://www.localhost.com', $staticify->getDomain());
    }

    public function testHandleNoJson()
    {
        $request = $this->getMockBuilder(\FMUP\Request\Http::class)->setMethods(array('getServer'))->getMock();
        $request->method('getServer')
            ->withConsecutive(
                array($this->equalTo(\FMUP\Request\Http::REQUEST_URI))
            )
            ->willReturnOnConsecutiveCalls(
                '/test/sub/folder'
            );
        $response = $this->getMockBuilder(\FMUP\Response::class)->setMethods(array('getBody', 'setBody'))->getMock();
        $response->expects($this->once())->method('getBody')->willReturn(<<<BODY
        <link href="/modules/order/cart/styles/cart.css?10.4.3" type="text/css" rel="stylesheet" />
        <a href="http://this.will.not.be.touched">a</a>
        <img src="http://neither/this.jpg" />
        <img src="/but/this/one.jpg" />
        <img src="and/this/too.png" />
        <script src="/scripts/lib/jquery-1.9.1-min.js?10.4.3" type="text/javascript"></script>
        <script src="/scripts/lib/jquery-1.9.1-min.js?10.4.3" type="text/javascript"></script>
        <script src="/modules/order/cart/styles/cart.js?10.4.3" type="text/javascript"></script>
BODY
);
        $bodyResponse = <<<BODY_RESPONSE
        <link href="https://static2.testdomain.tld/modules/order/cart/styles/cart.css?10.4.3" type="text/css" rel="stylesheet" />
        <a href="http://this.will.not.be.touched">a</a>
        <img src="http://neither/this.jpg" />
        <img src="https://static1.testdomain.tld/but/this/one.jpg" />
        <img src="https://static2.testdomain.tld/test/sub/and/this/too.png" />
        <script src="https://static3.testdomain.tld/scripts/lib/jquery-1.9.1-min.js?10.4.3" type="text/javascript"></script>
        <script src="https://static3.testdomain.tld/scripts/lib/jquery-1.9.1-min.js?10.4.3" type="text/javascript"></script>
        <script src="https://static1.testdomain.tld/modules/order/cart/styles/cart.js?10.4.3" type="text/javascript"></script>
BODY_RESPONSE;
        //link are processed after src
        //defined url with protocol will not be altered
        //relative path are now absolute depending on request URI
        //same path are on the same static
        $response->expects($this->once())->method('setBody')->with($this->equalTo($bodyResponse));

        $staticify = $this->getMockBuilder(\FMUP\Dispatcher\Plugin\Staticify::class)
            ->setMethods(array('getResponse', 'getRequest', 'getDomain'))
            ->getMock();
        $staticify->method('getResponse')->willReturn($response);
        $staticify->method('getRequest')->willReturn($request);
        $staticify->method('getDomain')->willReturn('https://www.testdomain.tld');
        /** @var $staticify \FMUP\Dispatcher\Plugin\Staticify */
        $staticify->handle();
    }

    public function testHandleJson()
    {
        $request = $this->getMockBuilder(\FMUP\Request\Http::class)->setMethods(array('getServer'))->getMock();
        $request->method('getServer')
            ->withConsecutive(
                array($this->equalTo(\FMUP\Request\Http::REQUEST_URI))
            )
            ->willReturnOnConsecutiveCalls(
                '/test/sub/folder/'
            );
        $response = $this->getMockBuilder(\FMUP\Response::class)
            ->setMethods(array('getBody', 'setBody', 'getHeaders'))
            ->getMock();
        $response->expects($this->once())->method('getHeaders')->willReturn(
            array(
                \FMUP\Response\Header\ContentType::TYPE => array(
                    new \FMUP\Response\Header\ContentType(\FMUP\Response\Header\ContentType::MIME_APPLICATION_JSON)
                )
            )
        );
        $response->expects($this->once())->method('getBody')->willReturn(json_encode(<<<BODY
        <link href="/modules/order/cart/styles/cart.css?10.4.3" type="text/css" rel="stylesheet" />
        <a href="http://this.will.not.be.touched">a</a>
        <img src="http://neither/this.jpg" />
        <img src="/but/this/one.jpg" />
        <img src="and/this/too.png" />
        <script src="/scripts/lib/jquery-1.9.1-min.js?10.4.3" type="text/javascript"></script>
        <script src="/scripts/lib/jquery-1.9.1-min.js?10.4.3" type="text/javascript"></script>
        <script src="/modules/order/cart/styles/cart.js?10.4.3" type="text/javascript"></script>

        <script type='text/javascript'>
            var src='/url.jpg';
        </script>
BODY
        ));
        $bodyResponse = <<<BODY_RESPONSE
        <link href="http://cdn2-testdomain.tld/modules/order/cart/styles/cart.css?10.4.3" type="text/css" rel="stylesheet" />
        <a href="http://this.will.not.be.touched">a</a>
        <img src="http://neither/this.jpg" />
        <img src="http://cdn1-testdomain.tld/but/this/one.jpg" />
        <img src="http://cdn2-testdomain.tld/test/sub/folder/and/this/too.png" />
        <script src="http://cdn3-testdomain.tld/scripts/lib/jquery-1.9.1-min.js?10.4.3" type="text/javascript"></script>
        <script src="http://cdn3-testdomain.tld/scripts/lib/jquery-1.9.1-min.js?10.4.3" type="text/javascript"></script>
        <script src="http://cdn1-testdomain.tld/modules/order/cart/styles/cart.js?10.4.3" type="text/javascript"></script>

        <script type='text/javascript'>
            var src='/url.jpg';
        </script>
BODY_RESPONSE;
        //link are processed after src
        //defined url with protocol will not be altered
        //relative path are now absolute depending on request URI
        //same path are on the same static
        //trailing slash on request uri will cause a new folder on requested asset
        $response->expects($this->once())->method('setBody')->with($this->equalTo(json_encode($bodyResponse)));

        $staticify = $this->getMockBuilder(\FMUP\Dispatcher\Plugin\Staticify::class)
            ->setMethods(
                array('getResponse', 'getRequest', 'getDomain', 'getSubDomain', 'getStaticPrefix', 'getStaticSuffix')
            )
        ->getMock();
        $staticify->method('getSubDomain')->willReturn('');
        $staticify->method('getStaticPrefix')->willReturn('cdn');
        $staticify->method('getStaticSuffix')->willReturn('-');
        $staticify->method('getResponse')->willReturn($response);
        $staticify->method('getRequest')->willReturn($request);
        $staticify->method('getDomain')->willReturn('http://testdomain.tld');
        /** @var $staticify \FMUP\Dispatcher\Plugin\Staticify */
        $staticify->handle();
    }
}
