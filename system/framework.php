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
        }

        // On fixe les fonctions appelées lors d'une erreur
        $this->defineErrorLog();
        $this->registerErrorHandler();
        $this->registerShutdownFunction();
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
        $controllerName = \FMUP\String::getInstance()->toCamelCase($sys_controller);

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
        $sys_function = \FMUP\String::getInstance()->toCamelCase($matches[3]);

        if (!class_exists(\FMUP\String::getInstance()->toCamelCase($sys_controller)) ||
            !is_callable(array(\FMUP\String::getInstance()->toCamelCase($sys_controller), $sys_function))
        ) {
            $this->getRouteError($sys_directory, $sys_controller);
        }
        return array(\FMUP\String::getInstance()->toCamelCase($sys_controller), $sys_function);
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
}
