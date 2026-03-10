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
 * Class for measuring.
 */
class BoundingBox
{
    public $x1;
    public $x2;
    public $y1;
    public $y2;

    public function __construct($x1, $y1, $x2, $y2)
    {
        $this->x1 = $x1;
        $this->x2 = $x2;
        $this->y1 = $y1;
        $this->y2 = $y2;
    }

    /**
     * Returns the width of the box.
     */
    public function width()
    {
        return $this->x2 - $this->x1;
    }

    /**
     * Returns the height of the box.
     */
    public function height()
    {
        return $this->y2 - $this->y1;
    }

    /**
     * Expands the box to fit the new sides.
     *
     * @param mixed $x1
     * @param mixed $y1
     * @param mixed $x2
     * @param mixed $y2
     */
    public function grow($x1, $y1, $x2, $y2): void
    {
        $this->x1 = min($this->x1, $x1);
        $this->y1 = min($this->y1, $y1);
        $this->x2 = max($this->x2, $x2);
        $this->y2 = max($this->y2, $y2);
    }

    /**
     * Expands using another BoundingBox.
     */
    public function growBox(self $box): void
    {
        $this->x1 = min($this->x1, $box->x1);
        $this->y1 = min($this->y1, $box->y1);
        $this->x2 = max($this->x2, $box->x2);
        $this->y2 = max($this->y2, $box->y2);
    }

    /**
     * Moves the box by $x, $y.
     *
     * @param mixed $x
     * @param mixed $y
     */
    public function offset($x, $y): void
    {
        $this->x1 += $x;
        $this->y1 += $y;
        $this->x2 += $x;
        $this->y2 += $y;
    }

    /**
     * Flips the Y-axis values.
     */
    public function flipY(): void
    {
        $tmp = $this->y1;
        $this->y1 = -$this->y2;
        $this->y2 = -$tmp;
    }
}
