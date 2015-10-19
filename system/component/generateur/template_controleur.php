/**
 * Classe <?php echo $nom_classe."\n"; ?>
 * Classe générée automatiquement le <?php echo Date::today('FR', true)."\n"; ?>
 */
class <?php echo $nom_classe; ?> extends Controller {

    /**
     * Appel à la vue filtre/liste des enregistrements de la table <?php echo $objet."\n"; ?>
     */
    public function filtrer()
    {
        $params = array();
        $params['popup'] = false;
        $params['titre'] = 'Liste des <?php echo $objet; ?>';

        new View('<?php echo $chemin_vues; ?>/filtrer', $params, array('javascripts' => array('system/filtre_liste')));
    }

    /**
     * Appel à la page d'édition d'un objet <?php echo $nom_modele."\n"; ?>
     * La page attend un paramètre GET['id'] (accès), POST['<?php echo $objet; ?>'] (enregistrement) ou n'attend rien (création)
     * Elle nécéssite les droits d'écriture
     */
    public function editer()
    {
        DroitHelperApplication::authorizeWrite('<?php echo $nom_fichier; ?>');
        
        $params = array();
        // si on a demandé explicitement un objet <?php echo $nom_modele; ?>
        
        if (!empty($_GET['id'])) {
            $<?php echo $objet; ?> = <?php echo $nom_modele; ?>::findOne($_GET['id']);

        // si on a posté à la page un objet <?php echo $nom_modele; ?>
        
        } elseif (isset($_POST['<?php echo $objet; ?>'])) {
            if (!FormHelper::checkToken()) throw new NotFoundError("Une erreur inconnue s'est produite.");
            $<?php echo $objet; ?> = new <?php echo $nom_modele; ?>();
            $<?php echo $objet; ?> = $<?php echo $objet; ?>->setAttributesSecure($_POST['<?php echo $objet; ?>']);
            if ($<?php echo $objet; ?>->save()) {
                self::redirect('<?php echo $chemin_vues; ?>/filtrer');
            }
            // pas de gestion d'erreurs car on va rediriger sur la page qui les affiche

        // sinon on crée un objet <?php echo $nom_modele; ?>
        
        } else {
            $<?php echo $objet; ?> = new <?php echo $nom_modele; ?>();
        }
        $this->appellerVueEditer($<?php echo $objet; ?>, true, $params);
    }
    
    /**
     * Appel à la page de consultation d'un objet <?php echo $nom_modele."\n"; ?>
     */
    public function consulter()
    {
        $params = array();
        if (isset($_GET['id'])) {
            $<?php echo $objet; ?> = <?php echo $nom_modele; ?>::findOne($_GET['id']);
        }
        
        if (!$<?php echo $objet; ?>) {
            throw new NotFoundError("<?php echo $objet; ?> n'existe pas.");
        }
        $this->appellerVueEditer($<?php echo $objet; ?>, false, $params);
    }

    /**
     * Appel à la page de suppression d'un objet <?php echo $nom_modele."\n"; ?>
     * La page attend un paramètre POST['<?php echo $objet; ?>']
     * Elle nécéssite les droits d'écriture
     */
    public function supprimer()
    {
        DroitHelperApplication::authorizeWrite('<?php echo $nom_fichier; ?>');
        if (!FormHelper::checkToken()) throw new NotFoundError("Une erreur inconnue s'est produite.");
        
        if (isset($_POST['<?php echo $objet; ?>'])) {
            $<?php echo $objet; ?> = <?php echo $nom_modele; ?>::findOne($_POST['<?php echo $objet; ?>']['id']);
            if ($<?php echo $objet; ?>) {
                if ($<?php echo $objet; ?>->delete()) {
                    self::setFlash("Suppression réalisée avec succès.::Traitement::ok");
                    self::redirect('<?php echo $chemin_vues; ?>/filtrer');
                } else {
                    $params = array();
                    $this->appellerVueEditer($<?php echo $objet; ?>, true, $params);
                    die;
                }
            }
        }
        self::setFlash("Commande inconnue.::Erreur::alerte");
        self::redirect('<?php echo $chemin_vues; ?>/filtrer');
    }
    
    /**
     * Appel la vue (sert à uniformier l'appel à la vue après une édition ou suppression)
     * @param <?php echo $nom_modele ?> : $<?php echo $objet; ?> Le test à afficher.
     * @param bool $edition : [OPT] Définit si la page est en édition ou en consultation, par défaut édition
     * @param array $params : [OPT] Contient différentes données comme par exemple les liens non sauvegardés, par défaut vide
     */
    protected function appellerVueEditer($<?php echo $objet; ?>, $edition = true, $params = array())
    {
        $params['popup'] = false;
        $javascript = array('system/edit', 'system/onglet');
        $style = array('system/onglet');
        
        if (!$edition) {
            $params['titre'] = 'Consultation de <?php echo $objet; ?>';
            $params['editable'] = false;
        } elseif ($<?php echo $objet; ?>->getId()) {
            $params['titre'] = 'Edition de <?php echo $objet; ?>';
            $params['editable'] = true;
        } else {
            $params['titre'] = 'Création de <?php echo $objet; ?>';
            $params['editable'] = true;
        }
        
        $params['<?php echo $objet; ?>'] = $<?php echo $objet; ?>;
        new View('<?php echo $chemin_vues; ?>/editer', $params, array('javascripts' => $javascript, 'styles' => $style));
    }
}