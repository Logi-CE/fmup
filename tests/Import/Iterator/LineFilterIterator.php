<?php
/**
 * LineFilterIterator.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Import\Iterator;


class LineFilterIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $config = $this->getMockBuilder(\FMUP\Import\Config::class)->setMethods(array('validateLine'))->getMock();
        $config->method('validateLine')->willReturnOnConsecutiveCalls(true, false, true);
        $arrayForIterator = array('string', $config, null, false, $config, $config);
        $count = 0;
        foreach (new \FMUP\Import\Iterator\LineFilterIterator(new \ArrayIterator($arrayForIterator)) as $current) {
            $count++;
        }
        $this->assertSame(2, $count);
    }
}
