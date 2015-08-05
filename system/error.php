<?php
/**
 * Classe gérant le comportement de l'application en cas d'erreur
 * Elle est etendue d'Exception et est donc appelée lorsqu'une erreur survient
 * @author afalaise
 * @version 1.0
 * @deprecated use any exception + \FMUP\Controller\Error instead
 */
class Error extends Exception
{
    protected $message;
    protected $code;
    protected $fichier;
    protected $ligne;
    protected $contexte;
    
    protected $type_erreur = array (
              0					 => 'Erreur'
            , E_ERROR              => 'Erreur Fatale'
            , E_WARNING            => 'Alerte'
            , E_PARSE              => 'Erreur d\'analyse'
            , E_NOTICE             => 'Notification'
            , E_CORE_ERROR         => 'Core Error'
            , E_CORE_WARNING       => 'Core Warning'
            , E_COMPILE_ERROR      => 'Compile Error'
            , E_COMPILE_WARNING    => 'Compile Warning'
            , E_USER_ERROR         => 'Erreur spécifique'
            , E_USER_WARNING       => 'Alerte spécifique'
            , E_USER_NOTICE        => 'Note spécifique'
            , E_STRICT             => 'Runtime Notice'
            , E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
            , 99 				=> 'Erreur de requête PDO'
    );

    /**
     * @param string $message
     * @param int $code
     * @param null $fichier
     * @param null $ligne
     * @param null $contexte
     * @throws Error
     * @deprecated use any exception + \FMUP\Controller\Error instead
     */
    public function __construct($message, $code = E_ERROR, $fichier = null, $ligne = null, $contexte = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->ligne = $ligne;
        $this->fichier = $fichier;
        $this->contexte = $contexte;

        parent::__construct($message, (int) $code);

        if (Config::paramsVariables('affichage_erreurs')) {
            // Trace de l'erreur toute propre
            $retour = $this->tracerErreur();
            if (!Config::isDebug()) {
                $this->envoyerMail($retour);
                if ($this->estBloquante()) {
                    echo Constantes::getMessageErreurApplication();
                    exit();
                }
            } else {
                Debug::output($retour);
            }
        }
    }

    /**
     * Fonction déterminant les niveaux d'erreurs bloquants
     */
    protected function estBloquante ()
    {
        // Erreur de parsing, erreur de type fatale (Error ou Exception)
        return (in_array($this->code, array(E_PARSE, E_ERROR, E_USER_ERROR)));
    }

    /**
     * Fonction envoyant un mail d'erreur au support avec l'URL et le contenu des données utilisées
     * Ce mail est véritablement envoyé si la limitation du nombre par minute est activée et n'a pas été dépassée
     * @param string $corps : Les données de debug sous forme de texte
     * @return bool : VRAI si le mail est envoyé
     */
    protected function envoyerMail($corps)
    {
        // Gestion de la limitation des mails d'erreur
        if (Config::paramsVariables('limite_mail_erreur') != -1) {
            $resultat = $this->incrementerCompteur();
        
            if ($resultat['nb_mails'] >= Config::paramsVariables('limite_mail_erreur')) {
                return false;
            }
        }
        $mail = new PHPMailer();
        $mail = EmailHelper::parametrerHeaders($mail);
        $mail->From       = Config::paramsVariables('mail_robot');
        $mail->FromName   = Config::paramsVariables('mail_robot_name');
        $mail->Subject    = '[Erreur '.$this->type_erreur[$this->code].'] '.$_SERVER['SERVER_NAME'];
        $mail->AltBody    = $corps;
        $mail->WordWrap   = 50; // set word wrap

        $mail->Body       = $corps;

        $recipients       = Config::paramsVariables('mail_support');
        if (strpos($recipients, ',') === false) {
             $mail->AddAddress($recipients, "Support");
        } else {
            $tab_recipients = explode(',', $recipients);
            foreach ($tab_recipients as $recipient) {
                $mail->AddAddress($recipient);
            }
        }

        return $mail->Send();
    }

