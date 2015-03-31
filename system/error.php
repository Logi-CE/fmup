<?php
/**
 * Classe de identifiant le comportement de l'application en cas d'erreur
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
     * Fonction déterminant les erreurs bloquantes
     */
    protected function estBloquante ()
    {
        // Erreur de parsing, erreur de type fatale (Error ou Exception)
        return (in_array($this->code, array(E_PARSE, E_ERROR, E_USER_ERROR)));
    }

    protected function envoyerMail($corps)
    {
        // Gestion de la limitation des mails d'erreur
        if (Config::paramsVariables('limite_mail_erreur') != -1) {
            $resultat = $this->incrementerCompteur();
        
            if ($resultat['nb_mails'] >= Config::paramsVariables('limite_mail_erreur')) {
                return false;
            }
        }
        
        require_once BASE_PATH.'/lib/PHPMailer_v5.0.2/class.phpmailer.php';
        
        $mail = new PHPMailer();
        if (Config::smtpServeur() != 'localhost') {
            $mail->IsSMTP();
        }
        $mail->CharSet = "UTF-8";
        $mail->SMTPAuth   = Config::smtpAuthentification();
        $mail->SMTPSecure = Config::smtpSecure();

        $mail->Host   = Config::smtpServeur();
        $mail->Port   = Config::smtpPort();

        if (Config::smtpAuthentification()) {
            $mail->Username   = Config::smtpUsername();     // Gmail identifiant
            $mail->Password   = Config::smtpPassword();		// Gmail mot de passe
        }

        $mail->From       = Config::mailRobot();
        $mail->FromName   = Config::erreurMailFromName();
        $mail->Subject    = '[Erreur '.$this->type_erreur[$this->code].'] '.$_SERVER['SERVER_NAME'];
        $mail->AltBody    = $corps;
        $mail->WordWrap   = 50; // set word wrap

        $mail->Body = $corps;

        $recipients = Config::mailSupport();
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
        $resultat = Model::getDb()->requeteUneLigne($sql);

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

    public function tracerErreur()
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
        if(isset($_SESSION['id_utilisateur']))		print_r($_SESSION['id_utilisateur']);
        if(isset($_SESSION['id_historisation']))	print_r($_SESSION['id_historisation']);
        if(isset($_SESSION['id_menu_en_cours']))	print_r($_SESSION['id_menu_en_cours']);
        if(isset($_SESSION['droits_controlleurs']))	print_r($_SESSION['droits_controlleurs']);
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
                            } else {
                                if (is_string($arg)) {
                                    $arg = '"'.$arg.'"';
                                }
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
                                } else {
                                    if (is_string($arg)) {
                                        $arg = '"'.$arg.'"';
                                    }
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
     * Bind de l'utilisateur au LDAP impossible
     */
    public static function erreurAjoutEntreeLdap($erreur)
    {
        return "Erreur lors de l'ajout d'une entrée LDAP : ".$erreur;
    }
    /**
     * Bind de l'utilisateur au LDAP impossible
     */
    public static function erreurModificationEntreeLdap($erreur)
    {
        return "Modification impossible : ".$erreur;
    }
    /**
     * Erreur inconnue
     **/
    public static function erreurInconnue()
    {
        return 'Erreur inconnue.';
    }
}
