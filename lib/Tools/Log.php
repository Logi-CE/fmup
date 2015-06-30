<?php
namespace FMUP\Tools;

class Log
{
    /**
     * @var \PHPMailer
     */
    protected $mailerInstance = NULL;

    /**
     * Sends log file for a specified date to support team
     * @param string $date
     * @return bool
     */
    public function sendErrorLog($date = NULL)
    {
        $mailAddresses = explode(';', \Config::mailSupport());
        return $this->sendFileToMail(\Config::pathToPhpErrorLog($date), $mailAddresses);
    }

    /**
     * Sends a file path content to a list of email addresses
     * @param $filePath
     * @param array $mailAddresses
     * @return bool
     * @throws \Error
     * @throws \Exception
     * @throws \phpmailerException
     */
    public function sendFileToMail($filePath, array $mailAddresses)
    {
        $body = file_exists($filePath) ? file_get_contents($filePath) : 'File ' . $filePath . ' seems not to exist';
        if (empty($body)) {
            $body = 'No log for today!';
        }
        $mailer = $this->getMailer();
        $mailer->IsHTML(false);
        $mailer->Subject = "[" . \Config::paramsVariables('version') . "] Daily log alert";
        $mailer->Body = $body;
        $mailer->AltBody = $body;
        foreach ((array) $mailAddresses as $mail) {
            $mailer->AddAddress($mail);
        }
        return $mailer->Send();
    }

    /**
     * @return \PHPMailer
     * @throws \phpmailerException
     */
    public function getMailer()
    {
        if (!$this->mailerInstance) {
            $mailer = new PHPMailer(true);
            $mailer->IsHTML(false);
            $mailer->CharSet = "UTF-8";
            $mailer->SetFrom(\Config::mailRobot(), \Config::mailRobotName());
            $this->mailerInstance = $mailer;
        }
        return $this->mailerInstance;
    }

    /**
     * @todo Hard dependency with PHPMailer // see for abstraction layer and use PHPMailer as a driver
     * @param PHPMailer $mailer
     * @return $this
     */
    public function setMailer(PHPMailer $mailer)
    {
        $this->mailerInstance = $mailer;
        return $this;
    }
}