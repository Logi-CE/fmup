<?php
namespace FMUP\Crypt\Driver;

use \FMUP\Crypt\CryptInterface;
use \FMUP\Crypt\Exception;

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
     * @param string $password
     * @throws Exception
     * @return string
     */
    public function unHash($password)
    {
        throw new Exception('Invalid method for this driver');
    }

}
