<?php

/**
 * Classe gérant l'affichage de certains éléments
 * @version 1.0
 */
class DisplayHelper
{
    /**
     * Retourne la classe associée à un numéro de ligne donnée
     * @param Integer le numéro de la ligne
     **/
    public static function getClassLigne($numero_ligne, $type = "")
    {
        $classes = array('ligne_paire' . $type, 'ligne_impaire' . $type);
        return $classes[$numero_ligne % 2];
    }

    public static function getExergueLigue($actif)
    {
        if ($actif) {
            $retour = "exergue";
        } else {
            $retour = "";
        }
        return $retour;
    }

    public static function getTitle($message, $actif)
    {
        if ($actif) {
            $retour = " title='" . $message . "' ";
        } else {
            $retour = "";
        }
        return $retour;
    }

    /**
     * Affiche un tableau de toutes les erreurs de validation d'un objet donné
     * @params {Object} L'objet pour lequel afficher les erreurs
     **/
    public static function errorsFor($object)
    {
        if ($object->getErrors()) {
            return DisplayHelper::errors($object->getErrors()) . "<br/>";
        } else {
            return '';
        }
    }

    /**
     * Affiche des erreurs à partir d'un tableau d'erreurs
     * @params {Array} Le tableau d'erreurs
     **/
    public static function errors($array)
    {
        $retour = "";
        if (count($array)) {
            $message = UniteHelper::getSingulierPluriel(count($array), "Erreur", "Erreurs", false);
            $retour .= "<div class='erreurs'><p class='erreurs_titre'>" . $message . "</p><ul>";
            foreach ($array as $erreur) {
                $retour .= "<li>$erreur</li>";
            }
            $retour .= "</ul></div>";
        }
        return $retour;
    }

    public static function convertCaracteresSpeciaux($chaine)
    {
        $chaine = str_replace('"', '&#034;', $chaine);
        $chaine = str_replace("'", '&#039;', $chaine);
        $chaine = str_replace('à', '&#224;', $chaine);
        $chaine = str_replace('è', '&#232;', $chaine);
        $chaine = str_replace('ê', '&#234;', $chaine);
        $chaine = str_replace('é', '&#233;', $chaine);
        $chaine = str_replace('ô', '&#244;', $chaine);
        return $chaine;
    }

    public static function caracteresSpeciauxToNormal($chaine)
    {
        $chaine = str_replace('&#034;', '"', $chaine);
        $chaine = str_replace('&#039;', "'", $chaine);
        $chaine = str_replace('&#224;', 'à', $chaine);
        $chaine = str_replace('&agrave;', 'à', $chaine);
        $chaine = str_replace('&#232;', 'è', $chaine);
        $chaine = str_replace('&egrave;', 'è', $chaine);
        $chaine = str_replace('&#234;', 'ê', $chaine);
        $chaine = str_replace('&ecirc;', 'ê', $chaine);
        $chaine = str_replace('&#233;', 'é', $chaine);
        $chaine = str_replace('&eacute;', 'é', $chaine);
        $chaine = str_replace('&#244;', 'ô', $chaine);
        $chaine = str_replace('&ocirc;', 'ô', $chaine);
        $chaine = str_replace('&ouml;', 'ö', $chaine);
        $chaine = str_replace('&icirc;', 'î', $chaine);
        $chaine = str_replace('&euml;', 'ë', $chaine);
        $chaine = str_replace('&auml;', 'ä', $chaine);
        $chaine = str_replace('&atilde;', 'ã', $chaine);
        $chaine = str_replace('&ccidil;', 'ç', $chaine);
        $chaine = str_replace('&euro;', '€', $chaine);
        return $chaine;
    }

    /* ****************************
     * true/false affiche oui/non *
      **************************** */
    public static function getLibelleboolean($value)
    {
        if ($value == 1) {
            return "oui";
        } else {
            return "non";
        }
    }

    /**
     * Convertit un texte en syntaxe Wiki-like
     * en html
     *
     * **gras**
     * //italique//
     * __souligné__
     * %% nouvelle ligne
     *
     * @params {String} $text le texte à convertir
     * @return le html correspondant
     **/
    public function wikiToHtml($text)
    {
        $return = $text;
        // le gras
        $return = preg_replace('/\*\*(.*?)\*\*/ms', '<b>$1</b>', $return);
        // l'italique
        $return = preg_replace('/\/\/(.*?)\/\//ms', '<i>$1</i>', $return);
        // le souligné
        $return = preg_replace('/__(.*?)__/ms', '<u>$1</u>', $return);
        // les retours à la ligne
        $return = preg_replace('/%%/ms', '<br />', $return);
        // les titres
        $return = preg_replace('/!!(.*?)!!/ms', '<h3>$1</h3>', $return);

        return $return;
    }

    /*
     * Nettoie le texte de toute les balises html
     */
    public static function htmlToText($text)
    {
        $pattern = '/<[^<>]*>/';
        return preg_replace($pattern, '', $text);
    }

    /**
     *
     */
    public static function formatterCommentaire($text)
    {
        $order = array("\r\n", "\n", "\r");
        $replace = '<br />';
        return str_replace($order, $replace, $text);
    }

    /**
     * Retourne les @taille premières lettre du texte donnée en paramètre
     * en ne coupant pas un mot
     **/
    public static function getTexteCoupe($valeur, $taille, $coupe_violement = false, $balise_fermante = "")
    {
        //$phrase_coupee = split('\|', wordwrap($valeur, $taille, '|', $coupe_violement));
        $phrase_coupee = explode('|', wordwrap($valeur, $taille, '|', $coupe_violement));
        $valeur_formate = $phrase_coupee[0];

        //if (strlen($valeur) > $taille + 3) {
        if (strlen($valeur) > $taille) {
            if (!$coupe_violement) $valeur_formate .= ' ';
            $valeur_formate .= '...';
            $valeur_formate .= $balise_fermante;     // dans le cas d'une coupure de commentaire avec un <p> dedans.
        }
        return $valeur_formate;
    }

    /*
     * si le texte est trop long, alors on ne l'affiche pas
     * @param  TXT texte à tester et afficher au besoi
     * @param  INT taille maximum authorisée
     * @return TXT texte à afficher
     */
    public static function getTexteSiPasTropLong($valeur, $taille)
    {
        if (strlen($valeur) > $taille) {
            return "<i>trop long à afficher</i>...";
        } else {
            return $valeur;
        }
    }

    public static function errorsClass($tableau_erreurs, $libelle)
    {
        $retour = '';
        if (isset($tableau_erreurs[$libelle])) {
            $retour = 'erreur';
        }
        return $retour;
    }
}
