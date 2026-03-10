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
 * Class for axis with specific tick marks.
 */
class AxisFixedTicks extends Axis
{
    protected $ticks;
    protected $unit_length = 0;

    public function __construct(
        $length,
        $max,
        $min,
        $ticks,
        $units_before,
        $units_after,
        $decimal_digits,
        $label_callback,
        $values,
    ) {
        sort($ticks);
        $this->ticks = [];

        // only keep the ticks that are inside the axis bounds
        foreach ($ticks as $t) {
            if ($t >= $min && $t <= $max) {
                $this->ticks[] = $t;
            }
        }

        if (\count($this->ticks) < 1) {
            throw new \Exception('No ticks in axis range');
        }

        $this->unit_length = $max - $min;

        if ($this->unit_length === 0) {
            throw new \Exception('Zero length axis (min >= max)');
        }

        // min_unit = 1, min_space = 1, fit = false
        parent::__construct(
            $length,
            $max,
            $min,
            1,
            1,
            false,
            $units_before,
            $units_after,
            $decimal_digits,
            $label_callback,
            $values,
        );

        $this->setLength($length);
    }

    /**
     * For bar graphs, increase length by 1 unit.
     */
    public function bar(): void
    {
        ++$this->unit_length;
        $this->setLength($this->length);
        parent::bar();
    }

    /**
     * Returns the size of a unit in grid space.
     */
    public function unit()
    {
        return $this->unit_size;
    }

    /**
     * Returns the distance along the axis where 0 should be.
     */
    public function zero()
    {
        return $this->zero;
    }

    /**
     * Set length, adjust scaling.
     *
     * @param mixed $l
     */
    public function setLength($l): void
    {
        $this->length = $l;

        // these values are fixed, based on length
        $this->unit_size = $this->length / $this->unit_length;
        $this->zero = -$this->min_value * $this->unit_size;

        // not used by this class, but others expect it to be set
        $this->grid_spacing = 1;
    }

    /**
     * Returns the grid points as an array of GridPoints
     *  if $start is NULL, just set up the grid spacing without returning points.
     *
     * @param mixed $start
     */
    public function getGridPoints($start)
    {
        if ($start === null) {
            return;
        }

        $points = [];

        foreach ($this->ticks as $value) {
            $position = $start + $this->direction * ($this->zero + $value * $this->unit_size);
            $points[] = $this->getGridPoint($position, $value);
        }

        if ($this->direction < 0) {
            usort($points, static function ($a, $b) {
                return $b->position - $a->position;
            });
        } else {
            usort($points, static function ($a, $b) {
                return $a->position - $b->position;
            });
        }

        return $points;
    }
}
