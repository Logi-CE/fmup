<?php
/**
 * Patch with old system that passes all request URI in a sys param (GET + REQUEST)
 * @deprecated
 * @todo refactor to avoid use of this code
 */
if (!isset($_GET['sys'])) {
    $result = preg_match('~/([^&?]+)((\&|\?).*)?$~', $_SERVER['REQUEST_URI'], $matches);
    $_GET['sys'] = isset($matches[1]) ? $matches[1] : '';
    if (isset($matches[2])) {
        $url = ltrim($matches[2], '&?');
        parse_str($url, $urlDecoded);
        $_GET = $_REQUEST = array_merge($_REQUEST, $_GET, $urlDecoded);
    }
    $_REQUEST['sys'] = $_GET['sys'];
}
/**
 * Page principale du site
 **/
date_default_timezone_set("Europe/Paris");

require_once('autoload.php');

$sys_directory = null;
$sys_controller = null;
$sys_function = null;
$sys_controller_instance = null;

/**
 * Classe d'initialisation du framework
 */
class Framework
{
    public function initialize ()
    {
        global $sys_directory;
        global $sys_controller;
        global $sys_function;

        if (!defined('APPLICATION')) {
            throw new Error("La variable APPLICATION doit être définie.");
        } else {
            define('APP', "App".String::toCamlCase(APPLICATION));
        }

        // On détermine le niveau d'erreur
        error_reporting(Config::errorReporting());
        ini_set('display_errors', Config::isDebug());
        ini_set('display_startup_errors', Config::isDebug());


        // On fixe les fonctions appelées lors d'une erreur
        $this->defineErrorLog();
        $this->registerErrorHandler();
        $this->registerShutdownFunction();
        $this->instantiateSession();

        // On allume la console des logs
        Console::initialiser();

        //log des pages
        $url = '';
        if(isset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'])) $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if (Config::isDebug()) {
            if (isset($_SESSION['id_utilisateur'])) {
                FileHelper::fLog($url, 'URL_'.$_SESSION['id_utilisateur']);
                FileHelper::fLog($url."\r\n".print_r($_REQUEST, 1), 'POST_'.$_SESSION['id_utilisateur']);
            } else {
                FileHelper::fLog($url, 'URL');
                FileHelper::fLog($url."\r\n".print_r($_REQUEST, 1), 'POST');
            }
        }
        $this->preDispatch();
        $this->dispatch();

        // historisation
        HistoriqueHelper::stockageHistoriqueNavigation($sys_directory, $sys_controller, $sys_function);
    }

    /**
     * Allow overwriting of an eventual pre treatment
     */
    protected function preDispatch()
    {
    }

    /**
     * @return $this
     * @throws NotFoundError
     */
    protected function dispatch()
    {
        list($controllerName, $action) = $this->getRoute();
        $controllerInstance = $this->instantiate($controllerName, $action);
        $this->postDispatch($controllerInstance);
        return $this;
    }

    /**
     * Allow overwriting of an eventual post treatment
     * @param Controller $controller
     */
    protected function postDispatch(Controller $controller)
    {
    }

    protected function instantiate($sys_controller, $sys_function)
    {
        global $sys_controller_instance;
        // Création d'une instance du controlleur
        $controller_name = String::toCamlCase($sys_controller);

        $db = null;
        if (!is_null($sys_controller_instance)) {
            $db = $sys_controller_instance->getDb();
        }
        /** @var $sys_controller_instance Controller */
        $sys_controller_instance = new $controller_name();
        if (!is_null($db)) {
            $sys_controller_instance->setDb($db);
        }
        
        // Préfiltre
        $sys_controller_instance->preFiltre($sys_function);

        
        // Si la fonction peut être appelée on l'appelle
        if (method_exists($sys_controller_instance, $sys_function)) {

            call_user_func(array($sys_controller_instance, $sys_function));
        // Sinon on appelle la page 404
        } else {
            throw new NotFoundError(Error::fonctionIntrouvable($sys_function));
        }

        // Postfiltre
        $sys_controller_instance->postFiltre();
        return $sys_controller_instance;
    }

    protected function instantiateSession()
    {
        if (Config::getGestionSession()) {
            $session = new HlpSessions('BACK');
            session_set_save_handler(array(&$session, 'open'), array(&$session, 'close'), array(&$session, 'read'), array(&$session, 'write'), array(&$session, 'destroy'), array(&$session, 'gc'));
        }

        /*
         * bloc utilisé pour l'activation de session crée sur un autre domaine
         * --> reprise d'une session forcée
         */
        if (isset($_REQUEST['psid']) && $_REQUEST['psid'] != '') {
            $multi_onglet = Config::getGestionMultiOnglet();

            //on recharge la session par celle en parametre
            session_id($_REQUEST['psid']);
            session_start();
            $old_session = $_SESSION;
            if ($multi_onglet) $old_session["window.name"] = date('YmdHis');

            session_regenerate_id();
            $_SESSION = $old_session;

            if ($multi_onglet) {
                //specifique
                if (isset($_SESSION['utilisateur'])) {
                    $_SESSION['utilisateur']->setCookie($_SESSION['utilisateur']->getMatricule(), $_SESSION['utilisateur']->getId(), $_SESSION['utilisateur']->getPassword());
                }
            }

            $uri = (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'/');
            $uri = str_replace($_REQUEST['psid'], '', $uri); // pour ne pas boucler

            header('Location: '.$uri);
            exit();
        } else {
            session_start();
        }
    }

