<?php
/**
 * CsvToConfigIterator.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Iterator;


class CsvToConfigIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $config = $this->getMock(\FMUP\Import\Config::class);
        /** @var \FMUP\Import\Config $config */
        $csv = new \FMUP\Import\Iterator\CsvToConfigIterator(new \ArrayIterator(array()), $config);
        $this->assertSame($config, $csv->getConfig());
    }

    public function testCurrentWhenOneField()
    {
        $field = $this->getMock(\FMUP\Import\Config\Field::class, array('setValue'), array(), '', false);
        $field->expects($this->at(0))->method('setValue')->with($this->isNull());
        $config = $this->getMock(\FMUP\Import\Config::class, array('getField', 'getListeField'));
        $config->expects($this->at(0))->method('getListeField')->willReturn(array(1));
        $config->expects($this->at(1))->method('getField')->willReturn($field)->with($this->equalTo(0));
        /** @var \FMUP\Import\Config $config */
        $iterator = new \ArrayIterator(array(array('oneElement')));
        $csv = new \FMUP\Import\Iterator\CsvToConfigIterator($iterator, $config);
        $count = 0;
        foreach ($csv as $current) {
            $this->assertInstanceOf(\FMUP\Import\Config::class, $current);
            $count++;
        }
        $this->assertSame(1, $count);
    }

    public function testCurrentWhenMoreFields()
    {
        $field = $this->getMock(\FMUP\Import\Config\Field::class, array('setValue'), array(), '', false);
        $field->expects($this->at(0))->method('setValue')->with($this->equalTo('oneElement'));
        $config = $this->getMock(\FMUP\Import\Config::class, array('getField', 'getListeField'));
        $config->expects($this->at(0))->method('getListeField')->willReturn(array(1));
        $config->expects($this->at(1))->method('getField')->willReturn($field)->with($this->equalTo(0));
                /** @var \FMUP\Import\Config $config */
        $iterator = new \ArrayIterator(array(
            array('oneElement', 'oneMoreElement', 'thirdElement'),
        ));
        $csv = new \FMUP\Import\Iterator\CsvToConfigIterator($iterator, $config);
        $count = 0;
        foreach ($csv as $current) {
            $this->assertInstanceOf(\FMUP\Import\Config::class, $current);
            $count++;
        }
        $this->assertSame(1, $count);
    }

    public function testCurrentWhenLessFields()
    {
        $field = $this->getMock(\FMUP\Import\Config\Field::class, array('setValue'), array(), '', false);
        $field->expects($this->at(0))->method('setValue')->with($this->equalTo('oneElement'));
        $field->expects($this->at(1))->method('setValue')->with($this->equalTo('oneMoreElement'));
        $field->expects($this->at(2))->method('setValue')->with($this->equalTo('thirdElement'));
        $field->expects($this->at(3))->method('setValue')->with($this->isNull());
        $field->expects($this->at(4))->method('setValue')->with($this->isNull());
        $config = $this->getMock(\FMUP\Import\Config::class, array('getField', 'getListeField', 'setField'));
        $config->expects($this->at(0))->method('getListeField')->willReturn(array(1, 2, 3, 4, 5));
        $config->expects($this->at(1))->method('getField')->willReturn($field)->with($this->equalTo(0));
        $config->expects($this->at(2))->method('getField')->willReturn($field)->with($this->equalTo(1));
        $config->expects($this->at(3))->method('getField')->willReturn($field)->with($this->equalTo(2));
        $config->expects($this->at(4))->method('getField')->willReturn($field)->with($this->equalTo(3));
        $config->expects($this->at(5))->method('getField')->willReturn($field)->with($this->equalTo(4));

        /** @var \FMUP\Import\Config $config */
        $iterator = new \ArrayIterator(array(
            array('oneElement', 'oneMoreElement', 'thirdElement'),
        ));
        $csv = new \FMUP\Import\Iterator\CsvToConfigIterator($iterator, $config);
        $count = 0;
        foreach ($csv as $current) {
            $this->assertInstanceOf(\FMUP\Import\Config::class, $current);
            $count++;
        }
        $this->assertSame(1, $count);
    }
}
