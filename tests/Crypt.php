<?php
namespace Tests;

/**
 * Description of Crypt
 *
 * @author cbras
 */
class CryptTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $cache = new \FMUP\Crypt();
        $cache2 = new \FMUP\Crypt(\FMUP\Crypt\Factory::DRIVER_MD5);
        $this->assertInstanceOf(\FMUP\Crypt::class, $cache, 'Instance of ' . \FMUP\Crypt::class);
        $this->assertNotSame($cache2, $cache, 'New crypt instance must be not same');

        return $cache2;
    }

    /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testSetGetDriver(\FMUP\Crypt $cryptOriginal)
    {
        $crypt = clone $cryptOriginal;
        //Driver Mocké = Md5
        $mock = $this->getMockBuilder(\FMUP\Crypt\CryptInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(
            \FMUP\Crypt\CryptInterface::class,
            $crypt->getDriver(),
            'Instance of ' . \FMUP\Crypt\CryptInterface::class
        );
        /** @var $mock \FMUP\Crypt\CryptInterface */
        $return = $crypt->setDriver($mock);
        $this->assertSame($mock, $return->getDriver(), 'Set settings must return its instance');

        return $crypt;
    }

    /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testGetDriverName(\FMUP\Crypt $crypt)
    {
        //default driver = Md5
        $this->assertEquals($crypt->getDriverName(), "Md5", "Driver name must be 'Md5'");
        return $crypt;
    }

    /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testHash(\FMUP\Crypt $crypt)
    {

        //Driver Mocké = Md5
        $mock = $this->getMockBuilder(\FMUP\Crypt\CryptInterface::class)->getMock();

        $mock->method('hash')->willReturn('e2fc714c4727ee9395f324cd2e7f331f');
        /** @var $mock \FMUP\Crypt\CryptInterface */
        $this->assertEquals($mock->hash("abcd"), $crypt->hash("abcd"), 'Hashed values must be same');
        $this->assertTrue(is_string($crypt->hash("abcd")), "Hash must return a string");

        return $crypt;
    }

    /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testUnHash(\FMUP\Crypt $cryptOriginal)
    {
        $crypt = clone $cryptOriginal;
        $res = 'e2fc714c4727ee9395f324cd2e7f331f';

        //driver Mocké = DRIVER_MCRYPT
        $mock = $this->getMockForAbstractClass(
            \FMUP\Crypt\CryptInterface::class,
            array("unHash"),
            \FMUP\Crypt\Factory::DRIVER_MCRYPT
        );

        $mock->method('unHash')->willReturn('abcd');
        /** @var $mock \FMUP\Crypt\CryptInterface */
        $crypt->setDriver($mock);

        $this->assertEquals($mock->unHash($res), $crypt->unHash($res), 'unHashed values must be same');
        $this->assertTrue(is_string($crypt->unHash($res)), "unHash must return a string");

        return $crypt;
    }

    /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testVerify(\FMUP\Crypt $crypt)
    {
        $test = array(
            array('abcd', 'e2fc714c4727ee9395f324cd2e7f331f', true),
            array('abcdef', 'e2fc714c4727ee9395f324cd2e7f331f', false),
        );
        foreach ($test as $password) {
            $verify = $crypt->verify($password[0], $password[1]);
            //vérifie que la méthode doit renvoyer un booléen
            $this->assertTrue(is_bool($verify), "verify must return a boolean");
            //vérifie que la méthode renvoie la valeur attendue
            $this->assertSame($verify, $password[2], "test verification failure");
        }

        return $crypt;
    }
}
