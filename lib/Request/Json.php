<?php
namespace FMUP\Request;

/**
 * Class Json
 * @package FMUP\Request
 */
class Json extends Http
{
    private $json;

    /**
     * Transform request json body to array
     * @return array
     */
    private function getJson()
    {
        return $this->json = (array)($this->json ?: json_decode(file_get_contents('php://input'), true));
    }

    /**
     * Return requested value in Json structure
     * @param string $name Name to retrieve
     * @param mixed $defaultValue Value returned if name is not defined in query
     * @return mixed
     */
    public function get($name, $defaultValue = null)
    {
        return $this->has($name) ? $this->getJson()[$name] : $defaultValue;
    }

    /**
     * Check if a parameter name exists
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->getJson());
    }
}
