<?php

/**
 * Class DroitHelper
 * @see \LogiCE\Acl
 */
class DroitHelper
{
    /**
     * Envoie un message et redirige l'utilisateur
     *
     * @param object $controlleur le controlleur que l'on tente d'atteindre
     */
    public static function redirectIdentification($controlleur)
    {
        $controlleur->setFlash("vous n'avez pas les droits d'accès");
        $controlleur->redirect('identification/login');
    }

    public static function getTitreAdmin($administrateur)
    {
        return "Edition de l'administrateur ".$administrateur -> getIdentifiant();
    }
    /**
     * Renvoie le nom du bouton en fonction des droits d'écriture
     *
     * @param Boolean $pas_droit_ecriture true si l'admin n'a pas le droit d'écriture
     * @return String
     */
    public static function getBoutonFiltrer($pas_droit_ecriture)
    {
        if ($pas_droit_ecriture) {
            return 'voir.png';
        } else {
            return 'edit.png';
        }
    }
    /**
     * Renvoie le libellé de la colonne des vues filtrer
     *
     * @param Boolean $pas_droit_ecriture true si l'admin n'a pas le droit d'écriture
     * @return String
     */
    public static function getLibelleFiltrer($pas_droit_ecriture)
    {
        if ($pas_droit_ecriture) {
            return 'Voir';
        } else {
            return 'Editer';
        }
    }
    public static function afficherMenuParNature ($id_nature, $menu)
    {
        $droits = DroitNature::findAll(array("id_menu = ".$menu, "id_nature = ".$id_nature));
        debug::output($droits);
        foreach ($droits as $droit) {
            return $droit->getDroit();
        }

    }

    public static function aLeDroit ($id_utilisateur, $controller, $function)
    {
        /*
        debug::output($controller." - ".$function);
        debug::output($_SERVER['REQUEST_URI']);
        debug::output($droit);
        debug::output($SQL);
        die;
        */


        $utilisateur 	= Utilisateur::findOne($id_utilisateur);
        $id_nature 		= $utilisateur->getIdNature();
        $authorized		= false;

        //Désactivation temporaire des droits
        return true;

        if ($controller == "ctrl_home" || $controller == "ctrl_filtre_liste") {
            return true;
        }
        //debug::output($controller, true);
        //Vérfication des droits de l'utilisateur sur le controlleur
        $SQL = 'SELECT DNC.droit FROM droits__natures_controllers DNC
                    INNER JOIN droits__controllers DC
                        ON DC.id = DNC.id_controller
                    WHERE id_nature		= ' . Sql::secureId($id_nature) . '
                        AND DC.path		= ' .Sql::secure($controller) . '
                        AND DNC.droit	= 1';
        $droit = Model::getDb()->requete($SQL);
        if ($droit) {
            $SQL = 'SELECT DNLCF.droit FROM droits__natures_lcf DNLCF
                    INNER JOIN droits__liens_controllers_functions DLCF
                        ON DLCF.id = DNLCF.id_lcf
                    INNER JOIN droits__functions DF
                        ON DF.id = DLCF.id_function
                    INNER JOIN droits__controllers DC
                        ON DC.id = DLCF.id_controller
                    WHERE id_nature		= ' . Sql::secureId($id_nature) . '
                        AND DC.path		= ' .Sql::secure($controller) . '
                        AND DF.name		= ' .Sql::secure($function) . '
                        AND DNLCF.droit	= 1';
            $droit = Model::getDb()->requete($SQL);
            if ($droit) $authorized = true;
        }

        return $authorized;
    }


    public static function getClauseRequeteEta($where = array())
    {
        switch (Utilisateur::getIdNatureConnecte()) {
            case Constantes::getIdNatureEcoorganisme():
                // TODO à corriger l'id_utilisateur n'étant pas l'id_ecoorganise
                $where['eco_organisme'] = '(id_eco_organisme = '.$_SESSION['id_eco_organisme'].' OR ';
                $where['eco_organisme'] .= 'id_eco_organisme_referent = '.$_SESSION['id_eco_organisme'].')';
                break;
            case Constantes::getIdNatureAdmin():
            default:
                break;
        }

        return $where;
    }

    public static function authorize($sys_controller, $sys_function, $sys_directory)
    {
        DroitHelperApplication::authorizeRead($sys_controller, $sys_function, $sys_directory);
    }
    /**
     * Fonction validant l'accès au site backend (utilisateur enregistré et tout et tout).
     * A redéfinir dans DroitHelperApplication si nécessaire.
     */
    public static function authorizeRead($sys_controller, $sys_function, $sys_directory)
    {
        if ($sys_controller == 'ctrl_accuse_reception'
            || $sys_controller == 'ctrl_identification'
            /* && $sys_function == 'Login' */
       ) {
            // Toutes les fonctions du controlleur d'identification et accussé de réception sont autorisées à tous.

        } elseif ($sys_controller != 'ctrl_liste') {	// controlleur accordé à tout le monde !
            if (!isset($_SESSION['id_utilisateur'])) {
                Controller::setFlash("Vous devez vous connecter avant d'accèder à cette zone");
                $_SESSION['get_initial'] = $_GET;
                Controller::redirect(AppBack::authController());
            } elseif ($sys_controller=='ctrl_home') {
                // tout le monde à le droit à ce controlleur !  (une fois connecté)
            } else {
                // TODO : tests de droits à remettre en place par la suite
                if (!DroitHelperApplication::aLeDroit($_SESSION['id_utilisateur'], $sys_directory.$sys_controller, $sys_function)) {
                    $id_menu_en_cours = 0;
                    if (isset($_SESSION['id_menu_en_cours'])) {
                        $id_menu_en_cours = $_SESSION['id_menu_en_cours'];
                    }
                    Controller::setFlash("Vous n'avez pas les droits suffisants. ".$sys_controller."/".$sys_function.' (menu:'.$id_menu_en_cours.')');
                    // Controller::redirect(AppBack::defaultController());
                    Accueil::prepareVueAccueil();
                    die;
                }
            }
        }
    }
    
    public static function authorizeWrite ($sys_controller, $sys_function, $sys_directory)
    {
        
    }
}
