<?php
/**
 * Classe comprenant les paramètres de configuration de l'application
 * @deprecated override \FMUP\Config instead and set in your \FMUP\Bootstrap
 */
class Config
{
    use \FMUP\Config\OptionalTrait {
        getConfig as getFmupConfig;
        setConfig as setFmupConfig;
    }
    private static $instance;
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

    private function getBashPath()
    {
        return __DIR__ . '/../../../../';
    }

    private function getParamDefaut()
    {

        $param_defaut = array(
            // Mode DEBUG, il désactive les envois de mail et affiche les erreurs à l'écran. Il active la console
            'is_debug' => true,
            'envoi_mail' => false,// Force l'envoi de mail sur les autres versions que "prod"
            'mail_robot' => 'no-reply@castelis.com',// Mail d'envoi des mails de l'application
            'mail_robot_name' => 'Application CASTELIS',// Nom des mails d'envoi de l'application
            // Mail de test qui se substitue à l'adresse classique en cas d'envoi impossible ou pour les tests
            'mail_reply' => 'support@castelis.com',// Mail de retour des mails envoyés par l'application
            'mail_reply_name' => 'CASTELIS',// Nom des mails de retour des mails envoyés par l'application
            'mail_support' => 'castelis@castelis.local',// Mail support recevant les erreurs de l'application
            'taille_max_fichier' => 1024 * 1000 * 20,// Taille maximum autorisé pour un upload de fichier
            'smtp_serveur' => 'smtp.castelis.local',// Nom du serveur de mail
            'smtp_port' => 25,// Numéro de port utilisé pour les mails
            'smtp_authentification' => false,// Indique si le serveur mail nécéssite une authentification
            'smtp_secure' => '',// "", "ssl" ou "tls"
            'smtp_username' => '',// Identifiant de connexion au serveur de mail
            'smtp_password' => '',// Mot de passe de connexion au serveur de mail
            'nom_version' => '',//Nom de la version
            'php_error_log' => $this->getBashPath() . '/logs/php/error/%date%.log',//Path vers le fichier de log d'erreur de PHP
        );
        return $param_defaut;
    }

    private function initDefault()
    {
        if (!$this->inited) {
            $param_defaut = $this->getParamDefaut();
            if (class_exists('ConfigApplication') && is_callable(array('ConfigApplication', 'setVariables'))) {
                $confApp = new ConfigApplication();
                $params = $confApp->setVariables($param_defaut);
            } else {
                $params = $param_defaut;
            }
            /*
             * fichier config.ini est obligatoire (placé à la racine du site)
             * mais ne doit surtout pas être intégré dans le SVN;
             * il doit contenir les paramétrage d'accès à la BDD
             * un fichier d'exemple nommé config_exemple.ini indique les paramètres obligatoires à
             * renseigner dans le fichier config.ini.
             * Dans le cas des tests unitaires, le serveur aura pour nom 'phpunit'
             * et nécessitera une connexion particulière.
             * Le fichier config_test.ini (placé au même endroit que config.ini) sera alors chargé à la place
            */
            $nom_fichier_config = (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'phpunit')
                ? 'config_test.php'
                : 'config.php';

            if (file_exists($this->getBashPath() . '/' . $nom_fichier_config)) {
                include_once($this->getBashPath() . '/' . $nom_fichier_config);
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
     * @throws \FMUP\Exception
     */
    public static function paramsVariables($index = false)
    {
        if ($index === false || empty($index)) {
            return self::getInstance()->get();
        } elseif (self::getInstance()->has($index)) {
            return self::getInstance()->get($index);
        } else {
            throw new \FMUP\Exception("Paramètre de Config absent : $index");
        }
    }

    public static function getCheminData()
    {
        return self::getBashPath() . '/data/';
    }

    /**
     * Détermine si un envoi de mail est possible,
     * par défaut seulement en production ou en réécrivant le paramètre envoi_mail
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

    public static function isDebug()
    {
        return self::getInstance()->get('is_debug');
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
        return (bool)self::getInstance()->get('use_daily_alert');
    }
}
