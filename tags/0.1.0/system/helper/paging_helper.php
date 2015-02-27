<?php
class PagingHelper
{
    /**
     * Retourne le nombre de produits par page
     * @return {String}
     */
    public static function getNbProduits($where_recherche = null, $jeune_createur = null)
    {
        if (isset($_POST['nb_produits']) && Model::isInteger($_POST['nb_produits'])) {
            $_SESSION['nb_produits'] = $_POST['nb_produits'];
            return $_POST['nb_produits'];
        } elseif (isset($_SESSION['nb_produits']) && Model::isInteger($_SESSION['nb_produits'])) {
            return $_SESSION['nb_produits'];
        } else {
            return 6;
        }
    }
    /**
     * Retourne la page courante
     * @return {String}
     */
    public static function getPage()
    {
        if (isset($_GET['page'])) {
            return $_GET['page'];
        } else {
            return 1;
        }
    }

    public static function getLimit($nb_par_page, $page)
    {
        if ($page==1) {
            return 0;
        } else {
            return $nb_par_page * ($page - 1);
        }
    }
    public static function getNbPages ($nb_elements)
    {
        if ($nb_elements > 0) {
            $nb_pages = ceil($nb_elements/PagingHelper::getNbProduits());
            if ($nb_pages == 1) {
                return $nb_pages.' Page ';
            } else {
                return $nb_pages.' Pages';
            }
        } else {
            return null;
        }
    }
    /**
     * Retourne les numÃ©ros de page (paging)
     * @param {String} $nb_elements le nombre d'Ã©lÃ©ments au total
     */
    public static function getNavigation($nb_elements)
    {
        if ($nb_elements > 0) {
            $offset = 2; // nombre minimum de pages Ã  afficher
            $nb_pages = ceil($nb_elements/PagingHelper::getNbProduits());
            if ($nb_pages > 1) {
                $retour = "";
                $page_courante = PagingHelper::getPage();

                if ($page_courante != 1) {
                    $retour .= PagingHelper::getUrlFromPage($page_courante-1, "Précédente&nbsp;");
                }
                if ($page_courante > $offset+1) {
                    //$retour .= "â€¦ ";
                }
                for ($page = $page_courante - $offset; $page <= $page_courante + $offset; $page++) {
                    if ($page < 1 || $page > $nb_pages) {
                        continue;
                    }
                    if ($page == $page_courante) {
                        $retour .= "$page ";
                    } else {
                        $retour .= PagingHelper::getUrlFromPage($page, $page)." ";
                    }
                }
                if ($page_courante < $nb_pages-$offset) {
                    //$retour .= "â€¦ ";
                }
                if ($page_courante != $nb_pages) {
                    $retour .= PagingHelper::getUrlFromPage($page_courante+1, "Suivante");
                }
                return $retour;
            }
        } else {
            return "Aucune référence trouvée !";
        }
    }
    /**
     * Retourne l'url associÃ© Ã  un page
     *
     * @param {Integer} Un numÃ©ro de page
     */
    public static function getUrlFromPage($page, $titre)
    {
        $url = preg_replace('/[&\?]page=[0-9]+/', '', $_SERVER['REQUEST_URI']);
        if (!strpos($url, '?')) {
            return "<a href='$url?page=$page' class='lien_index'>$titre</a>";
        } else {
            return "<a href='$url&amp;page=$page' class='lien_index'>$titre</a>";
        }
    }
    /**
     * Retourne un tableau des diffÃ©rents numÃ©ros de pages disponibles
     */
    public static function getArrayPaging()
    {
        return array(
            '' => 'Produits par page',
            '4' => '4 produits par page',
            '8' => '8 produits par page',
            '16' => '16 produits par page'
        );
    }
}
