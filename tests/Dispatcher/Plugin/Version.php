<?php
/**
 * Version.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Dispatcher\Plugin;


class VersionTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetVersion()
    {
        $version = $this->getMock(\FMUP\Dispatcher\Plugin\Version::class, array('getFromProjectVersion'));
        $version->expects($this->once())->method('getFromProjectVersion')->willReturn('unitTesting');
        /** @var $version \FMUP\Dispatcher\Plugin\Version */
        $this->assertSame('unitTesting', $version->getVersion());
        $this->assertSame('unitTesting', $version->getVersion());
        $this->assertSame($version, $version->setVersion('test2'));
        $this->assertSame('test2', $version->getVersion());
    }


    public function testHandle()
    {
        $body = <<< BODY
<script src='test.js?version' />
<link href="test.css"></link>
BODY;
        $bodyExpected = <<< BODY
<script src='test.js?unitTest' />
<link href="test.css?unitTest"></link>
BODY;
        $response = $this->getMock(\FMUP\Response::class, array('setBody', 'getBody'));
        $response->expects($this->exactly(1))->method('getBody')->willReturn($body);
        $response->expects($this->exactly(1))->method('setBody')->with($this->equalTo($bodyExpected));
        $version = $this->getMock(\FMUP\Dispatcher\Plugin\Version::class, array('getResponse'));
        $version->method('getResponse')->willReturn($response);
        /** @var $version \FMUP\Dispatcher\Plugin\Version */
        $this->assertInstanceOf(\FMUP\Dispatcher\Plugin::class, $version);
        $this->assertSame('Version', $version->getName());
        $this->assertSame($version, $version->setVersion('unitTest')->handle());
    }
}
