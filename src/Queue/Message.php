<?php
/**
 * Message.php
 * @author: jmoulin@castelis.com
 */

namespace FMUP\Queue;

class Message
{
    private $original;
    private $translated;

    /**
     * Define original message without transformation
     * @param mixed $message
     * @return $this
     */
    public function setOriginal($message)
    {
        $this->original = $message;
        return $this;
    }

    /**
     * Retrieve defined original message
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Define Translated message from the original
     * @param $message
     * @return $this
     */
    public function setTranslated($message)
    {
        $this->translated = $message;
        return $this;
    }

    /**
     * Retrieve translated message
     * @return mixed
     */
    public function getTranslated()
    {
        return $this->translated;
    }
}