    /**
     * Fonction gérant la limitation d'envoi du nombre de mail par minute
     * Elle enregistre ce nombre dans la table compteur_mail
     * @return array : Le nombre de mail déjà envoyés (nb_mails) et la date courante (date_envoi)
     */
    protected function incrementerCompteur ()
    {
        switch ($this->code) {
            case E_ERROR:
                $champ = 'nb_erreurs';
                break;
            case E_WARNING:
                $champ = 'nb_avertissements';
                break;
            case E_NOTICE:
                $champ = 'nb_notifications';
                break;
            default:
                $champ = 'nb_autres';
                break;
        }

        $date = date('Y-m-d H-i');
        $sql = 'SELECT nb_mails, date_envoi FROM compteur_mail WHERE date_envoi = "'.$date.'-00"';
        $db = Model::getDb();
        if (!$db instanceof \FMUP\Db) {
            $resultat = $db->requeteUneLigne($sql);
        } else {
            $resultat = $db->fetchRow($sql);
        }

        if (!isset($resultat['nb_mails'])) {
            $resultat['nb_mails'] = 0;
            $resultat['date_envoi'] = $date.'-00';
            $sql = 'INSERT INTO compteur_mail (nb_mails, date_envoi, '.$champ.') VALUES (1, "'.$date.'-00", 1)';
        } else {
            $sql = 'UPDATE compteur_mail SET nb_mails = nb_mails + 1, '.$champ.' = '.$champ.' + 1 WHERE date_envoi = "'.$date.'-00"';
        }
        Model::getDb()->execute($sql);

        return $resultat;
    }

