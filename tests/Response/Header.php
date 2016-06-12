<?php
/**
 * Header.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response;

class HeaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $header = $this->getMockBuilder('\FMUP\Response\Header')->setMethods(array('getType', 'header'))->getMock();
        $header->expects($this->exactly(1))->method('getType')->willReturn('getTypeReturn');
        $header->expects($this->exactly(1))->method('header')->with($this->equalTo('getTypeReturn: testUnit'));
        /** @var $header \FMUP\Response\Header */
        $this->assertSame($header, $header->setValue('testUnit'));
        $this->assertSame('testUnit', $header->getValue());
        $this->assertSame($header, $header->render());
    }
}
