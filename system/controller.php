<?php
/**
 * Classe dont dérivent tous les contolleurs.
 * Elle gère notamment la connexion à la base de données et la vérification des droits
 * @version 1.0
 * @deprecated
 * @see \FMUP\Controller
 */
class Controller
{
    /**
     * Une instance de la classe de connexions aux bases de données
     */
    private $db_connection;

    /**
     * Allow construct rewrite
     */
    public function __construct()
    {
    }


    /**
     * Précharge une vue mais ne l'affiche pas (intéressant pour les sous-vues)
     * @param string $vue : Nom de la vue
     * @param array $params : Paramètres et variables à passer dans la vue
     */
    public function chargerVue ($vue, $params)
    {
        $params['popup'] = true;
        ob_start();
        new View($vue, $params);
        return ob_get_clean();
    }

    /**
     * Redirige vers une autre page
     * @param string $controlleur : l'URL interne sous la forme controlleur/fonction à laquelle accéder
     * @param bool $adresse : [OPT] VRAI si le paramètre précédent est une adresse externe, FAUX par défaut
     * @deprecated use \FMUP\Exception\Location instead
     */
    public static function redirect($controlleur = "", $adresse = false)
    {
        if (strpos($controlleur, '://') === false) {
            if ($controlleur{0} != '/') {
                $controlleur = '/' . $controlleur;
            }
        }
        header('Location: '.$controlleur);
        exit;
    }

    /**
     * Retourne l'URL gardé en mémoire lors de l'expiration de la session
     * @return string : L'URL
     */
    protected function getUrlSaved()
    {
        $url = '';
        //Récupération de la page enregistrée
        $url = $_SESSION['get_initial']['sys'];
        unset ($_SESSION['get_initial']['sys']);

        //Ajout des variables de get
        if (count($_SESSION['get_initial'] > 0)) {
            $url .= '?';
            $first = true;
            foreach ($_SESSION['get_initial'] as $key => $value) {
                if (!$first) $url .= '&';
                $url .= $key.'='.$value;
                $first = false;
            }
        }
        unset ($_SESSION['get_initial']);
        return $url;
    }

    /**
     * @return DbConnectionMssql|DbConnectionMysql|\FMUP\Db
     */
    public function getDb()
    {
        if (!$this->db_connection) {
            $param = Config::parametresConnexionDb();
            switch ($param['driver']) {
                case 'mssql':
                    $this->db_connection = DbHelper::get($param);
                    break;
                case 'mysql':
                    $this->db_connection = DbHelper::get($param);
                    break;
                default:
                    new Error('Moteur de base de donnée non paramétré');
            }
        }
        return $this->db_connection;
    }

    /**
     * @param DbConnectionMssql|DbConnectionMysql|\FMUP\Db $dbConnection
     * @return Controller
     */
    public function setDb($dbConnection)
    {
        $this->db_connection = $dbConnection;
        return $this;
    }

    /**
     * Affecte un message qui sera transmis à la page suivante (sauvergardé en session)
     * @param string $message : Le message à transmettre
     */
    public static function setFlash($flash)
    {
        \FMUP\FlashMessenger::getInstance()->add(new \FMUP\FlashMessenger\Message($flash));
    }

    /**
     * Renvoie le message envoyé par la dernière page
     * @return string : Le message
     */
    public static function getFlash()
    {
        return \FMUP\FlashMessenger::getInstance()->get();
    }
    
    /**
     * Efface le message sauvegardé en session
     */
    public static function clearFlash()
    {
        return \FMUP\FlashMessenger::getInstance()->clear();
    }

    /**
     * Fonction exécutée avant chaque accès au controlleur.
     */
    public function preFilter($calledAction = NULL)
    {
        // Si l'application nécéssite une connexion on vérifie les droits
        if (call_user_func(array(APP, "hasAuthentification"))) {
            global $sys_function;
            $calledAction = $calledAction === null ? $sys_function : $calledAction;
            $this->authorize($calledAction);
        }
    }

    /**
     * Fonction exécutée éventuellement après accès au controlleur.
     * Contrairement au préfiltre il n'est pas toujours exécuté.
     * (En cas de redirection dans le controlleur, de die(), d'erreur, ...)
     */
    public function postFilter($calledAction = NULL)
    {
        if (class_exists('Historisation')) Historisation::destroy();
        Controller::clearFlash();
    }

    /**
     * Fonction validant l'accès au site en vérifiant les droits et la connexion de l'utilisateur courant
     */
    private function authorize($calledAction)
    {
        global $sys_controller;
        global $sys_directory;

        DroitHelperApplication::authorizeRead($sys_controller, $calledAction, $sys_directory);
    }
}
