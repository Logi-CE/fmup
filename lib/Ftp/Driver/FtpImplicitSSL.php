<?php
/**
 * @author jyamin
 */

namespace FMUP\Ftp\Driver;

use FMUP\Ftp\FtpAbstract;
use FMUP\Ftp\Exception as FtpException;
use FMUP\Logger;

class FtpImplicitSSL extends FtpAbstract implements Logger\LoggerInterface
{
    use Logger\LoggerTrait;

    const CURL_OPTIONS = 'curl_options';
    const PASSIVE_MODE = 'passive_mode';

    /** @var string $url */
    private $url;

    /**
     * @return array
     */
    protected function getCurlOptions()
    {
        return isset($this->settings[self::CURL_OPTIONS]) ? $this->settings[self::CURL_OPTIONS] : array();
    }

    /**
     * @return bool
     */
    protected function getPassiveMode()
    {
        return isset($this->settings[self::PASSIVE_MODE]) ? $this->settings[self::PASSIVE_MODE] : false;
    }

    /**
     * @return string
     */
    protected function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    protected function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * FtpImplicitSSL constructor.
     * @param array $params
     */
    public function __construct($params = array())
    {
        $params = array_replace_recursive(array(
            self::CURL_OPTIONS => array(
                CURLOPT_FTP_SSL => CURLFTPSSL_ALL,
                CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30,
            ),
         ), $params);
        parent::__construct($params);
    }

    /**
     * @param string $host
     * @param int $port
     * @throws FtpException
     * @return $this
     */
    public function connect($host, $port = 990)
    {
        $this->setSession($this->phpCurlInit());
        if (!$this->getSession()) {
            $this->log(Logger::ERROR, "Could not initialize cURL", (array)$this->getSettings());
            throw new FtpException("Could not initialize cURL");
        }
        $this->settings[self::CURL_OPTIONS][CURLOPT_PORT] = $port;
        $this->setUrl('ftps://' . $host . '/');
        if (!$this->getPassiveMode()) {
            $this->settings[self::CURL_OPTIONS][CURLOPT_FTPPORT] = '-';
        }
        return $this;
    }

    /**
     * @param string $user
     * @param string $pass
     * @throws FtpException
     * @return bool
     */
    public function login($user, $pass)
    {
        $this->settings[self::CURL_OPTIONS][CURLOPT_USERPWD] = $user . ':' . $pass;

        foreach ($this->getCurlOptions() as $optName => $optValue) {
            if (!$this->phpCurlSetOpt($this->getSession(), $optName, $optValue)) {
                $this->log(Logger::ERROR, 'Unable to set cURL option : ' . $optName, (array)$this->getSettings());
                throw new FtpException('Unable to set cURL option : ' . $optName);
            }
        }
        return true;
    }

    /**
     * @param string $localFile
     * @param string $remoteFile
     * @throws FtpException
     * @return bool
     */
    public function get($localFile, $remoteFile)
    {
        $file = $this->phpFopen($localFile, 'w');
        if (!$file) {
            $this->log(Logger::ERROR, 'Unable to open file to write : ' . $localFile, (array)$this->getSettings());
            throw new FtpException('Unable to open file to write : ' . $localFile);
        }
        $this->phpCurlSetOpt($this->getSession(), CURLOPT_URL, $this->getUrl() . $remoteFile);
        $this->phpCurlSetOpt($this->getSession(), CURLOPT_FOLLOWLOCATION, 1);
        $this->phpCurlSetOpt($this->getSession(), CURLOPT_RETURNTRANSFER, 1);
        $this->phpCurlSetOpt($this->getSession(), CURLOPT_UPLOAD, false);
        $this->phpCurlSetOpt($this->getSession(), CURLOPT_FILE, $file);
        $result = $this->phpCurlExec($this->getSession());
        $this->phpFclose($file);
        return $result;
    }

    /**
     * Put file on ftp server
     * @param string $remoteFile
     * @param string $localFile
     * @return bool
     * @throws FtpException
     */
    public function put($remoteFile, $localFile)
    {
        $file = $this->phpFopen($localFile, 'r');
        if (!$file) {
            $this->log(Logger::ERROR, 'Unable to open file to read : ' . $localFile, (array)$this->getSettings());
            throw new FtpException('Unable to open file to read : ' . $localFile);
        }
        $this->phpCurlSetOpt($this->getSession(), CURLOPT_URL, $this->getUrl() . $remoteFile);
        $this->phpCurlSetOpt($this->getSession(), CURLOPT_UPLOAD, 1);
        $this->phpCurlSetOpt($this->getSession(), CURLOPT_INFILE, $file);
        $this->phpCurlExec($this->getSession());
        $this->phpFclose($file);
        return !$this->phpCurlError($this->getSession());
    }


    /**
     * @param string $file
     * @return bool
     */
    public function delete($file)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function close()
    {
        $this->phpCurlClose($this->getSession());
        return true;
    }

    /**
     * @param string|null $url
     * @return resource
     * @codeCoverageIgnore
     */
    protected function phpCurlInit($url = null)
    {
        return curl_init($url);
    }

    /**
     * @param resource $ch
     * @return mixed
     * @codeCoverageIgnore
     */
    protected function phpCurlExec($ch)
    {
        return curl_exec($ch);
    }

    /**
     * @param resource $ch
     * @param int $option
     * @param mixed $value
     * @return bool
     * @codeCoverageIgnore
     */
    protected function phpCurlSetOpt($ch, $option, $value)
    {
        return curl_setopt($ch, $option, $value);
    }

    /**
     * @param resource $ch
     * @return string
     * @codeCoverageIgnore
     */
    protected function phpCurlError($ch)
    {
        return curl_error($ch);
    }

    /**
     * @param resource $ch
     * @codeCoverageIgnore
     */
    protected function phpCurlClose($ch)
    {
        curl_close($ch);
    }

    /**
     * @param string $filename
     * @param string $mode
     * @return resource
     * @codeCoverageIgnore
     */
    protected function phpFopen($filename, $mode)
    {
        return fopen($filename, $mode);
    }

    /**
     * @param resource $handle
     * @return bool
     * @codeCoverageIgnore
     */
    protected function phpFclose($handle)
    {
        return fclose($handle);
    }
}
