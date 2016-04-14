<?php
namespace FMUP;

/**
 * String handling class
 */
abstract class String
{
    /**
     * private construct - singleton
     */
    final private function __construct()
    {
    }

    /**
     * private clone - singleton
     */
    final private function __clone()
    {
    }

    /**
     * Convert a string from camelCase to snake_case
     */
    final public static function toSnakeCase($string)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1_$2', $string));
    }

    /**
     * Convert from snake_case to camelCase
     * @param string $string
     * @return mixed
     */
    final public static function toCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
    
    /**
     * Sanitize a string
     * @param string $string
     * @return string
     */
    final public static function sanitize($string)
    {
        $a = 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ@!?.:/\\,;:!?./-(){}"\'= ';
        $b = 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY_________________________';
        return strtr(utf8_decode($string), utf8_decode($a), $b);
    }
}
