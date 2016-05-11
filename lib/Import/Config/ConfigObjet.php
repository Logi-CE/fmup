<?php
namespace FMUP\Import\Config;

/**
 * Permet de générer un objet voulu à partir du fichier
 *
 * @author csanz
 *
 */
class ConfigObjet
{
    const INSERT = 'insert';
    const UPDATE = 'update';

    /**
     * Nom de la classe de l'objet
     *
     * @var string
     */
    private $objectName;

    /**
     *
     * @var int
     */
    private $priority;

    /**
     *
     * @var string[]
     */
    private $mandatoryId = null;

    /**
     *
     * @var string[]
     */
    private $attributeName = array();

    /**
     *
     * @var string
     */
    private $status = "";

    /**
     * liste des index correspondant aux champs associés à l'objet
     *
     * @var int[]
     */
    private $indexFieldList = array();

    /*
     * ***************************
     * GETTERS
     * ***************************
     */

    /**
     * @return int[]
     */
    public function getListeIndexChamp()
    {
        return $this->indexFieldList;
    }

    /**
     * @return int
     */
    public function getPriorite()
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getNomObjet()
    {
        return $this->objectName;
    }

    /**
     * @return array|\string[]
     */
    public function getIdNecessaire()
    {
        return $this->mandatoryId;
    }

    /**
     * @return string
     */
    public function getStatut()
    {
        return $this->status;
    }

    /**
     * @return \string[]
     */
    public function getNomAttribut()
    {
        return $this->attributeName;
    }

    /*
     * ***************************
     * SETTERS
     * ***************************
     */
    public function setStatutInsertion()
    {
        $this->status = self::INSERT;
        return $this;
    }

    public function setStatutMaj()
    {
        $this->status = self::UPDATE;
        return $this;
    }

    public function setNomObjet($nom)
    {
        $this->objectName = (string)$nom;
        return $this;
    }

    public function setNomAttribut($objectName, $attributeName)
    {
        $this->attributeName[$objectName] = $attributeName;
        return $this;
    }

    /**
     * @param int $index
     * @return $this
     */
    public function addIndex($index)
    {
        array_push($this->indexFieldList, $index);
        return $this;
    }

    /**
     *
     * @param string $objectName
     *            : Nom de l'objet présent dans le model
     * @param integer $priority
     *            : Niveau de priorité et de dépendance
     * @param string $mandatoryId
     *            : Nom de l'objet dont l'id est necessaire pour remplir l'objet this
     */
    public function __construct($objectName, $priority, $mandatoryId = "")
    {
        $this->setNomObjet($objectName);
        $this->priority = $priority;
        $this->mandatoryId = explode(";", $mandatoryId);
        foreach ($this->mandatoryId as $id) {
            $this->attributeName[$id] = "id_" . \FMUP\String::toCamelCase($id);
        }
    }
}
