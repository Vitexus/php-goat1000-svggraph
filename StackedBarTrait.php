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

trait StackedBarTrait
{
    use MultiGraphTrait;

    // used to determine where the total label should go
    protected $last_position_pos = [];
    protected $last_position_neg = [];
    private $bar_visibility = [];

    /**
     * Displays the bar totals.
     *
     * @param mixed $bnum
     * @param mixed $yplus
     * @param mixed $yminus
     * @param mixed $dataset
     */
    public function barTotals(DataItem $item, $bnum, $yplus, $yminus, $dataset): void
    {
        $bar_x = $this->gridPosition($item, $bnum);

        if ($this->getOption('show_bar_totals') && $bar_x !== null) {
            $cb = $this->getOption('bar_total_callback');

            if ($yplus) {
                $bar = $this->barDimensions($item, $bnum, 0, null, $dataset);
                $this->barY($yplus, $bar);

                if (\is_callable($cb)) {
                    $total = $cb($item->key, $yplus);
                } else {
                    $total = new Number($yplus);
                    $total = $total->format();
                }

                $this->addContentLabel(
                    'totalpos-'.$dataset,
                    $bnum,
                    $bar['x'],
                    $bar['y'],
                    $bar['width'],
                    $bar['height'],
                    $total,
                );
            }

            if ($yminus) {
                $bar = $this->barDimensions($item, $bnum, 0, null, $dataset);
                $this->barY($yminus, $bar);

                if (\is_callable($cb)) {
                    $total = $cb($item->key, $yminus);
                } else {
                    $total = new Number($yminus);
                    $total = $total->format();
                }

                $this->addContentLabel(
                    'totalneg-'.$dataset,
                    $bnum,
                    $bar['x'],
                    $bar['y'],
                    $bar['width'],
                    $bar['height'],
                    $total,
                );
            }
        }
    }

    /**
     * Overridden to prevent drawing on other bars.
     *
     * @param mixed $dataset
     * @param mixed $index
     * @param mixed $item
     * @param mixed $x
     * @param mixed $y
     * @param mixed $w
     * @param mixed $h
     * @param mixed $label_w
     * @param mixed $label_h
     */
    public function dataLabelPosition(
        $dataset,
        $index,
        &$item,
        $x,
        $y,
        $w,
        $h,
        $label_w,
        $label_h,
    ) {
        [$pos, $target] = parent::dataLabelPosition(
            $dataset,
            $index,
            $item,
            $x,
            $y,
            $w,
            $h,
            $label_w,
            $label_h,
        );

        if (!is_numeric($dataset)) {
            // doing this supports stacked grouped bar graph totals too
            [$d] = explode('-', $dataset);

            if ($d === 'totalpos') {
                if (isset($this->last_position_pos[$index])) {
                    [$lpos, $l_h] = $this->last_position_pos[$index];
                    [$hpos, $vpos] = Graph::translatePosition($lpos);

                    if ($vpos === 'ot') {
                        $num_offset = new Number(-$l_h);

                        return ['above 0 '.$num_offset, $target];
                    }
                }

                return ['above', $target];
            }

            if ($d === 'totalneg') {
                if (isset($this->last_position_neg[$index])) {
                    [$lpos, $l_h] = $this->last_position_neg[$index];
                    [$hpos, $vpos] = Graph::translatePosition($lpos);

                    if ($vpos === 'ob') {
                        $num_offset = new Number($l_h);

                        return ['below 0 '.$num_offset, $target];
                    }
                }

                return ['below', $target];
            }
        }

        if ($label_h > $h && Graph::isPositionInside($pos)) {
            $pos = str_replace(['top', 'bottom', 'above', 'below'], 'middle', $pos);
        }

        if ($item->value > 0) {
            $this->last_position_pos[$index] = [$pos, $label_h];
        } else {
            $this->last_position_neg[$index] = [$pos, $label_h];
        }

        return [$pos, $target];
    }

