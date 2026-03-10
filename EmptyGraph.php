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

class EmptyGraph extends Graph
{
    /**
     * Does nothing, no colours to set up.
     */
    protected function setup(): void
    {
    }

    /**
     * Ignore values, not used on empty graph.
     *
     * @param mixed $values
     */
    public function values($values): void
    {
    }

    /**
     * Draws an empty graph.
     */
    protected function draw()
    {
        // maybe not completely empty
        return $this->underShapes().$this->overShapes();
    }

    /**
     * Drawing nothing, so check nothing.
     */
    protected function checkValues(): void
    {
    }
}
