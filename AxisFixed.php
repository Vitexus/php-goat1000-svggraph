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
 * Axis with fixed measurements.
 */
class AxisFixed extends Axis
{
    protected $step;
    protected $orig_max_value;
    protected $orig_min_value;

    public function __construct(
        $length,
        $max_val,
        $min_val,
        $step,
        $units_before,
        $units_after,
        $decimal_digits,
        $label_callback,
        $values,
    ) {
        // min_unit = 1, min_space = 1, fit = false
        parent::__construct(
            $length,
            $max_val,
            $min_val,
            1,
            1,
            false,
            $units_before,
            $units_after,
            $decimal_digits,
            $label_callback,
            $values,
        );
        $this->orig_max_value = $max_val;
        $this->orig_min_value = $min_val;
        $this->step = $step;
    }

    /**
     * Sets the bar style, adding an extra unit.
     */
    public function bar(): void
    {
        if (!$this->rounded_up) {
            $this->orig_max_value += $this->min_unit;
            parent::bar();
        }
    }

    /**
     * Calculates a grid based on min, max and step
     * min and max will be adjusted to fit step.
     */
    protected function grid()
    {
        // use the original min/max to prevent compounding of floating-point
        // rounding problems
        $min = $this->orig_min_value;
        $max = $this->orig_max_value;

        // if min and max are the same side of 0, only adjust one of them
        if ($max * $min >= 0) {
            $count = $max - $min;

            if (abs($max) >= abs($min)) {
                $this->max_value = $min + $this->step * ceil($count / $this->step);
            } else {
                $this->min_value = $max - $this->step * ceil($count / $this->step);
            }
        } else {
            $this->max_value = $this->step * ceil($max / $this->step);
            $this->min_value = $this->step * floor($min / $this->step);
        }

        $count = ($this->max_value - $this->min_value) / $this->step;
        $ulen = $this->max_value - $this->min_value;

        if ($ulen === 0) {
            throw new \Exception('Zero length axis (min >= max)');
        }

        $this->unit_size = $this->length / $ulen;
        $grid = $this->length / $count;
        $this->zero = (-$this->min_value / $this->step) * $grid;

        return $grid;
    }
}
