<?php
if (!defined('APPLICATION')) {
    define('APPLICATION', 'application');
}
if (!defined('BASE_PATH')) {
    define('BASE_PATH', implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..')));
}

// pour la manipulation des urls
require_once 'string.php';

// Déclaration de l'autoloader pour phpunit
if (!function_exists('fmu_autoload')) {
    /**
     * Chargement automatique des classes
     * Les classes sytème ont priorité
     * puis les classes du modèle
     * puis les helpers
     */
    function fmu_autoload($class_name)
    {
        $class_name = strtolower(String::toSnakeCase($class_name));

        //liste des repertoires pouvant contenir une classe à include
        $classes_path = array(
            __DIR__ . '/../../../../application',
            __DIR__ . '/../../../../application/model',
            __DIR__ . '/../../../../application/model/base',
            __DIR__ . '/../../../../application/' . APPLICATION . '/controller',
            __DIR__ . '/../../../../application/' . APPLICATION . '/controller/component',
            __DIR__,
            __DIR__ . '/helper',
            __DIR__ . '/component',
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
        if ($include_path) {
            require $include_path;
        }
    }
}

if (function_exists('spl_autoload_register')) {
    spl_autoload_register('fmu_autoload');
} elseif (!function_exists('__autoload')) {
    function __autoload($class)
    {
        fmu_autoload($class);
    }
}
