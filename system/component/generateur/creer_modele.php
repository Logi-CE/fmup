<?php
	define('APPLICATION', '');
	define('BASE_PATH', implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..', '..', '..')));
	if (file_exists(BASE_PATH."/vendor/autoload.php")) {
		require_once(BASE_PATH . "/vendor/autoload.php");
	}
	require_once(__DIR__."/generateur.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">	 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <style>
        td{
        	font-family:Arial;
        	font-size:12px;
        	border-color:#000000;
        }
        </style>
    </head>
<body>

<div style="position:fixed;top:0;left:0;bottom:0;width:300px;overflow:auto;">
	<form target="maframe" action="./gen_model.php" method="post">
    	<button>Generer</button>
    	<button type="button" onclick="location.href='index.php';">Retour</button>
    	<input type="checkbox" id="nettoyage" name="nettoyage" value="1" /><label for="nettoyage">Nettoyage</label>
    	<table border="1" width="100%" style="border-collapse:collapse;border-color:#000000;">
    		<tr style="font-weight:bold;background-color:#ddddff;color:#005500;">
    			<th>TABLE</th>
    			<th>Gen</th>
    			<th>Log</th>
    			<!--th>&nbsp;</th-->
    		</tr>
        	<?php
        	$generateur = new Generateur();
        	$tables = $generateur->getTables();
        	foreach ($tables['tableau_table'] as $table) : ?>
        		<tr>
        			<td><?php echo $table; ?></td>
        			<td>
        				<?php echo (is_file(BASE_PATH.'/application/model/base/base_'.String::to_Case($table).'.php')) ? 'Oui' : 'Non'; ?>
        			</td>
        			<td>
        				<input type="hidden" name="generation_massive[<?php echo $table; ?>]" value="0" />
        				<input type="checkbox" name="generation_massive[<?php echo $table; ?>]" <?php if (in_array($table, $tables['tableau_log'])) echo 'checked="checked"'; ?> value="1" />
        			</td>
        			<!--td align="center">
        				<a target="maframe" href="./gen_model.php?table_name=<?php echo $table; ?>&class_name=<?php echo String::toCamlCase($table); ?>">GO</a>
        			</td-->
        		</tr>
        	<?php endforeach; ?>
    	</table>
    </form>
</div>

<div style="position:fixed;top:0;left:301px;bottom:0;right:0;border:solid 1px #000000;">
	<iframe style="width:100%;height:100%;" frameborder="no" id="maframe" name="maframe"></iframe>
</div>

</body>
</html>
