<?php
	define('APPLICATION', '');
	define('BASE_PATH', implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..', '..', '..')));
	if (file_exists(BASE_PATH."/vendor/autoload.php")) {
		require_once(BASE_PATH . "/vendor/autoload.php");
	}
?>
<html>
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

<a href="creer_modele.php">Modèle</a>
<br/>
<a href="creer_controleur.php">Controleur + vue</a>
<br/>

</body>
</html>
