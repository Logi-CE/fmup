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
            && !$config->get('use_daily_alert') && !$this->iniGet('display_errors')
        );
    }

    /**
     * @param string $key
     * @return string
     * @codeCoverageIgnore
     */
    protected function iniGet($key)
    {
        return ini_get($key);
    }

    /**
     * @return $this
     */
    public function handle()
    {
        $this->sendMail($this->getBody());
        return $this;
    }

    /**
     * @param string $body
     * @return bool
     * @throws \Exception
     * @throws \FMUP\Config\Exception
     * @throws \FMUP\Exception
     * @throws \phpmailerException
     */
    protected function sendMail($body)
    {
        $config = $this->getBootstrap()->getConfig();
        /** @var \FMUP\Request\Http $request */
        $request = $this->getRequest();
        $serverName = $this->getBootstrap()->getSapi()->get() != Sapi::CLI
            ? $request->getServer(\FMUP\Request\Http::SERVER_NAME)
            : $config->get('erreur_mail_sujet');
        $mail = $this->createMail($config);
        $mail->From = $config->get('mail_robot');
        $mail->FromName = $config->get('mail_robot_name');
        $mail->Subject = '[Erreur] ' . $serverName;
        $mail->AltBody = (string)$body;
        $mail->WordWrap = 50; // set word wrap

        $mail->Body = (string)$body;

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
     * Creates a new mail for config
     * @param \FMUP\Config\ConfigInterface $config
     * @return \FMUP\Mail
     * @codeCoverageIgnore
     */
    protected function createMail(\FMUP\Config\ConfigInterface $config)
    {
        return new \FMUP\Mail($config);
    }

    /**
     * @todo clean this
     * @return string
     */
    protected function getBody()
    {
        ob_start();
        $exception = $this->getException();
        echo "<strong>Erreur : " . $exception->getMessage() . "</strong><br/>";
        echo "Erreur sur la ligne <strong>" . $exception->getLine() . "</strong> dans le fichier ";
        echo '<strong>' . $exception->getFile() . "</strong><br/>";

        if (isset($_SERVER["REMOTE_ADDR"])) {
            echo "Adresse IP de l'internaute : " . $_SERVER["REMOTE_ADDR"];
            echo ' ' . gethostbyaddr($_SERVER["REMOTE_ADDR"]) . "<br/>";
        }
        if (isset($_SERVER["HTTP_HOST"])) {
            echo "URL appelée : http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "<br/><br/>";
        }

        echo "Trace complète :<br/>";

        $retour = $exception->getTrace();
        ksort($retour);
        echo '<style>td{padding: 3px 5px;}</style>';
        echo '<table border="1"><tr><th>Fichier</th><th>Ligne</th><th>Fonction</th></tr>';
        unset($retour[0]);
        $this->renderTraces($retour);
        echo '</table>';
        $tampon = ob_get_clean();

        return $tampon;
    }

    /**
     * @param array $traces
     * @return $this
     */
    protected function renderTraces(array $traces = array())
    {
        $exception = $this->getException();
        foreach ($traces as $trace) {
            echo '<tr>';
            echo '<td>' . ((isset($trace['file'])) ? $trace['file'] : $exception->getFile()) . '</td>';
            echo '<td style="text-align: right;">' . ((isset($trace['line']))
                    ? $trace['line']
                    : $exception->getLine()) . '</td>';
            echo '<td>' . ((isset($trace['class'])) ? $trace['class'] : '');
            echo (isset($trace['type'])) ? $trace['type'] : '';
            echo (isset($trace['function'])) ? $trace['function'] : '';

            $arguments = array();
            if (!empty($trace['args'])) {
                foreach ($trace['args'] as $arg) {
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
        return $this;
    }
}
