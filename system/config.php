<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__FILE__).'..'));
}
/**
 * Classe comprenant les paramètres de configuration de l'application
 **/
class Config extends ConfigApplication
{
    public static $instanceParams = array();

    /**
     * Constantes pouvant varier suivant le site
     * @param null $index
     * @return array
     * @throws Error
     */
    public static function paramsVariables($index = NULL)
    {
        if (empty(self::$instanceParams)) {
            $param_defaut = array();
            $param_defaut['app_port'] = '80'; // port par défaut.
            $param_defaut['ISOtoUTF8'] = false; // indique si on doit encoder les valeurs de la Bdd
            $param_defaut['maintenance_forcee'] = false;
            $param_defaut['maintenance_plages'] = array(); //jour / heure (-1 si parametre omis)
            $param_defaut['is_logue'] = false;
            $param_defaut['affichage_erreurs'] = true; // Affichage de l'erreur sur la page (false pour phpunit)
            $param_defaut['historisation_navigation'] = false; //historisation en BDD de toutes les URL appelées, dans la table hitorique_navigation
            $param_defaut['historisation_requete'] = false; //historisation en BDD de toutes les requetes lancées, dans la table hitorique_requetes
            $param_defaut['is_debug'] = true;
            // $param_defaut['mail_support']              = 'support@castelis.com';
            $param_defaut['mail_robot'] = 'no-reply@castelis.com';
            $param_defaut['mail_robot_name'] = 'Application CASTELIS';
            // $param_defaut['mail_reply']                  = 'support@castelis.com';
            // $param_defaut['mail_reply_name']          = 'CASTELIS';
            $param_defaut['id_castelis'] = 1;
            $param_defaut['id_cron'] = -1;
            $param_defaut['nom_version'] = '';
            $param_defaut['limite_mail_erreur'] = -1; // nb par minute (-1 correspond à pas de limitation)
            $param_defaut['is_multilingue'] = false; // Condition site multilingue
            $param_defaut['utilise_parametres'] = false; // Utilisation de la table de paramètrage
            $param_defaut['php_error_log'] = BASE_PATH . '/logs/php/error/%date%.log'; //PHP Error log

            $params = parent::setVariables($param_defaut);

            /* fichier config.ini est obligatoire (placé à la racine du site) mais ne doit surtout pas être intégré dans le SVN; il doit contenir les paramétrage d'accès à la BDD
            * un fichier d'exemple nommé config_exemple.ini indique les paramètres obligatoires à renseigner dans le fichier config.ini
            * Dans le cas des tests unitaires, le serveur aura pour nom 'phpunit' et nécessitera une connexion particulière.
            * Le fichier config_test.php (placé au même endroit que config.php) sera alors chargé à la place
            */
            if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'phpunit') {
                $nom_fichier_config = 'config_test.php';
            } else {
                $nom_fichier_config = 'config.php';
            }
            if (file_exists(dirname(__FILE__) . '/../' . $nom_fichier_config)) {
                include(dirname(__FILE__) . '/../' . $nom_fichier_config);
            }

            if (empty($params['mail_envoie_test'])) {
                // email à qui on envoie les mails dans le cas de TEST et de non envoi d'email de l'application
                // si non renseigné alors on envoie au support
                $params['mail_envoie_test'] = $params['mail_support'];
            }
            /**
             * FMUP daily alert
             */
            if (isset($params['use_daily_alert']) && $params['use_daily_alert']) {
                $param_defaut['affichage_erreurs'] = false;
            }
            self::$instanceParams = $params;
        }

