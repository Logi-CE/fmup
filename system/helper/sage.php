<?php
/**
 * Classe permettant de générer des imports pour sage
 * @author vbitaud
 */
class Sage
{
    const DEFAULT_REFERENCE = 'Facture manuelle';

    public $buffer = '';

    /**
     * Génération des headers pour le document
     * @return Les headers
     */
    public function headers()
    {
        $strtmp = "#FLG 000\r\n";
        $strtmp .= "#VER 14\r\n";
        $strtmp .= "#DEV\r\n";
        $this->addToBuffer($strtmp);
        return $strtmp;
    }

    /**
     * Génération des footers pour le document
     * @return Les footers
     */
    public function footers()
    {
        $strtmp = "#FIN";
        $this->addToBuffer($strtmp);
        return $strtmp;
    }

    /**
     * Créée une ligne d'analytique
     *
     * @param  integer $nbPlan
     * @param  string  $axeAna
     * @param  float   $montant
     * @param  integer $quantite
     * @return
     */
    public function ligneAna($nbPlan = 1, $axeAna="", $montant=0, $quantite=0)
    {
        //axeAna: 13 #, montant: 14 #
        if ($axeAna == "")
        {
            $axeAna = Doctrine::getTable('ParametreGlobal')->getParametre(ParametreGlobal::SECTION_ANALYTIQUE_DEFAUT);
        }
        $axeAna = strtoupper($axeAna);
        $strtmp = "#MECA\r\n";
        $strtmp .= $nbPlan."\r\n";
        $strtmp .= $axeAna."\r\n";
        $strtmp .= number_format(abs($montant), 2, ",", "")."\r\n";
        $strtmp .= number_format($quantite, 2, ",", "")."\r\n";
        $this->addToBuffer($strtmp);
        return $strtmp;
    }

    /**
     * Créée une ligne de comptabilité
     *
     * @param string $codeJourn
     * @param string $dateEcr
     * @param string $piece
     * @param string $numFacture
     * @param string $general
     * @param string $tiers
     * @param string $intitule
     * @param string $dateEcheance
     * @param string $montant
     * @param string $reference
     * @param string $contrepartieGene
     * @return string
     */
    public function ligneCompta($codeJourn,$dateEcr,$piece,$numFacture,$general,$tiers,$intitule, $dateEcheance=null, $montant, $reference, $contrepartieGene="")
    {
        // piece: 13 #, numFacture: 17#
        // general: 13 #, tiers: 17 #, intitule: 35 #
        // montant: <0 débit, >0 credit
        // reference: 17 #
        $intitule   = $this->removeSpecialCaracters($intitule);
        $tiers      = strtoupper($tiers);
        $numFacture = str_replace(' ', '', $numFacture);
        $reference  = substr($reference, 0, 17);
        $intitule   = substr($intitule, 0, 35);
        $piece      = substr($piece, 0, 13);
        $numFacture = substr($numFacture, 0, 17);

        $strtmp = "#MECG\r\n";
        $strtmp .= $codeJourn."\r\n";
        $strtmp .= $this->dateFormat($dateEcr)."\r\n";
        $strtmp .= $this->dateFormat(date("d/m/Y"))."\r\n";
        $strtmp .= $piece."\r\n";
        $strtmp .= $numFacture."\r\n";
        $strtmp .= "\r\n"; //piece de treso
        $strtmp .= $general."\r\n";
        $strtmp .= $contrepartieGene."\r\n"; //general contrepartie
        $strtmp .= $tiers."\r\n";
        $strtmp .= "\r\n"; //tiers contrepartie
        $strtmp .= $intitule."\r\n";
        $strtmp .= "0\r\n"; //num regl
        $strtmp .= $this->dateFormat($dateEcheance)."\r\n";
        $strtmp .= "0,000000\r\n"; // parite
        $strtmp .= "0,00\r\n"; // qté
        $strtmp .= "0\r\n"; // num devise
        if ($montant < 0) {
            $strtmp .= "0\r\n";
        } else {
            $strtmp .= "1\r\n";
        }
        $strtmp .= number_format(abs($montant), 2, ",", "")."\r\n";
        $strtmp .= "\r\n"; //num lettre montant
        $strtmp .= "\r\n"; //num lettre devise
        $strtmp .= "\r\n"; //pointage
        $strtmp .= "0\r\n"; //nb rappel
        $strtmp .= "0\r\n"; //type
        $strtmp .= "0\r\n"; //revision ?
        $strtmp .= "\r\n"; //montant devise
        $strtmp .= "\r\n"; //code taxe
        $strtmp .= "0\r\n"; //norme
        $strtmp .= "0\r\n"; //provenance
        $strtmp .= "0\r\n"; //type penalité
        $strtmp .= "\r\n"; //date penalite
        $strtmp .= "\r\n"; //date relance
        $strtmp .= "\r\n"; //date rappro
        $strtmp .= $reference."\r\n"; //reference
        $strtmp .= "0\r\n"; //statut reglement
        $strtmp .= "\r\n"; //montant regle
        $strtmp .= "\r\n"; //date der reglement
        $strtmp .= "\r\n"; //date ope

        $this->addToBuffer($strtmp);
        return $strtmp;
    }


