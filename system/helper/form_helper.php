<?php

/**
 * Classe permettant de créer différents éléments d'un formulaire
 * @version 1.0.
 */
class FormHelper
{
    /**
     * Crée un input hidden contenant le token du formulaire
     * @return string : L'input HTML
     */
    public static function getInputToken()
    {
        return self::inputSimple('hidden', array('name' => 'tokenform', 'value' => $_SESSION['jeton_formulaire']));
    }

    /**
     * Fonction vérifiant le token retourné dans un formulaire
     * @return bool : Vrai si le token est validé
     */
    public static function checkToken()
    {
        $retour = false;
        if (isset($_REQUEST['tokenform'], $_SESSION['jeton_formulaire'])) {
            $retour = ($_SESSION['jeton_formulaire'] == $_REQUEST['tokenform']);
        }

        return $retour;
    }

    /**
     * Fonction déterminant si le champ d'un formulaire est considéré comme éditable ou non
     * (non éditable transforme l'input en span)
     * @param Object $object : L'objet de l'attribut
     * @param string $attribute : L'attribut à tester
     * @param bool $editable : [OPT] Le flag éditable dans la fonction
     * @return bool : VRAI si pas éditable
     */
    protected static function getDroitInput($object, $attribute, $editable = true)
    {
        return (!$editable || !$object->isChampModifiable($attribute));
    }

    /**
     * Fonction permettant de formater les params
     * @param Object $object : L'objet de l'attribut
     * @param string $attribute : L'attribut à tester
     * @param array $params : Les params
     * @return array : les params
     */
    public static function formaterClassePourInput($object, $attribute, $params)
    {
        $class_name = String::to_Case(get_class($object));

        $params['value'] = $object->getAttribute($attribute);

        if (isset($params["autocomplete"])) {
            $params["autocomplete"] = ' autocomplete="off"';
        }

        if (!isset($params["name"])) {
            $params["name"] = $class_name;
            // ???
            if (array_key_exists('premier_tableau', $params) && $params['premier_tableau'] != '') {
                $params["name"] .= "[" . $params['premier_tableau'] . "]";
            }
            $params["name"] .= "[" . $attribute . "]";
            if (array_key_exists('sous_tableau', $params) && $params['sous_tableau'] != '') {
                $params["name"] .= "[" . $params['sous_tableau'] . "]";
            }
        }

        if (empty($params["id"])) {
            $params["id"] = $class_name . "_" . $attribute;
        }

        if (empty($params["class"])) {
            $params["class"] = '';
        }

        if (empty($params["invite"])) {
            $params["invite"] = array();
        }

        return $params;
    }

    /**
     * Retourne un input avec la value de l'object $object suivant la propriété $attribute
     * @param mixed $object : L'objet à exploiter
     * @param string $attribute : Nom de l'attribut de l'objet à utiliser
     * @param bool $editable : [OPT] Transforme l'input en span, pas défaut non
     * @param array $params : [OPT] Tableau contenant des paramètres d'utilisation :
     * - name/id : Obligatoire
     * - formatage : Fonction de UniteHelper pour formater la valeur
     * - id_span : ID du span en readonly
     * - AUTRE : Sera mis à la suite en attribut sous la forme cle="valeur"
     * @return string : La ou les balises HTML
     * @example : inputText($client, "name")
     * donne < input type="text" name="client[name]" value="$client->getName()" id="client_name" />
     */
    public static function inputText($object, $attribute, $editable = true, $params = array())
    {
        $errors = $object->getErrors();
        $differences = $object->compareVersion();

        $no_edit = self::getDroitInput($object, $attribute, $editable);

        $params = self::formaterClassePourInput($object, $attribute, $params);

        // formatage optionnel de la valeur, $params['formatage'] doit etre une fonction de unite helper
        $valeur_formatee = $params['value'];
        if (!empty($params['formatage'])) {
            if (method_exists('UniteHelper', $params['formatage'])) {
                $valeur_formatee = UniteHelper::$params['formatage']($params['value']);
            }
            unset($params['formatage']);
        }

        if (isset($params["id_span"])) {
            $id_span = $params["id_span"];
            unset($params["id_span"]);
        } else {
            $id_span = "span_" . $params["id"];
        }

        // En cas d'erreurs sur ce champ
        if (isset($errors[$attribute])) {
            $params["class"] .= " erreur";
        } elseif (isset($differences[$attribute])) {
            $params["class"] .= " difference";
            $params["title"] = "ce champ a &eacute;t&eacute; modifi&eacute;";
        }

        // Création du champ
        if ($no_edit) {
            $retour = self::spanSimple($valeur_formatee, $id_span, $params);
        } else {
            // Création du champ
            $retour = self::inputSimple('text', $params);
        }

        return $retour;
    }

