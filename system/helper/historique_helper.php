<?php

/**
 * Classe enregistrant les différentes actions des utilisateurs
 * @author shuet
 */
class HistoriqueHelper
{

    /**
     * @param $directory
     * @param $controller
     * @param $function
     * @param string $commentaire
     * @return bool
     * @deprecated see self::stockageHistoriqueNavigation
     * @see self::stockageHistoriqueNavigation
     */
    public static function stocakgeHistoriqueNavigation($directory, $controller, $function, $commentaire = '')
    {
        return self::stockageHistoriqueNavigation($directory, $controller, $function, $commentaire);
    }

    /*
     * Fonction de stockage en BDD de l'historique de navigation de tous les utilisateurs
     * avec possibilité d'y ajouter un commentaire au besoin.
     *
     * cette focntion n'ai lancée que si la config est paramétrée pour.
     * @param string $directory
     * @param string $controller
     * @param string $function
     * @param string $commentaire
     */
    public static function stockageHistoriqueNavigation($directory, $controller, $function, $commentaire = '')
    {
        if (Config::paramsVariables('historisation_navigation')) {
            $id_utilisateur = '';
            $nature_id = '';
            if (isset($_SESSION['id_utilisateur'])) $id_utilisateur = $_SESSION['id_utilisateur'];
            if (isset($_SESSION['nature_id'])) $nature_id = $_SESSION['nature_id'];

            $format = 'US';
            $temp = Config::parametresConnexionDb();
            if ($temp['driver'] == 'mssql') {
                $format = 'FR';
            }

            $sql = "INSERT INTO historique_navigation
                      (url,
                      sys_directory,
                      sys_controller,
                      sys_function,
                      id_utilisateur_connecte,
                      adresse_ip,
                      adresse_host,
                      date,
                      commentaire)
                  VALUES (
                      " . Sql::secure($_SERVER['REQUEST_URI']) . ",
                        " . Sql::secure($directory) . ",
                        " . Sql::secure($controller) . ",
                        " . Sql::secure($function) . ",
                        " . Sql::secureId($id_utilisateur) . ",
                        " . Sql::secure($_SERVER["REMOTE_ADDR"]) . ",
                        " . Sql::secure(gethostbyaddr($_SERVER["REMOTE_ADDR"])) . ",
                        " . Sql::secureDate(Date::today(true, $format)) . ",
                        " . Sql::secure($commentaire) . "
                    )";
            //debug::output($controller);
            $controller = new Controller();
            $controller->getDb()->execute($sql);
            return true;
        } else {
            //la config de l'application ne permet pas d'historiser cette donnée
            return false;
        }

    }

    /*
     * fonction qui va loguer dans la BDD toutes les requetes exécutées sur l'application
     * @param : tableau de paramètres contenant au minimum :
     *  			- requete
     * 			 	- duree (temps d'exécution de la requete)
     * 			 	- memoire (la mémoire serveur utilisée)
     * 			 	- resultat (nb lignes retournées)
     */
    public static function logRequete($params = array(), $commentaire = '')
    {
        /* REQUETE DE CREATION DE TABLE (SQL SERVEUR) :

			CREATE TABLE [historique_requetes](
				[id] [int] IDENTITY(1,1) NOT null,
				[requete] [text] null,
				[temps_execution] [float] null,
				[memoire_utilisee] [float] null,
				[nb_lignes_retournees] [int] null,
				[date_execution] [datetime] null,
				[hostname] [text] null,
				[url_appelle] [text] null,
				[id_utilisateur_connecte] [int] null,
				[commentaire] [text] null,
			 CONSTRAINT [PK_historique_requetes2] PRIMARY KEY CLUSTERED
			(
				[id] ASC
			)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
			) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
		*/


        // vérification des paramètres obligtaoire s
        if (!isset($params['requete'])) return false;
        if (!isset($params['duree'])) return false;
        if (!isset($params['memoire'])) return false;
        if (!isset($params['resultat'])) return false;

        if (Config::paramsVariables('historisation_requete')) {

            // on va vérifier de ne pas sauvegarder en base des requetes inutiles, ou qui risqueraient d'engendrer des boucles infinies
            $chaine_a_verifier = array();
            $chaine_a_verifier[] = 'INSERT INTO historique_requetes';
            $chaine_a_verifier[] = 'INSERT INTO historique_navigation';
            $chaine_a_verifier[] = 'SELECT @@';
            foreach ($chaine_a_verifier as $texte) {
                if (false === strpos($params['requete'], $texte)) {
                    // on continue !
                } else {
                    // STOP !
                    // on ne sauvegarde pas les requetes de sauvegarde de requete, sinon : boucle infinie !!!
                    return false;
                }
            }

            $id_utilisateur = '';
            if (isset($_SESSION['id_utilisateur'])) {
                $id_utilisateur = $_SESSION['id_utilisateur'];
            }

            $format = 'US';
            $temp = Config::parametresConnexionDb();
            if ($temp['driver'] == 'mssql') {
                $format = 'FR';
            }

            $sql = "INSERT INTO historique_requetes
                      (requete,
                      temps_execution,
                      memoire_utilisee,
                      nb_lignes_retournees,
                      date_execution,
                      hostname,
                      url_appelle,
                      id_utilisateur_connecte,
                      commentaire)
                  VALUES (
                      " . Sql::secure($params['requete']) . ",
                        " . Sql::secureDecimal($params['duree']) . ",
                        " . Sql::secureDecimal($params['memoire']) . ",
                        " . Sql::secureInteger($params['resultat']) . ",
                        " . Sql::secureDate(Date::today(true, $format)) . ",
                        " . ((isset($_SERVER['HTTP_HOST'])) ? Sql::secure($_SERVER['HTTP_HOST']) : Sql::secure('CRON')) . ",
                        " . ((isset($_SERVER['REQUEST_URI'])) ? Sql::secure($_SERVER['REQUEST_URI']) : Sql::secure('CRON')) . ",
                        " . Sql::secureId($id_utilisateur) . ",
                        " . Sql::secure($commentaire) . "
                    )";

            $controller = new Controller();
            $controller->getDb()->execute($sql);
            return true;
        } else {
            //la config de l'application ne permet pas d'historiser cette donnée
            return false;
        }

    }
}