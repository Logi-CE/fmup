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
            $this->key = 'test';
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
        return mcrypt_encrypt(MCRYPT_3DES, $this->getKey(), $password, MCRYPT_MODE_ECB);
    }

    /**
     * 
     * @param type $password
     * @return type
     */
    public function unHash($password)
    {
        return mcrypt_decrypt(MCRYPT_3DES, $this->getKey(), $password, MCRYPT_MODE_ECB);
    }

}
