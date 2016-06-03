<?php
/**
 * String.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;


class StringHandlingTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $reflection = new \ReflectionMethod(\FMUP\StringHandling::class, '__construct');
        $this->assertTrue($reflection->isPrivate());
        $this->assertTrue($reflection->isFinal());

        $reflection = new \ReflectionMethod(\FMUP\StringHandling::class, '__clone');
        $this->assertTrue($reflection->isPrivate());
        $this->assertTrue($reflection->isFinal());
    }

    public function testToSnakeCase()
    {
        $tests = array(
            'tosnakecase' => 'tosnakecase',
            'toSnakeCase' => 'to_snake_case',
            'SnakeCase' => 'snake_case',
            'SNAKE_CASE' => 's_n_a_k_e_c_a_s_e',
            'SNAKECASE' => 's_n_a_k_e_c_a_s_e',
        );
        foreach ($tests as $toConvert => $converted) {
            $this->assertSame($converted, \FMUP\StringHandling::toSnakeCase($toConvert));
        }
    }

    public function testToCamelCase()
    {
        $tests = array(
            'tocamelcase' => 'Tocamelcase',
            'toCamelCase' => 'ToCamelCase',
            'to_Camel_Case' => 'ToCamelCase',
            'To_Camel_Case' => 'ToCamelCase',
            'to_camel_case' => 'ToCamelCase',
        );
        foreach ($tests as $toConvert => $converted) {
            $this->assertSame($converted, \FMUP\StringHandling::toCamelCase($toConvert));
        }
    }

    public function testSanitize()
    {
        $a = 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ@!?.:/,;-(){}"= \'\\';
        $b = 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY__________________';
        $this->assertSame($b, \FMUP\StringHandling::sanitize($a));
    }
}
