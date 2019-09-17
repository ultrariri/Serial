<?php
declare(strict_types=1);

// PHP version
if (PHP_VERSION_ID < 70100) {
    die(sprintf('PHP %s.%s < 7.1', PHP_MAJOR_VERSION, PHP_MINOR_VERSION));
}

define('SRC_ROOT', realpath(__DIR__.'/src'));

// Autoload
spl_autoload_register(function($class) {
    $classFile = SRC_ROOT.'/'.preg_replace('/\\\/', DIRECTORY_SEPARATOR, $class).'.php';

    if (($classFile = realpath($classFile)) !== false) {
        return require_once $classFile;
    }
;
    die("{$class} not found from ".get_called_class().PHP_EOL);
});
