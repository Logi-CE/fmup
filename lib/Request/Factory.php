<?php
namespace FMUP\Request;

class Factory
{
    use \FMUP\Sapi\OptionalTrait;

    const CONTENT_TYPE = 'Content-Type';
    const JSON_HEADER = 'application/json';

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

    private function isCli()
    {
        return $this->getSapi()->get() == \FMUP\Sapi::CLI;
    }

    private function isJson()
    {
        $headers = !$this->isCli() ? (array)$this->getHeaders() : [];
        return isset($headers[self::CONTENT_TYPE]) && in_array(self::JSON_HEADER, (array)$headers[self::CONTENT_TYPE]);
    }

    private function getHeaders()
    {
        return (function_exists('getallheaders') ? getallheaders() : false);
    }
}
