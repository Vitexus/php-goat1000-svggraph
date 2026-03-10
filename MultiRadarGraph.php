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
 * MultiRadarGraph - multiple radar graphs on one plot.
 */
class MultiRadarGraph extends RadarGraph
{
    use MultiGraphTrait;

    protected function draw()
    {
        $body = $this->grid().$this->underShapes();
        $plots = '';

        $datasets = $this->multi_graph->getEnabledDatasets();

        foreach ($datasets as $i) {
            $bnum = 0;
            $points = [];
            $plot = '';
            $line_breaks = $this->getOption(['line_breaks', $i]);
            $y_axis = $this->y_axes[$this->main_y_axis];
            $first_point = null;

            foreach ($this->multi_graph[$i] as $item) {
                if ($line_breaks && $item->value === null && \count($points) > 0) {
                    $plot .= $this->drawLine($i, $points, 0);
                    $points = [];
                } else {
                    $point_pos = $this->gridPosition($item, $bnum);

                    if ($item->value !== null && $point_pos !== null) {
                        $val = $y_axis->position($item->value);
                        $angle = $this->arad + $point_pos / $this->g_height;
                        $x = $this->xc + ($val * sin($angle));
                        $y = $this->yc + ($val * cos($angle));
                        $points[] = [$x, $y, $item, $i, $bnum];

                        if ($first_point === null) {
                            $first_point = $points[0];
                        }
                    }
                }

                ++$bnum;
            }

            // close graph or segment?
            if ($first_point && (!$line_breaks || $first_point[4] === 0)) {
                $first_point[2] = null;
                $points[] = $first_point;
            }

            $plot .= $this->drawLine($i, $points, 0);

            if ($this->getOption('semantic_classes')) {
                $plots .= $this->element('g', ['class' => 'series'], null, $plot);
            } else {
                $plots .= $plot;
            }
        }

        $group = [];

        if ($this->getOption('semantic_classes')) {
            $group['class'] = 'series';
        }

        $plots = $this->element('g', $group, null, $plots);

        $group = [];
        $shadow_id = $this->defs->getShadow();

        if ($shadow_id !== null) {
            $group['filter'] = 'url(#'.$shadow_id.')';
        }

        if (!empty($group)) {
            $plots = $this->element('g', $group, null, $plots);
        }

        $body .= $plots;
        $body .= $this->overShapes();
        $body .= $this->axes();
        $body .= $this->drawMarkers();

        return $body;
    }
}