    /**
     * Retourne un input password avec la value de l'object $object suivant la propriété $attribute
     * @param mixed $object : L'objet à exploiter
     * @param string $attribute : Nom de l'attribut de l'objet à utiliser
     * @param bool $editable : [OPT] Transforme l'input en span, pas défaut non
     * @param array $params : [OPT] Tableau contenant des paramètres d'utilisation
     * @return string : La ou les balises HTML
     */
    public static function inputPassword($object, $attribute, $editable = true, $params = array())
    {
        $no_edit = self::getDroitInput($object, $attribute, $editable);

        $params = self::formaterClassePourInput($object, $attribute, $params);

        // Création du champ
        if ($no_edit) {
            $retour = self::spanSimple('xxxx', $params['id'], $params);
        } else {
            $retour = self::inputSimple('password', $params);
        }

        return $retour;
    }

    /**
     * Retourne un input hidden avec la value de l'object $object suivant la propriété $attribute
     * @param mixed $object : L'objet à exploiter
     * @param string $attribute : Nom de l'attribut de l'objet à utiliser
     * @param array $params : [OPT] Tableau contenant des paramètres d'utilisation
     * @return string : La ou les balises HTML
     */
    public static function inputHidden($object, $attribute, $params = array())
    {
        $params = self::formaterClassePourInput($object, $attribute, $params);
        $params['value'] = $object->getAttribute($attribute);
        $retour = self::inputSimple('hidden', $params);

        return $retour;
    }

    /**
     * Crée un input avec les paramètres données
     * @param string $type : Attribut type : text, checkbox, password et hidden disponibles
     * @param array $params : Chaque paramètre est un attribut ajouté à l'input
     * @return string : l'input sous forme HTML
     */
    public static function inputSimple($type, $params)
    {
        $retour = '<input type="' . $type . '"';
        foreach ($params as $param => $valeur_param) {
            if ($param && $valeur_param != '' && $param != 'invite') {
                if ($param == 'value') {
                    $valeur_param = DisplayHelper::convertCaracteresSpeciaux($valeur_param);
                }
                $retour .= ' ' . DisplayHelper::convertCaracteresSpeciaux($param) . '="'
                    . DisplayHelper::convertCaracteresSpeciaux($valeur_param) . '"';
            }
        }
        $retour .= ' />';

        return $retour;
    }

    /**
     * Crée un span avec un input caché à côté
     * @param string $valeur : Le texte dans le span
     * @param string $id : ID du span
     * @param array $params : Chaque paramètre est un attribut ajouté à l'input
     * @param bool $autoriser_html : Active ou non htmlentities
     * @return string : Le HTML
     */
    public static function spanSimple($texte, $id, $params, $autoriser_html = false)
    {
        if (empty($params["title"])) {
            $params["title"] = '';
        }

        // Si on est readonly on ne met pas la classe calendrier qui met l'icone en JS
        if ($params["class"] == "calendrier") {
            $params["class"] = "";
        }

        if (!$autoriser_html) {
            $defaultCharset = version_compare(PHP_VERSION, '5.6', '>=') ? ini_get('default_charset') : 'UTF-8';
            if (!defined('ENT_COMPAT')) {
                define('ENT_COMPAT', 2);
            }
            if (!defined('ENT_HTML401')) {
                define('ENT_HTML401', 0);
            }
            $texte = htmlentities($texte, ENT_COMPAT | ENT_HTML401, $defaultCharset);
        }

        $retour = '<span title="{' . $params["title"] . '" class="' . $params["class"] . '" id="' . $id . '">'
            . $texte . '</span>';
        $retour .= self::inputSimple('hidden', $params);

        return $retour;
    }

