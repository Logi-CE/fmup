<?php

namespace FMUPTests\Response\Header;

class XFrameOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructors()
    {
        $header = new \FMUP\Response\Header\XFrameOptions;
        $this->assertInstanceOf(\FMUP\Response\Header::class, $header);
        $this->assertInstanceOf(\FMUP\Response\Header\XFrameOptions::class, $header);
        $this->assertSame('Deny', $header->getOptions());
        $this->assertSame(['*'], $header->getUri());

        $header = new \FMUP\Response\Header\XFrameOptions('blu');
        $this->assertSame('blu', $header->getOptions());
        $header = new \FMUP\Response\Header\XFrameOptions(new \stdClass());
        $this->assertSame('Deny', $header->getOptions());

        $header = new \FMUP\Response\Header\XFrameOptions('blu', ['hhh']);
        $this->assertSame(['hhh'], $header->getUri());
        $header = new \FMUP\Response\Header\XFrameOptions('blu', ['hhh', 'fff']);
        $this->assertSame(['hhh', 'fff'], $header->getUri());
        $header = new \FMUP\Response\Header\XFrameOptions('blu', []);
        $this->assertSame(['*'], $header->getUri());
    }

    public function testGetValueDefault()
    {
        $obj = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions'])
            ->getMock();
        $obj->expects($this->once())->method('getOptions')
            ->willReturn(\FMUP\Response\Header\XFrameOptions::OPTIONS_DENY);
        /**
         * @var \FMUP\Response\Header\XFrameOptions $obj
         */
        $this->assertSame(
            \FMUP\Response\Header\XFrameOptions::OPTIONS_DENY,
            $obj->getValue(),
            'Default value is not deny'
        );
    }

    public function testGetValueWhenOptionsAllowWithEmptyUri()
    {
        $obj = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions'])
            ->getMock();
        $obj->expects($this->once())->method('getOptions')
            ->willReturn(\FMUP\Response\Header\XFrameOptions::OPTIONS_ALLOW_FROM);
        /**
         * @var \FMUP\Response\Header\XFrameOptions $obj
         */
        $this->assertSame('ALLOW_FROM *;', $obj->getValue(), 'value is not empty');
    }

    public function testGetValueWhenOptionsAllowWithSomeUri()
    {
        $obj = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'getUri'])
            ->getMock();
        $obj->expects($this->once())->method('getOptions')
            ->willReturn(\FMUP\Response\Header\XFrameOptions::OPTIONS_ALLOW_FROM);
        $obj->expects($this->once())->method('getUri')->willReturn(['http://google.com', 'bob.local']);
        /**
         * @var \FMUP\Response\Header\XFrameOptions $obj
         */
        $this->assertSame('ALLOW_FROM http://google.com;ALLOW_FROM bob.local;', $obj->getValue(), 'Unexpected value');
    }

    public function testSetGetOptions()
    {
        $header = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        /**
         * @var \FMUP\Response\Header\XFrameOptions $header
         */
        $this->assertSame('Deny', $header->getOptions(), 'Unexpected default value');
        $this->assertSame($header, $header->setOptions('wawa'), 'Unexpected fluid interface');
        $this->assertSame('wawa', $header->getOptions(), 'Unexpected changed value');
        $header->setOptions(new \stdClass());
        $this->assertSame('Deny', $header->getOptions(), 'Unexpected default value on wrong parameter');
        $header->setOptions(true);
        $this->assertSame('Deny', $header->getOptions(), 'Unexpected default value on wrong parameter #2');
    }

    public function testSetGetUri()
    {
        $header = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        /**
         * @var \FMUP\Response\Header\XFrameOptions $header
         */
        $this->assertSame(['*'], $header->getUri(), 'Unexpected default value');
        $this->assertSame($header, $header->setUri(['wawa']), 'Unexpected fluid interface');
        $this->assertSame(['wawa'], $header->getUri(), 'Unexpected changed value');
        $header->setUri([]);
        $this->assertSame(['*'], $header->getUri(), 'Unexpected default value on wrong array');
    }

    public function testGetType()
    {
        $header = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        /**
         * @var \FMUP\Response\Header\XFrameOptions $header
         */
        $this->assertSame('X-Frame-Options', $header->getType(), 'Unexpected type value');
    }

    public function testRenderWhenOptionsAllowWithEmptyUri()
    {
        $obj = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'getUri', 'header'])
            ->getMock();
        $obj->expects($this->once())->method('getUri')->willReturn(['*']);
        $obj->expects($this->never())->method('getOptions');
        $obj->expects($this->never())->method('header');
        /**
         * @var \FMUP\Response\Header\XFrameOptions $obj
         */
        $this->assertSame($obj, $obj->render(), 'render should be empty by default');
    }

    public function testRenderWhenOptionsAllowWithSomeUri()
    {
        $obj = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'getUri', 'header'])
            ->getMock();
        $obj->expects($this->once())->method('getOptions')
            ->willReturn(\FMUP\Response\Header\XFrameOptions::OPTIONS_ALLOW_FROM);
        $obj->method('getUri')->willReturn(['http://google.com', 'bob.local']);
        $obj->expects($this->once())->method('header')
            ->with($this->equalTo('X-Frame-Options: ALLOW_FROM http://google.com;ALLOW_FROM bob.local;'));
        /**
         * @var \FMUP\Response\Header\XFrameOptions $obj
         */
        $this->assertSame($obj, $obj->render(), 'Unexpected value');
    }
}
