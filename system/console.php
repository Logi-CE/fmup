<?php
define('LOG_INITIALISATION', 1);
define('LOG_VARIABLE', 2);
define('LOG_SQL', 3);
define('LOG_ERREUR', 4);
define('LOG_STATS', 5);
define('LOG_CONNEXION', 6);
define('LOG_PARAMS', 7);

/**
 * Classe de debogage grâce à un affichage de logs sur le coté du site
 * Elle se sert de variables de session
 * @author afalaise
 * @version 1.0
 * @deprecated you might want to use \FMUP\Logger and a FirePHP/ChromePHP Plugin
 */
class Console
{
    public static $statut_console = 'eteinte';
    public static $compteur_page = null;
    public static $nb_requetes;
    public static $duree_script;
    public static $memoire_utilisee;
    public static $compteur_interne;
    public static $options_console = array(
          'sql'			=> 'les logs SQL'
        , 'contenu'		=> 'les logs de contenu'
        , 'erreur'		=> 'les logs d\'erreur'
        , 'stats'		=> 'les stats'
        , 'params'		=> 'les variables globales'
    );

    /**
     * Fonctino d'initialisation de la console suivant les différents paramètres enregistrés
     */
    public static function initialiser ()
    {
        if (Config::consoleActive()) {

            self::$nb_requetes = 0;
            self::$duree_script = microtime(1);
            self::$memoire_utilisee = memory_get_usage();

            if (empty($_SESSION['console'])) {
                $_SESSION['console'] = array();
                self::$compteur_page = 0;
            } else {
                $cles = array_keys($_SESSION['console']);
                self::$compteur_page = array_pop($cles) + 1;
            }

            if (!empty($_SESSION['statut_console'])) {
                self::$statut_console = $_SESSION['statut_console'];
            }
            if (self::$statut_console == 'eteinte') {
                $_SESSION['statut_console'] = 'eteinte';
            }
            self::enregistrer('', LOG_INITIALISATION);
            $params = array();
            if (isset($_GET)) {
                $params['GET'] = $_GET;
            }
            if (isset($_POST)) {
                $params['POST'] = $_POST;
            }
            if (isset($_SESSION)) {
                foreach ($_SESSION as $index => $valeur) {
                    if ($index != 'console' && $index != 'statut_console' && $index != 'option_console_params' && $index != 'filtre_liste') {
                        $params['SESSION'][$index] = $valeur;
                    }
                }
            }
            self::enregistrer($params, LOG_PARAMS);
        } else {
            if (!empty($_SESSION['console'])) {
                self::vider();
            }
        }
    }

    /**
     * Fonction enregistrant un log à afficher
     * @param mixed $message : Le texte ou la variable à afficher
     * @param int $type : [OPT] Le type de log, par défaut log de type standard (2)
     */
    public static function enregistrer ($message, $type = LOG_VARIABLE)
    {
        if (Config::consoleActive() && self::$statut_console != 'eteinte') {
            switch ($type) {
                case LOG_INITIALISATION:
                    $_SESSION['console'][self::$compteur_page]['page'] = $_SERVER['REQUEST_URI'];
                    break;
                case LOG_VARIABLE:
                    if (is_array($message) || is_object($message)) {
                        $message = print_r($message, true);
                    }
                    $source = debug_backtrace();
                    $_SESSION['console'][self::$compteur_page]['contenu'][] = array('message' => $message, 'type' => $type, 'fichier' => $source[0]['file'], 'ligne' => $source[0]['line']);
                    break;
                case LOG_CONNEXION:
                    $_SESSION['console'][self::$compteur_page]['connexion'][] = $message;
                    break;
                case LOG_SQL:
                    $_SESSION['console'][self::$compteur_page]['sql'][] = $message;
                    self::$nb_requetes++;
                    break;
                case LOG_ERREUR:
                    $_SESSION['console'][self::$compteur_page]['erreur'][] = $message;
                    break;
                case LOG_STATS:
                    $_SESSION['console'][self::$compteur_page]['stats'] = $message;
                    break;
                case LOG_PARAMS:
                    $_SESSION['console'][self::$compteur_page]['params'] = $message;
                    break;
            }
        }
    }

