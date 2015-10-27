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

/**
 * Classe d'initialisation du framework
 * @deprecated use \FMUP\Framework instead
 */
class Framework
{
    public function initialize ()
    {
        global $sys_directory;
        global $sys_controller;
        global $sys_function;

        if (!defined('APPLICATION')) {
            throw new \FMUP\Exception("La variable APPLICATION doit être définie.");
        } else {
            define('APP', "App".String::toCamlCase(APPLICATION));
        }

        // On fixe les fonctions appelées lors d'une erreur
        $this->definePhpIni();
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
                FileHelper::fLog('URL_'.$_SESSION['id_utilisateur'], $url);
                FileHelper::fLog('POST_'.$_SESSION['id_utilisateur'], $url."\r\n".print_r($_REQUEST, 1));
            } else {
                FileHelper::fLog('URL', $url);
                FileHelper::fLog('POST', $url."\r\n".print_r($_REQUEST, 1));
            }
        }
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
     */
    protected function dispatch()
    {
        list($controllerName, $action) = $this->getRoute();
        $this->instantiate($controllerName, $action);
        return $this;
    }

    /**
     * Allow overwriting of an eventual post treatment
     */
    protected function postDispatch()
    {
    }

    protected function instantiate($sys_controller, $sys_function)
    {
        // Création d'une instance du controlleur
        $controllerName = String::toCamlCase($sys_controller);

        /** @var $controllerInstance \FMUP\Controller */
        $controllerInstance = new $controllerName();
        $controllerInstance->preFilter($sys_function);

        if (method_exists($controllerInstance, $sys_function)) {
            call_user_func(array($controllerInstance, $sys_function));
        } else {
            throw new \FMUP\Exception\Status\NotFound("Fonction introuvable : $sys_function");
        }
        $controllerInstance->postFilter();
        return $controllerInstance;
    }

    protected function instantiateSession()
    {
        if (Config::getGestionSession()) {
            $session = new HlpSessions('BACK');
            session_set_save_handler(
                array(&$session, 'open'),
                array(&$session, 'close'),
                array(&$session, 'read'),
                array(&$session, 'write'),
                array(&$session, 'destroy'),
                array(&$session, 'gc')
            );
        } else {
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
                        $_SESSION['utilisateur']->setCookie(
                            $_SESSION['utilisateur']->getMatricule(),
                            $_SESSION['utilisateur']->getId(),
                            $_SESSION['utilisateur']->getPassword()
                        );
                    }
                }

                $uri = (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'/');
                $uri = str_replace($_REQUEST['psid'], '', $uri); // pour ne pas boucler

                $header = new \FMUP\Response\Header\Location($uri);
                $header->render(); //@todo something better must be done
                exit; //@todo something better must be done
            } else {
                \FMUP\Session::getInstance()->start();
            }
        }
    }

    /**
     * Sends exception in case of error
     * @param int $code
     * @param string $msg
     * @throws \FMUP\Exception
     */
    public function errorHandler($code, $msg, $errFile = null, $errLine = 0, array $errContext = array())
    {
        $block = E_PARSE | E_ERROR | E_USER_ERROR;
        $binary = $code & $block;
        if ($binary) {
            $message = $msg . ' in file ' . $errFile . ' on line ' . $errLine;
            if ($errContext) {
                $message .= ' {' . serialize($errContext) . '}';
            }
            throw new \FMUP\Exception($message, $code);
        }
    }

    /**
     * Cette fonction sera lancée à la fin du script quel que soit la cause (fin normale ou erreur)
     * Elle nous permet de récupérer les erreurs fatales qui sont ignorées par la fonction précédente
     * @deprecated maybe you want to override this
     */
    public function shutDown()
    {
        if (Config::consoleActive()) Console::finaliser();
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
        } elseif ((isset($_SESSION['id_utilisateur']) && $_SESSION['id_utilisateur']) || !is_callable(array(APP,"hasAuthentification")) || !call_user_func(array(APP,"hasAuthentification"))) {
            $sys = is_callable(array(APP, "defaultController")) ? call_user_func(array(APP, "defaultController")) : null;
        } else {
            $sys = is_callable(array(APP, "authController")) ? call_user_func(array(APP,"authController")) : null;
        }

        if (!Config::siteOuvert()) {
            $callables = is_callable(array(APP, "hasAuthentification")) && is_callable(array(APP, "defaultController")) &&
                is_callable(array(APP, "authController")) && is_callable(array(APP, "closedAppController"));
            if ($callables &&
                (
                    (!call_user_func(array(APP, "hasAuthentification")) && $sys == call_user_func(array(APP, "defaultController")))
                || $sys == call_user_func(array(APP, "authController"))
                )
            ) {
                \FMUP\FlashMessenger::getInstance()->clear();
                $sys = call_user_func(array(APP, "closedAppController"));
            } else {
                \FMUP\FlashMessenger::getInstance()->add(new \FMUP\FlashMessenger\Message(Constantes::getMessageFlashMaintenance()));
            }
        }
        preg_match("/^(.*\/)?([0-9a-zA-Z\-_]*)\/([0-9a-zA-Z\-_]*)$/", $sys, $matches);
        if (is_null($sys) || count($matches) < 3) {
            $this->getRouteError();
        }

        $sys_directory = $matches[1];
        $sys_controller = "ctrl_".$matches[2];
        $sys_function = String::toCamlCase($matches[3]);

        
        if (
            !class_exists(\String::toCamlCase($sys_controller)) ||
            !is_callable(array(\String::toCamlCase($sys_controller), $sys_function)))
        {
            $this->getRouteError();
        }
        return array(\String::toCamlCase($sys_controller), $sys_function);
    }

    /**
     * @param string $sys_directory
     * @param string $sys_controller
     * @throws \FMUP\Exception\Status\NotFound
     */
    protected function getRouteError()
    {
        global $sys_directory;
        global $sys_controller;
        throw new \FMUP\Exception\Status\NotFound(
            "Controlleur introuvable : " . $sys_directory.$sys_controller.
            ' ('.BASE_PATH."/application/".APPLICATION."/controller/".$sys_directory.$sys_controller.".php".')'
        );
    }

    /**
     * @return $this
     */
    protected function registerErrorHandler()
    {
        set_error_handler(array($this, 'errorHandler'));
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

    /**
     * @return $this
     */
    protected function definePhpIni()
    {
        // On détermine le niveau d'erreur
        error_reporting(Config::errorReporting());
        ini_set('display_errors', Config::isDebug());
        ini_set('display_startup_errors', Config::isDebug());
        return $this;
    }
}
