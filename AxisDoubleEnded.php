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
 * Class for axis with +ve on both sides of zero.
 */
class AxisDoubleEnded extends Axis
{
    /**
     * Constructor calls Axis constructor with 0.5 * length.
     *
     * @param mixed $length
     * @param mixed $max_val
     * @param mixed $min_val
     * @param mixed $min_unit
     * @param mixed $min_space
     * @param mixed $fit
     * @param mixed $units_before
     * @param mixed $units_after
     * @param mixed $decimal_digits
     * @param mixed $label_callback
     */
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
        $label_callback,
    ) {
        if ($min_val < 0) {
            throw new \Exception('Negative value for double-ended axis');
        }

        parent::__construct(
            $length / 2,
            $max_val,
            $min_val,
            $min_unit,
            $min_space,
            $fit,
            $units_before,
            $units_after,
            $decimal_digits,
            $label_callback,
            false,
        );
    }

    /**
     * Return the full axis length, not the 1/2 length.
     */
    public function getLength()
    {
        return $this->length * 2;
    }

    /**
     * Returns the distance along the axis where 0 should be.
     */
    public function zero()
    {
        return $this->zero = $this->length;
    }

    /**
     * Returns the grid points as an array of GridPoints.
     *
     * @param mixed $start
     */
    public function getGridPoints($start)
    {
        $points = parent::getGridPoints($start);

        if ($start === null) {
            return;
        }

        $new_points = [];
        $z = $this->zero();

        foreach ($points as $p) {
            $new_points[] = new GridPoint($p->position + $z, $p->getText(), $p->value);

            if ($p->value !== 0) {
                $new_points[] = new GridPoint((2 * $start) + $z - $p->position, $p->getText(), $p->value);
            }
        }

        if ($this->direction < 0) {
            usort($new_points, static function ($a, $b) {
                return $b->position - $a->position;
            });
        } else {
            usort($new_points, static function ($a, $b) {
                return $a->position - $b->position;
            });
        }

        return $new_points;
    }

    /**
     * Returns the grid subdivision points as an array.
     *
     * @param mixed $min_space
     * @param mixed $min_unit
     * @param mixed $start
     * @param mixed $fixed
     */
    public function getGridSubdivisions($min_space, $min_unit, $start, $fixed)
    {
        $divs = parent::getGridSubdivisions($min_space, $min_unit, $start, $fixed);
        $new_divs = [];
        $z = $this->zero();

        foreach ($divs as $d) {
            $new_divs[] = new GridPoint($d->position + $z, '', 0);
            $new_divs[] = new GridPoint((2 * $start) + $z - $d->position, '', 0);
        }

        return $new_divs;
    }
}
