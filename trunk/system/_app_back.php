<?php
/**
 * Classe comprenant les paramètres de configuration de l'application
 **/

class AppBack
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
        //return 'stats/stat/accueil';
        return 'home/index';
    }

    /**
     * Le layout par défaut de l'application
     **/
    public static function defaultLayout()
    {
        //return 'admin';
        return 'default';
    }

    /**
     * Les CSS par défaut de l'application
     **/
    public static function defaultCSS()
    {
        //return array('system/default', 'system/style', 'system/layout', 'client/identification');
        return array(
                      'system/defaut'
                    , 'system/style'
                    , 'system/layout'
                    , 'system/console_log'
                    , 'composants/calendar/jscal2'
                    , 'lib/MooDialog'
                    , 'lib/mooRainbow/mooRainbow'
                    );
    }


    /**
     * Le controlleur chargé par défaut par l'application
     **/
    public static function authController()
    {
        //return '/home/index';
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
     * Les javascripts par défaut de l'application
     **/
    public static function defaultJavascripts()
    {
        return array(
                      'lib/mootools-1.2'
                    , 'lib/mootools-1.2-more'
                    , 'navigation'
                    , 'interface'
                    , 'menu'
                    , 'fonctions'
                    , 'lib/calendar/jscal2'
                    , 'lib/calendar/lang/fr'
                    , 'lib/MooDialog/MooDialog'
                    , 'lib/MooDialog/MooDialog.Alert'
                    , 'lib/MooDialog/MooDialog.Confirm'
                    , 'lib/MooDialog/MooDialog.Error'
                    , 'lib/MooDialog/MooDialog.Iframe'
                    , 'lib/MooDialog/MooDialog.Prompt'
                    , 'lib/MooDialog/MooDialog.Request'
                    , 'lib/MooDialog/Overlay'
                    , 'lib/mooRainbow/mooRainbow'
                    );
        // 'lib/enhanceselect', 'frontend'
    }
}
