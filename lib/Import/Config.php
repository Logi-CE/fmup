<?php
namespace FMUP\Import;

use FMUP\Import\Config\ConfigObjet;
use FMUP\Import\Config\Field;
use FMUP\Import\Config\Field\Validator\Required;

/**
 * Répresente une line d'un fichier
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
    private $fiedList = array();

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
    private $duplicatedLines;

    /**
     * Liste des objets config
     *
     * @var ConfigObjet[]
     */
    private $configList = array();

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
        array_push($this->fiedList, $field);
        if ($field->getRequired()) {
            $field->addValidator(new Required());
        }
    }

    /**
     * Ajoute un ConfigObjet
     *
     * @param ConfigObjet $configObject
     */
    public function addConfigObjet(ConfigObjet $configObject)
    {
        array_push($this->configList, $configObject);
    }

    /**
     *
     * @param int $line
     */
    public function setDoublonLigne($line)
    {
        $this->duplicatedLines = (int)$line;
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
        return $this->fiedList;
    }

    /**
     *
     * @param Integer $index
     * @return \FMUP\Import\Config\Field
     */
    public function getField($index)
    {
        return $this->fiedList[$index];
    }

    /**
     *
     * @return \FMUP\Import\Config\ConfigObjet[]
     */
    public function getListeConfigObjet()
    {
        return $this->configList;
    }

    /**
     *
     * @return number
     */
    public function getDoublonLigne()
    {
        return $this->duplicatedLines;
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
        foreach ($this->getListeField() as $field) {
            $validField = $field->validateField();
            if (!$validField) {
                $this->errors[$field->getName()] = "non valide";
            }
        }
        $validLine = count($this->errors) > 0 ? false : true;
        if ($validLine) {
            $this->validateObjects();
        }
        return $validLine;
    }

    private function sortByPriority($a, $b)
    {
        /**
         * @var $a \FMUP\Import\Config\ConfigObjet
         * @var $b \FMUP\Import\Config\ConfigObjet
         */
        if ($a->getPriorite() == $b->getPriorite()) {
            return 0;
        }
        return ($a->getPriorite() < $b->getPriorite()) ? -1 : 1;
    }

    /**
     * @uses $this->sortByPriority
     */
    public function validateObjects()
    {
        $ids = array();
        $configList = $this->getListeConfigObjet();
        // on trie le tableau par priorité
        usort($configList, array($this, 'sortByPriority'));
        // pour chaque configObject
        foreach ($configList as $configObject) {
            $objectName = $configObject->getNomObjet();
            // On créé un objet du type donnée
            /** @var \Model $objectInstance */
            $objectInstance = new $objectName();
            $where = array();
            // Si on a besoin d'un id, on va le chercher dans le tableau
            if (count($configObject->getIdNecessaire()) > 0 && count($configObject->getNomAttribut()) > 0) {
                $attributeList = $configObject->getNomAttribut();
                foreach ($configObject->getIdNecessaire() as $mandatoryId) {
                    if (isset($ids[$mandatoryId])) {
                        // et on le set
                        $objectInstance->setAttribute($attributeList[$mandatoryId], $ids[$mandatoryId]);
                        $where[$attributeList[$mandatoryId]] = $attributeList[$mandatoryId]
                            . "LIKE '%" . $ids[$mandatoryId] . "%'";
                    }
                }
            }
            // pour tous les champs renseignés
            foreach ($configObject->getListeIndexChamp() as $index) {
                // on hydrate l'objet
                $objectInstance->setAttribute($this->getField($index)
                    ->getChampCible(), $this->getField($index)
                    ->getValue());
                // et on prépare le filtre
                $where[$this->getField($index)->getChampCible()] = $this->getField($index)->getChampCible()
                    . " LIKE '%"
                    . $this->getField($index)->getValue() . "%'";
            }
            // on va chercher l'objet en base
            if (!$objectInstance::findFirst($where)) {
                // si on l'a pas trouvé, il va falloir l'insérer
                $configObject->setStatutInsertion();
            } else {
                // sinon on va mettre à jours
                $configObject->setStatutMaj();
            }
        }
    }

    /**
     * Génère des objets à partir de la line
     * puis les rentres en base
     * Via un insert si on ne l'a pas trouvé en base
     * Via un update si on l'a trouvé
     */
    public function insertLine()
    {
        $tableau_id = array();
        $configList = $this->getListeConfigObjet();
        // on trie le tableau par priorité
        usort($configList, array($this, "sortByPriority"));
        // pour chaque configObject
        foreach ($configList as $configObject) {
            $objectName = $configObject->getNomObjet();
            // On créé un objet du type donnée
            /* @var $objectInstance \Model */
            $objectInstance = new $objectName();
            $where = array();

            // Si on a besoin d'un id, on va le chercher dans le tableau
            if (count($configObject->getIdNecessaire()) > 0 && count($configObject->getNomAttribut()) > 0) {
                $attributeList = $configObject->getNomAttribut();
                foreach ($configObject->getIdNecessaire() as $mandatoryId) {
                    if (isset($tableau_id[$mandatoryId])) {
                        // on le set
                        $objectInstance->setAttribute($attributeList[$mandatoryId], $tableau_id[$mandatoryId]);
                        // et on prépare le filtre
                        $where[$attributeList[$mandatoryId]] = $attributeList[$mandatoryId] . " LIKE '%"
                            . $tableau_id[$mandatoryId] . "%'";
                    }
                }
            }
            // pour tous les champs obligatoires renseignés
            foreach ($configObject->getListeIndexChamp() as $index) {
                // on hydrate l'objet
                $objectInstance->setAttribute($this->getField($index)
                    ->getChampCible(), $this->getField($index)
                    ->getValue());
                // et on prépare le filtre
                $where[$this->getField($index)->getChampCible()] = $this->getField($index)->getChampCible() . " LIKE '%"
                    . $this->getField($index)->getValue() . "%'";
            }
            // on hydrate toutes les infos sur l'objet
            foreach ($this->fiedList as $field) {
                if (\FMUP\String::toCamelCase($field->getTableCible()) == $objectName) {
                    $objectInstance->setAttribute($field->getChampCible(), $field->getValue());
                }
            }
            // on va chercher l'objet en base
            $foundInstance = $objectInstance::findFirst($where);

            if (!$foundInstance) {
                // si on l'a pas trouvé, on l'enregiste et on récupère l'id
                $result = $objectInstance->save();
                if ($result) {
                    $tableau_id[$objectName] = $result;
                } else {
                    throw new Exception(implode(';', $objectInstance->getErrors()));
                }
            } else {
                // sinon on récupère directement l'id
                $tableau_id[$objectName] = $foundInstance->getId();

                // puis on met à jours
                $objectInstance->setAttribute("Id", $foundInstance->getId());
                $objectInstance->save();
            }
        }
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        $string = '';
        foreach ($this->getListeField() as $key => $field) {
            $string .= $key . " \t" . $field->getName() . " \t" . $field->getValue() . " \t" . PHP_EOL;
        }
        return $string;
    }
}
