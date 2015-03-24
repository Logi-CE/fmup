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
        error_log(self::getTrace());
    }

    static public function getTrace()
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
        return ob_get_clean();
    }

    static public function sendMail()
    {
        $mailBody = self::mailContent();
        require_once __DIR__ .'/../../../../lib/PHPMailer_v5.0.2/class.phpmailer.php';

        $mail = new \PHPMailer();
        if (\Config::smtpServeur() != 'localhost') {
            $mail->IsSMTP();
        }
        $mail->CharSet = "UTF-8";
        $mail->SMTPAuth   = \Config::smtpAuthentification();
        $mail->SMTPSecure = \Config::smtpSecure();

        $mail->Host   = \Config::smtpServeur();
        $mail->Port   = \Config::smtpPort();

        if (\Config::smtpAuthentification()) {
            $mail->Username   = \Config::smtpUsername();     // Gmail identifiant
            $mail->Password   = \Config::smtpPassword();		// Gmail mot de passe
        }

        $mail->From       = \Config::mailRobot();
        $mail->FromName   = \Config::erreurMailFromName();
        $mail->Subject    = '[Erreur] '.$_SERVER['SERVER_NAME'];
        $mail->AltBody    = $mailBody;
        $mail->WordWrap   = 50; // set word wrap

        $mail->Body = $mailBody;

        $recipients = \Config::mailSupport();
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

    static public function mailContent()
    {
        $trace = self::getTrace();
        ob_start();
        echo str_replace(PHP_EOL, '<br/>', $trace);
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
        return ob_get_clean();
    }
}
