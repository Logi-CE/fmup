<?php
namespace FMUP\Ftp;

use FMUP\Logger;

abstract class FtpAbstract implements FtpInterface, Logger\LoggerInterface
{
    use Logger\LoggerTrait;
    protected $settings;
    private $session;

    /**
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->settings = $params;
    }

    /**
     * @param resource $session
     * @return $this
     */
    public function setSession($session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return resource
     * @throws Exception
     */ 
    public function getSession()
    {
        if (!is_resource($this->session)) {
            $this->log(Logger::ERROR, "Unable to connect to FTP server", (array)$this->getSettings());
            throw new Exception('Unable to connect to the FTP server');
        }
        return $this->session;
    }

    /**
     * Retrieve settings
     * @param string|null $param
     * @return mixed
     */
    protected function getSettings($param = null)
    {
        return is_null($param) ? $this->settings : (isset($this->settings[$param]) ? $this->settings[$param] : null);
    }
}
