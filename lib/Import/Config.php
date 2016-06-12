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
    private $fieldList = array();

    /**
     * liste des erreurs
     *
     * @var string[]
     */
    private $errors = array();

    /**
     * Ligne correspondant au premier doublon
     *
     * @var int
     */
    private $duplicatedLine;

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
     * @return $this
     */
    public function addField(Field $field)
    {
        array_push($this->fieldList, $field);
        if ($field->getRequired()) {
            $field->addValidator(new Required());
        }
        return $this;
    }

    /**
     * Ajoute un ConfigObjet
     *
     * @param ConfigObjet $configObject
     * @return $this
     */
    public function addConfigObjet(ConfigObjet $configObject)
    {
        array_push($this->configList, $configObject);
        return $this;
    }

    /**
     *
     * @param int $line
     * @return $this
     */
    public function setDoublonLigne($line)
    {
        $this->duplicatedLine = (int)$line;
        return $this;
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
        return $this->fieldList;
    }

    /**
     *
     * @param int $index
     * @return \FMUP\Import\Config\Field|null
     */
    public function getField($index)
    {
        $index = (int)$index;
        return isset($this->fieldList[$index]) ? $this->fieldList[$index] : null;
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
     * @return int
     */
    public function getDoublonLigne()
    {
        return (int)$this->duplicatedLine;
    }

    /**
     *
     * @return string[]
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

    /**
     * @param $a
     * @param $b
     * @return int
     * @usedby self::usort
     * @SuppressWarnings(PMD.UnusedPrivateMethod)
     */
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
     * @param $configList
     * @uses self::sortByPriority
     * @codeCoverageIgnore
     */
    protected function usort($configList)
    {
        usort($configList, array($this, 'sortByPriority'));
    }

    /**
     * @return $this
     */
    public function validateObjects()
    {
        $configList = $this->getListeConfigObjet();
        // on trie le tableau par priorité
        $this->usort($configList);
        // pour chaque configObject
        foreach ($configList as $configObject) {
            $objectName = $configObject->getNomObjet();
            // On créé un objet du type donnée
            /** @var \Model $objectInstance */
            $objectInstance = $this->createNomObject($objectName);
            $where = array();
            // pour tous les champs renseignés
            foreach ($configObject->getListeIndexChamp() as $index) {
                // on hydrate l'objet
                $field = $this->getField($index);
                $objectInstance->setAttribute($field->getChampCible(), $field->getValue());
                // et on prépare le filtre
                $where[$field->getChampCible()] = $field->getChampCible() . " LIKE '%" . $field->getValue() . "%'";
            }
            // on va chercher l'objet en base
            if (!$objectInstance->findFirst($where)) {
                // si on l'a pas trouvé, il va falloir l'insérer
                $configObject->setStatutInsertion();
            } else {
                // sinon on va mettre à jours
                $configObject->setStatutMaj();
            }
        }
        return $this;
    }

    /**
     * Génère des objets à partir de la line
     * puis les rentres en base
     * Via un insert si on ne l'a pas trouvé en base
     * Via un update si on l'a trouvé
     * @return $this
     * @throws Exception
     * @throws \FMUP\Exception
     */
    public function insertLine()
    {
        $ids = array();
        $configList = $this->getListeConfigObjet();
        // on trie le tableau par priorité
        $this->usort($configList);
        // pour chaque configObject
        foreach ($configList as $configObject) {
            $objectName = $configObject->getNomObjet();
            // On créé un objet du type donnée
            /* @var $objectInstance \Model */
            $objectInstance = $this->createNomObject($objectName);
            $where = array();

            // Si on a besoin d'un id, on va le chercher dans le tableau
            $attributeList = $configObject->getNomAttribut();
            foreach ($configObject->getIdNecessaire() as $mandatoryId) {
                if (isset($ids[$mandatoryId]) && isset($attributeList[$mandatoryId])) {
                    // on le set
                    $objectInstance->setAttribute($attributeList[$mandatoryId], $ids[$mandatoryId]);
                    // et on prépare le filtre
                    $where[$attributeList[$mandatoryId]] = $attributeList[$mandatoryId] . " LIKE '%"
                        . $ids[$mandatoryId] . "%'";
                }
            }
            // pour tous les champs obligatoires renseignés
            foreach ($configObject->getListeIndexChamp() as $index) {
                // on hydrate l'objet
                $field = $this->getField($index);
                $objectInstance->setAttribute($field->getChampCible(), $field->getValue());
                // et on prépare le filtre
                $where[$field->getChampCible()] = $field->getChampCible() . " LIKE '%" . $field->getValue() . "%'";
            }
            // on hydrate toutes les infos sur l'objet
            $this->hydrateInstance($objectInstance, $objectName);
            // on va chercher l'objet en base
            $foundInstance = $objectInstance->findFirst($where);

            if (!$foundInstance) {
                // si on l'a pas trouvé, on l'enregiste et on récupère l'id
                $result = $objectInstance->save();
                if ($result) {
                    $ids[$objectName] = $result;
                } else {
                    throw new Exception(implode(';', $objectInstance->getErrors()));
                }
            } else {
                // sinon on récupère directement l'id
                $ids[$objectName] = $foundInstance->getId();

                // puis on met à jours
                $objectInstance->setAttribute("Id", $foundInstance->getId());
                $objectInstance->save();
            }
        }
        return $this;
    }

    /**
     * @param \Model $objectInstance
     * @param $objectName
     * @return $this
     */
    protected function hydrateInstance($objectInstance, $objectName)
    {
        /** @var $objectInstance \Model */
        foreach ($this->getListeField() as $field) {
            if (\FMUP\String::toCamelCase($field->getTableCible()) == $objectName) {
                $objectInstance->setAttribute($field->getChampCible(), $field->getValue());
            }
        }
        return $this;
    }

    /**
     * @param string $objectName
     * @return mixed
     * @codeCoverageIgnore
     */
    protected function createNomObject($objectName)
    {
        return new $objectName();
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
