<?php
/**
 * Environment.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests;


class Environment extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \FMUP\Environment
     */
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass(\FMUP\Environment::class);
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\Environment::getInstance());
            $this->assertTrue(false, 'We must not be able to clone environments');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Environment::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $environment = \FMUP\Environment::getInstance();
        $this->assertInstanceOf(\FMUP\Environment::class, $environment, 'Instance of ' . \FMUP\Environment::class);
        $environment2 = \FMUP\Environment::getInstance();
        $this->assertSame($environment, $environment2, 'Must be same instance of the driver');
        return $environment;
    }

    /**
     * @param \FMUP\Environment $environment
     * @depends testGetInstance
     * @return \FMUP\Environment
     */
    public function testGet(\FMUP\Environment $environment)
    {
        try {
            $environment->get();
            $this->assertTrue(false, 'Get must send exception if no config is defined');
        } catch (\FMUP\Exception $e) {
            $this->assertEquals('No environment detected', $e->getMessage(), 'Exception message is not correct');
        }

        $configMock = $this->getMockBuilder(\FMUP\Config::class)->getMock();
        try {
            $environment->setConfig($configMock)->get();
            $this->assertTrue(false, 'Config is empty and does not contain version. It should not work');
        } catch (\FMUP\Exception $e) {
            $this->assertEquals('No environment detected', $e->getMessage(), 'Exception message is not correct');
        }
        $envDefined = 'unitTest';
        $configMock->method('has')->willReturn(true);
        $configMock->method('get')->willReturn($envDefined);

        $this->assertEquals($envDefined, $environment->get());
        define('ENVIRONMENT', 'EnvironmentByConstant');
        $this->assertEquals(ENVIRONMENT, $environment->get());
        return $environment;
    }
}
