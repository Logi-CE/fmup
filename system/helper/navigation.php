<?php

/**
 * Class Navigation
 * @deprecated
 */
class Navigation
{
    /**
     * Défini si le site est ouvert au public
     * @return booleen
     */
    public static function siteOuvert()
    {
        $retour = true;
        if (Config::paramsVariables('maintenance_forcee')) {
            $retour = false;
        }

        $day_number = date('w');
        $heure = date('H');
        foreach (Config::paramsVariables('maintenance_plages') as $plage) {
            list($var_jour, $var_heure_debut, $var_heure_fin) = $plage;
            if ($var_jour == -1) $var_jour = $day_number;
            if ($var_heure_debut == -1) $var_heure_debut = $heure;
            if ($var_heure_fin == -1) $var_heure_fin = $heure;
            if ($day_number == $var_jour && $heure <= $var_heure_fin && $heure >= $var_heure_debut) {
                $retour = false;
            }
        }
        
        if (Config::paramsVariables('utilise_parametres') && ParametreHelper::getInstance()->trouver('Maintenance')) {
            $retour = false;
        }
        
        return $retour;
    }
}
