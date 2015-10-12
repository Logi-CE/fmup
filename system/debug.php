<?php
/**
 * Classe permettant le débogage de l'application
 * @version 1.0
 * @deprecated use \FMUP\Logger with channel Debug instead
 * @see \FMUP\Logger
 */
class Debug
{
	public static $duree;       // Variable utilisée pour les mesures de temps d'exécution
    public static $memoire;     // Variable utilisée pour les mesures de consommation mémoire
    
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
      $headers .= "From: ".Config::paramsVariables('mail_robot'). "\r\n";
      return mail(Config::paramsVariables('mail_support'), $title, $buffer, $headers);
    }
    /**
     * Backtrace
     **/
    public static function backtrace()
    {
      ob_start();
      debug_print_backtrace();
      $backtrace = preg_replace('/.*?\n/', "", ob_get_contents(), 1);
      ob_end_clean();
      echo "<pre style='border: 2px solid red; padding: 10px; background-color: #FFCCCC'>$backtrace</pre>";
      echo "<hr />";
	}

    /**
     * Méthode pour initialiser la mesure de mémoire et de temps d'exécution
     * @return un tableau avec la durée initiale et la mémoire initiale
     */
    public static function debuterMesureTempsMemoire()
    {
        self::$duree = microtime(true);
        self::$memoire = memory_get_usage();
    }

    /**
     * Méthode terminant la mesure de mémoire et de temps d'exécution
     * @param {console} : Affichage du résultat dans la console (par défaut à false : un Debug::output sera effectué)
     * @param {message} : Un message à afficher au début de l'affichage du résultat de la mesure
     */
    public static function terminerMesureTempsMemoire($console = false, $message = "")
    {
        self::$duree -= microtime(true);
        self::$memoire -= memory_get_usage();
        $retour = "";
        if ($message) {
            $retour .= $message."\n";
        }
        $retour .= "Temps d'exécution : ".round(abs($duree), 4)."\n";
        $retour .= "Mémoire consommée : ".round(abs($memoire), 4)."\n";
        if (!$console) {
            Debug::output($retour);
        } else {
            Console::enregistrer($retour);
        }
    }
}
