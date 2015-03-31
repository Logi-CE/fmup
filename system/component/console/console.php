<?php if (Config::consoleActive()) : ?>
    <pre id="debug-zone-button">
        <!-- par défaut la zone est cachée -->
        <button id="bouton_allumer" title="Allumer la console" onclick="Console.allumer();" <?php if (Console::$statut_console != 'eteinte') echo 'style="display:none"'; ?>></button>
        <button id="bouton_eteindre" title="Eteindre la console" onclick="Console.eteindre();" <?php if (Console::$statut_console == 'eteinte') echo 'style="display:none"'; ?>></button>
        <span id="bloc_boutons" <?php if (Console::$statut_console == 'eteinte') echo 'style="display:none"'; ?>>
            <button id="bouton_cacher" title="Fermer la console" onclick="Console.cacher();" <?php if (in_array(Console::$statut_console, array('veille', 'eteinte'))) echo 'style="display:none"'; ?>></button>
            <button id="bouton_ouvrir" title="Ouvrir la console" onclick="Console.ouvrir();" <?php if (!in_array(Console::$statut_console, array('veille', 'eteinte'))) echo 'style="display:none"'; ?>></button>
            <button id="bouton_agrandir" title="Agrandir la console" onclick="Console.agrandir();"></button>
            <button id="bouton_vider" title="Vider la console" onclick="Console.vider();"></button>
            <button id="bouton_rafraichir" title="Rafraichir la console" onclick="Console.rafraichir();"></button>
            <button id="bouton_descendre" title="Aller à la fin" onclick="Console.descendre();"></button>
            <button id="bouton_detacher" title="Détacher la console" onclick="Console.detacher();"></button>
            <?php foreach (Console::$options_console as $option => $titre) : ?>
                <button id="bouton_activer_<?php echo $option; ?>" title="Activer <?php echo $titre; ?>" onclick="Console.activer(1, '<?php echo $option; ?>');" <?php if (isset($_SESSION['option_console_'.$option])) echo 'style="display:none"'; ?>></button>
                <button id="bouton_desactiver_<?php echo $option; ?>" title="Désactiver <?php echo $titre; ?>" onclick="Console.activer(0, '<?php echo $option; ?>');" <?php if (!isset($_SESSION['option_console_'.$option])) echo 'style="display:none"'; ?>></button>
            <?php endforeach; ?>
        </span>
    </pre>
    <pre id="debug-zone" <?php if (in_array(Console::$statut_console, array('eteinte', 'veille'))) echo 'style="display:none"'; ?>>
        <span id="debug-contenu"><?php Console::afficher(); ?></span>
    </pre>
<?php endif;