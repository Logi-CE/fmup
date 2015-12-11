<?php
namespace FMUP;

/**
 * String handling class
 */
class String
{
    /**
     * @var $this
     */
    private static $instance;

    /**
     * private construct - singleton
     */
    private function __construct()
    {
    }

    /**
     * private clone - singleton
     */
    private function __clone()
    {
    }

    /**
     * @return $this
     */
    final public static function getInstance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    /**
     * Convert a string from camelCase to snake_case
     */
    public function toSnakeCase($string)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1_$2', $string));
    }

    /**
     * Convert from snake_case to camelCase
     * @param string $string
     * @return mixed
     */
    public function toCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
