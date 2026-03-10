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
 * Data class for SVG attribute.
 */
class Attribute implements \Stringable
{
    public $name;
    public $value;
    public $encoding;
    public $units = '';

    // these properties require units to work well
    private static $require_units = [
        'baseline-shift' => 1,
        'font-size' => 1,
        'kerning' => 1,
        'letter-spacing' => 1,
        'stroke-dashoffset' => 1,
        'stroke-width' => 1,
        'word-spacing' => 1,
    ];

    public function __construct($name, $value, $encoding)
    {
        $this->name = $name;

        if (isset(self::$require_units[$name])) {
            $this->units = 'px';
        }

        $this->value = $value;
        $this->encoding = $encoding;

        if (is_numeric($value)) {
            $this->value = new Number($value, $this->units);
        }
    }

    public function __toString()
    {
        if ($this->value === null) {
            return '';
        }

        if (\is_object($this->value)) {
            return (string) $this->value;
        }

        return htmlspecialchars($this->value, \ENT_COMPAT, $this->encoding);
    }
}
