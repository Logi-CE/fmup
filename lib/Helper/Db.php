<?php
namespace FMUP\Helper;

/**
 * Class Db
 * @package FMUP\Helper
 */
abstract class Db
{
    /**
     * @var \FMUP\Db
     */
    protected static $instance;

    /**
     * @return \FMUP\Db
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new \FMUP\Db(\Config::parametresConnexionDb());
        }

        return self::$instance;
    }

    /**
     * Singleton - private construct
     */
    private function __construct()
    {

    }
}
