<?php
/**
 * ValidatorIterator.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Iterator;


class ValidatorIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testFailsWhenNotConfig()
    {
        $iterator = new \FMUP\Import\Iterator\ValidatorIterator(new \ArrayIterator(array('here')));
        $this->expectException(\FMUP\Import\Exception::class);
        $this->expectExceptionMessage('Iterator can only validate Config');
        foreach ($iterator as $current);
    }

    public function testIterator()
    {
        $configObject = $this->getMock(\FMUP\Import\Config\ConfigObjet::class, array('getStatut'), array(), '', false);
        $configObject->method('getStatut')
            ->willReturnOnConsecutiveCalls(
                'insert', 'update', '', //result update
                'insert', 'update', '', //result update
                'insert', 'update', '', //result update
                'insert', 'update', '', //result update
                'insert', '', 'insert', //result insert
                '', 'insert', 'update', //result update
                '', '', '' //result ''
            );
        $config = $this->getMock(\FMUP\Import\Config::class, array('validateLine', 'getListeConfigObjet', 'getDoublonLigne'));
        $config->method('getListeConfigObjet')->willReturn(array($configObject, $configObject, $configObject));
        $config->method('validateLine')
            ->willReturnOnConsecutiveCalls(
                false, //result error
                false, //result error
                true,
                true,
                true,
                true,
                true
            );
        $config->method('getDoublonLigne')
            ->willReturnOnConsecutiveCalls(
                false,
                true, //result error
                false,
                false,
                false
            );
        $iterator = new \FMUP\Import\Iterator\ValidatorIterator(
            new \ArrayIterator(array($config, $config, $config, $config, $config, $config, $config))
        );
        $types = array(
            'update',
            'update',
            'update',
            'update',
            'insert',
            'update',
            ''
        );
        $count = 0;
        foreach ($iterator as $current) {
            $this->assertSame($config, $current);
            $this->assertSame($count >= 2, $iterator->getValid());
            $this->assertSame($types[$count], $iterator->getType());
            $count++;
        }
        $this->assertSame(7, $count);
        $this->assertSame(3, $iterator->getTotalErrors());
        $this->assertSame(2, $iterator->getTotalUpdate());
        $this->assertSame(1, $iterator->getTotalInsert());
    }
}
