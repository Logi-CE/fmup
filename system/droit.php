<?php
class Droit
{
    protected $lecture;
    protected $ecriture;

    public function __construct($administrateur, $menu)
    {
        $this->lecture = false;
        $this->ecriture = false;
        $menu = Menu::findOneByControlleur($menu);
        $droit = LienAdministrateurMenu::findOneByIds($administrateur, $menu);
        if (isset ($droit)) {
             $lecture = $droit -> getLecture();
        }
        if (isset ($lecture)) {
            if ($lecture == 1) {
                $this->lecture = true;
            }
        }
        if (isset ($ecriture)) {
            if ($ecriture == 1) {
                $this->ecriture = true;
            }
        }
    }

    public function testAdmin($menu)
    {
        $administrateur = Administrateur::findOne($_SESSION['admin_id']);
        $menu = Menu::findOneByEspace($menu);
        $droit = LienAdministrateurMenu::findOneByIds($administrateur -> getId(), $menu);
        if (isset ($droit)) {
            $lecture = $droit -> getLecture();
            $ecriture = $droit -> getOperationEcriture();
        }
        if (isset ($lecture)) {
            if ($lecture == 1) {
                return true;
            }
        }
        return false;
    }

    public function getLecture()
    {
        return $this -> lecture;
    }

    public function getOperationEcriture()
    {
        return $this -> ecriture;
    }

    public function getOperationEcritureInput()
    {
        return !$this -> getOperationEcriture();
    }
}
