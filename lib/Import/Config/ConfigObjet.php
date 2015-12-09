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

    /**
     * Nom de la classe de l'objet
     *
     * @var String
     */
    private $nom_objet;

    /**
     *
     * @var integer
     */
    private $priorite;

    /**
     *
     * @var Array[string]
     */
    private $id_necessaire = null;

    /**
     *
     * @var Array[string]
     */
    private $nom_attribut = array();

    /**
     *
     * @var string
     */
    private $statut = "";

    /**
     * liste des index correspondant aux champs associés à l'objet
     *
     * @var array[int]
     */
    private $liste_index_champ = array();

    /*
     * ***************************
     * GETTERS
     * ***************************
     */

    /**
     * @return array[int]
     */
    public function getListeIndexChamp()
    {
        return $this->liste_index_champ;
    }

    public function getPriorite()
    {
        return $this->priorite;
    }

    public function getNomObjet()
    {
        return $this->nom_objet;
    }

    public function getIdNecessaire()
    {
        return $this->id_necessaire;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function getNomAttribut()
    {
        return $this->nom_attribut;
    }

    /*
     * ***************************
     * SETTERS
     * ***************************
     */
    public function setStatutInsertion()
    {
        $this->statut = "insert";
    }

    public function setStatutMaj()
    {
        $this->statut = "update";
    }

    public function setNomObjet($nom)
    {
        $this->nom_objet = $nom;
    }

    public function setNomAttribut($nom_objet, $nom_attribut)
    {
        $this->nom_attribut[$nom_objet] = $nom_attribut;
    }

    /**
     * @param int $index
     * @return $this
     */
    public function addIndex($index)
    {
        array_push($this->liste_index_champ, $index);
        return $this;
    }

    /**
     *
     * @param string $nom_objet
     *            : Nom de l'objet présent dans le model
     * @param integer $priorite
     *            : Niveau de priorité et de dépendance
     * @param string $id_necessaire
     *            : Nom de l'objet dont l'id est necessaire pour remplir l'objet this
     */
    public function __construct($nom_objet, $priorite, $id_necessaire = "")
    {
        $this->nom_objet = $nom_objet;
        $this->priorite = $priorite;
        $this->id_necessaire = explode(";", $id_necessaire);
        foreach ($this->id_necessaire as $id) {
            $this->nom_attribut[$id] = "id_" . \String::toSnakeCase($id);
        }
    }
}