    /*
     * génère un bloc d'écriture typé SAGE pour l'intégration de compte Tiers
     *
     * @$TypeTiers :
     *                 0=client
     *                1=fournisseur
     *                2=salarié
     *                3=autre
     * @$ComptePrincipal : compte général par défaut du tiers (41100000)
     * @$tiers : array() données du TIERS à créer
     *                 $tiers["code"]
     *                 $tiers["libelle"]
     *                 $tiers["adresse_1"]
     *                 $tiers["adresse_2"]
     *                 $tiers["code_postal"]
     *                 $tiers["ville"]
     *                 $tiers["pays"]
     */
    public function ligneComptaTiers($tiers, $TypeTiers, $ComptePrincipal){

        //CONSTANTES
        $FALSE = "0";
        $TRUE = "1";
        $ZERO_0000 = "0,0000";
        $ZERO_00 = "0,00";

        // le compte tier est intégré à la date du jour
        $DATEINTEGRATION = $this->dateFormat(date("d/m/Y"));
        $RC = "\r\n"; //  <=> chr(13).chr(10);

        $code_postal = $this->removeSpecialCaracters(Sage::formatExport($tiers["code_postal"]));
        $code_postal = str_replace(' ', '', $code_postal);
        $code_postal = str_replace('-', '', $code_postal);

        $code = str_replace('_', '', $tiers["code"]);
        $code = strtoupper($code);

        $data = "#MPCT".$RC;

        $data .= $code.$RC; //Numéro compte tiers
        $data .= $this->removeSpecialCaracters(substr(Sage::formatExport($tiers["libelle"]),0,35)).$RC; //Intitulé du compte
        $data .= $TypeTiers.$RC; //Type
        $data .= $ComptePrincipal.$RC; //Numéro compte général principal
        $data .= $RC; //Qualité
        $data .= $this->removeSpecialCaracters(substr(Sage::formatExport($tiers["libelle"]),0,17)).$RC; //Abrégé
        $data .= $RC; //Contact
        $data .= $this->removeSpecialCaracters(Sage::formatExport(substr($tiers["adresse_1"],0,35))).$RC; //Adresse
        $data .= $this->removeSpecialCaracters(Sage::formatExport(substr($tiers["adresse_2"],0,35))).$RC; //Adresse complément
        $data .= $code_postal.$RC;  //Code postal
        $data .= $this->removeSpecialCaracters(substr(Sage::formatExport($tiers["ville"]),0,35)).$RC;  //Ville
        $data .= $RC; //Code région
        $data .= $this->removeSpecialCaracters(substr(Sage::formatExport($tiers["pays"]),0,35)).$RC;  //Pays
        $data .= $RC; //Raccourci
        $data .= $FALSE.$RC; //Numéro devise  (0=aucun, sinon position dans table des devises)
        $data .= $RC; //Code NAF (APE)
        $data .= $RC; //N° Identifiant
        $data .= $RC; //N° Siret
        $data .= $RC; //Valeurs Statistiques
        $data .= $RC; //Commentaire
        $data .= $RC; //Encours
        $data .= $RC; //Plafond assurance crédit
        $data .= $RC; //Numéro compte tiers payeur
        $data .= $RC; //Code risque
        $data .= $RC; //Catégorie tarifaire
        $data .= $RC; //Montant taux
        $data .= $RC; //Catégorie comptable
        $data .= $RC; //Périodicité
        $data .= $RC; //Nombre de factures
        $data .= $ZERO_00.$RC; //Un BL par facture
        $data .= $ZERO_00.$RC; //Langue
        $data .= $code.$RC; //Code Edi1
        $data .= $TRUE.$RC; //Code Edi2
        $data .= $TRUE.$RC; //Code Edi3
        $data .= $ZERO_0000.$RC; //Expédition
        $data .= $ZERO_0000.$RC; //Condition
        $data .= $ZERO_0000.$RC; //Saut lignes
        $data .= $ZERO_0000.$RC; //Option lettrage
        $data .= $TRUE.$RC; //Validation des dates d'échéance
        $data .= $TRUE.$RC; //Mise en sommeil
        $data .= $TRUE.$RC; //Contrôle de l'encours
        $data .= $FALSE.$RC; //Date de création
        $data .= $FALSE.$RC; //Hors rappel/relevé
        $data .= $RC; //Numéro analytique
        $data .= $RC;
        $data .= $RC;
        $data .= $TRUE.$RC; //Numéro section analytique
        $data .= $TRUE.$RC; //Téléphone
        $data .= $TRUE.$RC; //Télécopie
        $data .= $TRUE.$RC; //Adresse e-mail
        $data .= $FALSE.$RC; //Site
        $data .= $FALSE.$RC; //Numéro EASY
        $data .= $FALSE.$RC; //Placé sous surveillance
        $data .= $DATEINTEGRATION.$RC; //Date création société
        $data .= $FALSE.$RC; //Forme juridique
        $data .= $FALSE.$RC; //Effectif
        $data .= $RC; //Chiffre d'affaires
        $data .= $RC; //Résultat net
        $data .= $RC; //Incidents de paiement
        $data .= $RC; //Date du dernier incident
        $data .= $RC; //Privilèges
        $data .= $RC; //Régularité des paiements
        $data .= $FALSE.$RC; //Cotation de la solvabilité
        $data .= $RC; //Date dernière mise à jour
        $data .= $RC; //Objet dernière mise à jour
        $data .= $RC; //Date arrêté de bilan
        $data .= $ZERO_0000.$RC; //Nombre de mois du bilan
        $data .= $ZERO_0000.$RC; //Numéro plan IFRS
        $data .= $FALSE.$RC; //Numéro section IFRS
        $data .= $RC; //Priorité livraison
        $data .= $FALSE.$RC; //Livraison partielle
        $data .= $RC; //Intitulé modèle de règlement
        $data .= $RC; //Non soumis à pénalités de retard
        $data .= $RC; //Code banque élément banque
        $data .= $RC; //Code Guichet élément banque
        $data .= $RC; //Compte élément banque
        $data .= $FALSE.$RC; //Numéro devise élément banque
        $data .= $FALSE.$RC; //Numéro tiers centrale d’achat
        $data .= $RC; //Collaborateur nom
        $data .= $FALSE.$RC; //Collaborateur prénom
        $data .= $FALSE.$RC; //Date fermeture début
        $data .= $RC; //Date fermeture fin
        $data .= $FALSE.$RC; //Format facture
        $data .= $RC; //Type NIF
        $data .= $RC; //Intitulé représentant légal
        $data .= $RC; //NIF représentant légal
        $data .= $FALSE.$RC;

        $data .= "\r\n";
        $data .= "\r\n";
        $data .= "\r\n";
        $data .= "\r\n";
        $data .= "\r\n";
        $data .= $FALSE."\r\n";
        $data .= $FALSE."\r\n";
        $data .= "\r\n";
        $data .= "\r\n";
        $data .= $ComptePrincipal.$RC; //Numéro compte général ratta­ché1

        $this->addToBuffer($data);

        return $data;
    }

