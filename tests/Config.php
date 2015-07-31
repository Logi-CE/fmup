<?php
class Config extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $config = new \FMUP\Config;
        $this->assertTrue($config instanceof \FMUP\Config);
    }
}