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
 * Class for modifying a colour.
 */
class ColourFilter implements \Stringable
{
    private $colour;
    private $filters;

    public function __construct($colour, $filters)
    {
        // these are special cases, can't be processed
        if ($colour === 'transparent' || $colour === 'none') {
            throw new \InvalidArgumentException('Unable to filter colour ['.$colour.']');
        }

        $this->colour = new RGBColour($colour);

        $filters = explode('/', $filters);

        foreach ($filters as $f) {
            $filter = $f;
            $args = [];
            $fpos = strpos($f, '(');
            $epos = strpos($f, ')');

            if ($fpos > 0) {
                $filter = substr($f, 0, $fpos);
                $a = '';

                if ($epos > $fpos) {
                    $a = substr($f, $fpos + 1, $epos - $fpos - 1);
                }

                if ($a !== '') {
                    $args = preg_split('/[\s,]+/', $a);
                }
            }

            if (method_exists($this, $filter)) {
                \call_user_func_array([$this, $filter], $args);
            }
        }
    }

    public function __toString()
    {
        return $this->colour->getHex();
    }

    /**
     * Increase or decrease brightness.
     *
     * @param mixed $amount
     */
    public function brightness($amount = '1.2'): void
    {
        [$operator, $value] = $this->expression($amount);

        if ($value === null) {
            throw new \InvalidArgumentException('Invalid brightness ['.$amount.']');
        }

        [$h, $s, $l] = $this->colour->getHSL();

        $l = min(1.0, max(0.0, $operator === '+' ? $l + $value : $l * $value));
        $this->colour->setHSL($h, $s, $l);
    }

    /**
     * Increase or decrease saturation.
     *
     * @param mixed $amount
     */
    public function saturation($amount = '0.0'): void
    {
        [$operator, $value] = $this->expression($amount);

        if ($value === null) {
            throw new \InvalidArgumentException('Invalid saturation ['.$amount.']');
        }

        [$h, $s, $l] = $this->colour->getHSL();

        $s = min(1.0, max(0.0, $operator === '+' ? $s + $value : $s * $value));
        $this->colour->setHSL($h, $s, $l);
    }

    /**
     * Modify the hue.
     *
     * @param mixed $amount
     */
    public function hue($amount = '60'): void
    {
        if (!is_numeric($amount)) {
            throw new \InvalidArgumentException('Invalid hue ['.$amount.']');
        }

        [$h, $s, $l] = $this->colour->getHSL();
        $h += $amount;
        $this->colour->setHSL($h, $s, $l);
    }

    /**
     * Returns the expression to be applied.
     *
     * @param mixed $a
     */
    public static function expression($a)
    {
        $operator = '*';
        $value = null;

        if ($a[0] === '+' || $a[0] === '-') {
            $operator = '+';
        }

        $p = strpos($a, '%');

        if ($p > 0) {
            $a = substr($a, 0, $p);
        }

        if (is_numeric($a)) {
            $value = $p > 0 ? $a / 100 : $a;
        }

        return [$operator, $value];
    }
}