    /**
     * Fonction d'affichage des données dans la console
     */
    public static function afficher ()
    {
        if (!empty($_SESSION['console'])) {
            foreach ($_SESSION['console'] as $contenu) {
                if (!empty($contenu['page'])) {
                    echo '<div class="page">'.$contenu['page'].'</div>';
                }
                if (!empty($contenu['params'])) {
                    $style = (!empty($_SESSION['option_console_params'])) ? '' : 'style="display: none;"';
                    foreach ($contenu['params'] as $type => $valeur) {
                        echo '<div class="log_params" '.$style.'>'
                            .'<br/><span class="minilog">'.$type.'</span>'
                            .'<br/>'.print_r($valeur, true)
                            .'</div>';
                    }
                }
                if (!empty($contenu['contenu'])) {
                    foreach ($contenu['contenu'] as $log) {
                        $style = (!empty($_SESSION['option_console_contenu'])) ? '' : 'style="display: none;"';
                        echo '<div class="log_contenu" '.$style.'>'
                            .'<br/><span class="minilog">'.$log['fichier'].':'.$log['ligne'].'</span>'
                            .'<br/>'.$log['message']
                            .'</div>';
                    }
                }
                if (!empty($contenu['connexion'])) {
                    $style = (!empty($_SESSION['option_console_sql'])) ? '' : 'style="display: none;"';
                    echo '<div class="log_sql" '.$style.'>'
                        .'<br/><span class="minilog">Connexion au(x) base(s) suivante(s) : '.implode(',', $contenu['connexion']).'</span>'
                        .'</div>';
                }
                if (!empty($contenu['sql'])) {
                    $style = (!empty($_SESSION['option_console_sql'])) ? '' : 'style="display: none;"';
                    foreach ($contenu['sql'] as $log) {
                        $alerte_duree = '';
                        $alerte_memoire = '';
                        if ($log['duree'] > 1) {
                            $alerte_duree = 'style="color: darkviolet;"';
                        }
                        if ($log['memoire'] > 1) {
                            $alerte_memoire = 'style="color: darkviolet;"';
                        }
                        echo '<div class="log_sql" '.$style.'>'
                            . '<br/>'
                            . preg_replace(
                                array('#(SELECT|UPDATE|INSERT|INTO|DELETE|FROM|INNER|JOIN|LEFT|ASC|DESC|WHERE|ORDER BY|GROUP BY|TOP|ISnull|YEAR|MONTH|IDENTITY|'.
                                                        'IFnull|AND |MIN|MAX|COUNT|SUM|ON |IN |OR |IS |NOT |null|null|CONCAT|GROUP_CONCAT|HAVING|BETWEEN|'.
                                                        'CASE|WHEN|THEN|ELSE|END|AS |SEPARATOR|SQL_CALC_FOUND_ROWS|LIMIT|DISTINCT|CURRENT_TIMESTAMP|CURRENT_DATE|'.
                                                        'DATE_FORMAT|IF\(|UNION|FOUND_ROWS|NOW|INTERVAL|DAY|MONTH|MINUTE|SECOND|VALUES|SET|LIKE|DATE_ADD)#'),
                                '<span style="color: blue;">$1</span>',
                                preg_replace(
                                    array('#(\d)#'),
                                    '<span style="color: red;">$1</span>',
                                    preg_replace(
                                        array('#(["|\'][a-zA-Z0-9_, ]+["|\'])#'),
                                        '<span style="color: purple;">$1</span>',
                                        preg_replace(
                                            array('#	#'),
                                            '',
                                            preg_replace(
                                                array('#	AS#'),
                                                '  AS',
                                                $log['requete']
                                            )
                                        )
                                    )
                                )
                            )
                            . '<br/><span class="minilog" '.$alerte_duree.'>Durée : '.$log['duree'].' sec</span>'
                            . '<br/><span class="minilog" '.$alerte_memoire.'>Memoire : '.$log['memoire'].' Mo</span>'
                            . '<br/><span class="minilog">Retour : '.$log['resultat'].' lig</span>'
                            . '</div>';
                    }
                }
                if (!empty($contenu['erreur'])) {
                    $style = (!empty($_SESSION['option_console_erreur'])) ? '' : 'style="display: none;"';
                    foreach ($contenu['erreur'] as $log) {
                        echo '<div class="log_erreur" '.$style.'>'
                            .'<br/><strong style="color: red;">'.$log['fichier'].':'.$log['ligne'].'</strong>'
                            .'<br/><strong>'.$log['erreur'].'</strong>'
                            .'</div>';
                    }
                }
                if (!empty($contenu['stats'])) {
                    $style = (!empty($_SESSION['option_console_stats'])) ? '' : 'style="display: none;"';
                    echo '<div class="log_stats" '.$style.'>'
                        .'<br/><strong style="color: red;">Durée totale : '.$contenu['stats']['duree_script'].'sec'
                        .'<br/>Mémoire totale : '.$contenu['stats']['memoire_utilisee'].' Mo'
                        .'<br/>Nombre de requête : '.$contenu['stats']['nb_requetes'].'</strong>'
                        .'</div>';
                }
            }
        }
    }
    
    /**
     * Calcule la mémoire consommée par la console
     * @return string : La mémoire consomée
     */
    public static function compterTailleConsole ()
    {
        $debut = memory_get_usage();
        $variable = unserialize(serialize($_SESSION['console']));
        $taille = memory_get_usage() - $debut;
        unset($variable);
        
        return round($taille / 1024 / 1024, 3).'Mo';
    }

    /**
     * Fonction lançant un chronomètre
     */
    public static function demarrerCompteur ()
    {
        if (Config::consoleActive() && self::$statut_console != 'eteinte') {
            
            self::$compteur_interne = microtime(true);
        }
    }
    
    /**
     * Fonction stoppant le chronomètre et enregistrant le résultat dans la console
     * @param string $message : Méssage associé
     */
    public static function arreterCompteur ($message = 'Durée enregistrée')
    {
        if (Config::consoleActive() && self::$statut_console != 'eteinte') {
            if (!empty(self::$compteur_interne)) {
    		    self::$compteur_interne -= microtime(true);
    		    self::enregistrer($message.' '.round(abs(self::$compteur_interne), 4).' sec');
    		    self::$compteur_interne = null;
            }
        }
    }

    /**
     * Fonction vidant toutes les données enregistrées dans la console
     */
    public static function vider ()
    {
        if (isset($_SESSION['console'])) {
            unset($_SESSION['console']);
            self::$compteur_page = null;
        }
    }

    /**
     * Fonction exécutée à la fin du script pour indiquer le temps et la mémoire totale consommée
     */
    public static function finaliser ()
    {
        if (Config::consoleActive() && self::$statut_console != 'eteinte') {

            self::$duree_script -= microtime(1);
            self::$memoire_utilisee -= memory_get_usage();

            $stats = array('duree_script' => round(abs(self::$duree_script), 4), 'memoire_utilisee' => round(abs(self::$memoire_utilisee) / 1000000, 3), 'nb_requetes' => self::$nb_requetes);
            self::enregistrer($stats, LOG_STATS);
        }
    }
}
