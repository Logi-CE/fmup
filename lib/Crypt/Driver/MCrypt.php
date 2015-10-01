<?php

namespace FMUP\Crypt\Driver;

/**
 * Description of MCrypt
 *
 * @author sweffling
 */
use \FMUP\Crypt\CryptInterface;

class MCrypt implements CryptInterface
{

    private $key = null;

    /**
     * 
     * @return string
     */
    private function getKey()
    {
        if (!$this->key) {
            $this->key = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
        }
        return $this->key;
    }

    /**
     * Hash the given password
     * @param string $password
     * @return string 
     */
    public function hash($password)
    {
        return mcrypt_encrypt(MCRYPT_3DES, $this->getKey(), $password, MCRYPT_MODE_CBC);
    }

    /**
     * 
     * @param type $password
     * @return type
     */
    public function unHash($password)
    {
        return mcrypt_decrypt(MCRYPT_3DES, $this->getKey(), $password, MCRYPT_MODE_CBC);
    }

}
