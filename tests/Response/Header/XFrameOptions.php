<?php
/**
 * Created by PhpStorm.
 * User: jsallarsaib
 * Date: 19/10/17
 * Time: 16:33
 */

namespace FMUPTests\Response\Header;

class XFrameOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValue()
    {
        $obj = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions'])
            ->getMock();
        $obj->expects($this->once())->method('getOptions')->willReturn(\FMUP\Response\Header\XFrameOptions::OPTIONS_DENY);
        /**
         * @var \FMUP\Response\Header\XFrameOptions $obj
         */
        $this->assertSame(\FMUP\Response\Header\XFrameOptions::OPTIONS_DENY, $obj->getValue(), 'value is not deny');
    }

    public function testGetValueWhenOptionsAllowWithEmptyUri()
    {
        $obj = $this->getMockBuilder(\FMUP\Response\Header\XFrameOptions::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'getUri'])
            ->getMock();
        $obj->expects($this->once())->method('getOptions')->willReturn(\FMUP\Response\Header\XFrameOptions::OPTIONS_ALLOW_FROM);
        $obj->expects($this->once())->method('getUri')->willReturn([]);
        /**
         * @var \FMUP\Response\Header\XFrameOptions $obj
         */
        $this->assertSame('', $obj->getValue(), 'value is not empty');
    }
}