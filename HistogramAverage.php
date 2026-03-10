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
 * Class for making averages work on Histograms.
 */
class HistogramAverage extends Average
{
    /**
     * Calculates the mean average for a dataset.
     *
     * @param mixed $values
     * @param mixed $dataset
     */
    protected function calculate(&$values, $dataset)
    {
        $sum = 0;
        $count = 0;

        foreach ($values[$dataset] as $p) {
            if ($p->value === null) {
                continue;
            }

            $sum += $p->value;
            ++$count;
        }

        // histogram data ends with a 0 to pad the axis out
        --$count;

        return $count > 0 ? $sum / $count : null;
    }
}
