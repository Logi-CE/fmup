<?php
namespace FMUP\Import;

use FMUP\Import\Config\ConfigObjet;
use FMUP\Import\Config\Field;
use FMUP\Import\Config\Field\Validator\Required;

/**
 * Répresente une ligne d'un fichier
 *
 * @author csanz
 *
 */
class Config
{

    /*
     * ***************************
     * ATTRIBUTS
     * ***************************
     */
    /**
     * Liste des champs
     *
     * @var Field[]
     */
    private $liste_field = array();

    /**
     * liste des erreurs
     *
     * @var String[]
     */
    private $errors = array();

    /**
     * Ligne correspondant au premier doublon
     *
     * @var int
     */
    private $doublon_ligne;

    /**
     * Liste des objets config
     *
     * @var ConfigObjet[]
     */
    private $liste_config_objet = array();

    /*
     * ***************************
     * SETTERS
     * ***************************
     */

    /**
     * Ajoute un Field à la liste de la config
     *
     * @param Field $field
     */
    public function addField(Field $field)
    {
        array_push($this->liste_field, $field);
        if ($field->getRequired()) {
            $field->addValidator(new Required());
        }
    }

    /**
     * Ajoute un ConfigObjet
     *
     * @param ConfigObjet $config_objet
     */
    public function addConfigObjet(ConfigObjet $config_objet)
    {
        array_push($this->liste_config_objet, $config_objet);
    }

    /**
     *
     * @param integer $ligne
     */
    public function setDoublonLigne($ligne)
    {
        $this->doublon_ligne = $ligne;
    }

    /*
     * ***************************
     * GETTERS
     * ***************************
     */

    /**
     *
     * @return \FMUP\Import\Config\Field[]
     */
    public function getListeField()
    {
        return $this->liste_field;
    }

    /**
     *
     * @param Integer $index
     * @return \FMUP\Import\Config\Field
     */
    public function getField($index)
    {
        return $this->liste_field[$index];
    }

    /**
     *
     * @return \FMUP\Import\Config\ConfigObjet[]
     */
    public function getListeConfigObjet()
    {
        return $this->liste_config_objet;
    }

    /**
     *
     * @return number
     */
    public function getDoublonLigne()
    {
        return $this->doublon_ligne;
    }

    /**
     *
     * @return array[string]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /*
     * ***************************
     * FONCTIONS SPECIFIQUES
     * ***************************
     */

    /**
     * Appelle tous les formatters et validators de chaque champ
     * Renvoi true si tous les champs sont valides, false sinon
     *
     * Les erreurs sont stockées dans l'attribut $errors
     *
     * @return boolean
     */
    public function validateLine()
    {
        // Réinitialisation du tableau d'erreur
        $this->errors = array();

        foreach ($this->getListeField() as $key => $field) {
            $field->formatField();
            $valid_field = $field->validateField();
            if (!$valid_field) {
                $this->errors[$field->getName()] = "non valide";
            }
        }
        $line_valid = count($this->errors) > 0 ? false : true;
        if ($line_valid) {
            $this->validateObjects();
        }
        return $line_valid;
    }

    private function sortByPrio($a, $b)
    {
        if ($a->getPriorite() == $b->getPriorite()) {
            return 0;
        }
        return ($a->getPriorite() < $b->getPriorite()) ? -1 : 1;
    }

