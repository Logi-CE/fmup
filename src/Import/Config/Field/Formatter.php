<?php
namespace FMUP\Import\Config\Field;

/**
 *
 * @author csanz
 *
 */
interface Formatter
{
    /**
     * Modifie la valeur afin qu'elle corresponde au système
     * @param mixed $value
     * @return mixed formatted value
     */
    public function format($value);

    /**
     * Renvoi un message d'erreur correspondant au formatter
     * @param string|null $param
     * @return string message
     */
    public function getErrorMessage($param = null);

    /**
     * Retourne true si une erreur est survenue lors du formattage; false sinon
     * @return bool
     */
    public function hasError();
}
