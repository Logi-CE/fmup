<?php
/**
 * Classe comprenant les paramètres de configuration de l'application
 **/

/**
 * Class AppFront
 * @deprecated not used anymore in FMUP
 */
class AppFront
{
    /**
     * Est-ce que l'application a une partie admin
     */
    public static function hasAuthentification()
    {
        return true;
    }

    /**
     * La page 404
     **/
    public static function page404()
    {
        return 'accueil/erreur404';
    }

    /**
     * Le controlleur chargé par défaut par l'application
     **/
    public static function defaultController()
    {
        return 'home/index';
    }

    /**
     * Le controlleur chargé par défaut par l'application
     **/
    public static function authController()
    {
        return 'identification/login';
    }

    /**
     * Le controlleur utilisé en cas de fermeture de l'application
     **/
    public static function closedAppController()
    {
        return 'identification/closed_app';
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
        return array('system/default', 'system/style', 'lib/calendar', 'system/layout', 'lib/MooDialog');
    }

    /**
     * Les javascripts par défaut de l'application
     **/
    public static function defaultJavascripts()
    {
        return array(
            'lib/mootools-1.2',
            'lib/mootools-1.2-more',
            'lib/calendar',
            'navigation',
            'interface',
            'menu',
            'fonctions',
            'lib/MooDialog/MooDialog',
            'lib/MooDialog/MooDialog.Alert',
            'lib/MooDialog/MooDialog.Confirm',
            'lib/MooDialog/MooDialog.Error',
            'lib/MooDialog/MooDialog.Iframe',
            'lib/MooDialog/MooDialog.Prompt',
            'lib/MooDialog/MooDialog.Request',
            'lib/MooDialog/Overlay'
        );
    }

    /**
     * La racine du site
     **/
    public static function defaultWWWroot()
    {
        return Config::paramsVariables('front_www_root');
    }
}
