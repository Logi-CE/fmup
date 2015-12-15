<?php

/**
 * Contient les paramètres généraux de l'application
 * @version 1.0
 */
class ParametreHelper
{

    protected static $instance;

    protected $liste;
    protected $db;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    /**
     * Fonction pré-chargeant les données pour l'utilisation
     * Elle nous permettra de ne lancer qu'une requête puisqu'on gardera les résultats
     */
    protected function charger()
    {
        $sql = 'SELECT nom, valeur FROM parametre';
        $db = \Model::getDb();
        $result = $db->fetchAll($sql);
        $this->liste = array();
        foreach ($result as $liste) {
            $this->liste[$liste['nom']] = $liste['valeur'];
        }
    }

    /**
     * Fonction permettant de récupérer un paramètre particulier
     * @param String $libelle : Nom du paramètre
     */
    public function trouver($libelle)
    {
        if (empty($this->liste)) {
            $this->charger();
        }
        if (isset($this->liste[$libelle])) {
            $retour = $this->liste[$libelle];
        } else {
            throw new \FMUP\Exception("Paramètre de Config absent : " . $libelle);
        }
        return $retour;
    }

    /**
     * Fonction qui modifie la valeur d'un paramètre
     * @param String $libelle : Libellé du paramètre
     * @param String $valeur : Nouvelle valeur du paramètre
     */
    public function modifier($libelle, $valeur)
    {
        $sql = 'UPDATE parametre
                SET valeur = ' . Sql::secure($valeur) . '
                    , date_modification = NOW()
                    , id_modificateur = ' . Sql::secureId(\FMUP\Session::getInstance()->get('id_utilisateur')) . '
                WHERE nom = ' . Sql::secure($libelle) . '
                    AND modifiable = 1';
        $db = Model::getDb();
        $db->query($sql);
        // Mise à jour dans le tampon
        if (!empty($this->liste)) {
            $this->liste[$libelle] = $valeur;
        }
    }
}
