<?php
/**
 * DuplicateIterator.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Iterator;


class DuplicateIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorFail()
    {
        $iterator = new \FMUP\Import\Iterator\DuplicateIterator(new \ArrayIterator(array('here')));
        $this->setExpectedException('\FMUP\Import\Exception', 'Current object is not config');
        foreach ($iterator as $current);
    }

    public function testIterator()
    {
        $field = $this->getMockBuilder('\FMUP\Import\Config\Field')
            ->setMethods(array('getValue'))
            ->disableOriginalConstructor()
            ->getMock();
        $field->expects($this->at(1))->method('getValue')->willReturn(0);
        $field->expects($this->at(2))->method('getValue')->willReturn(1);
        $field->expects($this->at(3))->method('getValue')->willReturn(0);
        $configObject = $this->getMockBuilder('\FMUP\Import\Config\ConfigObjet')
            ->setMethods(array('getListeIndexChamp'))
            ->disableOriginalConstructor()
            ->getMock();
        $configObject->method('getListeIndexChamp')->willReturn(array(0));
        $config = $this->getMockBuilder('\FMUP\Import\Config')
            ->setMethods(array('getListeConfigObjet', 'getField', 'setDoublonLigne'))
            ->getMock();
        $config->method('getField')->willReturn($field);
        $config->expects($this->exactly(3))
            ->method('getListeConfigObjet')
            ->willReturn(array($configObject, $configObject, $configObject));
        $config->expects($this->at(4))->method('setDoublonLigne')->with($this->isFalse());
        $config->expects($this->at(9))->method('setDoublonLigne')->with($this->isFalse());
        $config->expects($this->at(14))->method('setDoublonLigne')->with($this->equalTo(0));
        $iterator = new \FMUP\Import\Iterator\DuplicateIterator(new \ArrayIterator(array($config, $config, $config)));
        foreach ($iterator as $current) {
            $this->assertSame($config, $current);
        }
    }
}
