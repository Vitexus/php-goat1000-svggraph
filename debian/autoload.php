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

spl_autoload_register(function ($class): void {
    $prefix = 'Goat1000\\SVGGraph\\';

    if (str_starts_with($class, $prefix)) {
        $baseDir = '/usr/share/php/SvgGraph';
        $relativeClass = substr($class, \strlen($prefix));
        $file = $baseDir.str_replace('\\', '/', $relativeClass).'.php';

        if (file_exists($file)) {
            require $file;
        }
    }
});
