<?php
/**
 * LineToConfigIterator.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import\Iterator;


class LineToConfigIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetConfig()
    {
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)->getMock();
        /** @var \FMUP\Import\Config $config */
        $iterator = new \FMUP\Import\Iterator\LineToConfigIterator(new \ArrayIterator(), $config);
        $this->assertSame($config, $iterator->getConfig());
    }

    public function testIterations()
    {
        $field = $this->getMockBuilder(\FMUP\Import\Config\Field::class)
            ->setMethods(array('setValue'))
            ->disableOriginalConstructor()
            ->getMock();
        $field->expects($this->at(0))->method('setValue')->with($this->equalTo('test'));
        $field->expects($this->at(1))->method('setValue')->with($this->equalTo('value'));
        $field->expects($this->at(2))->method('setValue')->with($this->equalTo('test2'));
        $field->expects($this->at(3))->method('setValue')->with($this->equalTo(''));
        $field->expects($this->at(4))->method('setValue')->with($this->equalTo('value2'));
        $field->expects($this->at(5))->method('setValue')->with($this->equalTo(''));
        $field->expects($this->at(6))->method('setValue')->with($this->equalTo('test3'));
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)->setMethods(array('getField'))->getMock();
        $config->method('getField')->willReturn($field);
        /** @var \FMUP\Import\Config $config */
        $iterator = new \FMUP\Import\Iterator\LineToConfigIterator(
            new \ArrayIterator(array('test;value', 'test2;;value2', '', 'test3')),
            $config
        );
        foreach ($iterator as $current) {
            $this->assertInstanceOf(\FMUP\Import\Config::class, $current);
        }
    }
}
