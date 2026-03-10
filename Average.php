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
 * Class for average lines (using guidelines).
 */
class Average
{
    private $graph;
    private $lines = [];

    public function __construct(&$graph, &$values, $datasets)
    {
        foreach ($datasets as $d) {
            if (!$graph->getOption(['show_average', $d])) {
                continue;
            }

            $avg = $this->calculate($values, $d);

            if ($avg === null) {
                continue;
            }

            $line = [$avg];

            $title = $this->getTitle($graph, $avg, $d);

            if ($title !== null && $title !== '') {
                $line[] = $title;
            }

            $cg = new ColourGroup($graph, null, 0, $d, 'average_colour');
            $line['colour'] = $cg->stroke();

            $tc = $graph->getOption(['average_title_colour', $d]);

            if (!empty($tc)) {
                $cg = new ColourGroup($graph, null, 0, $d, 'average_title_colour');
                $line['text_colour'] = $cg->stroke();
            }

            $line['stroke_width'] = new Number($graph->getOption(['average_stroke_width', $d], 1));
            $line['font_size'] = Number::units($graph->getOption(['average_font_size', $d]));

            $opts = ['opacity', 'above', 'dash', 'title_align',
                'title_angle', 'title_opacity', 'title_padding', 'title_position',
                'font', 'font_adjust', 'font_weight', 'length', 'length_units'];

            foreach ($opts as $opt) {
                $g_opt = str_replace('title', 'text', $opt);
                $line[$g_opt] = $graph->getOption(['average_'.$opt, $d]);
            }

            // prevent line from changing graph dimensions
            $line['no_min_max'] = true;
            $this->lines[] = $line;
        }

        $this->graph = &$graph;
    }

    /**
     * Adds the average lines to the graph's guidelines.
     */
    public function getGuidelines(): void
    {
        if (empty($this->lines)) {
            return;
        }

        $guidelines = Guidelines::normalize($this->graph->getOption('guideline'));
        $this->graph->setOption('guideline', array_merge($guidelines, $this->lines));
    }

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
            if ($p->value === null || !is_numeric($p->value)) {
                continue;
            }

            $sum += $p->value;
            ++$count;
        }

        return $count ? $sum / $count : null;
    }

    /**
     * Returns the average line title.
     *
     * @param mixed $graph
     * @param mixed $avg
     * @param mixed $dataset
     */
    protected function getTitle(&$graph, $avg, $dataset)
    {
        $tcb = $graph->getOption(['average_title_callback', $dataset]);

        if (\is_callable($tcb)) {
            return $tcb($dataset, $avg);
        }

        return $graph->getOption(['average_title', $dataset]);
    }
}
