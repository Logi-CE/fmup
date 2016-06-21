<?php
namespace FMUP\Import\Config\Field\Formatter;

use FMUP\Import\Config\Field\Formatter;

class DateSQL implements Formatter
{
    const DATE = 'Y-m-d';
    const DATE_TIME = 'Y-m-d H:i:s';

    /**
     * @var bool
     */
    private $hasError = false;

    /**
     * @var string
     */
    private $datePattern;

    /**
     * DateSQL constructor.
     * @param string $format
     */
    public function __construct($format = self::DATE_TIME)
    {
        $this->setDatePattern($format);
    }

    /**
     * @param string $value
     * @return string
     */
    public function format($value)
    {
        if ($value == "") {
            $this->hasError = true;
            return "Champ vide";
        } else {
            $result = $this->toDate($value);
            if ($result) {
                return $result;
            } else {
                $this->hasError = true;
                return $value;
            }
        }
    }

    protected function toDate($value)
    {
        $date = \DateTime::createFromFormat('d/m/Y', $value);
        if (!$date) {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
        }
        return $date->format($this->getDatePattern());
    }

    public function getErrorMessage($value = null)
    {
        return "La valeur $value n'est pas convertible";
    }

    public function hasError()
    {
        return $this->hasError;
    }

    /**
     * @return string
     */
    protected function getDatePattern()
    {
        return $this->datePattern;
    }

    /***
     * @param $datePattern
     * @return $this
     */
    protected function setDatePattern($datePattern)
    {
        $this->datePattern = $datePattern;
        return $this;
    }
}