    /**
     * Retourne un textarea avec la value de l'object $object suivant la propriété $attribute
     * @param mixed $object : L'objet à exploiter
     * @param string $attribute : Nom de l'attribut de l'objet à utiliser
     * @param bool $editable : [OPT] Transforme l'input en span, pas défaut non
     * @param array $params : [OPT] Tableau contenant des paramètres d'utilisation
     * @return string : La ou les balises HTML
     */
    public static function textArea($object, $attribute, $editable = true, $params = array())
    {
        $errors = $object->getErrors();
        $differences = $object->compareVersion();

        $no_edit = self::getDroitInput($object, $attribute, $editable);

        $params = self::formaterClassePourInput($object, $attribute, $params);

        if (empty($params["rows"])) {
            $params["rows"] = "2";
        }
        if (empty($params["cols"])) {
            $params["cols"] = "36";
        }

        // En cas d'erreurs sur ce champ
        if (isset($errors[$attribute])) {
            $params["class"] .= " erreur";
        } elseif (isset($differences[$attribute])) {
            $params["class"] .= " difference";
        }

        // Création du champ
        if ($no_edit) {
            $retour = self::spanSimple($params['value'], $params["id"], $params);
        } else {
            $retour = self::textareaSimple($params);
        }
        return $retour;
    }

    /**
     * Crée un input avec les paramètres données
     * @param array $params : Chaque paramètre est un attribut ajouté à l'input
     * @return string : le textarea sous forme HTML
     */
    public static function textareaSimple($params)
    {
        $retour = "<textarea";
        foreach ($params as $param => $valeur_param) {
            if ($param && $valeur_param) {
                if ($param == 'value') {
                    $valeur = $valeur_param;
                } else {
                    $retour .= ' ' . $param . '="' . $valeur_param . '"';
                }
            }
        }
        $retour .= ">";

        if (isset($valeur)) {
            $retour .= htmlentities($valeur);
        }
        $retour .= "</textarea>";

        return $retour;
    }

    /**
     * Retourne un ensemble d'inputs construit à partir d'une collection
     * @param Object $object : L'objet qui contient la valeur
     * @param int $attribute : L'attribut qui génère un tableau des valeurs à cocher
     * @param array [Object] $collection : La collection d'options à afficher.
     * @param string $element_value : L'attribut qui contient la value à afficher dans l'option
     * @param string $element_text : L'attribut qui contient le texte à afficher dans l'option
     * @param bool $editable : [OPT] Transforme le select en span, pas défaut non
     * @param array $params : [OPT] Paramètres supplémentaires
     * @return string : La ou les balises HTML
     */
    public static function checkboxesFromCollection(
        $object,
        $attribute,
        $collection,
        $element_value,
        $element_text,
        $editable = true,
        $params = array()
    ) {
        $array = Model::arrayFromCollection($collection, $element_value, $element_text);
        return FormHelper::checkboxesFromArray($object, $attribute, $array, $editable, $params);
    }

