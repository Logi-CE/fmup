<?php
namespace FMUP\Import\Iterator;

use FMUP\Import\Config;

/**
 * Permet de repérer les éventuels doublons présents dans le fichier
 *
 * @author csanz
 *
 */
class DoublonIterator extends \IteratorIterator
{

    /**
     *
     * @var Array
     */
    private $liste_cle = array();

    /**
     *
     * @return array associate key (int) to string
     */
    public function getListeCle()
    {
        return $this->liste_cle;
    }

    /**
     * (non-PHPdoc)
     *
     * @see IteratorIterator::next()
     */
    public function next()
    {
        parent::next();
        $current = $this->current();
        if ($current) {
            $this->verificationDoublon($current);
        }
    }

    /**
     * Vérifie l'unicité des objets de la ligne
     *
     * @param Config $current
     * @return string
     */
    public function verificationDoublon(Config $current)
    {
        $str = "";
        // on concatène tous les champs obligatoires
        foreach ($current->getListeConfigObjet() as $config_objet) {
            foreach ($config_objet->getListeIndexChamp() as $index) {
                $str .= $current->getField($index)->getValue() . ";";
            }
        }
        $cle_unicite = sha1($str);
        // on cherche le sha1 dans le tableau
        $key = array_search($cle_unicite, $this->liste_cle);
        if (!$key) {
            // si on ne le trouve pas, on l'ajoute dans le tableau
            $this->liste_cle[$this->getInnerIterator()->key()] = $cle_unicite;
            $current->setDoublonLigne(false);
        } else {
            // sinon il y a un doublon que l'on entre dans la config
            $current->setDoublonLigne($key);
        }
        return $str;
    }
}
