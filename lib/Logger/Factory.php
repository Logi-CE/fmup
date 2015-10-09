<?php
namespace FMUP\Logger;

/**
 * Class Factory
 * @package FMUP\Logger
 */
class Factory
{
    private static $instance;

    private function __construct()
    {

    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    private function __clone()
    {

    }

    /**
     * @param string $channel Channel to construct
     * @return Channel
     * @throws Exception
     */
    public function getChannel($channel)
    {
        $className = '\FMUP\Logger\Channel\\' . ucfirst($channel);
        if (!class_exists($className)) {
            $className = '\FMUP\Logger\Channel\Standard';
        }
        $instance = new $className();
        if (!$instance instanceof Channel) {
            throw new Exception('Channel ' . $channel . ' is not correctly formatted');
        }
        return $instance;
    }
}