        if (empty($index) || $index === false) {
            return self::$instanceParams;
        } else if (array_key_exists($index, self::$instanceParams)) {
            return self::$instanceParams[$index];
        } else {
            throw new Error(Error::configParamAbsent($index));
        }
    }

    public static function getVersionApplicationLibelle()
    {
        if (self::paramsVariables('version')=='prod') {
            return false;
        }
        $params = self::paramsVariables('parametres_connexion_db');
        $version_bdd = "BDD sur <span style=\"color:red;\">".$params['host']." [ ".$params['database']." ] </span> --- ";
        return $version_bdd.'<span style=\'color:red; _text-decoration:blink;\'>VERSION DE DEVELOPPEMENT</span>';

    }

    /**
     * Verifie si on peut envoyer un mail
     * @return bool
     * @throws Error
     */
    public static function isEnvoiMailPossible()
    {
        if (!self::paramsVariables('envoi_mail') && (self::paramsVariables('version') == 'prod')) {
            return true;
        } elseif (!self::paramsVariables('envoi_mail')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Debug
     */
    public static function consoleActive()
    {
        return self::isDebug() || (!empty($_SESSION['id_utilisateur']) && $_SESSION['id_utilisateur'] == self::idCastelis());
    }

    public static function isDebug()
    {
        return self::paramsVariables('is_debug');
    }

    public static function idCastelis()
    {
        return self::paramsVariables('id_castelis');
    }

    public static function idCron()
    {
        return self::paramsVariables('id_cron');
    }

    /**
     * doit-on loguer les changement dans la base de données (via l'application) ?
     */
    public static function isLogue()
    {
        return self::paramsVariables('is_logue');
    }

    /**
     * La racine du site
     **/
    public static function siteWWWRoot()
    {
        return call_user_func(array(APP, "defaultWWWroot")).":".self::getAppPort();
    }

    public static function getAppPort()
    {
        return self::paramsVariables('app_port')."/";
    }

    /* *
     * Chemin vers rep Data
     */
    public static function getCheminData()
    {
        return BASE_PATH."/data/";
    }

    /* *
     * Chemin vers rep temporaire de data
     */
    public static function getDataTempDirectory()
    {
        return BASE_PATH."/public/front/data/";
    }

    /* *
     * Chemin vers rep de templates
     */
    public static function getTemplatesDirectory()
    {
        return BASE_PATH."/public/front/templates/";
    }

    public static function timeLimit()
    {
        return 1200;
    }

    public static function memoryLimit()
    {
        return "540M";
    }

    public static function getMaxSize()
    {
        return 1024*1000*20;
    }
    public static function getTimeoutSessionId()
    {
        return 5*60;
    }


    public static function getNomExpediteur()
    {
        return "Framework";
    }


    /**
     * Nom du serveur SMTP utilisé
     */
    public static function smtpServeur()
    {
        return self::paramsVariables('smtp_serveur');
    }


    /**
     * Numero de port SMTP utilisé
     */
    public static function smtpPort()
    {
        return self::paramsVariables('smtp_port');
    }

    /**
     * Besoin d'authentification SMTP
     * @return true si une authentification est requise, false sinon
     */
    public static function smtpAuthentification()
    {
        return self::paramsVariables('smtp_authentification');
    }

    /**
     * Préfixe de connection SMTP
     * @return string
     */
    public static function smtpSecure()
    {
        return self::paramsVariables('smtp_secure');
    }

    /**
     * Login SMTP
     */
    public static function smtpUsername()
    {
        return self::paramsVariables('smtp_username');
    }
    /**
     * Mot de passe SMTP
     */
    public static function smtpPassword()
    {
        return self::paramsVariables('smtp_password');
    }
    /**
     * Nom du serveur LDAP pour connexion
     */
    public static function serveurLdap()
    {
        return self::paramsVariables('serveur_ldap');
    }
    /**
     * Port de connexion au serveur LDAP
     */
    public static function portConnexionLdap()
    {
        return self::paramsVariables('port_commexion_ldap');
    }
    /**
     * User pour la connexion au serveur LDAP
     */
    public static function userConnexionLdap()
    {
        return self::paramsVariables('user_connexion_ldap');
    }
    /**
     * Mot de passe pour la connexion au serveur LDAP
     */
    public static function passConnexionLdap()
    {
        return self::paramsVariables('mdp_connexion_ldap');
    }
    /**
     * Domaine racine du LDAP
     */
    public static function domaineRacineLdap()
    {
        return self::paramsVariables('domaine_racine_ldap');
    }
    /**
     * Nom de domaine racine du LDAP
     */
    public static function nomDomaineRacineLdap()
    {
        return self::paramsVariables('nom_domaine_racine_ldap');
    }

    /**
     * Nom de l'email From pour le mail d'erreur
     */
    public static function erreurMailFromName()
    {
        return self::paramsVariables('erreur_mail_from_name');
    }
    /**
     * Sujet du mail d'erreur
     */
    public static function erreurMailSubject()
    {
        return self::paramsVariables('erreur_mail_sujet');
    }


    /**
     * Mail support
     */
    public static function mailSupport()
    {
        return self::paramsVariables('mail_support');
    }
    /**
     * Mail à qui on envoi les mails dans le cas de TEST et de non envoie d'email de l'application
     */
    public static function mailEnvoieTest()
    {
        return self::paramsVariables('mail_envoie_test');
    }
    /**
     * Mail du robot qui envoie les mails
     **/
    public static function mailRobot()
    {
        return self::paramsVariables('mail_robot');
    }
    public static function mailRobotName()
    {
        return self::paramsVariables('mail_robot_name');
    }
    /**
     * Adresse où répondre aux mails
     **/
    public static function mailReply()
    {
        return self::paramsVariables('mail_reply');
    }
    public static function mailReplyName()
    {
        return self::paramsVariables('mail_reply_name');
    }

    /**
     * Liens vers site web du client
     **/
    public static function siteWebClient()
    {
        return self::paramsVariables('site_web_client');
    }

    /**
     * URL du front de l'appli
     */
    public static function urlFront()
    {
        return self::paramsVariables('url_front');
    }

    /**
     * Niveau d'error reporting
     */
    public static function errorReporting()
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            return E_ALL | E_STRICT;
        } else {
            return E_ALL;
        }
    }

    /**
     * Les paramètres de connexion à la base de données
     **/
    public static function parametresConnexionDb()
    {
        return self::paramsVariables('parametres_connexion_db');
    }

    /**
     * Numero de version de l'application
     * @return string
     */
    public static function version()
    {
        return self::paramsVariables('version_site');
    }
    /**
     * Nom de la version de l'application
     * @return string
     */
    public static function nomVersion()
    {
        return self::paramsVariables('nom_version');
    }

    /**
     * Ip locale pour la securisation du cron
     * @return string
     */
    public static function ipLocale()
    {
        return self::paramsVariables('ip_locale');
    }

    /**
     * retourne la page de non ouverture du site
     * @return string
     */
    public static function getMaintenancePlage()
    {
        try {
            return self::paramsVariables('maintenance_plages');
        } catch (Exception $e) {
            return false;
        }
    }
    /**
     * retourne sir le site est en maintenance actuellement
     * @return bool
     */
    public static function getMaintenanceForcee()
    {
        try {
            return (bool)self::paramsVariables('maintenance_forcee');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * pour savoir si on gère les sessions en bdd
     * @return bool
     */
    public static function getGestionSession()
    {
        $config = self::paramsVariables();
        if (isset($config['gestion_session_'.APPLICATION])) return $config['gestion_session_'.APPLICATION];
        if (isset($config['gestion_session'])) return $config['gestion_session'];
        return false;
    }

    /**
     * pour savoir si on gère les multi onglets
     */
    public static function getGestionMultiOnglet()
    {
        $config = self::paramsVariables();
        if (isset($config['mode_multi_onglet'])) return $config['mode_multi_onglet'];
        return false;
    }

    /**
     * Savoir si le site est multilingue
     */
    public static function getIsMultilingue()
    {
        return self::paramsVariables('is_multilingue');
    }

    public static function pathToPhpErrorLog($date = NULL)
    {
        $date = !is_null($date) ? strtotime($date) : time();
        return str_replace('%date%', date('Ymd', $date), self::paramsVariables('php_error_log'));
    }

    public static function useDailyAlert()
    {
        return (bool)self::paramsVariables('use_daily_alert');
    }
}
