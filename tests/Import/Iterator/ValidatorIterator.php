<?php
/**
 * ValidatorIterator.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Import\Iterator;

use FMUP\Import\Config\ConfigObjet;

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
        $configObject = $this->getMockBuilder(\FMUP\Import\Config\ConfigObjet::class)
            ->setMethods(array('getStatut'))
            ->disableOriginalConstructor()
            ->getMock();
        $configObject->method('getStatut')
            ->willReturnOnConsecutiveCalls(
                ConfigObjet::INSERT, ConfigObjet::UPDATE, '', //result update
                ConfigObjet::INSERT, ConfigObjet::UPDATE, '', //result update
                ConfigObjet::INSERT, ConfigObjet::UPDATE, '', //result update
                ConfigObjet::INSERT, ConfigObjet::UPDATE, '', //result update
                ConfigObjet::INSERT, '', ConfigObjet::INSERT, //result insert
                '', ConfigObjet::INSERT, ConfigObjet::UPDATE, //result update
                '', '', '' //result ''
            );
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)
            ->setMethods(array('validateLine', 'getListeConfigObjet', 'getDoublonLigne'))
            ->getMock();
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
            ConfigObjet::UPDATE,
            ConfigObjet::UPDATE,
            ConfigObjet::UPDATE,
            ConfigObjet::UPDATE,
            ConfigObjet::INSERT,
            ConfigObjet::UPDATE,
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