    /**
     * Retourne un ensemble d'inputs construit à partir d'un tableau
     * @param Object $object : L'objet qui contient la valeur
     * @param int $attribute : L'attribut qui génère un tableau des valeurs à cocher
     * @param array $array : La collection d'options à afficher.
     * @param string $element_value : L'attribut qui contient la value à afficher dans l'option
     * @param string $element_text : L'attribut qui contient le texte à afficher dans l'option
     * @param bool $editable : [OPT] Transforme le select en span, pas défaut non
     * @param array $params : [OPT] Paramètres supplémentaires
     * @return string : La ou les balises HTML
     */
    public static function checkboxesFromArray($object, $attribute, $array, $editable = true, $params = array())
    {
        $params = self::formaterClassePourInput($object, $attribute, $params);

        $no_edit = self::getDroitInput($object, $attribute, $editable);
        $nom = $params['name'];
        $id = $params['id'];

        $retour = "";
        foreach ($array as $value => $text) {
            $params['name'] = $nom;
            $params['name'] .= '[' . $value . ']';
            $params['id'] = $id;
            $params['id'] .= '_' . $value;

            $retour .= self::inputCheckbox($object, $attribute, !$no_edit, $params, array('0', $value));
            $retour .= self::labelSimple($text, $params);
        }
        return $retour;
    }

    /**
     * Retourne une checkbox pour l'object $object suivant la propriété $attribute
     * @param object $object : L'objet utilisé
     * @param string $attribute : L'attribut de l'objet utilisé
     * @param bool $editable : [OPT] Transforme l'input en span, pas défaut non
     * @param array $params : [OPT] Paramètres supplémentaires
     * @param array $valeurs : [OPT] Les valeurs pour le champ, non coché puis coché, par défaut 0 et 1
     * @return string : La ou les balises HTML
     */
    public static function inputCheckbox(
        $object,
        $attribute,
        $editable = true,
        $params = array(),
        $valeurs = array(0, 1)
    ) {
        $errors = $object->getErrors();
        $differences = $object->compareVersion();

        $no_edit = self::getDroitInput($object, $attribute, $editable);

        $params = self::formaterClassePourInput($object, $attribute, $params);

        if (!isset($params["style"])) {
            $params["style"] = "width: auto;";
        }

        $autoriser_html = false;
        $valeur_formatee = $params['value'];
        // Création du champ
        if (!empty($params["icone"])) {
            if ($params['value']) {
                $valeur_formatee = Constantes::imageOui();
            } else {
                $valeur_formatee = Constantes::imageNon();
            }
            $autoriser_html = true;
            unset($params["icone"]);
        }

        // En cas d'erreurs sur ce champ
        if (isset($errors[$attribute])) {
            $params['class'] .= "erreur";
        } elseif (isset($differences[$attribute])) {
            $params['class'] .= " difference";
        }

        if ($no_edit) {
            $retour = self::spanSimple($valeur_formatee, $params['id'], $params, $autoriser_html);
        } else {
            // Gestion du non coché (l'hidden sera écrasé par la checkbox seulement si elle est cochée)
            $retour = self::inputSimple('hidden', array('name' => $params['name'], 'value' => $valeurs[0]));

            if ($valeurs[1] . "" === $params['value'] . "") {
                $params['checked'] = "checked";
            }
            // La valeur de l'input ici est la valeur du coché, et non la valeur en base
            $params['value'] = $valeurs[1];

            // Création du champ
            $retour .= self::inputSimple('checkbox', $params);
        }
        return $retour;
    }

    /**
     * Retourne un ensemble d'inputs construit à partir d'une collection
     * @param Object $object : L'objet qui contient la valeur
     * @param int $attribute : L'attribut qui génère un tableau des valeurs à cocher
     * @param array [Object] $collection : La collection d'options à afficher.
     * @param string $element_value : L'attribut qui contient la value à afficher dans l'option
     * @param string $element_text : L'attribut qui contient le texte à afficher dans l'option
     * @param bool $editable : [OPT] Transforme le select en span, pas défaut non
     * @param array $params : [OPT] Paramètres supplémentaires
     * @return string : La ou les balises HTML
     */
    public static function radiosFromCollection(
        $object,
        $attribute,
        $collection,
        $element_value,
        $element_text,
        $editable = true,
        $params = array()
    ) {
        $array = Model::arrayFromCollection($collection, $element_value, $element_text);
        return FormHelper::radiosFromArray($object, $attribute, $array, $editable, $params);
    }


