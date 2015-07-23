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
     * @param unknown $value
     */
    public function format($value);
    
    /**
     * Renvoi un message d'erreur correspondant au formatter
     *
     * @return string message
     */
    public function getErrorMessage($param = null);
    
    /**
     * Retourne true si une erreur est survenue lors du formattage; false sinon
     * @return boolean
     */
    public function hasError();
    
}