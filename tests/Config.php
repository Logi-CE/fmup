<?php
class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $config = new \FMUP\Config;
        $this->assertInstanceOf('\FMUP\Config', $config, 'Instance of \FMUP\Config');
        return $config;
    }

    /**
     * @depends testConstruct
     * @param $config \FMUP\Config
     * @return \FMUP\Config
     */
    public function testClone(\FMUP\Config $config)
    {
        $config2 = clone $config;
        $this->assertEquals($config, $config2, "assert equals");
        $this->assertNotSame($config, $config2, "assert not same");
        return $config;
    }

    /**
     * @depends testConstruct
     * @param $config \FMUP\Config
     * @return \FMUP\Config
     */
    public function testSet(\FMUP\Config $config)
    {
        $config2 = clone $config;
        $config->set('test', 'test');
        $this->assertNotEquals($config, $config2, "set test");
        $config2 = clone $config;
        $config->set('2', 2);
        $this->assertNotEquals($config, $config2, "set 2 to int 2");
        $config2 = clone $config;
        $config->set('2', '3');
        $this->assertNotEquals($config, $config2, "set 2 to string 3");
        $config2 = clone $config;
        $config->set('test', NULL);
        $this->assertNotEquals($config, $config2, "set test to null");
        $config2 = clone $config;
        $config->set('notexist');
        $this->assertNotEquals($config, $config2, "set notexist to default param");
        return $config;
    }

    /**
     * @depends testSet
     * @param $config \FMUP\Config
     * @return \FMUP\Config
     */
    public function testHas(\FMUP\Config $config)
    {
        $this->assertInternalType('bool', $config->has('test'), "has return bool for test");
        $this->assertInternalType('bool', $config->has('boo'), "has return bool for boo");
        $this->assertFalse($config->has('test'), "test exists"); //test is set to null, must not exist anymore
        $this->assertTrue($config->has('2'), "2 exists"); //set to string 3
        $config->set(2);
        $this->assertFalse($config->has('2'), "2 not exists"); //set to string 3
        $this->assertFalse($config->has('notexist'), "notexist exists"); //set to default value
        $this->assertFalse($config->has('notexistanymore'), "notexist not exists"); //never set
    }

    /**
     * @depends testSet
     * @param $config \FMUP\Config
     * @return \FMUP\Config
     */
    public function testGet(\FMUP\Config $config)
    {
        $config->set(2, 2);
        $this->assertSame(2, $config->get(2), "2 contains int 2");
        $this->assertNotSame('2', $config->get(2), "2 contains string 2");
        $config->set(2, "bob");
        $this->assertSame("bob", $config->get(2), "2 contains string bob");
        $this->assertNull($config->get('notExist'), "test notExist");
        $config->set(2);
        $this->assertNull($config->get(2), "test removed value");
        $config->set('test', 'test');
        $config->set('foo', array('foo'));
        $config->set('bar', 3);
        $this->assertInternalType('array', $config->get(), "test retrieve all values");
        $this->assertArrayHasKey('test', $config->get(), "test test exist");
        $this->assertArrayHasKey('foo', $config->get(), "test foo exist");
        $this->assertArrayHasKey('bar', $config->get(), "test bar exist");
        $this->assertArrayNotHasKey('filler', $config->get(), "test filler not exist");
        $values = $config->get();
        $this->assertSame('test', $values['test'], "test all values - test");
        $this->assertSame(array('foo'), $values['foo'], "test all values - foo");
        $this->assertSame(3, $values['bar'], "test all values - bar");
        return $config;
    }

    /**
     * @depends testGet
     * @param $config \FMUP\Config
     * @return \FMUP\Config
     */
    public function testMergeConfig(\FMUP\Config $config)
    {
        $config2 = clone $config;
        $this->assertSame($config2->get(), $config->get(), "comparing config and config2 before merge");
        $config->mergeConfig();
        $this->assertSame($config2->get(), $config->get(), "comparing config and config2 after merge");
        $this->assertSame($config2->get('test'), $config->get('test'), "comparing config and config2 before merge - test");
        $config->mergeConfig(array('test' => 'not test'));
        $this->assertNotSame($config2->get('test'), $config->get('test'), "comparing config and config2 after merge - test");
        $config->set('test', 'test');
        $this->assertSame($config2->get('test'), $config->get('test'), "comparing config and config2 before merge - test");
        $config->mergeConfig(array('test' => 'not test'), true);
        $this->assertSame($config2->get(), $config->get(), "comparing config and config2 after merge with before param");
        return $config;
    }
}
