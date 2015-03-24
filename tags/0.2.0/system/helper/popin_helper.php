<?php
class PopinHelper
{
    public static function popinHaut()
    {
        echo '<div id="lbOverlay" style="display:none; visibility: visible; opacity: 0;">&nbsp;</div>';
        echo '<div style="clear:both"></div>';
        echo '<div id="popup" style="display:none;">'."\n";
        echo '<div id="popup_haut">'."\n";
        echo '<img src="/images/popin/fermer-trans.png" alt="Close" title="Close" class="popup_close" />'."\n";
        echo '</div>'."\n";
        echo '<div id="popup_contenu">'."\n";
    }

    public static function popinBas()
    {
        echo '</div>'."\n";
        echo '<div id="popup_bas"></div>'."\n";
        echo '</div>';
        echo '<div style="clear:both; height:0px; "> </div>';
    }
}
