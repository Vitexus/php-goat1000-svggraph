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
 * Class for logarithmic axis with specific tick marks.
 */
class AxisLogTicks extends AxisLog
{
    protected $ticks;

    public function __construct(
        $length,
        $max_val,
        $min_val,
        $min_unit,
        $min_space,
        $fit,
        $units_before,
        $units_after,
        $decimal_digits,
        $base,
        $divisions,
        $label_callback,
        $values,
        $ticks,
    ) {
        sort($ticks);
        $this->ticks = [];

        // only keep the ticks that are inside the axis bounds
        foreach ($ticks as $t) {
            if ($t >= $min_val && $t <= $max_val) {
                $this->ticks[] = $t;
            }
        }

        if (\count($this->ticks) < 1) {
            throw new \Exception('No ticks in axis range');
        }

        parent::__construct(
            $length,
            $max_val,
            $min_val,
            $min_unit,
            $min_space,
            $fit,
            $units_before,
            $units_after,
            $decimal_digits,
            $base,
            $divisions,
            $label_callback,
            $values,
        );
    }

    /**
     * Returns the grid points as an array of GridPoints.
     *
     * @param mixed $start
     */
    public function getGridPoints($start)
    {
        if ($start === null) {
            return;
        }

        $points = [];

        foreach ($this->ticks as $val) {
            $position = $this->position($val);
            $position = $start + ($this->direction * $position);
            $points[] = $this->getGridPoint($position, $val);
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
