<?php
// pour la manipulation des urls
require_once 'string.php';

// Déclaration de l'autoloader pour phpunit
spl_autoload_register('__autoload');

/**
* Chargement automatique des classes
* Les classes sytème ont priorité
* puis les classes du modèle
* puis les helpers
**/
function __autoload($class_name)
{
    $class_name = strtolower(String::to_Case($class_name));

    //liste des repertoires pouvant contenir une classe à include
    $classes_path = array(
        __DIR__,
        __DIR__ . '/helper',
        __DIR__ . '/component',
        __DIR__ . '/../application',
        __DIR__ . '/../application/model',
        __DIR__ . '/../application/model/base',
        __DIR__ . '/../../../../application',
        __DIR__ . '/../../../../application/model',
        __DIR__ . '/../../../../application/model/base',
        __DIR__ . '/../../../../application/' . APPLICATION . '/controller',
        __DIR__ . '/../../../../application/' . APPLICATION . '/controller/component',
    );

    //recherche de la classe
    $include_path = '';
    foreach ($classes_path as $class_path) {
        if (file_exists($class_path . '/' . $class_name . '.php')) {
            $include_path = $class_path . '/' . $class_name . '.php';
            break;
        }
    }

    //include de la classe
    if ($include_path) require_once $include_path;
}