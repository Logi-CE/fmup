<?php
/**
 * Définit les formats des references
 **/
class FormatReferences
{
    public static function formaterReferenceObjet ($reference_objet, $type)
    {
        $retour = "";
        if ($reference_objet) {
            $retour = FormatReferences::formaterReference($reference_objet->getId(), $type) ;
        }
        return $retour;
    }
}