    /**
     * On déclare la fonction permettant de remplacer une erreur par une exception
     * Cette méthode permet de gérer de la même manière des erreurs et les exceptions
     */
    public function errorToException($code, $msg, $file, $line, $context)
    {
        try {
            throw new Error($msg, $code, $file, $line, $context);
        } catch (Error $e) {
            // nothing
        }
    }

    /**
     * Cette fonction sera lancée à la fin du script quel que soit la cause (fin normale ou erreur)
     * Elle nous permet de récupérer les erreurs fatales qui sont ignorées par la fonction précédente
     */
    public function shutDown()
    {
        if (Config::consoleActive()) Console::finaliser();
        if (($error = error_get_last()) !== null) {
            try {
                throw new Error($error['message'], $error['type'], $error['file'], $error['line']);
            } catch (Error $e) {
                // nothing
            }
        }
        exit();
    }

    /**
     * Cette fonction récupère le controleur appelé dans l'URL
     * Elle va aussi gérer si l'utilisateur doit se connecter ou si le site est en maintenance (et changer le controleur en conséquence)
     * @return array : Un tableau contenant le dossier, le controleur et la fonction à appeler
     */
    public function getRoute()
    {
        global $sys_directory;
        global $sys_controller;
        global $sys_function;

        if (isset($_REQUEST["sys"]) && preg_match("/^(.*\/)?([0-9a-zA-Z\-_]*)\/([0-9a-zA-Z\-_]*)$/", $_REQUEST["sys"])) {
            $sys = $_REQUEST["sys"];
        } elseif ((isset($_SESSION['id_utilisateur']) && $_SESSION['id_utilisateur']) || !call_user_func(array(APP,"hasAuthentification"))) {
            $sys = call_user_func(array(APP, "defaultController"));
        } else {
            $sys = call_user_func(array(APP,"authController"));
        }

        if (!Navigation::siteOuvert()) {
            if ((!call_user_func(array(APP, "hasAuthentification")) && $sys == call_user_func(array(APP, "defaultController")))
                || $sys == call_user_func(array(APP, "authController"))
            ) {
                Controller::clearFlash();
                $sys = call_user_func(array(APP, "closedAppController"));
            } else {
                Controller::setFlash(Constantes::getMessageFlashMaintenance(), true);
            }
        }
        preg_match("/^(.*\/)?([0-9a-zA-Z\-_]*)\/([0-9a-zA-Z\-_]*)$/", $sys, $matches);

        $sys_directory = $matches[1];
        $sys_controller = "ctrl_".$matches[2];
        $sys_function = String::toCamlCase($matches[3]);

        // Si le fichier existe on l'inclue
        if (file_exists(BASE_PATH."/application/".APPLICATION."/controller/".$sys_directory.$sys_controller.".php")) {
            require_once(BASE_PATH."/application/".APPLICATION."/controller/".$sys_directory.$sys_controller.".php");
        } elseif (file_exists(BASE_PATH."/system/component/".$sys_directory.$sys_controller.".php")) {
            require_once(BASE_PATH."/system/component/".$sys_directory.$sys_controller.".php");
            // Sinon on appelle la page 404
        } else {
            $this->getRouteError();
        }
        return array(\String::toCamlCase($sys_controller), $sys_function);
    }

    /**
     * @throws NotFoundError
     */
    protected function getRouteError()
    {
        global $sys_directory;
        global $sys_controller;
        throw new NotFoundError(Error::contolleurIntrouvable($sys_directory.$sys_controller.' ('.BASE_PATH."/application/".APPLICATION."/controller/".$sys_directory.$sys_controller.".php".')'));
    }

    /**
     * @return $this
     */
    protected function registerErrorHandler()
    {
        set_error_handler(array($this, 'errorToException'));
        return $this;
    }

    /**
     * @return $this
     */
    protected function registerShutdownFunction()
    {
        register_shutdown_function(array($this, 'shutDown'));
        return $this;
    }

    /**
     * @return $this
     */
    protected function defineErrorLog()
    {
        ini_set('error_log', Config::pathToPhpErrorLog());
        return $this;
    }
}
