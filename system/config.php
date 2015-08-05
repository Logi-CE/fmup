<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..')));
}

/**
 * Classe comprenant les paramètres de configuration de l'application
 * @deprecated use \FMUP\Config\Framework instead
 */
class Config
{
    private static $instance;
    private $fmupConfig = null;
    private $inited = false;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    public function getFmupConfig()
    {
        if (!$this->fmupConfig) {
            $this->fmupConfig = new \FMUP\Config;
        }
        return $this->fmupConfig;
    }

    public function setFmupConfig(\FMUP\Config $config)
    {
        $this->fmupConfig = $config;
        return $this;
    }

    private function initDefault()
    {
        if (!$this->inited) {
            $param_defaut = array();
            // port par défaut.
            $param_defaut['app_port'] = '80';
            // Indique si les mots de passe sont cryptés en base, et si oui, l'application les encodera directement
            $param_defaut['mot_passe_crypte'] = true;
            // Force la page de maintenance
            $param_defaut['maintenance_forcee'] = false;
            //Fixe une plage de maintenance forcée. Format : jour / heure (-1 si parametre omis)
            $param_defaut['maintenance_plages'] = array();
            // Détermine si l'application utilise les logs
            $param_defaut['is_logue'] = false;
            // Chemin du dossier ou sont stockés les fichiers de log
            $param_defaut['log_path'] = BASE_PATH . '/log/';
            // Chemin physique vers les documents partagés ou générés
            $param_defaut['data_path'] = BASE_PATH . '/public/commun/documents/';
            // Chemin physique vers les fichiers templates
            $param_defaut['template_path'] = BASE_PATH . '/public/commun/templates/';
            // Chemin physique vers les fichiers de traduction
            $param_defaut['translate_path'] = BASE_PATH . '/data/translation/';
            // Chemin "SRC" des documents partagés et générés (pour les balises img)
            $param_defaut['data_src'] = '/documents/';
            // Utilisation de la table de paramètrage
            $param_defaut['utilise_parametres'] = false;
            // historisation en BDD de toutes les URL appelées, dans la table hitorique_navigation
            $param_defaut['historisation_navigation'] = false;
            // historisation en BDD de toutes les requetes lancées, dans la table hitorique_requetes
            $param_defaut['historisation_requete'] = false;
            // Mode DEBUG, il désactive les envois de mail et affiche les erreurs à l'écran. Il active la console à tous les utilisateurs
            $param_defaut['is_debug'] = true;
            // Affichage de l'erreur sur la page (false pour phpunit)
            $param_defaut['affichage_erreurs'] = true;
            // Nb de mails d'erreurs autorisés par minute (-1 correspond à pas de limitation)
            $param_defaut['limite_mail_erreur'] = -1;
            // Force l'envoi de mail sur les autres versions que "prod"
            $param_defaut['envoi_mail'] = false;
            // Mail d'envoi des mails de l'application
            $param_defaut['mail_robot'] = 'no-reply@castelis.com';
            // Nom des mails d'envoi de l'application
            $param_defaut['mail_robot_name'] = 'Application CASTELIS';
            // Mail de test qui se substitue à l'adresse classique en cas d'envoi impossible ou pour les tests
            $param_defaut['mail_envoi_test'] = 'castelis@castelis.local';
            // Mail de retour des mails envoyés par l'application
            $param_defaut['mail_reply'] = 'support@castelis.com';
            // Nom des mails de retour des mails envoyés par l'application
            $param_defaut['mail_reply_name'] = 'CASTELIS';
            // Mail support recevant les erreurs de l'application
            $param_defaut['mail_support'] = 'castelis@castelis.local';
            // Mail caché dans tous les envois et recevant tous les mails
            $param_defaut['mail_cache'] = '';
            // ID de l'utilisateur CASTELIS
            $param_defaut['id_castelis'] = 1;
            // ID de l'utilisateur CRON
            $param_defaut['id_cron'] = -1;
            // Condition site multilingue
            $param_defaut['is_multilingue'] = false;
            // indique si on doit encoder les valeurs de la Bdd
            $param_defaut['ISOtoUTF8'] = false;
            // Taille maximum autorisé pour un upload de fichier
            $param_defaut['taille_max_fichier'] = 1024 * 1000 * 20;
            // Nom du serveur de mail
            $param_defaut['smtp_serveur'] = 'smtp.castelis.local';
            // Numéro de port utilisé pour les mails
            $param_defaut['smtp_port'] = 25;
            // Indique si le serveur mail nécéssite une authentification
            $param_defaut['smtp_authentification'] = false;
            // "", "ssl" ou "tls"
            $param_defaut['smtp_secure'] = '';
            // Identifiant de connexion au serveur de mail
            $param_defaut['smtp_username'] = '';
            // Mot de passe de connexion au serveur de mail
            $param_defaut['smtp_password'] = '';
            //Nom de la version
            $param_defaut['nom_version'] = '';
            //Path vers le fichier de log d'erreur de PHP
            $param_defaut['php_error_log'] = BASE_PATH . '/logs/php/error/%date%.log';
            if (class_exists('ConfigApplication') && is_callable(array('ConfigApplication', 'setVariables'))) {
                $confApp = new ConfigApplication();
                $params = $confApp->setVariables($param_defaut);
            } else {
                $params = $param_defaut;
            }
            /* fichier config.ini est obligatoire (placé à la racine du site) mais ne doit surtout pas être intégré dans le SVN; il doit contenir les paramétrage d'accès à la BDD
            * un fichier d'exemple nommé config_exemple.ini indique les paramètres obligatoires à renseigner dans le fichier config.ini
            * Dans le cas des tests unitaires, le serveur aura pour nom 'phpunit' et nécessitera une connexion particulière.
            * Le fichier config_test.ini (placé au même endroit que config.ini) sera alors chargé à la place
            */
            if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'phpunit') {
                $nom_fichier_config = 'config_test.php';
            } else {
                $nom_fichier_config = 'config.php';
            }
            if (file_exists(BASE_PATH.'/' . $nom_fichier_config)) {
                include_once(BASE_PATH.'/' . $nom_fichier_config);
            }
            if (empty($params['mail_envoi_test'])) {
                // email à qui on envoie les mails dans le cas de TEST et de non envoi d'email de l'application
                // si non renseigné alors on envoie au support
                $params['mail_envoi_test'] = $params['mail_support'];
            }
            /**
             * FMUP daily alert
             */
            if (isset($params['use_daily_alert']) && $params['use_daily_alert']) {
                $params['affichage_erreurs'] = false;
            }
            $this->getFmupConfig()->mergeConfig($params, true);
            $this->inited = true;
        }
        return $this;
    }

    protected static $instanceParams = array();

    public function get($name = null)
    {
        return $this->initDefault()->getFmupConfig()->get($name);
    }

    public function has($name)
    {
        return $this->initDefault()->getFmupConfig()->has($name);
    }

    /**
     * Constantes pouvant varier suivant le site
     * @param bool|false $index Paramètre demandé, laisser à FAUX pour avoir tous les paramètres
     * @return array|null|mixed Le paramètre de retour OU un tableau contenant tous les paramètres
     * @throws Error
     */
    public static function paramsVariables($index = false)
    {
        if ($index === false || empty($index)) {
            return self::getInstance()->get();
        } elseif (self::getInstance()->has($index)) {
            return self::getInstance()->get($index);
        } else {
            throw new Error(Error::configParamAbsent($index));
        }
    }

    public static function getCheminData()
    {
        return BASE_PATH . '/data/';
    }

    /**
     * Détermine si un envoi de mail est possible, par défaut seulement en production ou en réécrivant le paramètre envoi_mail
     * @return bool : VRAI si envoi possible
     */
    public static function isEnvoiMailPossible()
    {
        if (!self::getInstance()->get('envoi_mail') && (self::getInstance()->get('version') == 'prod')) {
            return true;
        } elseif (!self::getInstance()->get('envoi_mail')) {
            return false;
        } else {
            return true;
        }
    }

    public static function grainSel()
    {
        return 'dS7vNfuVHj';
    }

    public static function crypter($chaine, $grain_sel = '')
    {
        return sha1(self::grainSel() . $chaine . $grain_sel);
    }

    /**
     * Debug
     */
    public static function consoleActive()
    {
        return self::isDebug() || (!empty($_SESSION['id_utilisateur']) && $_SESSION['id_utilisateur'] == self::getInstance()->get('id_castelis'));
    }

    public static function isDebug()
    {
        return self::getInstance()->get('is_debug');
    }


    /**
     * La racine du site
     */
    public static function siteWWWRoot()
    {
        return call_user_func(array(APP, "defaultWWWroot")) . ":" . self::getAppPort();
    }

    public static function getAppPort()
    {
        return self::getInstance()->get('app_port') . "/";
    }

    /**
     * Durée avant expiration de la session filtre-liste
     * @return int : Secondes
     */
    public static function getTimeoutSessionId()
    {
        return 24 * 60;
    }

    /**
     * Niveau d'error reporting
     */
    public static function errorReporting()
    {
        if (self::isDebug()) {
            return E_ALL;
        } else {
            return 0;
        }
    }

    /**
     * Défini si le site est ouvert au public
     * @return boolean
     */
    public static function siteOuvert()
    {
        $retour = true;
        if (self::getInstance()->get('maintenance_forcee')) {
            $retour = false;
        }

        $day_number = date('w');
        $heure = date('H');
        foreach (self::getInstance()->get('maintenance_plages') as $plage) {
            list($var_jour, $var_heure_debut, $var_heure_fin) = $plage;
            if ($var_jour == -1) $var_jour = $day_number;
            if ($var_heure_debut == -1) $var_heure_debut = $heure;
            if ($var_heure_fin == -1) $var_heure_fin = $heure;
            if ($day_number == $var_jour && $heure <= $var_heure_fin && $heure >= $var_heure_debut) {
                $retour = false;
            }
        }

        if (self::getInstance()->get('utilise_parametres') && ParametreHelper::getInstance()->trouver('Maintenance')) {
            $retour = false;
        }

        return $retour;
    }

    /**
     * Les paramètres de connexion à la base de données
     * @param string $libelle : [OPT] Nom du paramètre demandé, FAUX par défaut
     * @return string|array
     */
    public static function parametresConnexionDb($libelle = false)
    {
        $params = self::getInstance()->get('parametres_connexion_db');
        if ($libelle) {
            $params = $params[$libelle];
        }
        return $params;
    }

    /**
     * pour savoir si on gère les sessions en bdd
     */
    public static function getGestionSession()
    {
        $config = self::getInstance()->get();
        if (isset($config['gestion_session_' . APPLICATION])) {
            return $config['gestion_session_' . APPLICATION];
        } elseif (isset($config['gestion_session'])) {
            return $config['gestion_session'];
        }
        return false;
    }

    /**
     * pour savoir si on gère les multi onglets
     */
    public static function getGestionMultiOnglet()
    {
        $config = self::getInstance()->get();
        if (isset($config['mode_multi_onglet'])) {
            return $config['mode_multi_onglet'];
        }
        return false;
    }

    public static function pathToPhpErrorLog($date = null)
    {
        $date = !is_null($date) ? strtotime($date) : time();
        return str_replace('%date%', date('Ymd', $date), self::getInstance()->get('php_error_log'));
    }

    public static function useDailyAlert()
    {
        return (bool) self::getInstance()->get('use_daily_alert');
    }
}
