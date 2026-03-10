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
 * For more information, please contact <graham@goat1000.com>.
 */

namespace Goat1000\SVGGraph;

/**
 * Class for sorting array by field.
 */
class FieldSort
{
    private $key;
    private $reverse = false;

    public function __construct($key, $reverse = false)
    {
        $this->key = $key;
        $this->reverse = $reverse;
    }

    /**
     * Sorts the array based on value of key field.
     *
     * @param mixed $data
     */
    public function sort(&$data): void
    {
        $key = $this->key;
        $get_val = static function ($a, $key) {
            return !isset($a[$key]) || $a[$key] === null ? \PHP_INT_MIN : $a[$key];
        };
        $bigger = static function ($a, $b, $key) use ($get_val) {
            $va = $get_val($a, $key);
            $vb = $get_val($b, $key);

            if ($va === $vb) {
                return 0;
            }

            return $va > $vb ? 1 : -1;
        };
        $smaller = static function ($a, $b, $key) use ($get_val) {
            $va = $get_val($a, $key);
            $vb = $get_val($b, $key);

            if ($va === $vb) {
                return 0;
            }

            return $va < $vb ? 1 : -1;
        };
        $fn = $this->reverse ? $smaller : $bigger;
        usort($data, static function ($a, $b) use ($key, $fn) {
            return $fn($a, $b, $key);
        });
    }
}
