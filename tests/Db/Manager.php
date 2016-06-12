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
        $reflector = new \ReflectionClass('\FMUP\Db\Manager');
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
        $this->assertInstanceOf('\FMUP\Db\Manager', $manager);
        $manager2 = \FMUP\Db\Manager::getInstance();
        $this->assertSame($manager, $manager2);
    }

    public function testGetFailWhenNull()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Name must be set');
        $manager = new ManagerMockDb();
        $manager->get('');
    }

    public function testGetFailWhenDatabaseDontExists()
    {
        $config = $this->getMockBuilder('\FMUP\Config')->getMock();
        $this->setExpectedException('\OutOfRangeException', 'Trying to access a database name test that not exists');
        $manager = $this->getMockBuilder('\Tests\Db\ManagerMockDb')->setMethods(array('getConfig'))->getMock();
        $manager->method('getConfig')->willReturn($config);
        /** @var $manager ManagerMockDb */
        $manager->get('test');
    }

    public function testGetDefaultDb()
    {
        $config = $this->getMockBuilder('\FMUP\Config')->setMethods(array('get'))->getMock();
        $config->expects($this->once())->method('get')->with($this->equalTo('parametres_connexion_db'))->willReturn(array());
        $manager = $this->getMockBuilder('\Tests\Db\ManagerMockDb')->setMethods(array('getConfig'))->getMock();
        $manager->method('getConfig')->willReturn($config);
        /** @var $manager ManagerMockDb */
        $instance = $manager->get();
        $this->assertInstanceOf('\FMUP\Db', $instance);
        $this->assertSame($instance, $manager->get());
    }

    public function testGetOtherDb()
    {
        $config = $this->getMockBuilder('\FMUP\Config')->setMethods(array('get'))->getMock();
        $config->expects($this->once())->method('get')->with($this->equalTo('db'))->willReturn(array('other' => array()));
        $manager = $this->getMockBuilder('\Tests\Db\ManagerMockDb')
            ->setMethods(array('getConfig', 'hasLogger', 'getLogger'))
            ->getMock();
        $manager->method('getConfig')->willReturn($config);
        $manager->method('hasLogger')->willReturn(true);
        $manager->method('getLogger')->willReturn($this->getMockBuilder('\FMUP\Logger')->getMock());
        /** @var $manager ManagerMockDb */
        $instance = $manager->get('other');
        $this->assertInstanceOf('\FMUP\Db', $instance);
        $this->assertSame($instance, $manager->get('other'));
    }
}
