<?php
namespace FMUP\Import;

class LiaisonBase
{

    private $liste_objet = array();

    public function __construct(Config $config)
    {
        foreach ($config->getListeField() as $field) {
            $nom_objet = \String::to_Case($field->getTableCible());
            if (array_key_exists($nom_objet, $this->liste_objet)) {
                
            }
        }
    }
}