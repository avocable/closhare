<?php

spl_autoload_register(function ($className) {
    $namespace = 'Nedo\\Cache';

    if (strpos($className, $namespace) === 0) {
        $className = str_replace($namespace, '', $className);
        $fileName = __DIR__ . '/Cache/' . str_replace('\\', '/', $className) . '.php';
        if (file_exists($fileName)) {
            require($fileName);
        }
    }
});
/**
* Registers an autoload for all the classes in Nedo\Image
*/
spl_autoload_register(function ($className) {
    $namespace = 'Nedo\\Image';

    if (strpos($className, $namespace) === 0) {
        $className = str_replace($namespace, '', $className);
        $fileName = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
        if (file_exists($fileName)) {
            require($fileName);
        }
    }
});
