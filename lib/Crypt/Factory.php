<?php
namespace FMUP\Crypt;

final class Factory
{
	const DRIVER_MD5 = "Md5";

	private function __construct()
	{
	}

	/**
	 * 
	 * @param string $driver
	 * @param array $params
	 * @return \FMUP\Crypt\CryptInterface
	 * @throws \FMUP\Exception
	 */
	public static function create($driver = self::DRIVER_MD5)
	{
		$class = 'FMUP\\Crypt\\Driver\\' . $driver;
		if (!class_exists($class)) {
			throw new \FMUP\Exception('Unable to create ' . $class);
		}
		$instance = new $class();
		if (!$instance instanceof CryptInterface) {
			throw new \FMUP\Exception('Unable to create ' . $class);
		}
		return $instance;
	}
}