    /**
     * Retourne un ensemble d'inputs construit à partir d'un tableau
     * @param Object $object : L'objet qui contient la valeur
     * @param int $attribute : L'attribut qui génère un tableau des valeurs à cocher
     * @param array $array : La collection d'options à afficher.
     * @param bool $editable : [OPT] Transforme le select en span, pas défaut non
     * @param array $params : [OPT] Paramètres supplémentaires
     * @return string : La ou les balises HTML
     */
    public static function radiosFromArray($object, $attribute, $array, $editable = true, $params = array())
    {
        $params = self::formaterClassePourInput($object, $attribute, $params);

        $no_edit = self::getDroitInput($object, $attribute, $editable);

        $id = $params['id'];

        $retour = "";
        foreach ($array as $value => $text) {
            $params['id'] = $id;
            $params['id'] .= '_' . $value;

            $retour .= self::inputRadio($object, $attribute, !$no_edit, $params, $value);
            $retour .= self::labelSimple($text, $params);
        }
        return $retour;
    }

    /**
     * Retourne un radio pour l'object $object suivant la propriété $attribute
     * @param object $object : L'objet utilisé
     * @param string $attribute : L'attribut de l'objet utilisé
     * @param bool $editable : [OPT] Transforme l'input en span, pas défaut non
     * @param array $params : [OPT] Paramètres supplémentaires
     * @param bool $valeur : [OPT] La valeur pour le champ coché, par défaut 1
     * @return string : La ou les balises HTML
     */
    public static function inputRadio($object, $attribute, $editable = true, $params = array(), $valeur = 1)
    {
        $errors = $object->getErrors();
        $differences = $object->compareVersion();

        $params = self::formaterClassePourInput($object, $attribute, $params);

        $no_edit = self::getDroitInput($object, $attribute, $editable);

        // En cas d'erreurs sur ce champ
        if (isset($errors[$attribute])) {
            $params['class'] .= "erreur";
        } elseif (isset($differences[$attribute])) {
            $params['class'] .= " difference";
        }

        // Création du champ
        if ($no_edit) {
            $retour = self::spanSimple($params['value'], $params['id'], $params);
        } else {
            if ($valeur . "" === $params['value'] . "") {
                $params['checked'] = "checked";
            }
            // La valeur de l'input ici est la valeur du coché, et non la valeur en base
            $params['value'] = $valeur;

            // Création du champ
            $retour = self::inputSimple('radio', $params);
        }
        return $retour;
    }

    /**
     * Crée un label pour les checkbox/radios
     * @param string $valeur : Le texte dans le span
     * @param array $params : Chaque paramètre est un attribut ajouté à l'input
     * @param bool $autoriser_html : Active ou non htmlentities
     * @return string : Le HTML
     */
    public static function labelSimple($texte, $params, $autoriser_html = false)
    {
        if (empty($params["title"])) {
            $params["title"] = '';
        }

        if (!isset($params["class"])) {
            $params["class"] = "radio_label";
        } else {
            $params["class"] = "radio_label " . $params["class"];
        }

        if (!$autoriser_html) {
            $texte = htmlentities($texte);
        }

        $retour = '<label title="' . $params["title"] . '" ' .
            'class="' . $params["class"] . '" for="' . $params['id'] . '">' . $texte . '</label>';

        return $retour;
    }

    /**
     * Retourne un select construit à partir d'une collection
     * @param Object $object : L'objet qui contient la valeur
     * @param int $attribute : L'attribut qui génère un tableau des valeurs à cocher
     * @param array [Object] $collection : La collection d'options à afficher.
     * @param string $element_value : L'attribut qui contient la value à afficher dans l'option
     * @param string $element_text : L'attribut qui contient le texte à afficher dans l'option
     * @param bool $editable : [OPT] Transforme le select en span, pas défaut non
     * @param array $params : [OPT] Paramètres supplémentaires
     * @return string : La ou les balises HTML
     */
    public static function selectFromCollection(
        $object,
        $attribute,
        $collection,
        $element_value,
        $element_text,
        $editable = true,
        $params = array()
    ) {
        $array = Model::arrayFromCollection($collection, $element_value, $element_text);
        return FormHelper::selectFromArray($object, $attribute, $array, $editable, $params);
    }

