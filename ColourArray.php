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

class ColourArray implements \ArrayAccess
{
    private $colours;
    private $count;

    public function __construct($colours)
    {
        $this->colours = $colours;
        $this->count = \count($colours);
    }

    /**
     * Not used by this class.
     *
     * @param mixed $count
     */
    public function setup($count): void
    {
        // count comes from array, not number of bars etc.
    }

    /**
     * always true, because it wraps around.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return true;
    }

    /**
     * return the colour.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->colours[$offset % $this->count];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        $this->colours[$offset % $this->count] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new \Exception('Unexpected offsetUnset');
    }
}
