<?php
spl_autoload_register(function($class) {
    $ds = DIRECTORY_SEPARATOR;
    $namespacedir = str_replace('\\', $ds, $class . '.php');
    $file = __DIR__ . $ds . $namespacedir;
    require $file;
});
