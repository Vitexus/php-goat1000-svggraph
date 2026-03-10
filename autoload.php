<?php

declare(strict_types=1);

/**
 * This file is part of the SVGGraph package
 *
 * https://www.goat1000.com/svggraph.php
 *
 * (c) Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PSR-4 autoloader.
 *
 * For more information, please contact <graham@goat1000.com>
 */
spl_autoload_register(function ($class): void {
    // check class starts with namespace
    $ns = 'Goat1000\\SVGGraph\\';

    if (!str_starts_with($class, $ns)) {
        return;
    }

    $local_class = substr($class, \strlen($ns));
    $filename = __DIR__.\DIRECTORY_SEPARATOR.
      str_replace('\\', \DIRECTORY_SEPARATOR, $local_class).'.php';

    // if the file exists, load it
    if (file_exists($filename)) {
        require $filename;
    }

    // not found, fail silently
});
