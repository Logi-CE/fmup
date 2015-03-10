<?php
/**
 * Classe dont dérivent tous les contolleurs.
 **/

class Controller
{
    /**
     * Une instance de la classe de connexions au bases de données
     **/
    private $db_connection;
    /**
     * Une instance des droits
     */
    private $droits;

    /**
     * Allow construct rewrite
     */
    public function __construct()
    {

    }


    /**
     * Précharge une vue mais ne l'affiche pas (intéressant pour les sous-vues)
     * @param String $vue
     * @param Array $params
     */
    public static function chargerVue ($vue, $params)
    {
        $params['popup'] = true;
        ob_start();
        new View($vue, $params);
        return ob_get_clean();
    }

    /**
     * Redirige vers une autre page
     * @param {String} le controlleur+action auquel accéder
     * @param {Boolean} si le paramètre précédent est une adresse et pas un controlleur
     **/
    public static function redirect($controlleur = "", $adresse = false)
    {
        if ($adresse) {
            header('Location: '.$controlleur);
        } else {
            header('Location: /'.$controlleur);
        }
        die();
    }

    public function getUrlSaved()
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

/* *****************
* Base de données *
***************** */
    /**
     * @return DbConnection|DbConnectionMysql|\FMUP\Db
     */
    public function getDb()
    {
        if (!$this->db_connection) {
            $param = Config::parametresConnexionDb();
            switch ($param['driver']) {
                case 'mysql':
                    $this->db_connection = DbHelper::get($param);
                    break;
                case 'mssql':
                    $this->db_connection = DbHelper::get($param);
                    break;
                default:
                    new Error('Moteur de base de données non paramétré');
            }
        }
        return $this->db_connection;
    }

    /**
     * @param DbConnection|DbConnectionMysql|\FMUP\Db $dbConnection
     * @return Controller
     */
    public function setDb($dbConnection)
    {
        $this->db_connection = $dbConnection;
        return $this;
    }

/* **************************************************
* Gestion des flash (messages d'une page à l'autre *
************************************************** */
    /**
     * Affecte un message qui sera transmis à la page suivante
     * @param {String} $message Le message à transmettre
     */
    public static function setFlash($flash, $erreur = false)
    {
        $_SESSION['flash'] = $flash;
        // Couleur du flash
        if ($erreur == true) {
            $_SESSION['class_flash'] = "erreur";
        }
    }

    /**
     * Renvoie le message envoyé par la dernière page
     */
    public static function getFlash()
    {
        $flash = '';
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
        }
        return $flash;
    }
    /**
     * Renvoie la classe du dernier message flash
     */
    public static function getClassFlash()
    {
        $class = '';
        if (isset($_SESSION['class_flash'])) {
            $class = $_SESSION['class_flash'];
        }
        return $class;
    }
    /**
     * Efface le message de la dernière page
     */
    public static function clearFlash()
    {
        unset($_SESSION['flash']);
        unset($_SESSION['class_flash']);
    }

/* *********************
* Pré et post filtres *
***********************/
    /**
     * Filtre exécuté avant chaque accès au controlleur.
     */
    public function preFiltre($calledAction = null)
    {
        if (call_user_func(array(APP, "hasAuthentification"))) {
            $this->authorize($calledAction);
        }
    }

    public function getDroits()
    {
        return $this -> droits;
    }
    /**
     * Filtre exécuté éventuellement après accès au controlleur.
     * Contrairement au préfiltre il n'est pas toujours exécuté.
     * (En cas de redirection dans le controlleur, de die(), d'erreur, ...)
     */
    public function postFiltre()
    {
        if (class_exists('Historisation')) Historisation::destroy();
        Controller::clearFlash();
    }

/* ******************
* Authentification *
****************** */
    /**
     * Fonction validant l'accès au site backend (utilisateur enregistré et tout et tout).
     */
    private function authorize($calledAction = null)
    {
        global $sys_controller;
        global $sys_function;
        global $sys_directory;

        $calledAction = $calledAction === null ? $sys_function : $calledAction;

        DroitHelperApplication::authorize($sys_controller, $calledAction, $sys_directory);

    }
}
