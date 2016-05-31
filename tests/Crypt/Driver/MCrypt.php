<?php
/**
 * MCrypt.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Crypt\Driver;

use FMUP\Crypt;
use FMUP\Crypt\Driver\MCrypt;

class MCryptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return MCrypt
     */
    public function testConstruct()
    {
        $crypt = new MCrypt();
        $this->assertInstanceOf(Crypt\CryptInterface::class, $crypt);
        return $crypt;
    }

    /**
     * @depends testConstruct
     * @param MCrypt $cryptOriginal
     * @return MCrypt
     */
    public function testSetKey(MCrypt $cryptOriginal)
    {
        $crypt = clone $cryptOriginal;
        $mcrypt = new \ReflectionMethod(\FMUP\Crypt\Driver\MCrypt::class, 'getKey');
        $mcrypt->setAccessible(true);
        $this->assertEquals(\FMUP\Crypt\Driver\MCrypt::KEY, $mcrypt->invoke($crypt));
        $this->assertSame($crypt, $crypt->setKey('unitTest'));
        $this->assertEquals('unitTest', $mcrypt->invoke($crypt));
        return $cryptOriginal;
    }

    /**
     * @depends testConstruct
     * @param MCrypt $crypt
     */
    public function testGetIv(MCrypt $crypt)
    {
        $iv = $crypt->getIv();
        $this->assertTrue(is_string($iv));
        $this->assertSame($iv, $crypt->getIv());
    }

    /**
     * @depends testSetKey
     * @param MCrypt $cryptOriginal
     */
    public function testHash(MCrypt $cryptOriginal)
    {
        $crypt = clone $cryptOriginal;
        $specialKey = 'must_be_16_chars';
        $string = 'unitTest';
        $hash1 = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, \FMUP\Crypt\Driver\MCrypt::KEY, $string, MCRYPT_MODE_ECB, $crypt->getIv());
        $hash2 = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $specialKey, $string, MCRYPT_MODE_ECB, $crypt->getIv());
        $this->assertSame($hash1, $crypt->hash($string));
        $this->assertSame($hash2, $crypt->setKey($specialKey)->hash($string));
    }

    /**
     * @depends testSetKey
     * @param MCrypt $cryptOriginal
     */
    public function testUnHash(MCrypt $cryptOriginal)
    {
        $crypt = clone $cryptOriginal;
        $specialKey = 'must_be_16_chars';
        $string = 'unitTest';
        $hash1 = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, \FMUP\Crypt\Driver\MCrypt::KEY, $string, MCRYPT_MODE_ECB, $crypt->getIv());
        $hash2 = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $specialKey, $string, MCRYPT_MODE_ECB, $crypt->getIv());
        $this->assertSame($hash1, $crypt->unHash($string));
        $this->assertSame($hash2, $crypt->setKey($specialKey)->unHash($string));
    }
}
