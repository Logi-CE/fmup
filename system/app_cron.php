<?php
/**
 * Classe comprenant les paramètres de configuration de l'application
 **/

/**
 * Class AppCron
 * @deprecated not used anymore in FMUP
 */
class AppCron
{
    /**
     * Est-ce que l'application a une partie admin
     */
    public static function hasAuthentification()
    {
        return false;
    }
    /**
     * Le controlleur chargé par défaut par l'application
     **/
    public static function defaultController()
    {
        return 'cron/default_function';
        //return 'identification/login';
    }
    /**
     * Le controlleur chargé par défaut par l'application
     **/
    public static function authController()
    {
        //return '/home/index';
        return 'cron/default_function';
    }

    /**
     * Le layout par défaut de l'application
     **/
    public static function defaultLayout()
    {
        //return 'accueil';
        return 'default';
    }

    /**
     * Les CSS par défaut de l'application
     **/
    public static function defaultCSS()
    {
        //return array('system/default', 'system/style', 'system/layout', 'client/identification');
        return array('system/default', 'system/style', 'lib/calendar', 'system/layout');
    }

    /**
     * Les javascripts par défaut de l'application
     **/
    public static function defaultJavascripts()
    {
        return array('lib/mootools-1.2', 'lib/mootools-1.2-more', 'lib/calendar', 'navigation', 'interface', 'menu', 'fonctions');
        // 'lib/enhanceselect', 'frontend'
    }

    /**
     * La racine du site
     **/
    public static function defaultWWWroot()
    {
        return Config::paramsVariables('front_www_root');
    }
}
