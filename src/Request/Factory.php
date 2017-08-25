<?php
namespace FMUP\Request;

class Factory
{
    use \FMUP\Sapi\OptionalTrait;

    const CONTENT_TYPE = 'Content-Type';
    const JSON_HEADER = 'application/json';

    /**
     * Build the correct request object
     * @return Cli|Http|Json
     */
    public function get()
    {
        if ($this->isCli()) {
            return new \FMUP\Request\Cli();
        }
        if ($this->isJson()) {
            return new \FMUP\Request\Json();
        }
        return new \FMUP\Request\Http();
    }

    /**
     * Checks if request should be cli
     * @return bool
     */
    private function isCli()
    {
        return $this->getSapi()->get() == \FMUP\Sapi::CLI;
    }

    /**
     * Checks if request should be json
     * @return bool
     */
    private function isJson()
    {
        $headers = !$this->isCli() ? (array)$this->getHeaders() : [];
        return isset($headers[self::CONTENT_TYPE]) && in_array(self::JSON_HEADER, (array)$headers[self::CONTENT_TYPE]);
    }

    /**
     * Retrieve headers of the request
     * @codeCoverageIgnore
     * @return array|false
     */
    protected function getHeaders()
    {
        return function_exists('getallheaders') ? getallheaders() : false;
    }
}
