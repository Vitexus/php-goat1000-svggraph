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

trait Grouped3DGraphTrait
{
    use GroupedBarTrait;

    /**
     * Override AdjustAxes to change depth.
     *
     * @param mixed $x_len
     * @param mixed $y_len
     */
    protected function adjustAxes(&$x_len, &$y_len)
    {
        /**
         * The depth is roughly 1/$num - but it must also take into account the
         * bar and group spacing, which is where things get messy.
         */
        $ends = $this->getAxisEnds();
        $num = $ends['k_max'][0] - $ends['k_min'][0] + 1;

        $block = $x_len / $num;
        $datasets = $this->multi_graph->getEnabledDatasets();
        $group = \count($datasets);
        $a = $this->getOption('bar_space');
        $b = $this->getOption('group_space');
        $c = ($block - $a - ($group - 1) * $b) / $group;
        $d = ($a + $c) / $block;
        $this->depth = $d;

        return parent::adjustAxes($x_len, $y_len);
    }
}
