<?php
/**
 * Location.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Exception;


class LocationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLocation()
    {
        $location = new \FMUP\Exception\Location();
        $this->assertSame('/', $location->getLocation());
        $location = new \FMUP\Exception\Location('http://www.google.fr');
        $this->assertSame('http://www.google.fr', $location->getLocation());
        $location = new \FMUP\Exception\Location('ftp://127.0.0.1');
        $this->assertSame('ftp://127.0.0.1', $location->getLocation());
        $location = new \FMUP\Exception\Location('/home/test');
        $this->assertSame('/home/test', $location->getLocation());
        $location = new \FMUP\Exception\Location('home/test');
        $this->assertSame('/home/test', $location->getLocation());
    }
}
