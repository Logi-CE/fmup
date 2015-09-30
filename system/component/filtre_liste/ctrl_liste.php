<?php
class CtrlListe extends \FMUP\Controller
{
    public function getActionMethod($action)
    {
        return $action;
    }

    public function lister()
    {
        ini_set('max_execution_time', 40);

        if (isset($_REQUEST['unique_id'], $_SESSION['filtre_liste'][$_REQUEST['unique_id']])) {

            $filtre = $_SESSION['filtre_liste'][$_REQUEST['unique_id']];
            if (isset($_REQUEST['xls'])) {
                header("Content-Type: application/vnd.xls; name=\"export.xls\"");
                header("Content-Disposition: inline; filename=\"export.xls\";");
                echo $filtre->lister(true);
            } else {
                echo $filtre->lister();
            }

        } elseif (!FormHelper::checkToken()) {
            self::redirect('');
        } else {
            echo '<script>';
            echo 'location.reload();';
            echo '</script>';
        }
        
    }
}
