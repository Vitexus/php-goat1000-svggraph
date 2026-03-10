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

class ParetoChart extends BarAndLineGraph
{
    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        $fs = [
            'dataset_axis' => [0, 1],
            'line_dataset' => [1],
            'datetime_keys' => false,
        ];
        $s = [
            'tooltip_callback' => static function ($d, $k, $v) {
                if ($k === null || $v === null) {
                    return null;
                }

                if ($d === 0) {
                    return $k.': '.new Number($v);
                }

                return new Number($v).'%';
            },
        ];
        $fs = array_merge($fs, $fixed_settings);

        // to pass settings into line graph
        $settings = array_merge($s, $settings);

        parent::__construct($w, $h, $settings, $fs);
    }

    /**
     * Override to process values into order and add line graph.
     *
     * @param mixed $values
     */
    public function values($values)
    {
        $res = parent::values($values);

        if (empty($values) || $this->values->error) {
            return $res;
        }

        if ($this->values instanceof Data) {
            $this->values = StructuredData::convertFrom($this->values, true, false, false);
        }

        $dataset = $this->getOption(['dataset', 0], 0);
        $this->values->sort($dataset, true);
        $sum = 0;

        foreach ($this->values[$dataset] as $item) {
            if ($item->value < 0) {
                throw new \Exception('Negative values not supported');
            }

            $sum += $item->value;
        }

        $running = 0;
        $this->values->revalue(2, static function ($key, $row) use (&$running, $sum, $dataset) {
            $value = $row[$dataset];
            $running += $value;
            $new_row = [$value, $sum > 0 ? 100 * $running / $sum : 100];

            return $new_row;
        });

        $this->setOption('line_bar', 1);
        $this->setOption('units_y', [null, '%']);
        $this->setOption('minimum_units_y', 1);
        $this->setOption('dataset', null);

        // update MultiGraph with new data
        $this->multi_graph = new MultiGraph($this->values, false, false, false);
        $this->multi_graph->setEnabledDatasets([0, 1]);

        return $res;
    }

    /**
     * Override to prevent offset.
     *
     * @param mixed $dataset
     */
    public function getLineOffset($dataset)
    {
        return $this->x_axes[$this->main_x_axis]->unit();

        return 0;
    }

    /**
     * Adds starting point to line.
     *
     * @param mixed $dataset
     * @param mixed $points
     * @param mixed $y_bottom
     */
    public function drawLine($dataset, $points, $y_bottom)
    {
        $x = $this->gridX(0);
        $y = $this->gridY(0, 1);
        $p = [$x, $y, null, $dataset, 0];
        $points = array_merge([$p], $points);

        // add a marker at start of line
        $item = new DataItem(0, 0);
        $this->linegraph->addMarker($x, $y, $item, null, $dataset);

        return $this->linegraph->drawLine($dataset, $points, $y_bottom);
    }
}
