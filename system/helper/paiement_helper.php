<?php
/**
 * Classe PaiementHelper
 * @author : vbitaud
 * @description : Cette classe contient des méthodes de manipulation de données dédiées à différents moyens de paiement
 */
class PaiementHelper{

/********************************************************************************************
 ***************************** PAIEMENT VIA CM-CIC PAIEMENT *********************************
 *******************************************************************************************/
 
    /**
     * Retourne le code MAC nécessaire pour une demande d'aurotisation de paiement
     * @param {params} : Un tableau contenant les paramètres permettant la construction du code MAC et contenant au moins les champs suivants
     *  - cle_hash : La clé avec laquelle encoder la clé MAC
     *  - code_tpe : Le numéro du TPE utilisé pour le paiement (code à 7 caractères)
     *  - date_commande : La date de la commande au format jj/mm/aaaa:hh:ii:ss
     *  - montant_commande : Le montant de la commande
     *  - code_devise : Le code sur 3 caractères alphabétiques ISO4217 de la devise utilisée (EUR pour l'euro par défaut)
     *  - reference_commande : La référence de la commande (sur 12 caractères alphanumériques maximum)
     *  - textelibre : Un texte libre (3200 caractères maximum)
     *  - version : La version du module de paiement utilisé (3.0 par défaut)
     *  - code_langue : Le code langue souhaité par l'utilisateur (on met anglais si aucun code n'est renseigné)
     *  - code_societe : Le code de la société fourni 
     *  - mail_client : L'adresse mail du client qui passe commande
     *  - nb_echeances : Le nombre d'échéances de paiement (entre 2 et 4, uniquement pour le paiement fractionné)
     *  - date_echeance1 : Date de la première échéance (uniquement pour le paiement fractionné)
     *  - montant_echeance1 : Montant de la première échéance (uniquement pour le paiement fractionné)
     *  - date_echeance2 : Date de la seconde échéance (uniquement pour le paiement fractionné)
     *  - montant_echeance2 : Montant de la seconde échéance (uniquement pour le paiement fractionné)
     *  - date_echeance3 : Date de la troisième échéance (uniquement pour le paiement fractionné)
     *  - montant_echeance3 : Montant de la troisème échéance (uniquement pour le paiement fractionné)
     *  - date_echeance4 : Date de la quatrième échéance (uniquement pour le paiement fractionné)
     *  - montant_echeance4 : Montant de la quatrième échéance (uniquement pour le paiement fractionné)
     *  - type_paiement : Définit quel mode de paiement a été utilisé (immédiat ou différé)
     * @param {options} : Les éventuelles options pour la gestion du paiement
     * @return un tableau contenant le code MAC final si toutes les vérifications sont bonnes et les éventuelles erreurs rencontrées
     */
    public static function getCodeMacDemandeAutorisationPaiement($params, $options = "")
    {
        $valeur_mac = "";
        $tab_erreurs = array();
        // Vérification du code TPE fourni
        if (!$params['code_tpe']) {
            $tab_erreurs['code_tpe'] = "Code TPE non renseigné";
        } elseif (!PaiementHelper::isCodeTpePaiementCic($params['code_tpe'])) {
            $tab_erreurs['code_tpe'] = "Code TPE non valide";
        }
        
        // Vérification du formattage des dates
        if (!self::isDateValidePaiementAvecHeure($params['date_commande'])) {
            $tab_erreurs['date_commande'] = "Format de date de commande non valide";
        }
        if ($params['date_echeance1'] && !self::isDateFrancaise($params['date_echeance1'])) {
            $tab_erreurs['date_echeance1'] = "Format de première date d'échéance non valide";
        }
        if ($params['date_echeance2'] && !self::isDateFrancaise($params['date_echeance2'])) {
            $tab_erreurs['date_echeance2'] = "Format de seconde date d'échéance non valide";
        }
        if ($params['date_echeance3'] && !self::isDateFrancaise($params['date_echeance3'])) {
            $tab_erreurs['date_echeance3'] = "Format de troisième date d'échéance non valide";
        }
        if ($params['date_echeance4'] && !self::isDateFrancaise($params['date_echeance4'])) {
            $tab_erreurs['date_echeance4'] = "Format de quatrième date d'échéance non valide";
        }
        // Formattage des montants
        if (!in_array($params['code_devise'], self::getTableauCodesMonnaiesValides())) {
            $tab_erreurs['code_devise'] = "Code devise non reconnu";
        } else {
            $montant = number_format($params['montant_total'], 2, '.', '').$params['code_devise'];
            if ($params['montant_echeance1']) {
                $params['montant_echeance1'] = number_format($params['montant_echeance1'], 2, '.', '').$params['code_devise'];
            }
            if ($params['montant_echeance2']) {
                $params['montant_echeance2'] = number_format($params['montant_echeance2'], 2, '.', '').$params['code_devise'];
            }
            if ($params['montant_echeance3']) {
                $params['montant_echeance3'] = number_format($params['montant_echeance3'], 2, '.', '').$params['code_devise'];
            }
            if ($params['montant_echeance4']) {
                $params['montant_echeance4'] = number_format($params['montant_echeance4'], 2, '.', '').$params['code_devise'];
            }
        }
        
        // Vérification du nombre d'échéances entré
        if ($params['nb_echeances']) {
            if (!Is::integer($params['nb_echeances'])) {
                $tab_erreurs['nb_echeances'] = "Le nombre d'échéances entré n'est pas un nombre entier";
            } else {
                if ($params['nb_echeances'] < 2 || $params['nb_echeances'] > 4) {
                    $tab_erreurs['nb_echeances'] = "Le nombre d'échéances doit être compris entre 2 et 4 inclus";
                }
            }
        }
        
        // On met le code langue en anglais par défaut si il n'est pas renseigné
        if (!$params['code_langue'] || !in_array($params['code_langue'], self::getTableauCodesLangueAcceptesPaiement())) {
            $params['code_langue'] = 'EN';
        }
        
        // Si pas d'erreur
        if (count($tab_erreurs) == 0) {
            // On construit la chine de caractères
            $valeur_mac = $params['code_tpe'].
                            '*'.(($params['type_paiement'] == 'immediat') ? $params['date_commande'] : $params['date_paiement']).
                            '*'.$montant.
                            '*'.$params['reference_commande'].
                            '*'.$params['texte_libre'].
                            '*'.$params['version'].
                            '*'.$params['code_langue'].
                            '*'.$params['code_societe'].
                            '*'.$params['mail_client'].
                            '*'.$params['nb_echeances'].
                            '*'.$params['date_echeance1'].
                            '*'.$params['montant_echeance1'].
                            '*'.$params['date_echeance2'].
                            '*'.$params['montant_echeance2'].
                            '*'.$params['date_echeance3'].
                            '*'.$params['montant_echeance3'].
                            '*'.$params['date_echeance4'].
                            '*'.$params['montant_echeance4'].
                            '*'.$options;
            // Debug::output($valeur_mac, true);
            
            // Cryptage de la valeur MAC en utilisant SHA-1 et la clé passée en paramètre
            $cle_utilisable = self::getCleUtilisable($params['cle_hash']);
            $valeur_mac = strtoupper(hash_hmac('sha1', $valeur_mac, $cle_utilisable));
        }
        return array('code_mac' => $valeur_mac, 'erreurs' => $tab_erreurs);
    }
    /**
     * Retourne le code MAC nécessaire au recouvrement d'un paiement
     * @param {params} : Un tableau de paramètres permettant la construction de la clé contenant au moins les champs suivants
     *  - cle_hash : La clé avec laquelle encoder la clé MAC
     *  - code_tpe : Le numéro du TPE utilisé pour le paiement (code à 7 caractères)
     *  - date_commande : La date de la commande au format jj/mm/aaaa:hh:ii:ss
     *  - montant_commande : Le montant total de la commande
     *  - montant_a_capturer : Le montant à capturer pour la commande
     *  - montant_deja_capture : Le montant total déjà capturé pour la commande
     *  - montant_restant : Le montant restant à capturer pour la commande
     *  - code_devise : Le code sur 3 caractères alphabétiques ISO4217 de la devise utilisée (EUR pour l'euro par défaut)
     *  - reference_commande : La référence de la commande (sur 12 caractères alphanumériques maximum)
     *  - texte_libre : Un texte libre (3200 caractères maximum)
     *  - version : La version du module de paiement utilisé (3.0 par défaut)
     *  - code_langue : Le code langue souhaité par l'utilisateur (on met anglais si aucun code n'est renseigné)
     *  - code_societe : Le code de la société fourni
     * @return un tableau contenant le code MAC final si toutes les vérifications sont bonnes et les éventuelles erreurs rencontrées
     */
    public static function getCodeMacRecouvrementPaiement($params)
    {
        $valeur_mac = "";
        $tab_erreurs = array();
        // Vérification du code TPE fourni
        if (!$params['code_tpe']) {
            $tab_erreurs['tpe'] = "Code TPE non renseigné";
        } elseif (!PaiementHelper::isCodeTpePaiementCic($params['code_tpe'])) {
            $tab_erreurs['tpe'] = "Code TPE non valide";
        }
        
        // Vérification du formattage des dates
        if (!self::isDateValidePaiementAvecHeure($params['date_commande'])) {
            $tab_erreurs['date_commande'] = "Format de date de commande non valide";
        }
        
        // Formattage des montants
        if (!in_array($params['code_devise'], self::getTableauCodesMonnaiesValides())) {
            $tab_erreurs['code_devise'] = "Code devise non reconnu";
        } else {
            // On vérifie que la somme des trois montants est bien égale au montant total de la commande
            $somme_montants = $params['montant_a_capturer'] + $params['montant_deja_capture'] + $params['montant_restant'];
            if ($somme_montants != $params['montant_total']) {
                $tab_erreurs['montant_commande'] = "La somme des montants n'est pas égale au moment total de la commande.";
            } else {
                $params['montant_a_capturer'] = number_format($params['montant_a_capturer'], 2, '.', '').$params['code_devise'];
                $params['montant_deja_capture'] = number_format($params['montant_deja_capture'], 2, '.', '').$params['code_devise'];
                $params['montant_restant'] = number_format($params['montant_restant'], 2, '.', '').$params['code_devise'];
            }
        }
        
        // On met le code langue en anglais par défaut si il n'est pas renseigné
        if (!$params['code_langue'] || !in_array($params['code_langue'], self::getTableauCodesLangueAcceptesPaiement())) {
            $params['code_langue'] = 'EN';
        }
        if (count($tab_erreurs) == 0) {
            // Création de la chaine de caractère
            $valeur_mac = $params['code_tpe'].
                            '*'.$params['date_commande'].
                            '*'.$params['montant_a_capturer'].
                            $params['montant_deja_capture'].
                            $params['montant_restant'].
                            '*'.$params['reference_commande'].
                            '*'.$params['texte_libre'].
                            '*'.$params['version'].
                            '*'.$params['code_langue'].
                            '*'.$params['code_societe'].'*';
        
            // Cryptage de la valeur MAC en utilisant SHA-1 et la clé passée en paramètre
            $cle_utilisable = self::getCleUtilisable($params['cle_hash']);
            $valeur_mac = strtoupper(hash_hmac('sha1', $valeur_mac, $params['cle_hash']));
        }
        return array('code_mac' => $valeur_mac, 'erreurs' => $tab_erreurs);
    }
    /**
     * Calcule le code MAC de réponse envoyé par la banque pour vérification
     * @param {params} : Un tableau de paramètres permettant la construction de la clé contenant au moins les champs suivants
     *  - cle_hash : La clé avec laquelle encoder la clé MAC
     *  - code_tpe : Le numéro du TPE utilisé pour le paiement (code à 7 caractères)
     *  - date_paiement : La date de la demande d'autorisation de paiement au format jj/mm/aaaa:hh:ii:ss
     *  - montant_total : Le montant total de la commande
     *  - code_devise : Le code sur 3 caractères alphabétiques ISO4217 de la devise utilisée (EUR pour l'euro par défaut)
     *  - reference_commande : La référence de la commande (sur 12 caractères alphanumériques maximum)
     *  - texte_libre : Un texte libre (3200 caractères maximum)
     *  - version : La version du module de paiement utilisé (3.0 par défaut)
     *  - code_retour : Le code retour envoyé par la banque
     *  - cryptogramme_saisi : Indique si le cryptogramme a bien été saisi
     *  - date_validite_carte : la date de validité de la carte bancaire du client
     *  - type_carte : Le type de carte utilisé par le client (parmi AM, CB, MC, VI et na)
     *  - status3ds : Indicateur d'échange 3DSecure (parmi -1 (pas de 3DSecure), 1, 2, 3, 4)
     *  - numero_autorisation_banque : Numéro d'autorisation fourni par la banque pour la transaction (uniquement renseigné si transaction  acceptée)
     *  - motif_refus : le motif de refus (uniquement renseigné si la demande de paiement a été refusée)
     *  - origine_carte : Le code pays d'origine de la carte (uniquement renseigné si module prévention fraude)
     *  - code_banque_carte : Le code BIN de la banque client (uniquement renseigné si module prévention fraude)
     *  - hpan_cb : Hachage irréversible du numéro de carte client (uniquement renseigné si module prévention fraude)
     *  - ip_client : L'IP du client ayant fait la transaction (uniquement renseigné si module prévention fraude)
     *  - origine_transaction : Le code pays de l'origine de la transaction (uniquement renseigné si module prévention fraude)
     *  - etat_veres : Etat 3DSecure du VERes (uniquement renseigné si module prévention fraude et 3DSecure)
     *  - etat_pares : Etat 3DSecure du PARes (uniquement renseigné si module prévention fraude et 3DSecure)
     * @return un tableau contenant le code MAC final si toutes les vérifications sont bonnes et les éventuelles erreurs rencontrées
     */
    public static function getCodeMacReponseBanque($params)
    {
        // On ne fait aucune vérification car ce sont les champs renvoyés directement par la banque
        $valeur_mac = $params['code_tpe'].
                        '*'.$params['date_paiement'].
                        '*'.$params['montant_total'].
                        '*'.$params['reference_commande'].
                        '*'.$params['texte_libre'].
                        '*'.$params['version'].
                        '*'.$params['code_retour'].
                        '*'.$params['cryptogramme_saisi'].
                        '*'.$params['date_validite_carte'].
                        '*'.$params['type_carte'].
                        '*'.$params['status3ds'].
                        '*'.$params['numero_autorisation_banque'].
                        '*'.$params['motif_refus'].
                        '*'.$params['origine_carte'].
                        '*'.$params['code_banque_carte'].
                        '*'.$params['hpan_cb'].
                        '*'.$params['ip_client'].
                        '*'.$params['origine_transaction'].
                        '*'.$params['etat_veres'].
                        '*'.$params['etat_pares'].'*';
        
        // Cryptage de la valeur MAC en utilisant SHA-1 et la clé passée en paramètre
        $cle_utilisable = self::getCleUtilisable($params['cle_hash']);
        $valeur_mac = strtoupper(hash_hmac('sha1', $valeur_mac, $cle_utilisable));
        return $valeur_mac;
    }
    /**
     * Renvoie une version utilisable pour l'algorithme HMAC pour le calcul du code MAC
     * @param {cle_hash} : La clé à rendre utilisable
     */
    public static function getCleUtilisable($cle_hash)
    {
        $hexStrKey  = substr($cle_hash, 0, 38);
        $hexFinal   = "" . substr($cle_hash, 38, 2) . "00";
    
        $cca0=ord($hexFinal); 

        if ($cca0>70 && $cca0<97) 
            $hexStrKey .= chr($cca0-23) . substr($hexFinal, 1, 1);
        else { 
            if (substr($hexFinal, 1, 1)=="M") 
                $hexStrKey .= substr($hexFinal, 0, 1) . "0"; 
            else 
                $hexStrKey .= substr($hexFinal, 0, 2);
        }

        return pack("H*", $hexStrKey);
    }

