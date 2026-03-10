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

class BarAndLineGraph extends GroupedBarGraph
{
    protected $linegraph;
    protected $line_datasets = [];
    protected $bar_datasets = [];

    /**
     * We need an instance of the LineGraph class.
     *
     * @param mixed $w
     * @param mixed $h
     */
    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        parent::__construct($w, $h, $settings, $fixed_settings);

        // prevent repeated labels
        unset($settings['label']);
        $this->linegraph = new MultiLineGraph($w, $h, $settings);
    }

    /**
     * Return box or line for legend.
     *
     * @param mixed $x
     * @param mixed $y
     * @param mixed $w
     * @param mixed $h
     * @param mixed $entry
     */
    public function drawLegendEntry($x, $y, $w, $h, $entry)
    {
        if (isset($entry->style['line_style'])) {
            return $this->linegraph->drawLegendEntry($x, $y, $w, $h, $entry);
        }

        return parent::drawLegendEntry($x, $y, $w, $h, $entry);
    }

    /**
     * Returns the normal dataset order.
     */
    public function getLegendOrder()
    {
        $datasets = \count($this->multi_graph);

        return range(0, $datasets - 1);
    }

    /**
     * Returns the horizontal offset of the line relative to the grid.
     *
     * @param mixed $dataset
     */
    public function getLineOffset($dataset)
    {
        if ($this->getOption('datetime_keys')) {
            return 0;
        }

        $offset = 0;
        $o = $this->getOption(['line_bar', $dataset]);

        if (is_numeric($o) && \in_array($o, $this->bar_datasets, true)) {
            $offset = $this->calculated_bar_space +
            ($this->dataset_offsets[$o] * $this->group_bar_spacing) +
            ($this->calculated_bar_width / 2);
        } else {
            $g_width = $this->x_axes[$this->main_x_axis]->unit();
            $offset = $g_width / 2;
        }

        return $offset;
    }

    /**
     * Draws a single line.
     *
     * @param mixed $dataset
     * @param mixed $points
     * @param mixed $y_bottom
     */
    public function drawLine($dataset, $points, $y_bottom)
    {
        return $this->linegraph->drawLine($dataset, $points, $y_bottom);
    }

    /**
     * Draws the bars and lines.
     */
    protected function draw()
    {
        $body = $this->grid().$this->underShapes();

        // LineGraph has not been initialised, need to copy in details
        $copy = ['colours', 'links', 'x_axes', 'y_axes', 'main_x_axis',
            'main_y_axis', 'legend',
            // needed for best-fit line support
            'g_width', 'g_height', 'pad_left', 'pad_top'];

        foreach ($copy as $member) {
            $this->linegraph->{$member} = $this->{$member};
        }

        // keep gradients and patterns synced
        $this->linegraph->defs = &$this->defs;

        // find the lines
        $chunk_count = \count($this->multi_graph);
        $lines = $this->getOption('line_dataset');
        $line_breaks = [];
        $line_points = [];
        $line_offsets = [];
        $points = [];

        if (!\is_array($lines)) {
            $lines = [$lines];
        }

        rsort($lines);

        foreach ($lines as $line) {
            $line_breaks[$line] = $this->getOption(['line_breaks', $line]);
            $line_points[$line] = [];
            $points[$line] = [];
        }

        $this->line_datasets = $lines = array_flip($lines);

        $y_axis_pos = $this->height - $this->pad_bottom -
          $this->y_axes[$this->main_y_axis]->zero();
        $y_bottom = min($y_axis_pos, $this->height - $this->pad_bottom);

        // set up bars, then find line offsets
        $this->barSetup();

        foreach ($lines as $k => $l) {
            $line_offsets[$k] = $this->getLineOffset($k);
        }

        // draw bars, store line points
        $datasets = $this->multi_graph->getEnabledDatasets();
        $line_dataset = 0;
        $bars = '';

        foreach ($this->multi_graph as $bnum => $itemlist) {
            $item = $itemlist[0];
            $bar_pos = $this->gridPosition($item, $bnum);

            if ($bar_pos !== null) {
                for ($j = 0; $j < $chunk_count; ++$j) {
                    if (!\in_array($j, $datasets, true)) {
                        continue;
                    }

                    $y_axis = $this->datasetYAxis($j);
                    $item = $itemlist[$j];

                    if (\array_key_exists($j, $lines)) {
                        $line_dataset = $j;

                        if ($line_breaks[$line_dataset] && $item->value === null
                          && \count($points[$line_dataset]) > 0) {
                            $line_points[$line_dataset][] = $points[$line_dataset];
                            $points[$line_dataset] = [];
                        } elseif ($item->value !== null) {
                            $x = $bar_pos + $line_offsets[$line_dataset];
                            $y = $this->gridY($item->value, $y_axis);
                            $points[$line_dataset][] = [$x, $y, $item, $line_dataset, $bnum];
                        }

                        continue;
                    }

                    $this->setBarLegendEntry($j, $bnum, $item);
                    $bars .= $this->drawBar($item, $bnum, 0, $y_axis, $j);
                }
            }
        }

        foreach ($points as $line_dataset => $line) {
            if (!empty($line)) {
                $line_points[$line_dataset][] = $line;
            }
        }

        // draw lines clipped to grid
        $graph_line = '';

        foreach ($line_points as $dataset => $points) {
            foreach ($points as $p) {
                $graph_line .= $this->drawLine($dataset, $p, $y_bottom);
            }
        }

        $group = [];
        $this->clipGrid($group);
        [$best_fit_above, $best_fit_below] = $this->linegraph->bestFitLines();
        $bars .= $best_fit_below;
        $bars .= $this->element('g', $group, null, $graph_line);

        $group = [];

        if ($this->getOption('semantic_classes')) {
            $group['class'] = 'series';
        }

        $shadow_id = $this->defs->getShadow();

        if ($shadow_id !== null) {
            $group['filter'] = 'url(#'.$shadow_id.')';
        }

        if (!empty($group)) {
            $bars = $this->element('g', $group, null, $bars);
        }

        $body .= $bars;

        $body .= $this->overShapes();
        $body .= $this->axes();

        // add in the markers created by line graph
        $body .= $this->linegraph->drawMarkers();
        $body .= $best_fit_above;

        return $body;
    }

    /**
     * Sets up bar details.
     */
    protected function barSetup(): void
    {
        parent::barSetup();
        $datasets = $this->multi_graph->getEnabledDatasets();
        $chunk_count = \count($datasets);
        $bar = 0;

        foreach ($datasets as $i) {
            // reduce chunk count for lines, add bars to list
            if (\array_key_exists($i, $this->line_datasets)) {
                --$chunk_count;
            } else {
                $this->bar_datasets[$i] = $bar++;
            }
        }

        if (\count($this->bar_datasets) < 1) {
            throw new \Exception('No bar datasets enabled');
        }

        [$chunk_width, $bspace, $chunk_unit_width] =
          $this->barPosition(
              $this->getOption('bar_width'),
              $this->getOption('bar_width_min'),
              $this->x_axes[$this->main_x_axis]->unit(),
              $chunk_count,
              $this->getOption('bar_space'),
              $this->getOption('group_space'),
          );
        $this->group_bar_spacing = $chunk_unit_width;
        $this->setBarWidth($chunk_width, $bspace);
    }

    /**
     * Fills in the x and width of bar.
     *
     * @param mixed $item
     * @param mixed $index
     * @param mixed $bar
     * @param mixed $axis
     * @param mixed $dataset
     */
    protected function barX($item, $index, &$bar, $axis, $dataset)
    {
        $bar_x = $this->gridPosition($item, $index);

        if ($bar_x === null) {
            return null;
        }

        // relative position of bars stored in barSetup()
        $bar['x'] = $bar_x + $this->calculated_bar_space +
            ($this->bar_datasets[$dataset] * $this->group_bar_spacing);
        $bar['width'] = $this->calculated_bar_width;

        return $bar_x;
    }

    /**
     * Draws this graph's data labels, and the line graph's data labels.
     */
    protected function drawDataLabels()
    {
        $labels = parent::drawDataLabels();
        $labels .= $this->linegraph->drawDataLabels();

        return $labels;
    }
}
