<?php
/**
 * Location.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;


class LocationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $location = new \FMUP\Response\Header\Location('');
        $this->assertInstanceOf('\FMUP\Response\Header', $location);
        $this->assertSame('', $location->getValue());

        $location = new \FMUP\Response\Header\Location('/');
        $this->assertInstanceOf('\FMUP\Response\Header', $location);
        $this->assertSame('/', $location->getValue());

        $location = new \FMUP\Response\Header\Location('/unitTest');
        $this->assertInstanceOf('\FMUP\Response\Header', $location);
        $this->assertSame('/unitTest', $location->getValue());
    }

    public function testGetType()
    {
        $location = new \FMUP\Response\Header\Location('');
        $this->assertSame(\FMUP\Response\Header\Location::TYPE, $location->getType());
    }
}
