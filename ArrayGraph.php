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

class ArrayGraph extends Graph
{
    use MultiGraphTrait {
        values as mgValues;
    }
    protected $inner_options;
    protected $inner_types;
    protected $raw_data;
    protected $raw_settings;

    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        $this->raw_settings = $settings;
        parent::__construct($w, $h, $settings, $fixed_settings);
    }

    /**
     * Stores raw values to make it easier to set up subgraphs.
     *
     * @param mixed $values
     */
    public function values($values)
    {
        $this->raw_data = $values;

        return $this->mgValues($values);
    }

    /**
     * Now create the subgraphs.
     */
    public function checkValues(): void
    {
        parent::checkValues();
        $opt = [
            'keep_colour_order' => true,
            'back_stroke_width' => 0,
            'back_colour' => 'none',
            'back_shadow' => 0,
            'title' => null,
        ];
        $opt = array_merge($this->raw_settings, $opt);

        $graphs = $this->getLayout();

        foreach ($graphs as $graph) {
            $s = new Subgraph(
                $graph['type'],
                $graph['x'],
                $graph['y'],
                $graph['w'],
                $graph['h'],
                array_merge($opt, $graph['options']),
            );
            $s->values($this->raw_data);
            $s->setColours($this->colours);
            $this->subgraphs[] = $s;
        }
    }

    public function draw()
    {
        // all drawing is done by subgraphs
        return $this->underShapes().$this->overShapes();
    }

    /**
     * Returns the list of graphs to be drawn and where to draw them.
     */
    private function getLayout()
    {
        // find which datasets are going in each graph
        $enabled_datasets = $this->multi_graph->getEnabledDatasets();
        $graph_datasets = $this->getOption('array_graph_dataset');

        if ($graph_datasets === null) {
            // default is one dataset per graph
            $graph_datasets = [];

            foreach ($enabled_datasets as $d) {
                $graph_datasets[] = [$d];
            }
        } else {
            // need to check each of the selected datasets is enabled
            $new_outer = [];

            foreach ($graph_datasets as $gd) {
                $new_inner = [];

                if (\is_array($gd)) {
                    foreach ($gd as $d) {
                        if (!\in_array($d, $enabled_datasets, true)) {
                            continue;
                        }

                        $new_inner[] = $d;
                    }
                } else {
                    if (!\in_array($gd, $enabled_datasets, true)) {
                        continue;
                    }

                    $new_inner[] = $gd;
                }

                if (\count($new_inner) > 0) {
                    $new_outer[] = $new_inner;
                }
            }

            $graph_datasets = $new_outer;
        }

        $graph_count = \count($graph_datasets);
        $cols = $this->getOption('array_graph_columns');

        if ($cols === null || $cols === 'auto') {
            $cols = $this->calcCols($graph_count);
        } else {
            $cols = (int) $cols;

            if ($cols < 1) {
                throw new \Exception('Invalid array_graph_columns value: '.$cols);
            }

            if ($this->width / $cols < 20) {
                throw new \Exception('Option array_graph_columns too large: '.$cols);
            }
        }

        $rows = ceil($graph_count / $cols);
        $w = $this->width / $cols;
        $h = $this->height / $rows;

        $inner_options = $this->getOption('array_graph_options');

        if ($inner_options !== null) {
            if (!\is_array($inner_options)) {
                throw new \Exception('Option array_graph_options is not an array');
            }

            // check if it is an array of arrays
            if (!isset($inner_options[0]) || !\is_array($inner_options[0])) {
                // put single array into outer array
                $inner_options = [$inner_options];
            }
        } else {
            // no special options
            $inner_options = [[]];
        }

        $o_count = \count($inner_options);

        $index = 0;
        $layout = [];
        $last_row_count = $graph_count % $cols;
        $last_row_offset = 0;
        $align = $this->getOption('array_graph_align');

        if ($last_row_count && $align !== 'left') {
            $mult = $align === 'right' ? 1.0 : 0.5;
            $last_row_offset = ($this->width - $last_row_count * $w) * $mult;
        }

        foreach ($graph_datasets as $d) {
            $col = $index % $cols;
            $row = floor($index / $cols);
            $x_offset = ($row === $rows - 1 ? $x_offset = $last_row_offset : 0);

            $options = $inner_options[$index % $o_count];
            $options['dataset'] = $d;

            $g = [
                'x' => $x_offset + $w * $col,
                'y' => $h * $row,
                'w' => $w,
                'h' => $h,
                'type' => $this->getOption(['array_graph_type', $index], ['@', 'PieGraph']),
                'options' => $options,
            ];

            $layout[] = $g;
            ++$index;
        }

        return $layout;
    }

    /**
     * Calculates the best number of columns.
     *
     * @param mixed $count
     */
    private function calcCols($count)
    {
        if ($count === 1) {
            return 1;
        }

        $w = $this->width;
        $h = $this->height;

        if ($count === 2) {
            return $w > $h ? 2 : 1;
        }

        $matches = [];

        for ($c = 1; $c <= $count; ++$c) {
            $r = ceil($count / $c);
            $bw = $w / $c;
            $bh = $h / $r;
            $area = $bw * $bh;
            $aspect = $bw < $bh ? $bw / $h : $bh / $bw;
            $score = $area * ($aspect + 1);

            $matches[] = [
                'c' => $c,
                'r' => $r,
                'area' => $area,
                'aspect' => $aspect,
                'score' => $score,
            ];
        }

        usort($matches, static function ($a, $b) {
            return $a['score'] - $b['score'];
        });
        $winner = array_pop($matches);

        return $winner['c'];
    }
}
