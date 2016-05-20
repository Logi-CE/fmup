<?php
namespace FMUP\Ftp;

interface FtpInterface
{
    /**
     * Construct instance must allow array of parameters
     * @param array $params
     */
    public function __construct($params);

    /**
     * Connect to FTP server
     * @param string $host
     * @param int $port
     * @return $this
     * @throws Exception
     */
    public function connect($host, $port = 21);

    /**
     * Login to FTP server
     * @param string $user
     * @param string $pass
     * @return bool
     * @throws Exception
     */
    public function login($user, $pass);

    /**
     * Get ftp servers' file
     * @param string $localFile
     * @param string $remoteFile
     * @return bool
     */
    public function get($localFile, $remoteFile);

    /**
     * Delete file on ftp server
     * @param string $file
     * @return bool
     */
    public function delete($file);

    /**
     * Close connection with the ftp server
     * @return bool
     */
    public function close();
}
