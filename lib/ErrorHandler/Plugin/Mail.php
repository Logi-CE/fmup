<?php
namespace FMUP\ErrorHandler\Plugin;

use FMUP\Sapi;

class Mail extends Abstraction
{
    public function canHandle()
    {
        $config = $this->getBootstrap()->getConfig();
        return (
            (!$this->getException() instanceof \FMUP\Exception\Status)
            && !$config->get('use_daily_alert') && !$config->get('is_debug')
        );
    }

    public function handle()
    {
        $this->sendMail($this->getBody());
        return $this;
    }

    protected function sendMail($body)
    {
        $config = $this->getBootstrap()->getConfig();
        $serverName = $this->getBootstrap()->getSapi()->get() != Sapi::CLI
            ? $this->getRequest()->getServer(\FMUP\Request\Http::SERVER_NAME)
            : $this->getBootstrap()->getConfig()->get('erreur_mail_sujet');

        $mail = new \PHPMailer();
        $mail = \EmailHelper::parametrerHeaders($mail);
        $mail->From = $config->get('mail_robot');
        $mail->FromName = $config->get('mail_robot_name');
        $mail->Subject = '[Erreur] ' . $serverName;
        $mail->AltBody = $body;
        $mail->WordWrap = 50; // set word wrap

        $mail->Body = $body;

        $recipients = $config->get('mail_support');
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
     * @todo clean this
     * @return string
     */
    protected function getBody()
    {
        ob_start();
        echo "<strong>Erreur : " . $this->getException()->getMessage() . "</strong><br/>";
        echo "Erreur sur la ligne <strong>" . $this->getException()->getLine() . "</strong> dans le fichier <strong>" . $this->getException()->getFile() . "</strong><br/>";

        if (isset($_SERVER["REMOTE_ADDR"])) echo "Adresse IP de l'internaute : " . $_SERVER["REMOTE_ADDR"] . ' ' . gethostbyaddr($_SERVER["REMOTE_ADDR"]) . "<br/>";
        if (isset($_SERVER["HTTP_HOST"])) echo "URL appelée : http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br/><br/>";

        echo "Trace complète :<br/>";

        $retour = $this->getException()->getTrace();
        ksort($retour);
        echo '<style>td{padding: 3px 5px;}</style>';
        echo '<table border="1"><tr><th>Fichier</th><th>Ligne</th><th>Fonction</th></tr>';
        unset($retour[0]);
        foreach ($retour as $trace) {
            echo '<tr>';
            echo '<td>' . ((isset($trace['file'])) ? $trace['file'] : $this->getException()->getFile()) . '</td>';
            echo '<td style="text-align: right;">' . ((isset($trace['line'])) ? $trace['line'] : $this->getException()->getLine()) . '</td>';
            echo '<td>' . ((isset($trace['class'])) ? $trace['class'] : '');
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
                        $arg = '"' . $arg . '"';
                        $coupure = (strlen($arg) > 50) ? '...' : '';
                        $arguments[] = substr($arg, 0, 50) . $coupure;
                    }
                }
            }
            echo '(' . implode(',', $arguments) . ')</td>';

            echo '</tr>';
        }
        if (!empty($retour[0]['args'][0]) && is_object($retour[0]['args'][0])) {
            $traces = $retour[0]['args'][0]->getTrace();
            foreach ($traces as $trace) {
                echo '<tr>';
                echo '<td>' . ((isset($trace['file'])) ? $trace['file'] : '-') . '</td>';
                echo '<td style="text-align: right;">' . ((isset($trace['line'])) ? $trace['line'] : '-') . '</td>';
                echo '<td>' . ((isset($trace['class'])) ? $trace['class'] : '');
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
                            $arg = '"' . $arg . '"';
                            $coupure = (strlen($arg) > 50) ? '...' : '';
                            $arguments[] = substr($arg, 0, 50) . $coupure;
                        }
                    }
                }
                echo '(' . implode(',', $arguments) . ')</td>';

                echo '</tr>';
            }
        }
        echo '</table>';
        $tampon = ob_get_clean();

        return $tampon;
    }
}
