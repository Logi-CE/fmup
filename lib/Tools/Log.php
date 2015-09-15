<?php
namespace FMUP\Tools;

class Log
{
    /**
     * @var \PHPMailer
     */
    protected $mailerInstance = null;
    /**
     * @var \FMUP\Config
     */
    protected $config = null;

    /**
     * Sends log file for a specified date to support team
     * @param string $date
     * @return bool
     */
    public function sendErrorLog($date = null)
    {
        $mailAddresses = explode(';', $this->getConfig()->get('mail_support'));
        return $this->sendFileToMail(\Config::pathToPhpErrorLog($date), $mailAddresses);
    }

    /**
     * Sends a file path content to a list of email addresses
     * @param $filePath
     * @param array $mailAddresses
     * @return bool
     * @throws \Exception
     * @throws \phpmailerException
     */
    public function sendFileToMail($filePath, array $mailAddresses)
    {
        try {
            $body = file_exists($filePath) ? file_get_contents($filePath) : null;
            if (empty($body)) {
                $body = 'No log for today!';
            }
            $mailer = $this->getMailer();
            $mailer->IsHTML(false);
            $mailer->Subject = "[" . $this->getConfig()->get('version') . "] Daily log alert";
            $mailer->Body = $body;
            $mailer->AltBody = $body;
            foreach ((array)$mailAddresses as $mail) {
                $mailer->AddAddress($mail);
            }
            return $mailer->Send();
        } catch (\Exception $e) {
            var_dump($e);die;
            return false;
        }
    }

    /**
     * @return \PHPMailer
     * @throws \phpmailerException
     */
    public function getMailer()
    {
        if (!$this->mailerInstance) {
            $mailer = new \PHPMailer(true);
            $mailer->IsHTML(false);
            $mailer->CharSet = "UTF-8";
            $mailer->SetFrom($this->getConfig()->get('mail_robot'), $this->getConfig()->get('mail_robot_name'));
            $this->mailerInstance = $mailer;
        }
        return $this->mailerInstance;
    }

    /**
     * @todo Hard dependency with PHPMailer // see for abstraction layer and use PHPMailer as a driver
     * @param \PHPMailer $mailer
     * @return $this
     */
    public function setMailer(\PHPMailer $mailer)
    {
        $this->mailerInstance = $mailer;
        return $this;
    }

    /**
     * @param \FMUP\Config $config
     * @return $this
     */
    public function setConfig(\FMUP\Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return \FMUP\Config
     * @throws \LogicException
     */
    public function getConfig()
    {
        if (!$this->config) {
            throw new \LogicException("This object needs config!");
        }
        return $this->config;
    }
}
