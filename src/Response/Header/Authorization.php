<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class Authorization extends Header
{
    const TYPE = 'Authorization';
    const TYPE_BEARER = 'Bearer';

    /**
     * @var string
     */
    protected $authorizationType;

    /**
     * @var string
     */
    protected $token;

    public function __construct($authType = self::TYPE_BEARER, $token = null)
    {
        $this->setAuthorizationType($authType);
        $this->setToken($token);
    }

    /**
     * @return string
     */
    public function getAuthorizationType()
    {
        return $this->authorizationType;
    }

    /**
     * @param string $authorizationType
     * @return Authorization
     */
    public function setAuthorizationType($authorizationType)
    {
        $this->authorizationType = $authorizationType;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return Authorization
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return $this->getAuthorizationType() . ' ' . ($this->getToken() ? : '');
    }

    /**
     * Type for the header. Can be used to determine header to send
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }
}