    /**
     * Retourne un ensemble d'inputs construit à partir d'un tableau
     * @param Object $object : L'objet qui contient la valeur
     * @param int $attribute : L'attribut qui génère un tableau des valeurs à cocher
     * @param array $array : La collection d'options à afficher.
     * @param bool $editable : [OPT] Transforme le select en span, pas défaut non
     * @param array $params : [OPT] Paramètres supplémentaires
     * @return string : La ou les balises HTML
     */
    public static function selectFromArray($object, $attribute, $array, $editable = true, $params = array())
    {
        $errors = $object->getErrors();
        $differences = $object->compareVersion();

        $no_edit = self::getDroitInput($object, $attribute, $editable);

        $params = self::formaterClassePourInput($object, $attribute, $params);

        if (isset($params["id_span"])) {
            $html_id_span = $params["id_span"];
            unset($params["id_span"]);
        } else {
            $html_id_span = "span_" . $params["id"];
        }

        // En cas d'erreurs sur ce champ
        if (isset($errors[$attribute])) {
            $params["class"] .= " erreur";
        } elseif (isset($differences[$attribute])) {
            $params["class"] .= " difference";
        }

        // Création du champ
        if ($no_edit) {
            $texte_champ = "&nbsp;";
            foreach ($array as $value => $text) {
                if ($value == $params['value']) {
                    $texte_champ = $text;
                    break;
                }
            }
            $retour = self::spanSimple($texte_champ, $html_id_span, $params);
        } else {
            $retour = self::selectSimple($array, $params['value'], $params);
        }
        return $retour;
    }

    /**
     * Crée un select avec les paramètres données
     * @param string $tableau : Tableau des options à afficher
     * @param string $valeur : Attribut valeur, vide par défaut
     * @param array $params : Chaque paramètre est un attribut ajouté au select
     * @return string : le select sous forme HTML
     */
    public static function selectSimple($tableau, $valeur, $params)
    {
        $retour = '<select';
        foreach ($params as $param => $valeur_param) {
            if ($param && $valeur_param != '' && $param != 'invite') {
                $retour .= ' ' . $param . '="' . $valeur_param . '"';
            }
        }
        $retour .= '>';
        $retour .= self::optionsFromArray(
            $tableau,
            DisplayHelper::convertCaracteresSpeciaux($valeur),
            $params['invite']
        );
        $retour .= "</select>";
        return $retour;
    }

    /**
     * Construit une liste d'options à partir d'une collection
     * @param array $tableau : Le tableau sous la forme cle => valeur
     * @param string $valeur_selectionnee : La valeur sélectionnée
     * @param mixed $options_supplementaires : [OPT] La ou les options supplémentaires à ajouter,
     *                                      peut être une chaine ou un tableau
     * @return string : La ou les balises HTML
     */
    public static function optionsFromCollection(
        $collection,
        $element_value,
        $element_text,
        $selected_value,
        $options_supplementaires = array()
    ) {
        $array = Model::arrayFromCollection($collection, $element_value, $element_text);
        return FormHelper::optionsFromArray($array, $selected_value, $options_supplementaires);
    }

    /**
     * Construit une liste d'options à partir d'un tableau
     * @param array $tableau : Le tableau sous la forme cle => valeur
     * @param string $valeur_selectionnee : La valeur sélectionnée
     * @param mixed $options_supplementaires :
     *                  [OPT] La ou les options supplémentaires à ajouter, peut être une chaine ou un tableau
     * @return string : La ou les balises HTML
     */
    public static function optionsFromArray($tableau, $valeur_selectionnee, $options_supplementaires = array())
    {
        // Créer un input pour chaque élément de la collection
        $retour = "";

        if (!is_array($options_supplementaires)) {
            $options_supplementaires = array('' => $options_supplementaires);
        }

        // Option pour la value ""
        foreach ($options_supplementaires as $cle => $valeur) {
            $retour .= '<option value="' . $cle . '" '
                . ($valeur_selectionnee == $cle ? 'selected="selected"' : "")
                . '>' . $valeur . '</option>';
        }

        // Les autres options
        foreach ($tableau as $value => $text) {
            $retour .= "<option value='$value' ";
            // Suite à un bug obscur de PHP sur la comparaison de clés, on concatène avec une chaine vide
            if ($value . "" == $valeur_selectionnee . "") {
                $retour .= "selected='selected' ";
            }
            $retour .= '>' . htmlspecialchars($text) . '</option>' . "\n";
        }

        return $retour;
    }

