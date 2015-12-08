<?php
namespace FMUP\Request;

use FMUP\Request;

class Cli extends Request
{
    const SHORT = 'SHORT';
    const LONG = 'LONG';

    private $opt = array(self::SHORT => '', self::LONG => array('route:'));

    /**
     * @param string $short
     * @param array $long
     * @return $this
     */
    public function defineOpt($short = '', array $long = array())
    {
        $long[] = 'route:';
        $this->opt = array(
            self::SHORT => (string)$short,
            self::LONG => (array)$long,
        );
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $defaultValue
     * @return string|mixed
     */
    public function get($name, $defaultValue = null)
    {
        $options = $this->getOpt();
        return isset($options[$name]) ? $options[$name] : $defaultValue;
    }

    /**
     * @return array
     */
    public function getOpt()
    {
        return getopt($this->opt[self::SHORT], $this->opt[self::LONG]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->getOpt()[$name]);
    }

    /**
     * @param bool|false $withQuerySting
     * @return string
     */
    public function getRequestUri($withQuerySting = false)
    {
        $return = $this->get('route');
        if (isset($_SERVER['argc']) && $_SERVER['argc'] > 2 && $withQuerySting) {
            $args = $_SERVER['argv'];
            unset ($args[0]);
            $return = implode(' ', $args);
        }
        return $return;
    }
}
