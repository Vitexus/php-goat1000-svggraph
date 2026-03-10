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
 * Deal with the strange way population pyramids work.
 */
class PopulationPyramidAverage extends Average
{
    private $graph;
    private $lines = [];

    /**
     * Calculates the mean average for a dataset.
     *
     * @param mixed $values
     * @param mixed $dataset
     */
    protected function calculate(&$values, $dataset)
    {
        $val = parent::calculate($values, $dataset);

        if ($val === null) {
            return $val;
        }

        return $dataset % 2 ? $val : -$val;
    }

    /**
     * Need to sign-correct the average value for display.
     *
     * @param mixed $graph
     * @param mixed $avg
     * @param mixed $dataset
     */
    protected function getTitle(&$graph, $avg, $dataset)
    {
        return parent::getTitle($graph, $dataset % 2 ? $avg : -$avg, $dataset);
    }
}
