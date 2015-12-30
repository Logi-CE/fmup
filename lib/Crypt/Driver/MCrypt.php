<?php
namespace FMUP\Crypt\Driver;

use FMUP\Crypt\CryptInterface;

/**
 * Description of MCrypt
 *
 * @author sweffling
 */
class MCrypt implements CryptInterface
{
    const KEY = 'secret_test_key';

    private $key = null;
    private $iv = null;

    /**
     * @return string
     */
    private function getKey()
    {
        if (!$this->key) {
            $this->key = self::KEY;
        }
        return $this->key;
    }

    /**
     * @param string $key
     * @return \FMUP\Crypt\Driver\MCrypt
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getIv()
    {
        if (!$this->iv) {
            $this->iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
        }
        return $this->iv;
    }

    /**
     * Hash the given string
     * @param string $string
     * @return string
     */
    public function hash($string)
    {
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->getKey(), $string, MCRYPT_MODE_ECB, $this->getIv());
    }

    /**
     * unhash
     * @param string $string
     * @return string
     */
    public function unHash($string)
    {
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->getKey(), $string, MCRYPT_MODE_ECB, $this->getIv());
    }
}
