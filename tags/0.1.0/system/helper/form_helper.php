<?php
class FormHelper
{
    /**
     * Etoile rouge pour un champ obligatoire
     */
    public static function etoileChampObligatoire($style = "")
    {
        return '<span style="color:red; font-weight:bold; '.$style.'">*</span>';
    }

    /**
     * Retourne un input avec la value de l'object $object suivant la propriété $attribute
     * @param mixed $object : L'objet à exploiter
     * @param string $attribute : Nom de l'attribut de l'objet à utiliser
     * @param array $params : [OPT] Tablau contenant des paramètres d'utilisation :
     * - readonly
     * - name
     * - id
     * - maxlength
     * - size
     * - style
     * - autocomplete
     * - class
     * - id_span
     * - change
     * - formattage
     * - prefixe
     * - complement
     * @return string : La ou les balises HTML
     * @todo : Du ménage à faire
     * @example : inputText($client, "name") donne < input type="text" name="client[name]" value="$client->getName()" id="client_name" />
     */
    public static function inputText($object, $attribute, $params = array())
    {
        $class_name = str_replace('x_', '', String::to_Case(get_class($object)));
        $errors = $object->getErrors();
        $differences = $object->compareVersion();
        $title = "";

        if (isset($params["autocomplete"])) {
            $autocomplete = ' autocomplete="off"';
        } else {
            $autocomplete = '';
        }
        if ((isset($params["readonly"]) && $params["readonly"]) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }
        if (isset($params["maxlength"]) && $params["maxlength"]) {
            $maxlength = $params["maxlength"];
        } else {
            $maxlength = "";
        }
        if (isset($params["size"]) && $params["size"]) {
            $size = $params["size"];
        } else {
            $size = "";
        }
        if (isset($params["style"]) && $params["style"]) {
            $style = $params["style"];
        } else {
            $style = "";
        }
        if (isset($params["id"])) {
            $html_id = $params["id"];
        } else {
            $html_id = $class_name."_".$attribute;
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name;
            if (array_key_exists('premier_tableau', $params) && $params['premier_tableau'] != '') {
                $html_name .= "[".$params['premier_tableau']."]";
            }
            $html_name .= "[".$attribute."]";
            if (array_key_exists('sous_tableau', $params) && $params['sous_tableau'] != '') {
                $html_name .= "[".$params['sous_tableau']."]";
            }
        }
        if (isset($params["id_span"])) {
            $html_id_span = $params["id_span"];
        } else {
            $html_id_span = "span_".$html_id;
        }
        if (isset($params["complement"])) {
            $complement = $params["complement"];
        } else {
            $complement = "";
        }
        if (!empty($params["prefixe"])) {
            $prefixe 		= $params["prefixe"];
            $class_prefixe 	= " with_prefixe";
        } else {
            $prefixe 		= "";
            $class_prefixe	= "";
        }

        if (isset($params["placeholder"]) && $params["placeholder"]) {
            $placeholder = $params["placeholder"];
        } else {
            $placeholder = "";
        }

        $html_readonly = "";
        if (isset($params["class"])) {
            $html_class = $params["class"];
            if (preg_match("/disabled/", $html_class) || $html_class == "disabled") {
                $html_readonly = "readonly='readonly'";
            }
        } else {
            $html_class = "";
        }
        if (isset($params["change"]) && $params["change"]) {
            $change = $params["change"];
        } else {
            $change = "";
        }
        
        if (isset($params["onblur"]) && $params["onblur"]) {
            $blur = $params["onblur"];
        } else {
            $blur = "";
        }

        $valeur = $object->getAttribute($attribute);
        $valeur_formatee = $valeur;
        //formattage optionnel de la valeur, $params['formattage'] doit etre une fonction de unite helper
        if (isset($params['formattage'])) {
            $valeur_formatee = UniteHelper::$params['formattage']($valeur);
        }
        if ($no_edit) {
            // Création du champ
            if ($html_class=="calendrier") {
                $html_class = "";
            }
            if (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            }

            $html_class .= $class_prefixe;

            $retour = "<span title='$title' class='$html_class' id='$html_id_span'>".$prefixe.$valeur_formatee."</span>";
            $retour .= FormHelper::inputHidden($object, $attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            if (isset( $errors[$attribute] )) {
                $html_class .= " erreur";
            } elseif (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            }

            $html_class .= $class_prefixe;
            //$prefixe = ($prefixe) ? "<span class=\"prefixe\">".$prefixe."</span>" : "";

            // Création du champ
            $string = explode('[', $html_name, 4);
            $string = substr($string[1], 0, 4);
            if ($string == "date") {
                $valeur = Date::ukToFr($valeur);
            }
            $retour = $prefixe."<input title='$title' $html_readonly $autocomplete  type='text' name='$html_name' style='$style' value=\"";
            $retour .= DisplayHelper::convertCaracteresSpeciaux($valeur);
            $retour .= "\" id='$html_id'";
            if ($html_class != '') $retour .= " class='$html_class'";
            if ($maxlength != '') $retour .= " maxlength='$maxlength'";
            if ($size != '') $retour .= " size='$size'";
            if ($change != '') $retour .= " onchange='$change'";
            if ($blur != '') $retour .= " onblur='$blur'";
            if ($placeholder != '') $retour .= " placeholder='$placeholder'";
            $retour .= " />";
            $retour .= $complement;
        }

        return $retour;
    }
    /**
     * Retourne un input password avec la value de l'object $object suivant la propriété $attribute
     **/
    public static function inputPassword($object, $attribute, $params = array())
    {
        $class_name = str_replace('x_', '', String::to_Case(get_class($object)));
        $errors = $object->getErrors();

        if ((isset($params["readonly"]) && $params["readonly"]) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }
        if (isset($params["autocomplete"])) {
            $autocomplete = ' autocomplete="off"';
        } else {
            $autocomplete = '';
        }

        if (isset($params["id"])) {
            $html_id = $params["id"];
        } else {
            $html_id = $class_name."_".$attribute;
        }
        if (isset($params["class"])) {
            $html_class = $params["class"];
        } else {
            $html_class = "";
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name."[".$attribute."]";
        }

        // En cas d'erreurs sur ce champ
        if (isset( $errors[$attribute] )) {
            $html_class .= " erreur";
        }

        // Création du champ
        if ($no_edit) {
            $retour = "<span>xxxxx</span>";
        } else {
            $retour = "<input type='password' name='$html_name' $autocomplete value=\"";
            $retour .= str_replace('\"', '&quote;', $object->getAttribute($attribute));
            $retour .= "\" id='$html_id' class='$html_class' />";
        }

        return $retour;
    }

    /**
     * Retourne un input hidden avec la value de l'object $object suivant la propriété $attribute
     **/
    public static function inputHidden($object, $attribute, $params = array())
    {
        $class_name = str_replace('x_', '', String::to_Case(get_class($object)));

        if (isset($params["id"])) {
            $html_id = $params["id"];
        } else {
            $html_id = $class_name."_".$attribute;
        }
        if (isset($params["class"])) {
            $html_class = $params["class"];
        } else {
            $html_class = "hidden";
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name."[".$attribute."]";
        }

        // Création du champ
        $retour = "<input class='$html_class' type='hidden' name='$html_name' value='";
        $retour .= DisplayHelper::convertCaracteresSpeciaux($object->getAttribute($attribute));
        $retour .= "' id='$html_id' />";

        return $retour;
    }

     /**
     * Retourne un textArea avec la value de l'object $object suivant la propriété $attribute
     **/
    public static function textArea($object, $attribute, $params = array())
    {
        $class_name = str_replace('x_', '', String::to_Case(get_class($object)));
        $errors = $object->getErrors();
        $differences = $object->compareVersion();
        //debug::output($object);
        //debug::output($differences);

        if ((isset($params["readonly"]) && $params["readonly"]) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }
        if (isset($params["id"])) {
            $html_id = $params["id"];
        } else {
            $html_id = $class_name."_".$attribute;
        }
        $html_disabled = "";
        if (isset($params["class"])) {
            $html_class = $params["class"];
            if (preg_match("/disabled/", $html_class) || $html_class == "disabled") {
                $html_disabled = "disabled='disabled'";
            }
        } else {
            $html_class = "";
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name."[".$attribute."]";
        }

        if (isset($params["rows"])) {
            $html_rows = $params["rows"];
        } else {
            $html_rows = "2";
        }
        if (isset($params["cols"])) {
            $html_cols = $params["cols"];
        } else {
            $html_cols = "36";
        }
        if (isset($params["height"])) {
            $html_height = $params["height"];
        } else {
            $html_height = "55px";
        }
        if (isset($params["width"])) {
            $html_width = $params["width"];
        } else {
			$html_width = "auto";
		}
        $html_maxlength = '';
        if (isset($params["maxlength"])) {
            $html_maxlength = 'maxlength="'.$params["maxlength"].'"';
        }

        if (isset ($params["map_html"])) {
            $url = $params["map_html"];
            $map_html = '<area href="'.$url.'" coords="vos coordonnees" shape="votre forme" alt="votre légende"/>';
        } else {
            $map = "";
        }

        if ($no_edit) {
            if (isset($differences[$attribute])) {
                $html_class .= " difference";
            }
            // Création du champ
            $retour = "<span class='$html_class'>";
            //Si l'attribut est vide on le remplace par un espace
            $retour .= ($object->getAttribute($attribute)) ? $object->getAttribute($attribute) : "&nbsp;";
            $retour .= "</span>";
            $retour .= FormHelper::inputHidden($object, $attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            if (isset( $errors[$attribute] )) {
                $html_class .= " erreur";
            } elseif (isset($differences[$attribute])) {
                $html_class .= " difference";
            }

            // Création du champ
            $retour = "<textarea name='$html_name' $html_disabled id='$html_id' style='height:$html_height;width:$html_width;' class='$html_class' rows='$html_rows' cols='$html_cols' $html_maxlength>";
            //$retour .= str_replace('\"', '&quote;', $object->getAttribute($attribute));
            $retour .= $object->getAttribute($attribute);
            $retour .= "</textarea>";
        }
        return $retour;
    }

    /**
     * Retourne une checkbox pour l'object $object suivant la propriété $attribute
     * @param object $object : L'objet utilisé
     * @param string $attribute : L'attribut de l'objet utilisé
     * @param string $value : La valeur de la checkbox si cochée
     * @param array $params : Paramètres supplémentaires
     * @param string $checked_forced : Force le fait que la checkbox soit checké si à 1
     */
    public static function inputCheckbox($object, $attribute, $value = 1, $params = array(), $checked_forced = "")
    {
        $class_name = str_replace('x_', '', String::to_Case(get_class($object)));
        $errors = $object->getErrors();
        $differences = $object->compareVersion();

        if ((isset($params["readonly"]) && $params["readonly"]) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }
        if (isset($params["id"])) {
            $html_id = $params["id"];
        } else {
            $html_id = $class_name.'_'.$attribute.'_'.$value;
        }
        if (isset($params["class"])) {
            $html_class = $params["class"];
        } else {
            $html_class = "";
        }
        if (isset($params["style"])) {
            $html_style = $params["style"];
        } else {
            $html_style = "width: auto;";
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name."[".$attribute."]";
        }

        if ($no_edit) {
            if (isset($differences[$attribute])) {
                $html_class .= " difference";
            }
            // Création du champ
            if (!isset($params["icone"]) || $params["icone"]) {
                if ($object->getAttribute($attribute)) {
                    $retour = Images::oui();
                } else {
                    $retour = Images::non();
                }
            } else {
                $retour = "<span class='$html_class'>".$object->getAttribute($attribute)."</span>";
            }
            $retour .= FormHelper::inputHidden($object, $attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            if (isset( $errors[$attribute] )) {
                $html_class .= "erreur";
            } elseif (isset($differences[$attribute])) {
                $html_class .= " difference";
            }
            // Gestion du non coché (l'hidden sera écrasé par la checkbox seulement si elle est cochée)
            $retour = "<input type='hidden' name='$html_name' value='0' />"  ;
            // Création du champ
            $retour .= "<input type='checkbox' id='$html_id' name='$html_name' value='$value' class='$html_class' style='$html_style'"  ;
            if ($checked_forced==="") {
                if (is_array($object->getAttribute($attribute))) {
                    if (in_array($value."", $object->getAttribute($attribute))) {
                        $retour .= " checked='checked'";
                    }
                } else {
                    if ($value."" === $object->getAttribute($attribute)."") {
                        $retour .= " checked='checked'";
                    }
                }
            } elseif ($checked_forced==1) {
                    $retour .= " checked='checked'";
            }
            $retour .= " />";
        }
        return $retour;
    }

    /**
     * Retourne une radioBouton pour l'object $object suivant la propriété $attribute
     **/
    public static function inputRadio($object, $attribute, $value = "1", $params = array())
    {
        $class_name = str_replace('x_', '', String::to_Case(get_class($object)));
        $errors = $object->getErrors();
        $differences = $object->compareVersion();

        if ((isset($params["readonly"]) && $params["readonly"]) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }
        if (isset($params["id"])) {
            $html_id = $params["id"];
        } else {
            $html_id = $class_name."_".$attribute."_".$value;
        }
        if (isset($params["class"])) {
            $html_class = $params["class"];
        } else {
            $html_class = "";
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name."[".$attribute."]";
        }

        if ($no_edit) {
            if (isset($differences[$attribute])) {
                $html_class .= " difference";
            }
            // Création du champ
            $retour = "<span class='$html_class'>".$object->getAttribute($attribute)."</span>";
            $retour .= FormHelper::inputHidden($object, $attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            if (isset( $errors[$attribute] )) {
                $html_class .= "erreur";
            } elseif (isset($differences[$attribute])) {
                $html_class .= " difference";
            }
            // Création du champ
            $retour = "<input type='radio' id='$html_id' name='$html_name' value='$value' class='$html_class'";
            if ($value."" === $object->getAttribute($attribute)."") {
                $retour .= "checked='checked' ";
            }
            $retour .= " />";
        }
        return $retour;
    }

    /**
     * Retourne un ensemble d'inputs construit à partir d'un tableau
     * @param {Object} L'objet qui contient la valeur
     * @param {Integer} L'attribut qui génère un tableau des valeurs à cocher
     * @param {Array(Object)} La collection d'options à afficher.
     * @param {Integer} L'attribut qui contient la value à afficher dans l'option
     * @param {Integer} L'attribut qui contient le texte à afficher dans l'option
     * @param {String} Le texte s'il faut ajouter une value ""
     **/
    public static function checkboxesFromCollection($object, $attribute, $collection, $element_value, $element_text, $params = array())
    {
        $array = Model::arrayFromCollection($collection, $element_value, $element_text);
        return FormHelper::checkboxesFromArray($object, $attribute, $array, $params);
    }

    /**
     * Retourne un select construit à partir d'une collection
     * @param {Object} L'objet qui contient la valeur
     * @param {Integer} L'attribut qui génère un tableau des valeurs à cocher
     * @param {Array} Le tableau de values + texte à afficher.
     * @param {String} Le texte si il faut ajouter une value ""
     **/
    public static function checkboxesFromArray($object, $attribute, $array, $params = array())
    {
        $class_name = str_replace('x_', '', String::to_Case(get_class($object)));

        if ((isset($params["readonly"]) && $params["readonly"]) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }
        if (isset($params["id"])) {
            $html_id = $params["id"];
        } else {
            $html_id = $class_name."_".$attribute;
        }
        if (isset($params["class"])) {
            $html_class = $params["class"];
        } else {
            $html_class = "";
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name."[".$attribute."]";
        }
//debug::output($html_name, true);
        $retour = "";

        foreach ($array as $value => $text) {
            if ($no_edit) {
                // Création du champ
                if ($value == $object->getAttribute($attribute) || (is_array($object->getAttribute($attribute)) && in_array($value, $object->getAttribute($attribute)))) {
                    $retour .= "<span class='$html_class'>".$text."</span>";
                    $retour .= "<input type='hidden' value='$value' name='$html_name[$value]' />";
                }
            } else {
                $selected_value = str_replace('\"', '&quote;', $object->getAttribute($attribute));
                $var_tab = $html_name;
                $retour .= "<input id='$html_id' style='float:left;display:inline;width:25px;' class='$html_class' type='checkbox' name='".$html_name."[".$value."]' value='$value'";
                if ($value == $object->getAttribute($attribute) || (is_array($object->getAttribute($attribute)) && in_array($value, $object->getAttribute($attribute)))) {
                    $retour .= "checked='checked'";
                }
                $retour .= "/>";
                $retour .= "<span style='float:left;padding-top:3px;width:100px;'>$text</span><span style='min-height:0;clear:both;' ></span>";
            }
        }
        return $retour;
    }

/**
     * Retourne une compilation de radios construit à partir d'une collection
     * @param {Object} L'objet qui contient la valeur
     * @param {Integer} L'attribut qui génère un tableau des valeurs à cocher
     * @param {Array} Le tableau de values + texte à afficher.
     * @param {Array} Le tableau de values + help.
     * @param {String} Le texte si il faut ajouter une value ""
     **/
    public static function radiosFromArray($object, $attribute, $array, $array_help = array(), $params = array())
    {
        $class_name = str_replace('x_', '', String::to_Case(get_class($object)));
        $errors = $object->getErrors();

        if ((isset($params["readonly"]) && $params["readonly"]) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }
        if (isset($params["class"])) {
            $html_class = $params["class"];
        } else {
            $html_class = "span_radiobuton";
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name."[".$attribute."]";
        }
        if (isset( $errors[$attribute] )) {
            $html_class .= " erreur";
        }

        $retour = "";

        foreach ($array as $value => $text) {
            if ($no_edit) {
                // Création du champ
                if ($value == $object->getAttribute("$attribute")) {
                    $retour .= "<span class='$html_class'>".$text."</span>";
                    $retour .= "<input type='hidden' class='hidden' value='$value' name='$html_name' />";
                }
            } else {
                $retour .= "<span class='$html_class'>";
                $selected_value = str_replace('\"', '&quote;', $object->getAttribute($attribute));
                $retour .= "<input class='$html_class' type='radio' name='$html_name' value='$value'";
                if ($value == $object->getAttribute("$attribute")) {
                    $retour .= "checked='checked'";
                }
                $retour .= "/>";
                $retour .= $text;

                $retour .= "</span>";
                if (!empty($array_help)) {
                    $retour .= "&nbsp;".Boutons::help($text, $array_help[$value], 'edit_help help-incident');
                }
                //$retour .= "<br/>";
            }
        }
        return $retour;
    }

    /**
     * Retourne un select construit à partir d'un tableau
     * @param {Object} L'objet qui contient la valeur
     * @param {Integer} L'attribut qui contient la valeur à sélectionner
     * @param {Array(Object)} La collection d'options à afficher.
     * @param {Integer} L'attribut qui contient la value à afficher dans l'option
     * @param {Integer} L'attribut qui contient le texte à afficher dans l'option
     * @param {String} Le texte si il faut ajouter une value "";
     **/
    public static function selectFromCollection($object, $attribute, $collection, $element_value, $element_text, $params = array())
    {
        $array = Model::arrayFromCollection($collection, $element_value, $element_text);
        return FormHelper::selectFromArray($object, $attribute, $array, $params);
    }
    /**
     * Retourne un select construit à partir d'un tableau avezc un texte à afficher multiple sous forme 'texte - texte'
     * @param {Object} L'objet qui contient la valeur
     * @param {Integer} L'attribut qui contient la valeur à sélectionner
     * @param {Array(Object)} La collection d'options à afficher.
     * @param {Integer} L'attribut qui contient la value à afficher dans l'option
     * @param {Integer} L'attribut qui contient le premier texte à afficher dans l'option
     * @param {Integer} L'attribut qui contient le deuxième texte à afficher dans l'option
     * @param {String} Le texte si il faut ajouter une value "";
     **/
    public static function selectMultipleFromCollection($object, $attribute, $collection, $element_value, $element_text, $element_text2, $params = array())
    {
        $array = Model::arrayMultipleFromCollection($collection, $element_value, $element_text, $element_text2);
        return FormHelper::selectFromArray($object, $attribute, $array, $params);
    }
    /**
     * Retourne un select construit à partir d'un tableau
     * @param {Object} L'objet qui contient la valeur
     * @param {String} L'attribut qui contient la value
     * @param {Array} Le tableau de values + texte à afficher.
     * @param {Array} Paramètres (invite, readonly, id, class, name)
     **/
    public static function selectFromArray($object, $attribute, $array, $params = array())
    {
        $class_name = str_replace('x_', '', String::to_Case(get_class($object)));
        $errors = $object->getErrors();
        $differences = $object->compareVersion();

        if (isset($params["invite"])) {
            $invite = $params["invite"];
        } else {
            $invite = null;
        }
        if ((isset($params["readonly"]) && $params["readonly"]) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }

        if (isset($params["id"])) {
            $html_id = $params["id"];
        } else {
            $html_id = $class_name."_".$attribute;
        }
        if (isset($params["id_span"])) {
            $html_id_span = $params["id_span"];
        } else {
            $html_id_span = "span_".$html_id;
        }
        $html_disabled = "";
        if (isset($params["class"])) {
            $html_class = $params["class"];
            if (preg_match("/disabled/", $html_class) || $html_class == "disabled") {
                $html_disabled = 'disabled="disabled"';
            }
        } else {
            $html_class = "";
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name."[".$attribute."]";
        }
        if (isset($params["style"])) {
            $style = $params["style"];
        } else {
            $style = '';
        }
        if (isset($params["change"])) {
            $change = $params["change"];
        } else {
            $change = '';
        }

        $selected_value = str_replace('\"', '&quote;', $object->getAttribute($attribute));
        $retour = "";
        if ($no_edit) {
            if (isset($differences[$attribute])) {
                $html_class .= " difference";
            }
            // Création du champ
            foreach ($array as $value => $text) {
                if ($value == $object->getAttribute("$attribute")) {
                    $retour = "<span class='$html_class' style='$style' id='$html_id_span'>".$text."</span>";
                }
            }
            if ("" == $retour) {
                $retour = "<span class='$html_class' style='$style' id='$html_id_span'>&nbsp;</span>";
            }
            $retour .= FormHelper::inputHidden($object, $attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            if (isset( $errors[$attribute] )) {
                $html_class .= " erreur";
            } elseif (isset($differences[$attribute])) {
                $html_class .= " difference";
            }
            // Création du champ
            $retour = "<select name='$html_name' id='$html_id' class='$html_class' $html_disabled style='$style' onchange='$change'>";

            $retour .= FormHelper::optionsFromArray($array, $selected_value, $params);

            $retour .= "</select>";
        }
        return $retour;
    }
    /**
     * Construit une collection d'options
     * @param {Array} Le tableau de values + texte à afficher.
     * @param {Integer} La valeur à sélectionner
     **/
    public static function optionsFromArray($array, $selected, $params = array())
    {
        // Créer un input pour chaque élément de la collection
        $retour = "";

        if (isset($params["readonly"])) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }
        if (isset($params["invite"])) {
            $invite = $params["invite"];
        } else {
            $invite = null;
        }
        if (isset($params["options_particulieres"])) {
            $options_particulieres = $params["options_particulieres"];
        } else {
            $options_particulieres = array();
        }

        // Option pour la value ""
        if ($invite !== null) {
            $retour .= "<option value='' ".($selected === ""?"selected='selected'":"").">$invite</option>";
        }

        foreach ($options_particulieres as $value => $option) {
            $retour .= "<option value='".$value."' ".($selected === $value?"selected='selected'":"").">$option</option>";
        }

        // Option spécifique en plus de l'invite de commande (utilisée pour les statuts des ETVI principalement
        if (isset($params["option_supp"])) {
            foreach ($params["option_supp"] as $option_supp) {
                $retour .= "<option value='".$option_supp['value']."' ".($selected === $option_supp['value']?"selected='selected'":"").">".$option_supp['text']."</option>";
            }
        }

        // Les autres options
        foreach ($array as $value => $text) {
            $retour .= "<option value='$value' ";
            // 27/07/2011 : modification de la condition === en ==, a voir dans le temps
            if ($value."" == $selected."") {
                $retour .= "selected='selected' ";
            }
            $retour .= '>'.htmlspecialchars($text).'</option>'."\n";
        }

        return $retour;
    }
    /**
     * Construit une collection d'options
     * @param {Array} La collection
     * @param {Integer} L'attribut qui contient la value à afficher dans l'option
     * @param {Integer} L'attribut qui contient le texte à afficher dans l'option
     * @param {Integer} La valeur à sélectionner
     * @param {String} Le texte si il faut ajouter une value "";
     **/
    public static function optionsFromCollection($collection, $element_value, $element_text, $selected_value, $params = array())
    {
        $array = Model::arrayFromCollection($collection, $element_value, $element_text);
        return FormHelper::optionsFromArray($array, $selected_value, $params);
    }

    public static function textAreaMapHtml($object, $attribute, $params = array())
    {
        $class_name = String::to_Case(get_class($object));
        $errors = $object->getErrors();

        if ((isset($params["readonly"]) && $params["readonly"]) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }
        if (isset($params["id"])) {
            $html_id = $params["id"];
        } else {
            $html_id = $class_name."_".$attribute;
        }
        if (isset($params["class"])) {
            $html_class = $params["class"];
        } else {
            $html_class = "";
        }
        if (isset($params["name"])) {
            $html_name = $params["name"];
        } else {
            $html_name = $class_name."[".$attribute."]";
        }

        if (isset($params["rows"])) {
            $html_rows = $params["rows"];
        } else {
            $html_rows = "";
        }
        if (isset($params["cols"])) {
            $html_cols = $params["cols"];
        } else {
            $html_cols = "";
        }

        if (isset ($params["liste_categories"])) {
            $liste_categories = $params["liste_categories"];
            $map_html = "";
            $url = './categorie/categorie/entree?id_categorie=';
            foreach ($liste_categories as $numero => $categorie) {
                $map_html .= '<area href="'.$url.$categorie -> getId().'" coords="" shape="" alt=""/>';
            }
        } else {
            $map_html = "";
        }

        if (isset ($params["liste_sous_categories"])) {
            $liste_sous_categories = $params["liste_sous_categories"];
            $map_html = "";
            $url = './produits/produit/lister?id_sous_categorie=';
            foreach ($liste_sous_categories as $numero => $sous_categorie) {
                $map_html .= '<area href="'.$url.$sous_categorie -> getId().'" coords="" shape="" alt=""/>';
            }
        } else {
            $map_html = "";
        }


        if ($no_edit) {
            // Création du champ
            $retour = "<span class='$html_class'>".$object->getAttribute($attribute)."</span>";
            $retour .= FormHelper::inputHidden($object, $attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            if (isset( $errors[$attribute] )) {
                $html_class .= " erreur";
            }

            // Création du champ
            $retour = "<textarea name='$html_name' id='$html_id' class='$html_class' rows='$html_rows' cols='$html_cols'>";
            $retour .= str_replace('\"', '&quote;', $object->getAttribute($attribute));
            if ($object -> getAttribute($attribute)=="") {
                $retour .= $map_html;
            }
            $retour .= "</textarea>";
        }

        return $retour;
    }

    public static function inputAutocompleteText($object, $attribute, $params = array())
    {
        $class_name = String::to_Case(get_class($object));
        $errors = $object->getErrors();
        $differences = $object->compareVersion();
        $title = '';

        if ((isset($params['readonly']) && $params['readonly']) || !$object->isChampModifiable('id_'.$attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }

        $maxlength = (isset($params['maxlength']) && $params['maxlength']) ? $params['maxlength'] : '';

        $html_id = (isset($params['id'])) ? $params['id'] : $class_name.'_id_'.$attribute;

        $html_name = (isset($params['name'])) ? $params['name'] : $class_name.'_'.$attribute;

        $html_id_span = (isset($params['id_span'])) ? $params['id_span'] : 'span_'.$html_id;

        $libelle = (isset($params['libelle'])) ? $params['libelle'] : 'libelle';

        $suppression = (isset($params['suppression'])) ? $params['suppression'] : false;

        $html_readonly = '';
        if (isset($params['class'])) {
            $html_class = $params['class'];
            if ($html_class == 'disabled') {
                $html_readonly = 'readonly="readonly"';
            }
        } else {
            $html_class = '';
        }

        if (isset($params["change"]) && $params["change"]) {
            $change = $params["change"];
        } else {
            $change = "";
        }
        //récupération de la valeur à afficher avant l'autocomplétion
        $valeur_object = $object->getAttribute($attribute);
// debug::output($object);
// debug::output($attribute);
// debug::output($valeur_object);
// debug::output($libelle);
// debug::output($valeur_object->getAttribute($libelle), true);
        $valeur = $valeur_object->getAttribute($libelle);
        unset($valeur_object);

        $valeur_formatee = $valeur;
        //formattage optionnel de la valeur, $params['formattage'] doit etre une fonction de unite helper
        if (isset($params['formattage'])) {
            $valeur_formatee = UniteHelper::$params['formattage']($valeur);
        }

        $params['id'] = $html_id.'_id';
        if ($no_edit) {
            // Création du champ
            if ($html_class=="calendrier") {
                $html_class = "";
            }
            if (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            }

            $retour = "<span title='$title' class='$html_class' id='$html_id_span'>".$valeur_formatee."</span>";
            $retour .= FormHelper::inputHidden($object, 'id_'.$attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            if (isset( $errors[$attribute] )) {
                $html_class .= " erreur";
            } elseif (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            } else {
                $html_class .= " autocomplete";
            }

            // Création du champ
            $retour = '<input title="'.$title.'" '.$html_readonly.'  type="text" name="'.$html_name.'_'.$libelle.'" value="';
            $retour .= DisplayHelper::convertCaracteresSpeciaux($valeur);
            $retour .= '" id="'.$html_id.'"';
            if ($html_class != '') $retour .= ' class="'.$html_class.'"';
            if ($maxlength != '') $retour .= ' maxlength="'.$maxlength.'"';
            if ($change != '') $retour .= ' onchange="'.$change.'"';
            $retour .= ' autocomplete="off" />'."\n";
            if ($suppression) {
            	$cache = '';
            	if (!$valeur) {
            	    $cache = 'display: none; ';
            	}
            	$retour .= '<img src="'.Constantes::getSrcImageSuppressionAutocomplete().'" alt="X" title="Retirer la valeur" class="img_action" style="'.$cache.'cursor: pointer; position: absolute;" />';
            }
            $retour .= FormHelper::inputHidden($object, 'id_'.$attribute, $params);

        }

        return $retour;
    }

    public static function inputText3Points($object, $id_attribute, $libelle_attribute, $params = array())
    {
        $class_name = String::to_Case(get_class($object));
        $errors = $object->getErrors();
        $differences = $object->compareVersion();
        $title = '';

        if ((isset($params['readonly']) && $params['readonly']) || !$object->isChampModifiable($id_attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }

        $maxlength = (isset($params['maxlength']) && $params['maxlength']) ? $params['maxlength'] : '';

        //$html_id = (isset($params['id'])) ? $params['id'] : $attribute;
        $html_id = (isset($params['id_affichage'])) ? $params['id_affichage'] : $class_name.'_'.$libelle_attribute;

        //$html_name = (isset($params['name'])) ? $params['name'] : $attribute;

        $html_id_span = (isset($params['id_span'])) ? $params['id_span'] : 'span_'.$html_id;

        $libelle = (isset($params['libelle'])) ? $params['libelle'] : 'libelle';

        $action = (isset($params['action'])) ? $params['action'] : 'choix_contact';

        $html_class =  (isset($params['class'])) ? $params['class'] : '';

        $html_readonly = 'readonly="readonly" ';

        //récupération de la valeur à afficher avant l'autocomplétion
        /*$valeur_object = $object->getAttribute($attribute);
        $valeur = $valeur_object->getAttribute($libelle);*/
        $id = $object->getAttribute($id_attribute);
        $valeur = $object->getAttribute($libelle_attribute);
        unset($valeur_object);

        $valeur_formatee = $valeur;
        //formattage optionnel de la valeur, $params['formattage'] doit etre une fonction de unite helper
        if (isset($params['formattage'])) {
            $valeur_formatee = UniteHelper::$params['formattage']($valeur);
        }
        if ($no_edit) {
            // Création du champ
            if ($html_class=="calendrier") {
                $html_class = "";
            }
            /*if (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            }*/

            $retour = "<span title='$title' class='$html_class' id='$html_id_span'>".$valeur_formatee."</span>";
            $retour .= FormHelper::inputHidden($object, $id_attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            /*if (isset( $errors[$attribute] )) {
                $html_class .= " erreur";
            } elseif (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            }*/

            // Création du champ
            $retour = '<table class="table_edition_trois_point" id="'.$class_name.'_'.$libelle_attribute.'_table"><tr>';
            $retour .= '<td><input title="'.$title.'" '.$html_readonly.' type="text" value="';
            $retour .= DisplayHelper::convertCaracteresSpeciaux($valeur).'"';
            $retour .= ' id="'.$html_id.'"';
            if ($html_class != '') $retour .= ' class="'.$html_class.'"';
            if ($maxlength != '') $retour .= ' maxlength="'.$maxlength.'"';
            $retour .= ' size=60px';
            $retour .= ' autocomplete="off" /></td>'."\n";
            //$retour .= Boutons::trois_points("choix_".$id_attribute, $action)."\n";
            if (isset($params['onclick'])) {
                $retour .= '<td>'.Boutons::trois_points(array('onclick'=>$params['onclick']))."</td>\n";
            }
            $retour .= '</tr></table>';
            $retour .= FormHelper::inputHidden($object, $id_attribute, $params);
        }
        //debug::output($retour, true);

        return $retour;
    }

    /*
    public static function inputText3Points($object, $attribute, $params = array())
    {
        $class_name = String::to_Case(get_class($object));
        $errors = $object->getErrors();
        $differences = $object->compareVersion();
        $title = '';

        if ((isset($params['readonly']) && $params['readonly']) || !$object->isChampModifiable('id_'.$attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }

        $maxlength = (isset($params['maxlength']) && $params['maxlength']) ? $params['maxlength'] : '';

        $html_id = (isset($params['id'])) ? $params['id'] : $attribute;

        $html_name = (isset($params['name'])) ? $params['name'] : $attribute;

        $html_id_span = (isset($params['id_span'])) ? $params['id_span'] : 'span_'.$html_id;

        $libelle = (isset($params['libelle'])) ? $params['libelle'] : 'libelle';

        $action = (isset($params['action'])) ? $params['action'] : 'choix_contact';

        $html_class =  (isset($params['class'])) ? $params['class'] : '';

        $html_readonly = 'readonly="readonly" ';

        //récupération de la valeur à afficher avant l'autocomplétion
        $valeur_object = $object->getAttribute($attribute);
        $valeur = $valeur_object->getAttribute($libelle);
        unset($valeur_object);

        $valeur_formatee = $valeur;
        //formattage optionnel de la valeur, $params['formattage'] doit etre une fonction de unite helper
        if (isset($params['formattage'])) {
            $valeur_formatee = UniteHelper::$params['formattage']($valeur);
        }
        if ($no_edit) {
            // Création du champ
            if ($html_class=="calendrier") {
                $html_class = "";
            }
            if (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            }

            $retour = "<span title='$title' class='$html_class' id='$html_id_span'>".$valeur_formatee."</span>";
            $retour .= FormHelper::inputHidden($object, $attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            if (isset( $errors[$attribute] )) {
                $html_class .= " erreur";
            } elseif (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            }

            // Création du champ
            $retour = '<input title="'.$title.'" '.$html_readonly.' type="text" name="'.$html_name.'_'.$libelle.'" value="';
            $retour .= DisplayHelper::convertCaracteresSpeciaux($valeur);
            $retour .= '" id="'.$html_id.'"';
            if ($html_class != '') $retour .= ' class="'.$html_class.'"';
            if ($maxlength != '') $retour .= ' maxlength="'.$maxlength.'"';
            $retour .= ' autocomplete="off" />'."\n";
            $retour .= Boutons::trois_points("choix_id_".$attribute, $action)."\n";
            $retour .= FormHelper::inputHidden($object, 'id_'.$attribute, $params);
        }
        //debug::output($retour, true);

        return $retour;
    }

    */

    /*
     * Cette fonctionn est comme la précédente, mais renvoi du texte, et non pas un ID;
     */
    public static function inputText3PointsTexte($object, $attribute, $params = array())
    {
        $class_name = String::to_Case(get_class($object));
        $errors = $object->getErrors();
        $differences = $object->compareVersion();
        $title = '';

        if ((isset($params['readonly']) && $params['readonly']) || !$object->isChampModifiable($attribute)) {
            $no_edit = true;
        } else {
            $no_edit = false;
        }

        $maxlength = (isset($params['maxlength']) && $params['maxlength']) ? $params['maxlength'] : '';

        $html_id = (isset($params['id'])) ? $params['id'] : $class_name."_".$attribute;

        $html_name = (isset($params['name'])) ? $params['name'] : $attribute;

        $html_id_span = (isset($params['id_span'])) ? $params['id_span'] : 'span_'.$html_id;

        $libelle = (isset($params['libelle'])) ? $params['libelle'] : 'libelle';

        $action = (isset($params['action'])) ? $params['action'] : 'choix_contact';

        $html_class =  (isset($params['class'])) ? $params['class'] : '';

        $html_readonly = ''; //readonly="readonly" ';

        //récupération de la valeur à afficher avant l'autocomplétion
        $valeur = $object->getAttribute($attribute);

        $valeur_formatee = $valeur;
        //formattage optionnel de la valeur, $params['formattage'] doit etre une fonction de unite helper
        if (isset($params['formattage'])) {
            $valeur_formatee = UniteHelper::$params['formattage']($valeur);
        }

        if ($no_edit) {
            // Création du champ
            if ($html_class=="calendrier") {
                $html_class = "";
            }
            if (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            }

            $retour = "<span title='$title' class='$html_class' id='$html_id_span'>".$valeur_formatee."</span>";
            $retour .= FormHelper::inputHidden($object, $attribute, $params);
        } else {
            // En cas d'erreurs sur ce champ
            if (isset( $errors[$attribute] )) {
                $html_class .= " erreur";
            } elseif (isset($differences[$attribute])) {
                $html_class .= " difference";
                $title = "ce champ a &eacute;t&eacute; modifi&eacute;";
            }

            // Création du champ
            $retour = '<input title="'.$title.'" '.$html_readonly.' type="text"';
            $retour .= '" value="'.DisplayHelper::convertCaracteresSpeciaux($valeur).'"';
            $retour .= '" id="'.$html_id.'"';
            $retour .= '" name="'.$class_name.'['.$attribute.']"';
            if ($html_class != '') $retour .= ' class="'.$html_class.'"';
            if ($maxlength != '') $retour .= ' maxlength="'.$maxlength.'"';
            $retour .= ' autocomplete="off" />'."\n";
            $retour .= Boutons::trois_points("choix_text_".$attribute, $action)."\n";
        }
        //debug::output($retour, true);

        return $retour;
    }


    /*
     * fonction spécifiques à la saisie de temps
     */
    public static function selectHeure($heureDebut = 9, $heureFin = 21)
    {
        $option="";
        for ($heureDebut=$heureDebut; $heureDebut<=$heureFin; $heureDebut++) {
            $option.="<option  value='".$heureDebut."'>".$heureDebut."</option>";
        }
        return $option;
    }

    public static function selectMinute()
    {
        $minutes=array("0"=>"00", "1"=>"15", "2"=>"30", "3"=>"45");
        $option="";
        foreach ($minutes as $cles => $minute) {
            $option.="<option  value='".$minute."'>".$minute."</option>";
        }
        return $option;
    }


    public static function recurence()
    {
        $recurences=array(
             "0"=>"Une fois"
            , "1"=>"Tous les jours pendant la semaine"
            , "2"=>"Une fois par semaine pendant le mois"
            , "3"=>"Toutes les semaines de l'année"
        );
        $option="";
        foreach ($recurences as $cles => $recurrence) {
            $option.="<option  value='".$cles."'>".$recurrence."</option>";
        }
        return $option;
    }


    public static function recurrenceLaboPhonetique()
    {
        $recurences=array(
             "0"=>"Une fois"
            , "1"=>"Tous les jours pendant la semaine"
            , "2"=>"Une fois par semaine pendant le mois"
            , "3"=>"Tous les jours du mois"
        );
        $option="";
        foreach ($recurences as $cles => $recurrence) {
            $option.="<option  value='".$cles."'>".$recurrence."</option>";
        }
        return $option;
    }
}
