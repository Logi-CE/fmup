<?php
/**
 * Ini.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Config;


class IniTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigFailWhenFileDontExist()
    {
        $notExistingPath = '/not/existing/path';
        $this->expectException(\FMUP\Config\Exception::class);
        $this->expectExceptionMessage('File does not exist');
        $config = new \FMUP\Config\Ini($notExistingPath);
        $config->get('test');
    }

    public function testGetSetHas()
    {
        $file = php_ini_loaded_file();
        $config = new \FMUP\Config\Ini($file);
        $this->assertFalse($config->has('test'));
        $this->assertNull($config->get('test'));
        $this->assertTrue($config->has('max_execution_time'));
        $this->assertTrue(is_array($config->get()));
        $this->assertNotNull($config->get('max_execution_time'));
        $this->assertSame($config, $config->set('test', 1));
        $this->assertTrue($config->has('test'));
        $this->assertSame(1, $config->get('test'));
    }

    public function testMerge()
    {
        $file = php_ini_loaded_file();
        $config = new \FMUP\Config\Ini($file);
        $this->assertTrue($config->has('max_execution_time'));
        $this->assertNotSame(1234, $config->get('max_execution_time'));
        $config->mergeConfig(array('max_execution_time' => 1234));
        $this->assertSame(1234, $config->get('max_execution_time'));

        $config = new \FMUP\Config\Ini($file);
        $this->assertTrue($config->has('max_execution_time'));
        $this->assertFalse($config->has('test'));
        $oldValue = $config->get('max_execution_time');
        $this->assertNotSame(1234, $oldValue);
        $config->mergeConfig(array('max_execution_time' => 1234, 'test' => true), true);
        $this->assertNotSame(1234, $config->get('max_execution_time'));
        $this->assertSame($oldValue, $config->get('max_execution_time'));
        $this->assertTrue($config->has('test'));
    }

    public function testSection()
    {
        $file = php_ini_loaded_file();
        $config = new \FMUP\Config\Ini($file);
        $this->assertTrue($config->has('max_execution_time'));
        $config = new \FMUP\Config\Ini($file, 'notExistingSection');
        $this->assertFalse($config->has('max_execution_time'));
    }
}
