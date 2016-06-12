<?php
/**
 * Status.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;


class StatusTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $status = $this->getMockBuilder('\FMUP\Response\Header\Status')->setMethods(array('header'))->getMock();
        /** @var $status \FMUP\Response\Header\Status */
        $this->assertInstanceOf('\FMUP\Response\Header', $status);
        $this->assertSame(\FMUP\Response\Header\Status::VALUE_OK, $status->getValue());
    }

    public function testRender()
    {
        $status = $this->getMockBuilder('\FMUP\Response\Header\Status')->setMethods(array('header'))->getMock();
        $status->expects($this->exactly(1))->method('header')->with($this->equalTo('HTTP/1.1 200 OK'));
        /** @var $status \FMUP\Response\Header\Status */
        $this->assertSame($status, $status->render());
    }

    public function testGetType()
    {
        $status = new \FMUP\Response\Header\Status;
        $this->assertSame(\FMUP\Response\Header\Status::TYPE, $status->getType());
    }
}
