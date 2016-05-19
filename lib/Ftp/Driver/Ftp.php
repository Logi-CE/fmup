<?php
namespace FMUP\Ftp\Driver;

use FMUP\Ftp\FtpAbstract;
use FMUP\Ftp\Exception as FtpException;
use FMUP\Logger;

class Ftp extends FtpAbstract
{
    const TIMEOUT = 'timeout';
    const MODE = 'mode';
    const RESUME_POS = 'resumepos';

    /**
     * Connection timeout in second
     * @return int
     */
    protected function getTimeout()
    {
        return isset($this->settings[self::TIMEOUT]) ? $this->settings[self::TIMEOUT] : 90;
    }

    /**
     * Transfer mode (FTP_ASCII | FTP_BINARY)
     * @return int
     */
    protected function getMode()
    {
        return isset($this->settings[self::MODE]) ? $this->settings[self::MODE] : FTP_ASCII;
    }

    /**
     * Begin download position in remote file
     * @return int
     */
    protected function getResumePos()
    {
        return isset($this->settings[self::RESUME_POS]) ? $this->settings[self::RESUME_POS] : 0;
    }

    /**
     * @param string $host
     * @param int $port
     * @return $this
     * @throws FtpException
     */
    public function connect($host, $port = 21) {

        $this->setSession($this->ftpConnect($host, $port, $this->getTimeout()));
        return $this;
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $timeout
     * @return resource
     * @codeCoverageIgnore
     */
    protected function ftpConnect($host, $port = 21, $timeout = 90)
    {
        return ftp_connect($host, $port, $timeout);
    }

    /**
     * @param string $user
     * @param string $pass
     * @return bool
     * @throws FtpException
     */
    public function login($user, $pass)
    {
        $ret = $this->ftpLogin($this->getSession(), $user, $pass);
        if (!$ret) {
            $this->log(Logger::ERROR, "Unable to login to FTP server", (array)$this->getSettings());
            throw new FtpException('Unable to login to the FTP server');
        }
        return $ret;
    }

    /**
     * @param resource $ftpStream
     * @param string $username
     * @param string $password
     * @return bool
     * @codeCoverageIgnore
     */
    protected function ftpLogin($ftpStream, $username, $password)
    {
        return ftp_login($ftpStream, $username, $password);
    }

    /**
     * @param string $localFile
     * @param string $remoteFile
     * @return bool
     */
    public function get($localFile, $remoteFile)
    {
        return $this->ftpGet($this->getSession(), $localFile, $remoteFile, $this->getMode(), $this->getResumePos());
    }

    /**
     * @param resource $ftpStream
     * @param string $localFile
     * @param string $remoteFile
     * @param int $mode
     * @param int $resumePos
     * @return bool
     * @codeCoverageIgnore
     */
    protected function ftpGet($ftpStream, $localFile, $remoteFile, $mode, $resumePos)
    {
        return ftp_get($ftpStream, $localFile, $remoteFile, $mode, $resumePos);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function delete($file)
    {
        return $this->ftpDelete($this->getSession(), $file);
    }

    /**
     * @param resource $ftpStream
     * @param string $path
     * @return bool
     * @codeCoverageIgnore
     */
    protected function ftpDelete($ftpStream, $path)
    {
        return ftp_delete($ftpStream, $path);
    }

    /**
     * @return bool
     */
    public function close()
    {
        return $this->ftpClose($this->getSession());
    }

    /**
     * @param resource $ftpStream
     * @return bool
     * @codeCoverageIgnore
     */
    protected function ftpClose($ftpStream)
    {
        return ftp_close($ftpStream);
    }
}
