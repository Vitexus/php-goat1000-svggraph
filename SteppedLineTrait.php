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

trait SteppedLineTrait
{
    /**
     * Double each point to create a stepped line.
     *
     * @param mixed $points
     */
    protected function getLinePoints($points)
    {
        $new_points = [];
        $prev = null;

        foreach ($points as $point) {
            if ($prev) {
                $prev[0] = $point[0];
                $new_points[] = $prev;
            }

            $new_points[] = $point;
            $prev = $point;
        }

        return $new_points;
    }
}
