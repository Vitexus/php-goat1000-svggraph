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
 * Abstract class implements common methods.
 */
abstract class ColourRange implements \ArrayAccess
{
    protected $count = 2;

    /**
     * Sets up the length of the range.
     *
     * @param mixed $count
     */
    public function setup($count): void
    {
        $this->count = $count;
    }

    /**
     * always true, because it wraps around.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new \Exception('Unexpected offsetSet');
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new \Exception('Unexpected offsetUnset');
    }

    /**
     * Clamps a value to range $min-$max.
     *
     * @param mixed $val
     * @param mixed $min
     * @param mixed $max
     */
    protected static function clamp($val, $min, $max)
    {
        return min($max, max($min, $val));
    }
}
