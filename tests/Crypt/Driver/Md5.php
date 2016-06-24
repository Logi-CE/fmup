<?php
/**
 * Md5.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Crypt\Driver;

use FMUP\Crypt;
use FMUP\Crypt\Driver\Md5;

class Md5Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Md5
     */
    public function testConstruct()
    {
        $crypt = new Md5();
        $this->assertInstanceOf(Crypt\CryptInterface::class, $crypt);
        return $crypt;
    }

    /**
     * @depends testConstruct
     * @param Md5 $cryptOriginal
     */
    public function testHash(Md5 $cryptOriginal)
    {
        $string = uniqid();
        $this->assertSame(md5($string), $cryptOriginal->hash($string));
    }

    /**
     * @depends testConstruct
     * @param Md5 $cryptOriginal
     */
    public function testUnHash(Md5 $cryptOriginal)
    {
        $this->expectException(\FMUP\Crypt\Exception::class);
        $this->expectExceptionMessage('Invalid method for this driver');
        $cryptOriginal->unHash('test');
    }
}