    /**
     * Formats the date
     *
     * @param  string $date
     * @return string
     */
    public function dateFormat($date)
    {
        //format entree = JJ/MM/AAAA
        //format sortie = JJMMAA
        if($date == "" ) return "";
        $date = str_replace("-", "/", $date);
        list($jour, $mois, $annee) = explode("/", $date);

        $jour = substr("00".$jour,-2);
        $mois = substr("00".$mois,-2);
        $annee = substr($annee,-2);

        return $jour.$mois.$annee;
    }

    /**
     * Generates a file
     *
     * @param string $file
     */
    public function ToFile($file)
    {
      $fp=fopen($file, 'a');
      if($fp!=0)
      {
        fwrite($fp, $this->getBuffer() . " ");
        fclose($fp);
      }
    }

    /**
     * Adds a string into the buffer
     *
     * @param string $content
     */
    public function addToBuffer($content)
    {
        $this->buffer .= $content;
    }

    /**
     * Returns the content of the buffer
     *
     * @return string
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Generates a unique filename
     *
     * @param  string $dir
     * @param  string $file
     * @return string
     */
    public function generateUniqueFileName($dir, $file, $extension = '.txt')
    {
        $path       = $dir . '/' . $file . $extension;
        if (file_exists($path))
        {
            $i       = 1;
            $newPath = $path . '_' . $i . $extension;
            while (file_exists($newPath))
            {
                $i++;
                $newPath = $path . '_' . $i . $extension;
            }
            $path = $newPath;
        }
        return $path;
    }

    /**
     * Generates an id for an Operation object
     *
     * @param  Operation $operation
     * @return string
     */
    public function generateRefPieceId(Operation $operation)
    {
        $string = $operation->getId();
        while (strlen($string) < 6)
        {
            $string = '0' . $string;
        }
        return 'P' . $string;
    }

    static function formatExport($chaine) {
        $src     = array("\r", "\n", ";");
        $dest     = array(" ", " ", ":");
        return str_replace($src, $dest ,$chaine);
    }

    /**
     * Removes special characters from a string
     *
     * @param  string $string
     * @return string
     */
    public function removeSpecialCaracters($string)
    {
        return FileHelper::sanitize($string);
    }
}
