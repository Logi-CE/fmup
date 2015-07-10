<?php
/**
 * Clase permettant de créer un bouton générique
 * @author afalaise
 * @version 6.0
 */
class Bouton
{
    /**
     * Fonction de création du bouton
     * @param $params : Paramètres du boutons avec comme possibilité :
     *  - libelle : Texte écrit sur le bouton
     *  - image : [OPT] Image décorative sur le bouton
     *  - id : [OPT] ID du bouton
     *  - title : [OPT] Texte title du bouton
     *  - script : [OPT] Javascript sur le clic
     *  - submit : [OPT] Indique si le bouton doit poster le formulaire
     *  @return string : Code HTML du bouton
     */
    public static function construire ($params = array())
    {
        $script = "";
        $image = "";
        $title = "";
        $type = 'button';
        $id = 'btn_'.str_replace(' ', '_', strtolower($params['libelle']));

        if (isset($params['id'])) {
            $id = $params['id'];
        }
        if (isset($params['script'])) {
            $script = $params['script'];
        }
        if (isset($params['title'])) {
            $title = LangueHelper::display($params['title']);
        }
        if (isset($params['image'])) {
            $image = '<img alt="'.$title.'" src="/images/boutons/'.$params['image'].'" />';
        } elseif (isset($params['font-image'])) {
            $image = '<i class="fa fa-'.$params['font-image'].'"></i>';
        }
        if (isset($params['submit'])) {
            $type = 'submit';
        }

        $html = '<button type="'.$type.'" id="'.$id.'" title="'.$title.'" onclick="'.$script.'">';
        $html .= $image.'<span>'.LangueHelper::display($params['libelle']).'</span>';
        $html .= '</button>';

        return $html;
    }
    
    // Ajouter : plus, effacer : eraser, reparer : wrench, bloquer : unlock, voir : eye, chercher : search, rafraichir; refresh,
    // envoyer : envelope, historique : history, telecharger : download/upload, paramètrer : cogs, imprimer : print
    

    public static function exporter()
    {
        return self::construire(array('id' => 'btn_exporter', 'font-image' => /*'xls.png'*/'file', 'libelle' => 'Exporter'));
    }
    public static function enregistrer()
    {
        return self::construire(array('id' => 'btn_enregistrer', 'font-image' => /*'disk.png'*/'save', 'libelle' => 'Enregistrer'));
    }
    public static function editer()
    {
        return self::construire(array('id' => 'btn_editer', 'font-image' => /*'pencil.png'*/'pencil', 'libelle' => 'Editer'));
    }
    public static function supprimer()
    {
        return self::construire(array('id' => 'btn_supprimer', 'font-image' => /*'cross.png'*/'times', 'libelle' => 'Supprimer'));
    }
    public static function annuler()
    {
        return self::construire(array('id' => 'btn_annuler', 'font-image' => /*'cross.png'*/'ban', 'libelle' => 'Annuler'));
    }
    public static function retourListe()
    {
        return self::construire(array('id' => 'btn_retour_liste', 'font-image' => /*'backward_blue.png'*/'undo', 'libelle' => 'Retour à la liste'));
    }
    public static function creer()
    {
        return self::construire(array('id' => 'btn_creer', 'font-image' => /*'add.png'*/'check', 'libelle' => 'Créer'));
    }
}
