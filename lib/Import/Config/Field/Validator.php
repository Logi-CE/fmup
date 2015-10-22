<?php
namespace FMUP\Import\Config\Field;

/**
 *
 * @author csanz
 *
 */
interface Validator
{

    /**
     * Valide la valeur reçue en paramètre
     *
     * @param string $value
     * @return boolean
     */
    public function validate($value);

    /**
     * Renvoi un message d'erreur correspondant au validator
     *
     * @return string message
     */
    public function getErrorMessage();
}