    /**
     */
    public function validateObjects()
    {
        $tableau_id = array();
        $liste_config = $this->liste_config_objet;
        // on trie le tableau par priorité
        usort($liste_config, array($this, 'sortByPrio'));
        // pour chaque config_objet
        foreach ($liste_config as $config_objet) {
            $nom_objet = $config_objet->getNomObjet();
            // On créé un objet du type donnée
            $objet = new $nom_objet();
            $where = array();
            // Si on a besoin d'un id, on va le chercher dans le tableau
            if (count($config_objet->getIdNecessaire()) > 0 && count($config_objet->getNomAttribut()) > 0) {
                $liste_attribut = $config_objet->getNomAttribut();
                foreach ($config_objet->getIdNecessaire() as $id_necessaire) {
                    if (isset($tableau_id[$id_necessaire])) {
                        // et on le set
                        $objet->setAttribute($liste_attribut[$id_necessaire], $tableau_id[$id_necessaire]);
                        $where[$liste_attribut[$id_necessaire]] = $liste_attribut[$id_necessaire] . "LIKE '%" . $tableau_id[$id_necessaire] . "%'";
                    }
                }
            }
            // pour tous les champs renseignés
            foreach ($config_objet->getListeIndexChamp() as $index) {
                // on hydrate l'objet
                $objet->setAttribute($this->getField($index)
                    ->getChampCible(), $this->getField($index)
                    ->getValue());
                // et on prépare le filtre
                $where[$this->getField($index)->getChampCible()] = $this->getField($index)->getChampCible() . " LIKE '%" . $this->getField($index)->getValue() . "%'";
            }
            // on va chercher l'objet en base
            $objet_trouve = $nom_objet::findFirst($where);
            if (!$objet_trouve) {
                // si on l'a pas trouvé, il va falloir l'insérer
                $config_objet->setStatutInsertion();
            } else {
                // sinon on va mettre à jours
                $config_objet->setStatutMaj();
            }
        }
    }

    /**
     * Génère des objets à partir de la ligne
     * puis les rentres en base
     * Via un insert si on ne l'a pas trouvé en base
     * Via un update si on l'a trouvé
     */
    public function insertLine()
    {
        $tableau_id = array();
        $liste_config = $this->liste_config_objet;
        // on trie le tableau par priorité
        usort($liste_config, "self::sortByPrio");
        // pour chaque config_objet
        foreach ($liste_config as $config_objet) {
            $nom_objet = $config_objet->getNomObjet();
            // On créé un objet du type donnée
            /* @var $objet \Model */
            $objet = new $nom_objet();
            $where = array();

            // Si on a besoin d'un id, on va le chercher dans le tableau
            if (count($config_objet->getIdNecessaire()) > 0 && count($config_objet->getNomAttribut()) > 0) {
                $liste_attribut = $config_objet->getNomAttribut();
                foreach ($config_objet->getIdNecessaire() as $id_necessaire) {
                    if (isset($tableau_id[$id_necessaire])) {
                        // on le set
                        $objet->setAttribute($liste_attribut[$id_necessaire], $tableau_id[$id_necessaire]);
                        // et on prépare le filtre
                        $where[$liste_attribut[$id_necessaire]] = $liste_attribut[$id_necessaire] . " LIKE '%" . $tableau_id[$id_necessaire] . "%'";
                    }
                }
            }
            // pour tous les champs obligatoires renseignés
            foreach ($config_objet->getListeIndexChamp() as $index) {
                // on hydrate l'objet
                $objet->setAttribute($this->getField($index)
                    ->getChampCible(), $this->getField($index)
                    ->getValue());
                // et on prépare le filtre
                $where[$this->getField($index)->getChampCible()] = $this->getField($index)->getChampCible() . " LIKE '%" . $this->getField($index)->getValue() . "%'";
            }
            // on hydrate toutes les infos sur l'objet
            foreach ($this->liste_field as $field) {
                if (\String::toCamlCase($field->getTableCible()) == $nom_objet) {
                    $objet->setAttribute($field->getChampCible(), $field->getValue());
                }
            }
            // on va chercher l'objet en base
            $objet_trouve = $nom_objet::findFirst($where);

            if (!$objet_trouve) {
                // si on l'a pas trouvé, on l'enregiste et on récupère l'id
                $result = $objet->save();
                if ($result) {
                    $tableau_id[$nom_objet] = $result;
                } else {
                    throw new \Exception(implode(';', $objet->getErrors()));
                }
            } else {
                // sinon on récupère directement l'id
                $tableau_id[$nom_objet] = $objet_trouve->getId();

                // puis on met à jours
                $objet->setAttribute("Id", $objet_trouve->getId());
                $objet->save();
            }
        }
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        $string = "";
        foreach ($this->getListeField() as $key => $field) {
            $string .= $key . " \t" . $field->getName() . " \t" . $field->getValue() . " \t \n";
        }
        return $string;
    }
}
