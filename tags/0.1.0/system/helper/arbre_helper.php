<?php
/*
 * fonctions permettants d'afficher l'arborescen des filiales d'une utilisateur
 */
class ArbreHelper
{
    public function displayParents($user, $niveau = 0)
    {
        $parents = $user->getParents();
        $nb_parents = count($parents);
        ob_start();

        if ($nb_parents > 0) {
            echo '<table class="parents"><tr>';
            foreach ($parents as $parent) {
                echo '<td>'.ArbreHelper::displayParents($parent, $niveau+1).'</td>';
            }
            echo "</tr>";
            if ($niveau != 0) {
                echo "<tr><td colspan='$nb_parents' class='valeur'>".Images::flecheBas()."<br /><p>".$user->getLibelleArborescence()."</p><br />".Images::flecheHaut()."</td></tr>";
            }
            echo '</table>';
        } else {
            echo '<table class="parents"><tr><td class="valeur seule"><p>'.$user->getLibelleArborescence().'</p><br />'.Images::flecheHaut().'</td></tr></table>';
        }

        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    public function displayFils($user, $niveau = 0)
    {
        $fils = $user->getFils();
        $nb_fils = count($fils);
        ob_start();

        if ($nb_fils > 0) {
            echo '<table class="fils">';
            if ($niveau != 0) {
                echo "<tr><td class='valeur' colspan='$nb_fils'>".Images::flecheBas()."<br /><p>".$user->getLibelleArborescence()."</p><br />".Images::flecheHaut()."</td></tr>";
            }
            echo '<tr>';
            foreach ($fils as $fils_unique) {
                echo '<td>'.ArbreHelper::displayFils($fils_unique, $niveau+1).'</td>';
            }
            echo '</tr></table>';
        } else {
            echo '<table class="fils"><tr><td class="valeur seule">'.Images::flecheBas().'<br /><p>'.$user->getLibelleArborescence().'</p></td></tr></table>';
        }

        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    public function display($user)
    {
        ob_start();

        echo '<table><tr><td>';
        echo ArbreHelper::displayParents($user);
        echo '</td></tr><tr><td class="valeur"><p>';
        echo $user->getLibelleArborescence();
        echo '</p></td></tr><tr><td>';
        echo ArbreHelper::displayFils($user);
        echo '</td></tr></table>';

        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }
}
