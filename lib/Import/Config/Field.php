<?php
namespace FMUP\Import\Config;

/**
 * Représente un champ importé dans le fichier
 *
 * @author csanz
 *
 */
class Field
{

    private $name;

    private $value;

    private $table_cible;

    private $champ_cible;

    private $required;

    private $type;

    private $liste_validator = array();

    private $liste_formatter = array();

    private $liste_erreur = array();

    /*
     * *********
     * *GETTERS*
     * *********
     */
    public function __construct($name, $value, $table_cible, $champ_cible, $required, $type)
    {
        $this->name = $name;
        $this->value = $value;
        $this->table_cible = $table_cible;
        $this->champ_cible = $champ_cible;
        $this->required = $required;
        $this->type = $type;
        if ($type != "ignored" && $type != "") {
            $classe = "\FMUP\Import\Config\Field\Validator\\" . ucfirst($type);
            $validator = new $classe();
            $this->addValidator($validator);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getRequired()
    {
        return $this->required;
    }

    public function getTableCible()
    {
        return $this->table_cible;
    }

    public function getChampCible()
    {
        return $this->champ_cible;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getErreurs()
    {
        return $this->liste_erreur;
    }

    /*
     * *********
     * SETTERS
     * *********
     */
    public function setValue($valeur)
    {
        $this->value = trim($valeur);
    }

    /**
     *
     * @param \Import\Config\Field\Validator\Validator $validator
     */
    public function addValidator(\FMUP\Import\Config\Field\Validator $validator)
    {
        array_push($this->liste_validator, $validator);
    }

    /**
     *
     * @param \Import\Config\Field\Formatter\Formatter $formatter
     */
    public function addFormatterFin(\FMUP\Import\Config\Field\Formatter $formatter)
    {
        array_push($this->liste_formatter, $formatter);
    }

    /**
     *
     * @param \Import\Config\Field\Formatter\Formatter $formatter
     */
    public function addFormatterDebut(\FMUP\Import\Config\Field\Formatter $formatter)
    {
        array_push($this->liste_formatter, $formatter);
    }

    public function validateField()
    {
        $valid = true;
        if (count($this->liste_validator) > 0) {
            foreach ($this->liste_validator as $validator) {
                if (!$validator->validate($this->value)) {
                    $valid = false;
                    $this->liste_erreur[get_class($validator)] = $validator->getErrorMessage();
                }
            }
        }
        return $valid;
    }

    public function formatField()
    {
        if (count($this->liste_formatter) > 0) {
            foreach ($this->liste_formatter as $formatter) {
                $this->value = $formatter->format($this->value) ?: "";
                if ($formatter->hasError()) {
                    $this->liste_erreur[get_class($formatter)] = $formatter->getErrorMessage();
                }
            }
        }
    }
}
