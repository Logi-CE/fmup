<?php
if (!defined('APPLICATION')) {
    define('APPLICATION', 'application');
}

// Déclaration de l'autoloader pour phpunit
if (!function_exists('fmuAutoload')) {
    /**
     * Chargement automatique des classes
     * Les classes sytème ont priorité
     * puis les classes du modèle
     * puis les helpers
     */
    function fmuAutoload($className)
    {
        $className = strtolower(\FMUP\StringHandling::toSnakeCase($className));

        //liste des repertoires pouvant contenir une classe à include
        $classesPath = array(
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
        $includePath = '';
        foreach ($classesPath as $classPath) {
            if (file_exists($classPath . '/' . $className . '.php')) {
                $includePath = $classPath . '/' . $className . '.php';
                break;
            }
        }

        //include de la classe
        if ($includePath) {
            require $includePath;
        }
    }
}
spl_autoload_register('fmuAutoload');
