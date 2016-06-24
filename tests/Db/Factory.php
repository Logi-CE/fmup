<?php
/**
 * Factory.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Db;

class FactoryMockDbFactory extends \FMUP\Db\Factory
{
    public function __construct()
    {

    }

    protected function getClassNameForDriver($driver)
    {
        return __NAMESPACE__ . '\\' . $driver;
    }
}

class DriverFailMock
{
}

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass(\FMUP\Db\Factory::class);
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\Db\Factory::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Db\Factory::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $factory = \FMUP\Db\Factory::getInstance();
        $this->assertInstanceOf(\FMUP\Db\Factory::class, $factory);
        $factory2 = \FMUP\Db\Factory::getInstance();
        $this->assertSame($factory, $factory2);
    }

    public function testCreateFailWhenClassDontExists()
    {
        $factory = new FactoryMockDbFactory();
        $this->expectException(\FMUP\Db\Exception::class);
        $this->expectExceptionMessageRegExp('~^Unable to create ~');
        $factory->create('NotExistingDriver');
    }

    public function testCreateFailWhenClassNotCorrect()
    {
        $factory = new FactoryMockDbFactory();
        $this->expectException(\FMUP\Db\Exception::class);
        $this->expectExceptionMessageRegExp('~^Unable to create ~');
        $factory->create('DriverFailMock');
    }

    public function testCreate()
    {
        $factory = \FMUP\Db\Factory::getInstance();
        $driverDefault = $factory->create();
        $this->assertInstanceOf(\FMUP\Db\DbInterface::class, $driverDefault);
        $this->assertInstanceOf(\FMUP\Db\Driver\Pdo::class, $driverDefault);
        $driverDefault2 = $factory->create();
        $this->assertEquals($driverDefault, $driverDefault2);
        $this->assertNotSame($driverDefault, $driverDefault2);
        $driverDefault3 = $factory->create(FactoryMockDbFactory::DRIVER_PDO, array('test' => 1));
        $this->assertNotEquals($driverDefault, $driverDefault3);
    }
}