     /**
     * Renvoie la liste des codes langue valides pour le formulaire de paiement
     */
    public static function getTableauCodesLangueAcceptesPaiement()
    {
        return array(
                        1 => 'DE',
                        2 => 'EN',
                        3 => 'ES',
                        4 => 'FR',
                        5 => 'IT',
                        6 => 'JA',
                        7 => 'NL',
                        8 => 'PT',
                        9 => 'SV');
    }
    /**
     * Renvoie la liste des codes de monnaie ISO4217 acceptés pour le paiement
     */
    public static function getTableauCodesMonnaiesValides()
    {
        return array('AED', 'AFN', 'ALL', 'AMD', 'ANG', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN',
                        'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BOV', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHE', 'CHF',
                        'CHW', 'CLF', 'CLP', 'CNY', 'COP', 'COU', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP',
                        'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK',
                        'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW',
                        'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK',
                        'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MXV', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD',
                        'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR',
                        'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'SSP', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND',
                        'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'USN', 'UYI', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST',
                        'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'XSU', 'XUA', 'YER', 'ZAR', 'ZAR', 'ZMW', 'ZWL'
                        );
    }
    
    /**
     * Teste si la chaine passée est un code TPE valide pour le paiement via l'application CIC Paiement
     * NB : Un code TPE valide est un code de 7 caractères alphanumériques
     * @param {chaine} : La chaîne à tester
     */
    public static function isCodeTpePaiementCic($chaine)
    {
        if (!Is::chaineOuNombre($chaine)) {
            return false;
        }
        return (bool)preg_match('#^[A-Z,0-9,a-z]{7}$#', $chaine);
    }
    /**
     * Teste si une date est valide pour la création d'une chaine MAC
     * NB : Les dates valides sont au format jj/mm/aaaa:hh:ii:ss uniquement
     * @param {date} : La date à tester
     */
    public static function isDateValidePaiementAvecHeure($date)
    {
        if (!Is::chaineOuNombre($date)) {
            return false;
        }
        return (bool)preg_match('#^([0-3][0-9])/([0-1][0-9])/(20[0-9][0-9]):([0-2][0-9]):([0-5][0-9]):([0-9][0-9])$#', $date);
    }
    /**
     * Valide que la date donnée au format français (SANS heure) existe bien
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si la valeur passée en paramètre une date au format JJ/MM/AAAA ou JJ/MM/AA, avec comme séparateur / uniquement
     */
    public static function isDateFrancaise($valeur)
    {
        if (is_string($valeur)) {
            $resultat = preg_split('|/|', $valeur);
            if (count($resultat) == 3) {
                list($jour, $mois, $annee) = $resultat;
                if (Is::integer($jour) && Is::integer($mois) && Is::integer($annee)) {
                    if (strlen($annee) == 2) $annee = '20'.$annee;
                    if ($annee < 1000) return false; 
                    if ($annee > 9999) return false; 
                    return checkDate($mois, $jour, $annee);
                }
            }
        }
        return false;
    }
}
