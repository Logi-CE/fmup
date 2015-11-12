<?php
namespace Tests;

/**
 * Description of Crypt
 *
 * @author cbras
 */
class CryptTest extends \PHPUnit_Framework_TestCase {

    public function testConstruct() 
    {
        $cache = new \FMUP\Crypt();
        $cache2 = new \FMUP\Crypt(\FMUP\Crypt\Factory::DRIVER_MD5);
        $this->assertInstanceOf(
                '\FMUP\Crypt', 
                $cache, 
                'Instance of \FMUP\Crypt'
                );
        $this->assertNotSame(
                $cache2, 
                $cache, 
                'New cache instance must be not same'
                );
        
        return $cache;
    }

     /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testSetGetDriver(\FMUP\Crypt $cache) 
    {
        //Driver Mocké = Md5
        $mock = $this->getMockBuilder('\FMUP\Crypt\CryptInterface')
                ->disableOriginalConstructor()
                ->getMock();
        
        $this->assertInstanceOf(
                '\FMUP\Crypt\CryptInterface', 
                $cache->getDriver(), 
                'Instance of \FMUP\Crypt\CryptInterface'
                );
        
        $return = $cache->setDriver($mock);
        $this->assertSame(
                $mock, 
                $return->getDriver(), 
                'Set settings must return its instance'
                );
        
        return $cache;
    }
    
     /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testGetDriverName(\FMUP\Crypt $cache) 
    {
        //default driver = Md5
        $this->assertEquals(
                $cache->getDriverName(),
                "Md5", 
                "Driver name must be 'Md5'"
                );
        
        return $cache;
    }

     /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testHash(\FMUP\Crypt $cache) 
    {
        //reinit the constructor with default driver
        $cache =  new \FMUP\Crypt();

      
        //Driver Mocké = Md5
      $mock = $this->getMockBuilder('\FMUP\Crypt\CryptInterface')
              ->getMock(); 
      
      $mock->method('hash')
           ->willReturn('e2fc714c4727ee9395f324cd2e7f331f');
      
      $this->assertEquals(
                $mock->hash("abcd"), 
                $cache->hash("abcd"), 
                'Hashed values must be same'
                );
      
      $this->assertTrue(is_string($cache->hash("abcd")), "Hash must return a string");
      
        return $cache;
    }

     /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testUnHash(\FMUP\Crypt $cache) 
    {
        //reinit the constructor with default driver
        $cache =  new \FMUP\Crypt();
        
        $res= 'e2fc714c4727ee9395f324cd2e7f331f';
        
        //driver Mocké = DRIVER_MCRYPT
        $mock = $this->getMockForAbstractClass(
                '\FMUP\Crypt\CryptInterface',
                array("unHash"),
                \FMUP\Crypt\Factory::DRIVER_MCRYPT
                );
        
        $mock->method('unHash')
           ->willReturn('abcd');
        
        $cache->setDriver($mock);
        
        $this->assertEquals(
                $mock->unHash($res), 
                $cache->unHash($res), 
                'unHashed values must be same'
                );
        $this->assertTrue(
                is_string($cache->unHash($res)), 
                "unHash must return a string"
                );    
 
        return $cache;
    }
     /**
     * @depends testConstruct
     * @return \FMUP\Crypt
     */
    public function testVerify(\FMUP\Crypt $cache) 
    {     
        //reinit the constructor with default driver
        $cache =  new \FMUP\Crypt();
        
        $test = array(
            array('abcd', 'e2fc714c4727ee9395f324cd2e7f331f', true),
            array('abcdef', 'e2fc714c4727ee9395f324cd2e7f331f', false),
        );
        foreach ($test as $password) {
        
        //vérifie que la méthode doit renvoyer un booléen
        $this->assertTrue(
                is_bool($cache->verify($password[0], $password[1])),
                "verify must return a boolean"
                );
        //vérifie que la méthode renvoie la valeur attendue
        $this->assertSame(
                $cache->verify($password[0], $password[1]),
                $password[2],
                "test verification failure"
                );
        }
        

    return $cache;
    }

}
