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
 * Class for axis with specific tick marks and date/time keys.
 */
class AxisFixedTicksDateTime extends AxisDateTime
{
    protected $ticks;

    public function __construct($length, $max, $min, $ticks, $options)
    {
        // only keep the ticks that are inside the axis bounds
        $this->ticks = [];

        foreach ($ticks as $tstr) {
            $t = Graph::dateConvert($tstr);

            if ($t === null) {
                throw new \Exception('Ticks not in correct date/time format');
            }

            if ($t >= $min && $t <= $max) {
                $this->ticks[] = $t;
            }
        }

        if (\count($this->ticks) < 1) {
            throw new \Exception('No ticks in axis range');
        }

        // min_space = 1, fixed_division = null, levels = null,
        parent::__construct($length, $max, $min, 1, null, null, $options);
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
            $pos = $this->position($value);
            $position = $start + ($pos * $this->direction);
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
