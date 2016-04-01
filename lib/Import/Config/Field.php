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
    const TYPE_IGNORED = 'ignored';
    private $name;
    private $value;
    private $table_cible;
    private $champ_cible;
    private $required;
    private $type;
    private $validators = array();
    private $formatters = array();
    private $errors = array();

    public function __construct($name, $value, $table_cible, $champ_cible, $required, $type)
    {
        $this->name = $name;
        $this->value = $value;
        $this->table_cible = $table_cible;
        $this->champ_cible = $champ_cible;
        $this->required = $required;
        $this->type = $type;
        if ($type != self::TYPE_IGNORED && $type != "") {
            $classe = __NAMESPACE__ . '\Validator\\' . ucfirst($type);
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
        return $this->errors;
    }

    public function setValue($valeur)
    {
        $this->value = trim($valeur);
        return $this;
    }

    /**
     *
     * @param Field\Validator $validator
     * @return $this
     */
    public function addValidator(Field\Validator $validator)
    {
        array_push($this->validators, $validator);
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
            $this->validators[$key] = $validator;
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
        if (isset($this->validators[$key])) {
            return $this->validators[$key];
        }
        return null;
    }

    /**
     * Get defined validators
     * @return Field\Validator[]
     */
    public function getValidators()
    {
        return $this->validators;
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

    /**
     * Get defined formaters
     * @return Field\Formatter[]
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * Is field valid
     * @return bool
     */
    public function validateField()
    {
        $valid = true;
        foreach ($this->getValidators() as $validator) {
            if (!$validator->validate($this->value)) {
                $valid = false;
                $this->errors[get_class($validator)] = $validator->getErrorMessage();
            }
        }
        return $valid;
    }

    /**
     * Format field with defined formatters
     * @return $this
     */
    public function formatField()
    {
        foreach ($this->getFormatters() as $formatter) {
            $this->value = $formatter->format($this->value) ?: "";
            if ($formatter->hasError()) {
                $this->errors[get_class($formatter)] = $formatter->getErrorMessage();
            }
        }
        return $this;
    }
}
