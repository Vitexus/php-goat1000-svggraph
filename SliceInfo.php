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
 * Class for details of each pie slice.
 */
class SliceInfo
{
    public $start_angle;
    public $end_angle;
    public $radius_x;
    public $radius_y;

    public function __construct($start, $end, $rx, $ry)
    {
        $this->start_angle = $start;
        $this->end_angle = $end;
        $this->radius_x = $rx;
        $this->radius_y = $ry;
    }

    /**
     * Calculates the middle angle of the slice.
     */
    public function midAngle()
    {
        return $this->start_angle + ($this->end_angle - $this->start_angle) / 2;
    }

    /**
     * Returns the slice angle in degrees.
     */
    public function degrees()
    {
        return rad2deg($this->end_angle - $this->start_angle);
    }

    /**
     * Returns the bounding box for the slice, radius from 0,0.
     *
     * @param mixed $reverse
     *
     * @return array($x1, $y1, $x2, $y2)
     */
    public function boundingBox($reverse)
    {
        $x1 = $y1 = $x2 = $y2 = 0;
        $angle = fmod($this->end_angle - $this->start_angle, 2 * \M_PI);
        $right_angle = \M_PI * 0.5;

        $rx = $this->radius_x;
        $ry = $this->radius_y;
        $a1 = fmod($this->start_angle, 2 * \M_PI);
        $a2 = $a1 + $angle;
        $start_sector = floor($a1 / $right_angle);
        $end_sector = floor($a2 / $right_angle);

        switch ($end_sector - $start_sector) {
            case 0:
                // slice all in one sector
                $x = max(abs(cos($a1)), abs(cos($a2))) * $rx;
                $y = max(abs(sin($a1)), abs(sin($a2))) * $ry;

                switch ($start_sector) {
                    case 0:
                        $x2 = $x;
                        $y2 = $y;

                        break;
                    case 1:
                        $x1 = -$x;
                        $y2 = $y;

                        break;
                    case 2:
                        $x1 = -$x;
                        $y1 = -$y;

                        break;
                    case 3:
                        $x2 = $x;
                        $y1 = -$y;

                        break;
                }

                break;
            case 1:
                // slice across two sectors
                switch ($start_sector) {
                    case 0:
                        $x1 = cos($a2) * $rx;
                        $x2 = cos($a1) * $rx;
                        $y2 = $ry;

                        break;
                    case 1:
                        $x1 = -$rx;
                        $y1 = sin($a2) * $ry;
                        $y2 = sin($a1) * $ry;

                        break;
                    case 2:
                        $x1 = cos($a1) * $rx;
                        $x2 = cos($a2) * $rx;
                        $y1 = -$ry;

                        break;
                    case 3:
                        $x2 = $rx;
                        $y1 = sin($a1) * $ry;
                        $y2 = sin($a2) * $ry;

                        break;
                }

                break;
            case 2:
                // slice across three sectors
                $x1 = -$rx;
                $y1 = -$ry;
                $x2 = $rx;
                $y2 = $ry;

                switch ($start_sector) {
                    case 0:
                        $y1 = sin($a2) * $ry;
                        $x2 = cos($a1) * $rx;

                        break;
                    case 1:
                        $x2 = cos($a2) * $rx;
                        $y2 = sin($a1) * $ry;

                        break;
                    case 2:
                        $x1 = cos($a1) * $rx;
                        $y2 = sin($a2) * $ry;

                        break;
                    case 3:
                        $x1 = cos($a2) * $rx;
                        $y1 = sin($a1) * $ry;

                        break;
                }

                break;
            case 3:
                // slice across four sectors
                $x = max(abs(cos($a1)), abs(cos($a2))) * $rx;
                $y = max(abs(sin($a1)), abs(sin($a2))) * $ry;
                $x1 = -$rx;
                $y1 = -$ry;
                $x2 = $rx;
                $y2 = $ry;

                switch ($start_sector) {
                    case 0: $x2 = $x;

                        break;
                    case 1: $y2 = $y;

                        break;
                    case 2: $x1 = -$x;

                        break;
                    case 3: $y1 = -$y;

                        break;
                }

                break;
            case 4:
                // slice is > 270 degrees and both ends in one sector
                $x1 = -$rx;
                $y1 = -$ry;
                $x2 = $rx;
                $y2 = $ry;

                break;
        }

        if ($reverse) {
            // swap Y around origin
            $y = -$y1;
            $y1 = -$y2;
            $y2 = $y;
        }

        return [$x1, $y1, $x2, $y2];
    }
}