    /**
     * Returns the style options for bar labels (and totals).
     *
     * @param mixed $dataset
     * @param mixed $index
     * @param mixed $item
     */
    public function dataLabelStyle($dataset, $index, &$item)
    {
        $style = parent::dataLabelStyle($dataset, $index, $item);

        if (!is_numeric($dataset) && str_starts_with($dataset, 'total')) {
            // total settings can override label settings
            $simple = [
                'font', 'font_size', 'font_weight', 'space', 'type', 'fill',
                'font_adjust', 'angle', 'round', 'shadow_opacity',
                'tail_width', 'tail_length',
            ];

            foreach ($simple as $opt) {
                $val = $this->getOption('bar_total_'.$opt);

                if (!empty($val)) {
                    $style[$opt] = $val;
                }
            }

            $colour = new Colour($this, $this->getOption('bar_total_colour'));
            $back_colour = new Colour($this, $this->getOption('bar_total_back_colour'));

            if (!$colour->isNone()) {
                $style['colour'] = $colour;
            }

            if (!$back_colour->isNone()) {
                $style['back_colour'] = $back_colour;
            }

            $stroke = $this->getOption('bar_total_outline_colour');
            $stroke_width = $this->getOption('bar_total_outline_thickness');
            $pad_x = $this->getOption('bar_total_padding_x', 'bar_total_padding');
            $pad_y = $this->getOption('bar_total_padding_y', 'bar_total_padding');

            if (!empty($stroke)) {
                $style['stroke'] = $stroke;
            }

            if (!empty($stroke_width)) {
                $style['stroke_width'] = $stroke_width;
            }

            if (!empty($pad_x)) {
                $style['pad_x'] = $pad_x;
            }

            if (!empty($pad_y)) {
                $style['pad_y'] = $pad_y;
            }
        }

        return $style;
    }

    /**
     * Returns the maximum (stacked) value.
     */
    public function getMaxValue()
    {
        return $this->multi_graph->getMaxSumValue();
    }

    /**
     * Returns the minimum (stacked) value.
     */
    public function getMinValue()
    {
        return $this->multi_graph->getMinSumValue();
    }

    /**
     * Returns TRUE if the item is visible on the graph.
     *
     * @param mixed $item
     * @param mixed $dataset
     */
    public function isVisible($item, $dataset = 0)
    {
        $k = serialize([$dataset, $item->key]);

        return isset($this->bar_visibility[$k]) && $this->bar_visibility[$k];
    }

    /**
     * Returns the ordering for legend entries.
     */
    public function getLegendOrder()
    {
        return 'reverse';
    }

    /**
     * Draws the bars.
     */
    protected function drawBars()
    {
        $this->barSetup();

        $chunk_count = \count($this->multi_graph);
        $datasets = $this->multi_graph->getEnabledDatasets();
        $bars = '';
        $legend_entries = [];

        foreach ($this->multi_graph as $bnum => $itemlist) {
            $item = $itemlist[0];

            // sort the values from bottom to top, assigning position
            $yplus = $yminus = 0;
            $chunk_values = [];

            for ($j = 0; $j < $chunk_count; ++$j) {
                if (!\in_array($j, $datasets, true)) {
                    continue;
                }

                $item = $itemlist[$j];

                if ($item->value !== null) {
                    if ($item->value < 0) {
                        array_unshift($chunk_values, [$j, $item, $yminus]);
                        $yminus += $item->value;
                    } else {
                        $chunk_values[] = [$j, $item, $yplus];
                        $yplus += $item->value;
                    }
                }
            }

            $bar_count = \count($chunk_values);
            $b = 0;

            foreach ($chunk_values as $chunk) {
                [$j, $item, $start] = $chunk;

                $top = (++$b === $bar_count);
                $this->setBarVisibility($j, $item, $top);

                $legend_entries[$j][$bnum] = $item;
                $bars .= $this->drawBar($item, $bnum, $start, null, $j, ['top' => $top]);
            }

            $this->barTotals($item, $bnum, $yplus, $yminus, $j);
        }

        // assign legend entries in order of datasets
        foreach ($legend_entries as $j => $dataset) {
            foreach ($dataset as $bnum => $item) {
                $this->setBarLegendEntry($j, $bnum, $item);
            }
        }

        return $bars;
    }

    /**
     * Sets whether a bar is visible or not.
     *
     * @param mixed      $dataset
     * @param mixed      $top
     * @param null|mixed $override
     */
    protected function setBarVisibility($dataset, DataItem $item, $top, $override = null): void
    {
        $k = serialize([$dataset, $item->key]);
        $this->bar_visibility[$k] = ($override === null ? $item->value !== 0 : $override);
    }
}