    /**
     * Génère un texte contenant l'arborescence de l'erreur, les variables utilisées, postées et en session
     * @return string : Le texte
     */
    public function tracerErreur ()
    {
        Console::enregistrer(array('fichier' => $this->fichier, 'ligne' => $this->ligne, 'erreur' => 'Erreur ['.$this->type_erreur[$this->code].'] : '.$this->message), LOG_ERREUR);

        // On logue les erreurs
        error_log('Erreur ['.$this->type_erreur[$this->code].'] : '.$this->message."\n".'ligne: '.$this->ligne.', fichier: '.$this->fichier);
        
        ob_start();
        echo "<strong>Erreur [".$this->type_erreur[$this->code]."] : ".$this->message."</strong><br/>";
        echo "Erreur sur la ligne <strong>".$this->ligne."</strong> dans le fichier <strong>".$this->fichier."</strong><br/>";
        if (isset($_SERVER["REMOTE_ADDR"])) echo "Adresse IP de l'internaute : ".$_SERVER["REMOTE_ADDR"].' '.gethostbyaddr($_SERVER["REMOTE_ADDR"])."<br/>";
        if (isset($_SERVER["HTTP_HOST"])) echo "URL appelée : http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."<br/><br/>";

        echo "État des variables GET lors de l'erreur :<pre>";
        print_r($_GET);
        echo "</pre><br/>";
        echo "État des variables POST lors de l'erreur :<pre>";
        print_r($_POST);
        echo "</pre><br/>";
        echo "État des variables SESSION lors de l'erreur :<pre>";
        $session = $_SESSION;
        if (isset($session['filtre_liste'])) {
            unset($session['filtre_liste']);
        }
        print_r($session);
        echo "</pre><br/>";
        echo "État des variables HTTP lors de l'erreur :<pre>";
        $http_variable['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
        if (isset($_SERVER['HTTP_REFERER'])) $http_variable['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
        print_r($http_variable);
        echo "</pre><br/>";
        echo "Trace complète :<br/>";

        $retour = debug_backtrace();
        ksort($retour);
        echo '<style>td{padding: 3px 5px;}</style>';
        echo '<table border="1"><tr><th>Fichier</th><th>Ligne</th><th>Fonction</th></tr>';
            unset($retour[0]);
            foreach ($retour as $trace) {
                echo '<tr>';
                    echo '<td>'.( (isset($trace['file'])) ? $trace['file'] : $this->fichier ).'</td>';
                    echo '<td style="text-align: right;">'.( (isset($trace['line'])) ? $trace['line'] :  $this->ligne ).'</td>';
                    echo '<td>'.( (isset($trace['class'])) ? $trace['class'] : '' );
                    echo (isset($trace['type'])) ? $trace['type'] : '';
                    echo (isset($trace['function'])) ? $trace['function'] : '';

                    $arguments = array();
                    if (!empty($trace['args'])) {
                        foreach ($trace['args'] as $name => $arg) {
                            if (is_array($arg)) {
                                $arguments[] = 'Array';
                            } elseif (is_object($arg)) {
                                $arguments[] = 'Object';
                            } elseif (is_resource($arg)) {
                                $arguments[] = 'Resource';
                            } else {
                                $arg = '"'.$arg.'"';
                                $coupure = (strlen($arg) > 50) ? '...' : '';
                                $arguments[] = substr($arg, 0, 50).$coupure;
                            }
                        }
                    }
                    echo '('.implode(',', $arguments).')</td>';

                echo '</tr>';
            }
            if (!empty($retour[0]['args'][0]) && is_object($retour[0]['args'][0])) {
                $traces = $retour[0]['args'][0]->getTrace();
                foreach ($traces as $trace) {
                    echo '<tr>';
                        echo '<td>'.( (isset($trace['file'])) ? $trace['file'] : '-' ).'</td>';
                        echo '<td style="text-align: right;">'.( (isset($trace['line'])) ? $trace['line'] : '-' ).'</td>';
                        echo '<td>'.( (isset($trace['class'])) ? $trace['class'] : '' );
                        echo (isset($trace['type'])) ? $trace['type'] : '';
                        echo (isset($trace['function'])) ? $trace['function'] : '';

                        $arguments = array();
                        if (!empty($trace['args'])) {
                            foreach ($trace['args'] as $name => $arg) {
                                if (is_array($arg)) {
                                    $arguments[] = 'Array';
                                } elseif (is_object($arg)) {
                                    $arguments[] = 'Object';
                                } elseif (is_resource($arg)) {
                                    $arguments[] = 'Resource';
                                } else {
                                    $arg = '"'.$arg.'"';
                                    $coupure = (strlen($arg) > 50) ? '...' : '';
                                    $arguments[] = substr($arg, 0, 50).$coupure;
                                }
                            }
                        }
                        echo '('.implode(',', $arguments).')</td>';

                    echo '</tr>';
                }
            }
        echo '</table>';
        $tampon = ob_get_clean();

        return $tampon;
    }

    // chaine personnalisée représentant l'objet
    public function __toString()
    {
        return __CLASS__ . ": {$this->message}\n";
    }

    // Les messages d'erreur standards

    /**
     * Si on ne trouve pas une classe
     * @param String la classe introuvable
     **/
    public static function classeIntrouvable ($classe)
    {
        return "Classe introuvable : $classe.";
    }
    /**
     * Si on ne trouve pas le controlleur
     * @param String le controlleur introuvable
     **/
    public static function contolleurIntrouvable ($controlleur)
    {
        return "Controlleur introuvable : $controlleur.";
    }
    /**
     * Si on ne trouve pas la fonction du controlleur
     * @param String la fonction introuvable
     **/
    public static function fonctionIntrouvable ($fonction)
    {
        return "Fonction introuvable : $fonction.";
    }
    /**
     * Si on ne trouve pas le layout
     * @param String le layout introuvable
     **/
    public static function layoutIntrouvable ($layout)
    {
        return "Layout introuvable : $layout.";
    }
    /**
     * Si on ne trouve pas la vue
     * @param String la vue introuvable
     **/
    public static function vueIntrouvable ($vue)
    {
        return "Vue introuvable : $vue.";
    }
    /**
     * Si on ne trouve pas le composant
     * @param String le composant introuvable
     **/
    public static function composantIntrouvable ($composant)
    {
        return "Composant introuvable : $composant.";
    }
    /**
     * Si la connexion à la base de données échoue
     **/
    public static function connexionBDD ()
    {
        return 'Erreur de connexion à la base de données.';
    }
    /**
     * Si la sélection de la base de données échoue
     **/
    public static function selectionBDD ($base)
    {
        return "Erreur à la sélection de la base de données : $base.";
    }
    /**
     * Si l'on ne trouve pas le bon driver de base de données.
     */
    public static function driverBdInconnu ($engine)
    {
        return "Erreur, le moteur de base de données '$engine' n'est pas reconnu.";
    }
    /**
     * Si la sélection de la base de données échoue
     **/
    public static function typeDeRequeteInconnue ()
    {
        return 'Erreur : type de requète inconnue.';
    }
    /**
     * Si une requète échoue
     **/
    public static function erreurRequete($sql)
    {
        return "Erreur de requète : ".$sql." ----->  ".mysql_error();
    }
    /**
     * Si un paramètre de Config n'a pas été renseigné
     **/
    public static function configParamAbsent($index)
    {
        return "Paramètre de Config absent : ".$index;
    }
    /**
     * Template email inconnu
     */
    public static function emailTemplateAbsent($index)
    {
        return 'Template email absent : '.$index;
    }
    /**
     * Connexion impossible au serveur LDAP
     */
    public static function connexionImpossibleLdap()
    {
        return "Connexion au serveur LDAP impossible";
    }
    /**
     * Bind de l'utilisateur au LDAP impossible
     */
    public static function erreurBindLdap($erreur)
    {
        return "Liaison au serveur LDAP impossible : ".$erreur;
    }
    /**
     * Ajout de l'entrée LDAP impossible
     */
    public static function erreurAjoutEntreeLdap($erreur)
    {
        return "Erreur lors de l'ajout d'une entrée LDAP : ".$erreur;
    }
    /**
     * Modification de l'entrée LDAP impossible
     */
    public static function erreurModificationEntreeLdap($erreur)
    {
        return "Modification impossible : ".$erreur;
    }
    /**
     * Suppression de l'entrée LDAP impossible
     */
    public static function erreurSuppressionEntreeLdap($erreur)
    {
        return "Suppression impossible : ".$erreur;
    }
    /**
     * Erreur inconnue
     **/
    public static function erreurInconnue()
    {
        return 'Erreur inconnue.';
    }
}
