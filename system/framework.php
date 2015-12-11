<?php
/**
 * Patch with old system that passes all request URI in a sys param (GET + REQUEST)
 * @deprecated
 * @todo refactor to avoid use of this code
 */
if (!isset($_GET['sys']) && \FMUP\Sapi::getInstance()->get() != \FMUP\Sapi::CLI) {
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

/**
 * Classe d'initialisation du framework
 * @deprecated use \FMUP\Framework instead
 */
class Framework
{
    use \FMUP\Sapi\OptionalTrait;

    private $request;

    /**
     * @return \FMUP\Request
     */
    public function getRequest()
    {
        if (!$this->request) {
            $this->request = (
                $this->getSapi()->get() == \FMUP\Sapi::CLI ? new \FMUP\Request\Cli() : new \FMUP\Request\Http()
            );
        }
        return $this->request;
    }

    public function initialize()
    {
        if (!defined('APPLICATION')) {
            throw new \FMUP\Exception("La variable APPLICATION doit être définie.");
        } else {
            define('APP', "App" . String::toCamlCase(APPLICATION));
        }
        $toto = APP;

        // On fixe les fonctions appelées lors d'une erreur
        $this->definePhpIni();
        $this->defineErrorLog();
        $this->registerErrorHandler();
        $this->registerShutdownFunction();
        $this->instantiateSession();

        //log des pages
        $url = '';
        if (isset($_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        if (Config::isDebug()) {
            if (isset($_SESSION['id_utilisateur'])) {
                FileHelper::fLog('URL_' . $_SESSION['id_utilisateur'], $url);
                FileHelper::fLog('POST_' . $_SESSION['id_utilisateur'], $url . "\r\n" . print_r($_REQUEST, 1));
            } else {
                FileHelper::fLog('URL', $url);
                FileHelper::fLog('POST', $url . "\r\n" . print_r($_REQUEST, 1));
            }
        }
        $this->dispatch();
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
        if ($this->getSapi()->get() == \FMUP\Sapi::CLI) {
            return $this;
        }
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
                if ($multi_onglet) {
                    $old_session["window.name"] = date('YmdHis');
                }

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

                $uri = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
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
     * @deprecated
     */
    public function errorHandler($code, $msg, $errFile = null, $errLine = 0, array $errContext = array())
    {

    }

    /**
     * Cette fonction sera lancée à la fin du script quel que soit la cause (fin normale ou erreur)
     * Elle nous permet de récupérer les erreurs fatales qui sont ignorées par la fonction précédente
     * @deprecated maybe you want to override this
     */
    public function shutDown()
    {
    }

    /**
     * Cette fonction récupère le controleur appelé dans l'URL
     * Elle va aussi gérer si l'utilisateur doit se connecter ou si le site est en maintenance
     * (et changer le controleur en conséquence)
     * @return array : Un tableau contenant le dossier, le controleur et la fonction à appeler
     */
    public function getRoute()
    {
        preg_match("/^(.*\/)?([0-9a-zA-Z\-_]*)\/([0-9a-zA-Z\-_]*)$/", $this->getRequest()->getRequestUri(), $matches);
        if (count($matches) < 3) {
            $this->getRouteError(null, null);
        }

        $sys_directory = $matches[1];
        $sys_controller = "ctrl_" . $matches[2];
        $sys_function = String::toCamlCase($matches[3]);

        if (!class_exists(\String::toCamlCase($sys_controller)) ||
            !is_callable(array(\String::toCamlCase($sys_controller), $sys_function))
        ) {
            $this->getRouteError($sys_directory, $sys_controller);
        }
        return array(\String::toCamlCase($sys_controller), $sys_function);
    }

    /**
     * @uses string $directory
     * @uses string $controller
     * @throws \FMUP\Exception\Status\NotFound
     */
    protected function getRouteError($directory, $controller)
    {
        throw new \FMUP\Exception\Status\NotFound(
            "Controlleur introuvable : $directory$controller " .
            " (application/" . APPLICATION . "/controller/$directory$controller.php)"
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
