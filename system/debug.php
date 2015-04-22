<?php

/**
 * Class Debug
 * @deprecated use logs instead
 */
class Debug
{
    /**
     * Effectue une sortie lisible d'une variable
     * @param {Object} La variable
     * @param {Boolean} $parametre Si null rien de spécial, si false on affiche en commentaire, si true on tue le script
     **/
    public static function output($variable, $parametre = null)
    {
      if (Config::isDebug() || Utilisateur::isCastelis()) {

        echo ($parametre||null===$parametre)?'<pre style="border: 2px solid green; padding: 10px; background-color: #CCFFCC">':'<!--';
        // on affiche la ligne ou on se trouve
        $source = debug_backtrace();
        echo $source[0]['file'].':'.$source[0]['line'].'<br />';
        print_r($variable);
        echo ($parametre||null===$parametre)?'</pre>':'-->';


        if ($parametre) {
          /*echo '<pre>';
          debug_print_backtrace();
          echo '</pre>';*/
          die('Arrêt du script.');
        }
      }
    }
    /**
     * Retourne une sortie lisible d'une variable sous forme de chaine de caractère
     * @param {Object} La variable
     * @return {String} La variable en chaîne.
     **/
    public static function toString($variable)
    {
      ob_start();
      var_dump($variable);
      $resultat = ob_get_contents();
      ob_end_clean();
      return $resultat;
    }
    /**
     * Envoie un mail de débuggage
     *
     * @param {String} $buffer Le message
     * @param {String} $title Le titre du message
     */
    public static function mail($buffer, $title)
    {
      $headers  = 'MIME-Version: 1.0' . "\r\n";
      $headers .= "Content-type: text/html; charset=utf-8 \r\n";
      $headers .= "From: ".Config::mailRobot(). "\r\n";
      return mail(Config::mailSupport(), $title, $buffer, $headers);
    }
    /**
     * Backtrace
     **/
    public function backtrace()
    {
      ob_start();
      debug_print_backtrace();
      $backtrace = preg_replace('/.*?\n/', "", ob_get_contents(), 1);
      ob_end_clean();
      echo "<pre style='border: 2px solid red; padding: 10px; background-color: #FFCCCC'>$backtrace</pre>";
      echo "<hr />";
    }

    /**
     * @deprecated
     */
    public static function initConsole ()
    {
        if (Config::consoleActive()) {
            if (empty($_SESSION['console'])) {
                $_SESSION['console'] = '';
            }
            if (!empty($_SESSION['afficher_console'])) {
                $_SESSION['console'] .= '<div class="page">'.$_SERVER['REQUEST_URI'].'</div>';
            }
        } else {
            if (!empty($_SESSION['console'])) {
                unset($_SESSION['console']);
            }
        }
    }

    /**
     * @deprecated
     */
    public static function setConsole ($message)
    {
        if (Config::consoleActive() && !empty($_SESSION['afficher_console'])) {
            if (is_array($message) || is_object($message)) {
                $message = print_r($message, true);
            }
            if (isset($_SESSION['option_console_navigation'])) {
                $source = debug_backtrace();
                $_SESSION['console'] .= '<br/><strong style="color: red;">'.$source[0]['file'].':'.$source[0]['line'].'</strong>';
            }
            $_SESSION['console'] .= '<br/>'.$message;
        }
    }
}
