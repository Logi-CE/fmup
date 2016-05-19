<?php
namespace FMUP\Ftp\Driver;

use FMUP\Ftp\FtpAbstract;
use FMUP\Ftp\Exception as FtpException;
use FMUP\Logger;

class Sftp extends FtpAbstract implements Logger\LoggerInterface
{
    use Logger\LoggerTrait;

    const METHODS = 'methods';
    const CALLBACKS = 'callbacks';

    private $sftpSession;

    public function getSftpSession()
    {
        if (!$this->sftpSession) {
            $this->sftpSession = $this->ssh2Sftp($this->getSession());
        }
        return $this->sftpSession;
    }

    /**
     * @return array|null
     */
    public function getMethods()
    {
        return isset($this->settings[self::METHODS]) ? $this->settings[self::METHODS] : null;
    }

    /**
     * @return array|null
     */
    public function getCallbacks()
    {
        return isset($this->settings[self::CALLBACKS]) ? $this->settings[self::CALLBACKS] : null;
    }

    /**
     * @param string $host
     * @param int $port
     * @return $this
     * @throws FtpException
     */
    public function connect($host, $port = 22) {

        $this->setSession($this->ssh2Connect($host, $port, $this->getMethods(), $this->getCallbacks()));
        return $this;
    }

    /**
     * @param string $host
     * @param int $port
     * @param array|null $methods
     * @param array|null $callbacks
     * @return resource
     * @codeCoverageIgnore
     */
    protected function ssh2Connect($host, $port = 22, $methods, $callbacks)
    {
        return ssh2_connect($host, $port, $methods, $callbacks);
    }

    /**
     * @param string $user
     * @param string $pass
     * @return bool
     * @throws FtpException
     */
    public function login($user, $pass)
    {
        $ret = $this->ssh2AuthPassword($this->getSession(), $user, $pass);
        if (!$ret) {
            $this->log(Logger::ERROR, "Unable to login to SFTP server", (array)$this->getSettings());
            throw new FtpException('Unable to login to the SFTP server');
        }
        return $ret;
    }

    /**
     * @param resource $session
     * @param string $username
     * @param string $password
     * @return bool
     * @codeCoverageIgnore
     */
    protected function ssh2AuthPassword($session, $username, $password)
    {
        return ssh2_auth_password($session, $username, $password);
    }

    /**
     * @param string $localFile
     * @param string $remoteFile
     * @return int
     */
    public function get($localFile, $remoteFile)
    {
        return $this->filePutContents(
            $localFile,
            $this->fileGetContents(
                'ssh2.sftp://' . $this->getSftpSession() . '/' . $remoteFile,
                'r'
            )
        );
    }

    /**
     * @param string $fileName
     * @return string
     * @codeCoverageIgnore
     */
    protected function fileGetContents($fileName)
    {
        return file_get_contents($fileName);
    }

    /**
     * @param string $fileName
     * @param mixed $data
     * @return int
     * @codeCoverageIgnore
     */
    protected function filePutContents($fileName, $data)
    {
        return file_put_contents($fileName, $data);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function delete($file)
    {
        return $this->ssh2SftpUnlink($this->getSftpSession(), $file);
    }

    /**
     * @param resource $sftpSession
     * @param string $path
     * @return bool
     * @codeCoverageIgnore
     */
    protected function ssh2SftpUnlink($sftpSession, $path)
    {
        return ssh2_sftp_unlink($sftpSession, $path);
    }

    /**
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param resource $session
     * @return resource
     * @codeCoverageIgnore
     */
    protected function ssh2Sftp($session)
    {
        return ssh2_sftp($session);
    }
}
