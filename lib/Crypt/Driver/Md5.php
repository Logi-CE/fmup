<?php
namespace FMUP\Crypt\Driver;

use \FMUP\Crypt\CryptInterface;

class Md5 implements CryptInterface
{

    /**
     * Hash the given password
     * @param string $password
     * @return string 
     */
    public function hash($password) {
        return md5($password);
    }
    
    /**
     * 
     * @param type $password
     * @throws \LogiCE\Exception
     */
    public function unHash($password)
    {
        throw new \LogiCE\Exception('Invalid method for this driver');
    }

}