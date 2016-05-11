<?php
/**
 * Manager.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Db;

class ManagerMockDb extends \FMUP\Db\Manager
{
    public function __construct()
    {

    }
}

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass(\FMUP\Db\Manager::class);
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\Db\Manager::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Db\Manager::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $manager = \FMUP\Db\Manager::getInstance();
        $this->assertInstanceOf(\FMUP\Db\Manager::class, $manager);
        $manager2 = \FMUP\Db\Manager::getInstance();
        $this->assertSame($manager, $manager2);
    }

    public function testGetFailWhenNull()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name must be set');
        $manager = new ManagerMockDb();
        $manager->get('');
    }

    public function testGetFailWhenDatabaseDontExists()
    {
        $config = $this->getMock(\FMUP\Config::class);
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('Trying to access a database name test that not exists');
        $manager = $this->getMock(ManagerMockDb::class, array('getConfig'));
        $manager->method('getConfig')->willReturn($config);
        /** @var $manager ManagerMockDb */
        $manager->get('test');
    }

    public function testGetDefaultDb()
    {
        $config = $this->getMock(\FMUP\Config::class, array('get'));
        $config->expects($this->once())->method('get')->with($this->equalTo('parametres_connexion_db'))->willReturn(array());
        $manager = $this->getMock(ManagerMockDb::class, array('getConfig'));
        $manager->method('getConfig')->willReturn($config);
        /** @var $manager ManagerMockDb */
        $instance = $manager->get();
        $this->assertInstanceOf(\FMUP\Db::class, $instance);
        $this->assertSame($instance, $manager->get());
    }

    public function testGetOtherDb()
    {
        $config = $this->getMock(\FMUP\Config::class, array('get'));
        $config->expects($this->once())->method('get')->with($this->equalTo('db'))->willReturn(array('other' => array()));
        $manager = $this->getMock(ManagerMockDb::class, array('getConfig', 'hasLogger', 'getLogger'));
        $manager->method('getConfig')->willReturn($config);
        $manager->method('hasLogger')->willReturn(true);
        $manager->method('getLogger')->willReturn($this->getMock(\FMUP\Logger::class));
        /** @var $manager ManagerMockDb */
        $instance = $manager->get('other');
        $this->assertInstanceOf(\FMUP\Db::class, $instance);
        $this->assertSame($instance, $manager->get('other'));
    }
}
