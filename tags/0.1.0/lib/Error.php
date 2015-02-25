<?php
namespace FMUP;

/**
 * @Todo this should be in a better way
 * Class Error
 * @package FMUP
 */
class Error
{
    /**
     * @todo : rewrite this since this is really dirty
     * @todo : think SOLID : this function must not Format AND Write + access to superglobals that might not exit
     */
    public static function addContextToErrorLog()
    {
        ob_start();
        if (isset($_SERVER["REMOTE_ADDR"])) {
            echo "Adresse IP de l'internaute : ".$_SERVER["REMOTE_ADDR"].' '.gethostbyaddr($_SERVER["REMOTE_ADDR"]).PHP_EOL;
        }
        if (isset($_SERVER["HTTP_HOST"])) {
            echo "URL appelée : http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].PHP_EOL;
        }

        echo "État des variables GET lors de l'erreur :".PHP_EOL;
        print_r($_GET);
        echo PHP_EOL;
        echo "État des variables POST lors de l'erreur :".PHP_EOL;
        print_r($_POST);
        echo PHP_EOL;
        echo "État des variables SESSION lors de l'erreur :".PHP_EOL;
        if(isset($_SESSION['id_utilisateur'])) {
            print_r($_SESSION['id_utilisateur']);
            echo PHP_EOL;
        }
        if(isset($_SESSION['id_historisation'])) {
            print_r($_SESSION['id_historisation']);
            echo PHP_EOL;
        }
        if(isset($_SESSION['id_menu_en_cours'])) {
            print_r($_SESSION['id_menu_en_cours']);
            echo PHP_EOL;
        }
        if(isset($_SESSION['droits_controlleurs'])) {
            print_r($_SESSION['droits_controlleurs']);
            echo PHP_EOL;
        }
        echo "État des variables HTTP lors de l'erreur :".PHP_EOL;
        $http_variable['HTTP_USER_AGENT'] = !isset($_SERVER['HTTP_USER_AGENT']) ?: $_SERVER['HTTP_USER_AGENT'];
        if (isset($_SERVER['HTTP_REFERER'])) {
            $http_variable['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
        }
        print_r($http_variable);
        echo PHP_EOL;
        echo "__________________".PHP_EOL;
        error_log(ob_get_clean());
    }
}
