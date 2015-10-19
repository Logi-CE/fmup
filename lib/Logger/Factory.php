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
    final public function getChannel($channel)
    {
        $className = $this->getClassNameForChannel($channel);
        $instance = new $className();
        if (!$instance instanceof Channel) {
            throw new Exception('Channel ' . $channel . ' is not correctly formatted');
        }
        return $instance;
    }

    /**
     * Get channel full class name for a given channel
     * @param string $channel
     * @return string
     */
    protected function getClassNameForChannel($channel)
    {
        $className = '\FMUP\Logger\Channel\\' . ucfirst($channel);
        if (!class_exists($className)) {
            $className = '\FMUP\Logger\Channel\Standard';
        }
        return $className;
    }
}
