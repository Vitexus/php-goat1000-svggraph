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

class HorizontalGroupedBar3DGraph extends HorizontalBar3DGraph
{
    use Grouped3DGraphTrait;

    public function __construct($w, $h, $settings, $fixed_settings = [])
    {
        $fixed = ['single_axis' => true];
        $fixed_settings = array_merge($fixed, $fixed_settings);
        parent::__construct($w, $h, $settings, $fixed_settings);
    }

    /**
     * Sets up bar details.
     */
    protected function barSetup(): void
    {
        parent::barSetup();
        $datasets = $this->multi_graph->getEnabledDatasets();
        $dataset_count = \count($datasets);

        [$chunk_width, $bspace, $chunk_unit_width] =
          $this->barPosition(
              $this->getOption('bar_width'),
              $this->getOption('bar_width_min'),
              $this->y_axes[$this->main_y_axis]->unit(),
              $dataset_count,
              $this->getOption('bar_space'),
              $this->getOption('group_space'),
          );
        $this->group_bar_spacing = $chunk_unit_width;
        $this->setBarWidth($chunk_width, $bspace);

        $offset = 0;

        foreach ($datasets as $d) {
            $this->dataset_offsets[$d] = $offset;
            ++$offset;
        }
    }

    /**
     * Returns an array with x, y, width and height set.
     *
     * @param mixed $item
     * @param mixed $index
     * @param mixed $start
     * @param mixed $axis
     * @param mixed $dataset
     */
    protected function barDimensions($item, $index, $start, $axis, $dataset)
    {
        $bar_y = $this->gridPosition($item, $index);

        if ($bar_y === null) {
            return [];
        }

        $d_offset = $this->dataset_offsets[$dataset];
        $bar = [
            'y' => $bar_y - $this->calculated_bar_space -
              ($d_offset * $this->group_bar_spacing) - $this->calculated_bar_width,
            'height' => $this->calculated_bar_width,
        ];

        $this->barY($item->value, $bar, $start, $axis);

        return $bar;
    }
}
