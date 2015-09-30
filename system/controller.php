<?php
/**
 * Classe dont dérivent tous les contolleurs.
 * Elle gère notamment la connexion à la base de données et la vérification des droits
 * @version 1.0
 * @deprecated use \FMUP\Controller instead
 */
class Controller
{
    /**
     * Une instance de la classe de connexions aux bases de données
     */
    private $db_connection;
    /**
     * Session instance
     * @var \FMUP\Session
     */
    private $session;
    private $bootstrap;
    /**
     * @var \FMUP\Request
     */
    private $request;

    /**
     * @var \FMUP\View
     */
    private $view;

    /**
     * Allow construct rewrite
     */
    public function __construct()
    {
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
     * @return $this
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
    public function preFilter($calledAction = null)
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
    public function postFilter($calledAction = null)
    {
        if (class_exists('Historisation')) Historisation::destroy();
        \FMUP\FlashMessenger::getInstance()->clear();
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

    /**
     * @return \FMUP\Session
     */
    public function getSession()
    {
        if (!$this->session) {
            $this->session = \FMUP\Session::getInstance();
        }
        return $this->session;
    }

    /**
     * @param \FMUP\Session $session
     * @return $this
     */
    public function setSession(\FMUP\Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @param String $vue
     * @param Array $params
     * @return string
     */
    public function chargerVue ($vue, $params)
    {
        $params['popup'] = true;
        foreach (array('php', 'phtml') as $ext) {
            $viewPath = implode(
                    DIRECTORY_SEPARATOR,
                    array(__DIR__, '..', '..', '..', '..', 'application', APPLICATION, 'view')
                ) . DIRECTORY_SEPARATOR . $vue . '.' . $ext;
            if (file_exists($viewPath)) {
                $view = new \FMUP\View($params);
                return $view->setViewPath($viewPath)->render();
            }
        }
        throw new \FMUP\Exception('View ' . $vue . ' does not exist');
    }

    /**
     * @return \FMUP\Bootstrap
     */
    public function getBootstrap()
    {
        if (!$this->bootstrap) {
            $this->bootstrap = new \FMUP\Bootstrap();
            $this->bootstrap
                ->setRequest($this->getRequest())
                ->warmUp();
        }
        return $this->bootstrap;
    }

    /**
     * @return \FMUP\Request
     */
    public function getRequest()
    {
        if (!$this->request) {
            $this->request = new \FMUP\Request();
        }
        return $this->request;
    }

    /**
     * @param \FMUP\Request $request
     * @return $this
     */
    public function setRequest(\FMUP\Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @throws \Exception
     * @return \FMUP\View
     */
    public function getView()
    {
        if (!$this->view) {
            $this->view = new \FMUP\View();
        }
        foreach (array('php', 'phtml') as $ext) {
            $view = implode(
                DIRECTORY_SEPARATOR,
                array(__DIR__, '..', '..', '..', '..', 'application', APPLICATION, 'view', 'layout', 'default')
            );
            $view .= '.' . $ext;
            if (file_exists($view)) {
                return $this->view->setViewPath($view);
            }
        }
        throw new \LogicException('View does not exist');
    }

    /**
     * @param string $viewPath
     * @param array $params
     * @param array $options
     * @throws \Exception
     * @deprecated use \FMUP\View instead
     */
    public function oldRendering($viewPath, array $params = array(), array $options = array())
    {
        if (!isset($params['popup'])) {
            $params['popup'] = false;
        }

        $view = new \FMUP\View($params);

        $basePaths = array(implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..')),);
        foreach ($basePaths as $basePath) {
            $filePath = implode(DIRECTORY_SEPARATOR, array($basePath, 'application', APPLICATION, 'view', $viewPath . '.php'));
            if (file_exists($filePath)) {
                $view->setViewPath($filePath);
                break;
            }
        }

        if ($params['popup']) {
            echo $view->render();
        } else {
            echo $this->getView()->addParams($params)->addParams($options)->setParam('vue', $view)->render();
        }
    }
}
