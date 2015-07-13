<?php
/**
 * Controleur gérant les retours AJAX de la console
 */
class CtrlConsole extends Controller
{
    public function afficherConsole ()
    {
        echo '<style type="text/css">';
        include BASE_PATH.'/system/component/console/console.css';
        echo '</style>
        <pre id="debug-zone" style="left: 1em; bottom: 1em; width: auto; height: auto;">
            <span id="debug-contenu">';
            Console::afficher();
        echo '</span>
        </pre>';
    }

    public function activerOptionConsole ()
    {
        if (isset($_REQUEST['option'], $_REQUEST['valeur'])) {
            if ($_REQUEST['valeur'] == 1) {
                $_SESSION['option_console_'.$_REQUEST['option']] = true;
            } elseif (isset($_SESSION['option_console_'.$_REQUEST['option']])) {
                unset($_SESSION['option_console_'.$_REQUEST['option']]);
            }
        }
    }

    public function viderConsole ()
    {
        Console::vider();
    }

    public function rafraichirConsole ()
    {
        Console::afficher();
    }
    public function activerConsole ()
    {
        if (isset($_REQUEST['statut_console'])) {
            $_SESSION['statut_console'] = $_REQUEST['statut_console'];
            if ($_SESSION['statut_console'] == 'eteinte') {
                Console::vider();
            }
        }
    }
}
