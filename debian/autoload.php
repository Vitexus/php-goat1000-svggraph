<?php
// autoload.php for Goat1000\SVGGraph

spl_autoload_register(function ($class) {
    $prefix = 'Goat1000\\SVGGraph\\';
    if (strpos($class, $prefix) === 0) {
        $baseDir = '/usr/share/php/SvgGraph';
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});
