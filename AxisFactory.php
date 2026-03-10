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

class AxisFactory
{
    private $datetime = false;
    private $settings = [];
    private $fit = true;
    private $bar = false;
    private $reverse = false;

    /**
     * Constructor.
     *
     * $datetime = datetime keys (bool)
     * $settings = settings array
     * $fit = fit to values (bool)
     * $bar = bar-style axis (bool)
     * $reverse = reverse direction (bool)
     *
     * @param mixed $datetime
     * @param mixed $settings
     * @param mixed $fit
     * @param mixed $bar
     * @param mixed $reverse
     */
    public function __construct(
        $datetime,
        &$settings,
        $fit = true,
        $bar = false,
        $reverse = false,
    ) {
        $this->datetime = $datetime;
        $this->settings = &$settings;
        $this->fit = $fit;
        $this->bar = $bar;
        $this->reverse = $reverse;
    }

    /**
     * Creates and returns axis.
     *
     * $length = length of axis
     * $min = minimum value
     * $max = maximum value
     * $min_unit = minimum unit value
     * $min_space = minimum spacing
     * $grid_division = fixed division size
     * $units_before = text before units
     * $units_after = text after units
     * $decimal_digits = number of digits
     * $text_callback = text formatting function
     * $values = values array/object
     * $log = logarithmic axis (bool)
     * $log_base = log axis base
     * $levels = axis levels
     * $ticks = fixed axis ticks (array)
     *
     * @param mixed $length
     * @param mixed $min
     * @param mixed $max
     * @param mixed $min_unit
     * @param mixed $min_space
     * @param mixed $grid_division
     * @param mixed $units_before
     * @param mixed $units_after
     * @param mixed $decimal_digits
     * @param mixed $text_callback
     * @param mixed $values
     * @param mixed $log
     * @param mixed $log_base
     * @param mixed $levels
     * @param mixed $ticks
     */
    public function get(
        $length,
        $min,
        $max,
        $min_unit,
        $min_space,
        $grid_division,
        $units_before,
        $units_after,
        $decimal_digits,
        $text_callback,
        $values,
        $log,
        $log_base,
        $levels,
        $ticks,
    ) {
        if ($this->datetime) {
            // datetime axis

            if (\is_array($ticks)) {
                $axis = new AxisFixedTicksDateTime(
                    $length,
                    $max,
                    $min,
                    $ticks,
                    $this->settings,
                );
            } else {
                $axis = new AxisDateTime(
                    $length,
                    $max,
                    $min,
                    $min_space,
                    $grid_division,
                    $levels,
                    $this->settings,
                );
            }
        } elseif ($log) {
            // logarithmic axis
            if (\is_array($ticks)) {
                $axis = new AxisLogTicks(
                    $length,
                    $max,
                    $min,
                    $min_unit,
                    $min_space,
                    $this->fit,
                    $units_before,
                    $units_after,
                    $decimal_digits,
                    $log_base,
                    $grid_division,
                    $text_callback,
                    $values,
                    $ticks,
                );
            } else {
                $axis = new AxisLog(
                    $length,
                    $max,
                    $min,
                    $min_unit,
                    $min_space,
                    $this->fit,
                    $units_before,
                    $units_after,
                    $decimal_digits,
                    $log_base,
                    $grid_division,
                    $text_callback,
                    $values,
                );
            }
        } elseif (\is_array($ticks)) {
            // axis with fixed ticks
            $axis = new AxisFixedTicks(
                $length,
                $max,
                $min,
                $ticks,
                $units_before,
                $units_after,
                $decimal_digits,
                $text_callback,
                $values,
            );
        } elseif ($this->tightX()) {
            // create a fixed-tick axis
            $ticks = $this->getTicks($length, $min, $max, $grid_division, $min_unit, $min_space);
            $axis = new AxisFixedTicks(
                $length,
                $max,
                $min,
                $ticks,
                $units_before,
                $units_after,
                $decimal_digits,
                $text_callback,
                $values,
            );
        } elseif (is_numeric($grid_division)) {
            // fixed grid divisions
            $axis = new AxisFixed(
                $length,
                $max,
                $min,
                $grid_division,
                $units_before,
                $units_after,
                $decimal_digits,
                $text_callback,
                $values,
            );
        } else {
            // calculated axis
            $axis = new Axis(
                $length,
                $max,
                $min,
                $min_unit,
                $min_space,
                $this->fit,
                $units_before,
                $units_after,
                $decimal_digits,
                $text_callback,
                $values,
            );
        }

        if ($this->bar) {
            $axis->bar();
        }

        if ($this->reverse) {
            $axis->reverse();
        }

        return $axis;
    }

    /**
     * Returns TRUE for an X-axis with no space at end.
     */
    private function tightX()
    {
        return $this->fit && isset($this->settings['axis_tightness_x'])
          && $this->settings['axis_tightness_x'] > 0;
    }

    /**
     * Returns a list of ticks for axis with no space at ends.
     *
     * @param mixed $length
     * @param mixed $min
     * @param mixed $max
     * @param mixed $division
     * @param mixed $min_unit
     * @param mixed $min_space
     */
    private function getTicks($length, $min, $max, $division, $min_unit, $min_space)
    {
        $start = $min;
        $end = $max;

        if (is_numeric($division)) {
            $step = $division;
        } else {
            // use an axis to calculate the divisions
            $a = new Axis(
                $length,
                $max,
                $min,
                $min_unit,
                $min_space,
                true,
                null,
                null,
                1,
                null,
                null,
            );

            if ($this->bar) {
                $a->bar();
            }

            if ($this->reverse) {
                $a->reverse();
            }

            $p = $a->getGridPoints(0);

            $step = abs($p[0]->value - $p[1]->value);
            $max_step = max(abs($start), abs($end));

            // need smaller divisions
            if (\count($p) === 2 || $step > $max_step) {
                $multipliers = [0.1, 0.2, 0.25, 0.5];
                $mmax = \count($multipliers) - 1;
                $m = 0;
                $mn = $min_space / $length;

                for ($i = $mmax; $i >= 0; --$i) {
                    $sm = $step * $multipliers[$i];

                    if ($sm > $max_step) {
                        continue;
                    }

                    if ($multipliers[$i] < $mn) {
                        break;
                    }
                }

                $step = $sm;
            }
        }

        $start -= fmod($start, $step);

        return range($start, $end, $step);
    }
}
