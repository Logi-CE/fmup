<?php
namespace FMUP\Crypt;

interface CryptInterface
{
	/**
	 * Hash the given password
	 * @param string $password
	 * @return string 
	 */
	public function hash($password);

	
}