<?php
/**
 * Classe ImportHelper
 * @author : afalaise
 * @description : cette classe gère toutes les données concernant l'import
 * @version 1.0
 * @todo : Cette classe doit être dépassée
 */
class ImportHelper
{
    /****************************************************
     *		Fonctiosn de modifications de données		*
     *****************************************************/
    
    public static function numberToFloat($valeur)
    {
        $valeur = preg_replace('|^(\d+[., ]\d*[1-9])([0]*)$|', '$1', $valeur);
        $valeur = preg_replace('|^(\d+[., ])([0]*)$|', '$1', $valeur);
        $valeur = preg_replace('|^(\d+)([., ])$|', '$1', $valeur);
        return $valeur;
    }
    
    
    /************************************************
     *					Constantes					*
     ************************************************/
    
    // Pour l'affichage des erreurs
    public static function getNbCaractereAfficheImport()
    {
        return 33;
    }
    
    // Messages réservés à l'import
    public static function getMessageIndexImport()
    {
        return "Le fichier doit être <strong>colonné</strong> (<em>CSV</em>), séparé par des <strong>points virgules</strong>, et contenir une ligne d'<strong>en-têtes</strong>.";
    }
    public static function getMessageAideImport()
    {
        return "Les champs en rouge sont obligatoires.";
    }
    
    public static function warningOrange()
    {
        return 1;
    }
    public static function warningJaune()
    {
        return 2;
    }
    
    
    /************************************************
     *				Erreurs de l'import				*
     ************************************************/
    
    // Erreurs de base de l'import
    public static function getIdMessageErreurImportChampVide()
    {
        return 1;
    }
    public static function getIdMessageErreurImportChampNonVide()
    {
        return 2;
    }
    public static function getIdMessageErreurImportChampInconnu()
    {
        return 3;
    }
    public static function getIdMessageErreurImportChampNonNumerique()
    {
        return 4;
    }
    public static function getIdMessageErreurImportChampNonDate()
    {
        return 5;
    }
    public static function getIdMessageErreurImportDoublonDansLaBase()
    {
        return 6;
    }
    public static function getIdMessageErreurImportDoublonDansLeFichier()
    {
        return 7;
    }
    public static function getIdMessageErreurImportChampIncorrect()
    {
        return 8;
    }
    public static function getIdMessageErreurImportMiseEnErreur()
    {
        return 9;
    }
    
    // Erreurs de l'import
    public static function getIdMessageErreurImportStatutCommandeIncorrect()
    {
        return 10;
    }
    public static function getIdMessageErreurImportChampNonCorrespondantCommande()
    {
        return 11;
    }
    public static function getIdMessageErreurImportChampsPareil()
    {
        return 12;
    }
    public static function getIdMessageErreurImportLignesDoublon()
    {
        return 13;
    }
    public static function getIdMessageErreurImportLignesIncompatibles()
    {
        return 14;
    }
    public static function getIdMessageErreurImportLienNvOtInexistant()
    {
        return 15;
    }
    public static function getIdMessageErreurImportChampNonCorrespondantOt()
    {
        return 16;
    }
    public static function getIdMessageErreurImportLienNtOtDoublon()
    {
        return 17;
    }
    public static function getIdMessageErreurImportOtPresentDansLigne()
    {
        return 18;
    }
    public static function getIdMessageErreurImportFactureDejaExistante()
    {
        return 19;
    }
    public static function getIdMessageErreurImportTailleExcessive()
    {
        return 20;
    }
    public static function getIdMessageErreurImportPointDepartIncorrect()
    {
        return 21;
    }
    public static function getIdMessageErreurImportPointArriveeIncorrect()
    {
        return 22;
    }
    public static function getIdMessageErreurImportStatutDemandeIncorrect()
    {
        return 23;
    }
    public static function getIdMessageErreurImportChampNonCorrespondantDemande()
    {
        return 24;
    }
    public static function getIdMessageErreurImportOtExistant()
    {
        return 25;
    }
    public static function getIdMessageErreurImportOtNonPresent()
    {
        return 26;
    }
    public static function getIdMessageErreurImportDateInvalide()
    {
        return 27;
    }
    public static function getIdMessageErreurImportCodeNvUtilise()
    {
        return 28;
    }
    public static function getIdMessageErreurImportDemandeExistante()
    {
        return 29;
    }
    public static function getIdMessageErreurImportFluxEnDoublon()
    {
        return 30;
    }
    public static function getIdMessageErreurImportOtNonCorrespondantPointDepart()
    {
        return 31;
    }
    public static function getIdMessageErreurImportLienRefOtDoublon()
    {
        return 32;
    }
    public static function getIdMessageErreurImportLienNvOtDejaExistant()
    {
        return 33;
    }
    public static function getIdMessageErreurImportTotalIncorrect()
    {
        return 34;
    }
    public static function getIdMessageErreurImportEtaNonCorrespondant()
    {
        return 35;
    }
    public static function getIdMessageErreurImportMoisNonCorrespondantTrimestre()
    {
        return 36;
    }
    public static function getIdMessageErreurImportTailleRibIncorrect()
    {
        return 37;
    }
    public static function getIdMessageErreurImportRibIncorrect()
    {
        return 37;
    }
    public static function getIdMessageErreurImportTitreRecetteContientBordereau()
    {
        return 38;
    }
    public static function getIdMessageErreurImportMauvaisStatutTitreRecette()
    {
        return 39;
    }
    public static function getIdMessageErreurImportStatutDejaValide()
    {
        return 40;
    }
    public static function getIdMessageErreurImportConventionManquante()
    {
        return 41;
    }
    public static function getIdMessageErreurImportMailIncorrect()
    {
        return 42;
    }
    public static function getIdMessageErreurImportTonnageNonVideSiDateVide()
    {
        return 43;
    }
    public static function getIdMessageErreurImportDateOuvertureNonCorrespondantPointCollecte()
    {
        return 44;
    }
    public static function getIdMessageErreurImportEcoOrganismeNonCorrespondant()
    {
        return 45;
    }
    public static function getIdMessageErreurImportTitreRecetteDejaRembourse()
    {
        return 46;
    }
    public static function getIdMessageErreurImportMontantTitreRecetteDepasse()
    {
        return 47;
    }
    public static function getIdMessageErreurImportMontantTitreRecetteAtteint()
    {
        return 48;
    }
    public static function getIdMessageErreurImportBordereauExistant()
    {
        return 49;
    }
    public static function getIdMessageErreurImportChampNonHoraire()
    {
        return 50;
    }
    public static function getIdMessageErreurImportChampRegularisation()
    {
        return 51;
    }
    
    /************************************************
     *		Descripion des champs de l'import		*
     ************************************************/
    
    // Exemple de déclaration de tableau de contenu d'import
    /*public static function tableauImportEta()
    {
        return array(
            array(
                "libelle" => "code_collectivite",
                "type" => "code",
                "oblig" => true,
                "nom" => "collectivité",
                "table_origine" => "collectivites",
                "champ_origine" => "code",
                "warning" => false,
                "spec" => false,
                "exemple" => "02-0012"
            ),
            array(
                "libelle" => "code_point_collecte",
                "type" => "code",
                "oblig" => true,
                "nom" => "point de collecte",
                "table_origine" => "points_collecte",
                "champ_origine" => "code",
                "warning" => false,
                "spec" => false,
                "exemple" => "D001"
            )
        );
        
    }*/
}
