<?php
namespace FMUP\Request;

use FMUP\Request;

class Cli extends Request
{
    /**
     * @param string $name
     * @param mixed $defaultValue
     * @return string|mixed
     */
    public function get($name, $defaultValue = null)
    {
        $short = '';
        $long = array();
        if (strlen($name) == 1) {
            $short = $name . ':';
        }  else {
            $long = array($name . ':');
        }
        $options = getopt($short, $long);
        return isset($options[$name]) ? $options[$name] : $defaultValue;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        $short = '';
        $long = array();
        if (strlen($name) == 1) {
            $short = $name . ':';
        }  else {
            $long = array($name . ':');
        }
        $options = getopt($short, $long);
        return isset($options[$name]);
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
