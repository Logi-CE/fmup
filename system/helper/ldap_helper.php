<?php
/**
 * Classe permettant d'utiliser une connexion LDAP
 * @author vbitaud
 * @version 1.0
 */
class LdapHelper
{
    /**
     * Fonction effectuant une connection à un LDAP
     * @param {String} {server} : Le nom du serveur auquel se connecter
     * @param {int} {port} : Le port à utiliser pour la connection au serveur
     * @param {String} {user_dn} : L'identifiant pour la connection au LDAP
     * @param {String} {user_password} : Le mot de passe pour la connection au LDAP
     * @return {ressource} La ressource indiquant si la connection s'est bien passée
     */
    public static function connexionLdap($server = false, $port = false, $user_dn = false, $user_password = false)
    {
        if ($server === false) {
            $server = Config::paramsVariables('serveur_ldap');
        }
        if ($port === false) {
            $server = Config::paramsVariables('port_connexion_ldap');
        }
        if ($user_dn === false) {
            $server = Config::paramsVariables('user_connexion_ldap');
        }
        if ($user_password === false) {
            $server = Config::paramsVariables('mdp_connexion_ldap');
        }
        
        // Option pour décrire les erreurs au maximum
        ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7);
        
        // Connection au serveur spécifié
        $ds = ldap_connect($server, $port);
        if ($ds) {
            // Protocole LDAP v3 par défaut
            ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
            // Liaison avec les identifiants
            $ressource = ldap_bind($ds, $user_dn, $user_password);
            if ($ressource) {
                return $ds;
            } else {
                throw new \FMUP\Exception("Liaison au serveur LDAP impossible : " . ldap_error($ds), E_WARNING);
            }
        } else {
            throw new \FMUP\Exception("Connexion au serveur LDAP impossible", E_WARNING);
        }
    }
    /**
     * Fonction retournant le nombre d'entrées existantes dans l'annuaire LDAP
     * @param {ressource} {ressource} : La connexion avec le serveur ouverte
     * @param {String} {dn} : Le chemin de l'annuaire dans lequel rechercher (de la forme OU=toto;DC=castelis;DC=net par exemple)
     * @param {Array} {tab_recherche} : Un tableau avec tous les paramètres de recherche (de la forme paramètre => valeur)
     * @param {Array} {attributes} : Un tableau contenant tous les noms d'attribut à retourner (si atributes est vide, tous les attributs et leur valeur seront retournés)
     * @return {Array} Un tableau contenant le nombre de resultats et le résultat de la recherche
     */
    public static function getResultatsRecherche($ressource, $dn, $tab_recherche, $attributes = array())
    {
        $chaine_recherche = "";
        foreach ($tab_recherche as $cle_recherche => $valeur) {
            $chaine_recherche .= "(".$cle_recherche.'='.$valeur.")";
        }
        if ($chaine_recherche) {
            $resultat = ldap_search($ressource, $dn, $chaine_recherche, $attributes);
            if ($resultat) {
                return array('nb_resultats' => ldap_count_entries($ressource, $resultat), 'resultat' => $resultat);
            } else {
				$rc = ldap_get_option($ressource, LDAP_OPT_ERROR_STRING, $erreur);
				if ($rc) {
					throw new \FMUP\Exception("Erreur lors de l'ajout d'une entrée LDAP : ".$erreur, E_WARNING);
				}
			}
        }
        return array('nb_resultat' => '', 'resultat' => '');
    }
    /**
     * Fonction retournant le premier résultat d'une recherche 
     * @param {ressource} {ressource} La connexion active au serveur
     * @param {ressource} {resultat} Le résultat de la recherche
     * @return La première entrée du résultat
     */
    public static function getPremierResultatRecherche($ressource, $resultat)
    {
        return ldap_first_entry($ressource, $resultat);
    }
    /**
     * Ajout d'une entrée dans un annuaire via LDAP
     * @param {ressource} {ressource} La connexion active au serveur
     * @param {String} {cn_entree} : Le common name de la nouvelle entrée
     * @param {Array} {tab_entree} : Le tableau des paramètres de la nouvelle entrée
     */
    public static function ajoutEntreeLdap($ressource, $cn_entree, $tab_entree)
    {
        $ajout = ldap_add($ressource, $cn_entree, $tab_entree);
        if (!$ajout) {
            $rc = ldap_get_option($ressource, LDAP_OPT_ERROR_STRING, $erreur);
            if ($rc) {
                throw new \FMUP\Exception("Erreur lors de l'ajout d'une entrée LDAP : $erreur", E_WARNING);
            }
        } else {
            return true;
        }
        return false;
    }
    /**
     * Modification d'une entrée dans un annuaire via LDAP
     * @param {ressource} {ressource} La connexion active au serveur
     * @param {String} {cn_entree} : Le common name de la nouvelle entrée
     * @param {Array} {tab_entree} : Le tableau des paramètres de la nouvelle entrée
     */
    public static function modifierEntreeLdap($ressource, $cn_entree, $tab_entree)
    {
        $modification = ldap_modify($ressource, $cn_entree, $tab_entree);
        if (!$modification) {
            $rc = ldap_get_option($ressource, LDAP_OPT_ERROR_STRING, $erreur);
            if ($rc) {
                throw new \FMUP\Exception("Modification impossible : $erreur", E_WARNING);
            }
        } else {
            return true;
        }
        return false;
    }
    /**
     * Suppression d'une entrée dans un annuaire via LDAP
     * @param {ressource} {ressource} : L'identifiant de connexion de la connexion ouverte
     * @param {String} {dn} : Le chemin dans l'annuaire de l'entrée à supprimer
     */
    public static function supprimerEntreeLdap($ressource, $dn)
    {
        $retour = ldap_delete($ressource, $dn);
        if (!$retour) {
            $rc = ldap_get_option($ressource, LDAP_OPT_ERROR_STRING, $erreur);
            if ($rc) {
                throw new \FMUP\Exception("Suppression impossible : " . $erreur, E_WARNING);
            }
        } else {
            return true;
        }
        return false;
    }
    /**
     * Fermeture de la connection au LDAP
     * @param {ressource} {ressource} : La connexion qui a été ouverte
     */
    public static function fermetureConnexion($ressource)
    {
        ldap_close($ressource);
    }
}