    /**
     * Crée un input text gérant l'autocompletion avec la value de l'object $object suivant la propriété $attribute
     * @param mixed $object : L'objet à exploiter
     * @param string $attribute : Nom de l'attribut de l'objet à utiliser
     * @param bool $editable : [OPT] Transforme l'input en span, pas défaut non
     * @param array $params : [OPT] Tableau contenant des paramètres d'utilisation
     * @return string : l'input sous forme HTML
     */
    public static function inputAutocompleteText($object, $attribute, $editable = true, $params = array())
    {
        $no_edit = self::getDroitInput($object, $attribute, $editable);

        $params = self::formaterClassePourInput($object, $attribute, $params);

        if (isset($params["id_span"])) {
            $html_id_span = $params["id_span"];
            unset($params["id_span"]);
        } else {
            $html_id_span = "span_" . $params["id"];
        }

        // formatage optionnel de la valeur, $params['formatage'] doit etre une fonction de unite helper
        $valeur_formatee = $params['value'];
        if (!empty($params['formatage'])) {
            if (method_exists('UniteHelper', $params['formatage'])) {
                $valeur_formatee = UniteHelper::$params['formatage']($params['value']);
            }
            unset($params['formatage']);
        }

        if ($no_edit) {
            $retour = self::spanSimple($valeur_formatee, $html_id_span, $params);
        } else {
            $retour = self::inputAutocompleteSimple(get_class($object), $attribute, 'id', $params);
        }

        return $retour;
    }

    /**
     * Crée un input text gérant l'autocompletion
     * @param string $classe : Nom de l'objet auquel appartient l'attribut
     * @param string $attribut : Colonne utilisée pour la recherche autocomplétée
     * @param string $cle : [OPT] Clé de l'objet de retour, par défaut "id"
     * @param array $params : [OPT] Tableau pouvant contenir les paramètres mane, id, maxlength, valeur et id_valeur
     * @return string : l'input sous forme HTML
     */
    public static function inputAutocompleteSimple($classe, $attribut, $cle = 'id', $params = array())
    {
        $html_name = (isset($params['name'])) ? $params['name'] : 'filtre[' . $classe . ']';
        $html_id = (isset($params['id'])) ? $params['id'] : 'filtre_' . $classe;
        $valeur = (isset($params['valeur'])) ? $params['valeur'] : '';
        $id_valeur = (isset($params['id_valeur'])) ? $params['id_valeur'] : '';

        $parametres = array(
            'name' => $html_name . '[' . $attribut . ']',
            'id' => $html_id,
            'value' => $valeur,
            'autocomplete' => 'off'
        );
        if (!empty($params['maxlength'])) {
            $parametres['maxlength'] = $params['maxlength'];
        }

        $retour = self::inputSimple('text', $parametres);
        // Image de suppression
        $retour .= "\n";
        if (!empty($params['suppression'])) {
            $cache = '';
            if (!$valeur) {
                $cache = 'display: none; ';
            }
            $retour .= '<img src="' . Constantes::getSrcImageSuppressionAutocomplete() . '" '
                . 'alt="X" title="Retirer la valeur" class="img_action" '
                . 'style="' . $cache . 'cursor: pointer; position: absolute;" />';
        }
        $retour .= self::inputSimple(
            'hidden',
            array(
                'name' => $html_name . '[' . $cle . ']',
                'id' => $html_id . '_id',
                'value' => $id_valeur
            )
        );

        return $retour;
    }
}
