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

    private $formatters = array();

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
            $classe = '\FMUP\Import\Config\Field\Validator\\' . ucfirst($type);
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
     * @param Field\Validator $validator
     * @return $this
     */
    public function addValidator(Field\Validator $validator)
    {
        array_push($this->liste_validator, $validator);
        return $this;
    }

    /**
     * Set validator with specific key
     * @param Field\Validator $validator
     * @param string|null $key
     * @return $this
     */
    public function setValidator(Field\Validator $validator, $key = null)
    {
        if ($key === null) {
            $this->addValidator($validator);
        } else {
            $this->liste_validator[$key] = $validator;
        }
        return $this;
    }

    /**
     * Retrieve a specific validator
     * @param  string $key
     * @return \FMUP\Import\Config\Field\Validator|null
     */
    public function getValidator($key)
    {
        if (isset($this->liste_validator[$key])) {
            return $this->liste_validator[$key];
        }
        return null;
    }

    /**
     *
     * @param Field\Formatter $formatter
     * @deprecated use self::addFormatter instead
     * @see self::addFormatter
     * @return $this
     */
    public function addFormatterFin(Field\Formatter $formatter)
    {
        return $this->addFormatter($formatter);
    }

    /**
     *
     * @param Field\Formatter $formatter
     * @deprecated use self::addFormatter instead
     * @see self::addFormatter
     * @return $this
     */
    public function addFormatterDebut(Field\Formatter $formatter)
    {
        return $this->addFormatter($formatter);
    }

    /**
     * Add a formatter for this field
     * @param Field\Formatter $formatter
     * @return $this
     */
    public function addFormatter(Field\Formatter $formatter)
    {
        array_push($this->formatters, $formatter);
        return $this;
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
        if (count($this->formatters) > 0) {
            foreach ($this->formatters as $formatter) {
                $this->value = $formatter->format($this->value) ?: "";
                if ($formatter->hasError()) {
                    $this->liste_erreur[get_class($formatter)] = $formatter->getErrorMessage();
                }
            }
        }
    }
}
