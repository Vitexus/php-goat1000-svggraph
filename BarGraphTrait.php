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

trait BarGraphTrait
{
    // these are filled in by barSetup()
    protected $calculated_bar_width;
    protected $calculated_bar_space;

    /**
     * Returns the position for a data label.
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
        [$pos, $end] = parent::dataLabelPosition(
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
        $bpos = $this->getOption('bar_label_position');

        if (!empty($bpos)) {
            $pos = $bpos;
        }

        if ($label_h > $h && Graph::isPositionInside($pos)) {
            $pos = str_replace(['top', 'middle', 'bottom'], 'outside top inside ', $pos);
        }

        // flip top/bottom for negative values
        if ($item !== null && $item->value < 0) {
            if (str_contains($pos, 'top')) {
                $pos = str_replace('top', 'bottom', $pos);
            } elseif (str_contains($pos, 'above')) {
                $pos = str_replace('above', 'below', $pos);
            } elseif (str_contains($pos, 'below')) {
                $pos = str_replace('below', 'above', $pos);
            } elseif (str_contains($pos, 'bottom')) {
                $pos = str_replace('bottom', 'top', $pos);
            }
        }

        return [$pos, $end];
    }

    /**
     * Returns the style options for bar labels.
     *
     * @param mixed $dataset
     * @param mixed $index
     * @param mixed $item
     */
    public function dataLabelStyle($dataset, $index, &$item)
    {
        $style = parent::dataLabelStyle($dataset, $index, $item);

        // bar label settings can override global settings
        $opts = [
            'font' => 'bar_label_font',
            'font_size' => 'bar_label_font_size',
            'font_weight' => 'bar_label_font_weight',
            'colour' => 'bar_label_colour',
            'altcolour' => 'bar_label_colour_above',
            'space' => 'bar_label_space',
        ];

        foreach ($opts as $key => $opt) {
            if (isset($this->settings[$opt])) {
                $style[$key] = $this->settings[$opt];
            }
        }

        return $style;
    }

    /**
     * Return box for legend.
     *
     * @param mixed $x
     * @param mixed $y
     * @param mixed $w
     * @param mixed $h
     * @param mixed $entry
     */
    public function drawLegendEntry($x, $y, $w, $h, $entry)
    {
        $bar = ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h];

        return $this->element('rect', $bar, $entry->style);
    }

    /**
     * Draws the graph.
     */
    protected function draw()
    {
        $this->setup();

        $body = $this->grid();
        $bars = $this->drawBars();
        $bar_group = $this->barGroup();

        if (!empty($bar_group)) {
            $bars = $this->element('g', $bar_group, null, $bars);
        }

        $body .= $this->underShapes();
        $body .= $bars;
        $body .= $this->overShapes();
        $body .= $this->axes();

        return $body;
    }

    /**
     * Draws the bars.
     */
    protected function drawBars()
    {
        $dataset = $this->getOption(['dataset', 0], 0);
        $this->barSetup();

        $bars = '';

        foreach ($this->values[$dataset] as $bnum => $item) {
            $this->setBarLegendEntry($dataset, $bnum, $item);
            $bars .= $this->drawBar($item, $bnum, 0, null, $dataset);
        }

        return $bars;
    }

    /**
     * Returns the width of a bar.
     */
    protected function barWidth()
    {
        $bw = $this->getOption('bar_width');

        if (is_numeric($bw) && $bw >= 1) {
            return $bw;
        }

        $unit_w = $this->x_axes[$this->main_x_axis]->unit();
        $bw = $unit_w - $this->getOption('bar_space');

        return max(1, $bw, $this->getOption('bar_width_min'));
    }

    /**
     * Initialize bars.
     */
    protected function barSetup(): void
    {
        $width = $this->barWidth();
        $space = $this->barSpace($width);
        $this->setBarWidth($width, $space);
    }

    /**
     * Sets up the width of the bar and the spacing.
     *
     * @param mixed $width
     * @param mixed $space
     */
    protected function setBarWidth($width, $space): void
    {
        $this->calculated_bar_width = $width;
        $this->calculated_bar_space = $this->getOption('datetime_keys') ? 0 : $space;
    }

    /**
     * Returns an array of attributes for whole group of bars.
     */
    protected function barGroup()
    {
        $group = [];

        if ($this->getOption('semantic_classes')) {
            $group['class'] = 'series';
        }

        $shadow_id = $this->defs->getShadow();

        if ($shadow_id !== null) {
            $group['filter'] = 'url(#'.$shadow_id.')';
        }

        return $group;
    }

    /**
     * Sets the legend entry for a bar.
     *
     * @param mixed $dataset
     * @param mixed $index
     */
    protected function setBarLegendEntry($dataset, $index, DataItem $item): void
    {
        $bar = ['fill' => $this->getColour($item, $index, $dataset)];
        $this->setStroke($bar, $item, $index, $dataset);
        $this->setLegendEntry($dataset, $index, $item, $bar);
    }

    /**
     * Returns the SVG code for a bar.
     *
     * @param mixed      $index
     * @param mixed      $start
     * @param null|mixed $axis
     * @param mixed      $dataset
     * @param mixed      $options
     */
    protected function drawBar(
        DataItem $item,
        $index,
        $start = 0,
        $axis = null,
        $dataset = 0,
        $options = [],
    ) {
        if ($item->value === null) {
            return '';
        }

        $bar = $this->barDimensions($item, $index, $start, $axis, $dataset);

        if (empty($bar)) {
            return '';
        }

        // if the bar is empty and no legend or labels to show give up now
        if ((string) $bar['height'] === '0' && !$this->getOption('legend_show_empty')
          && !$this->getOption('show_data_labels')) {
            return '';
        }

        $this->setStroke($bar, $item, $index, $dataset);
        $bar['fill'] = $this->getColour($item, $index, $dataset);

        if ($this->getOption('semantic_classes')) {
            $bar['class'] = 'series'.$dataset;
        }

        $label_shown = $this->addDataLabel(
            $dataset,
            $index,
            $bar,
            $item,
            $bar['x'],
            $bar['y'],
            $bar['width'],
            $bar['height'],
        );

        if ($this->getOption('show_tooltips')) {
            $this->setTooltip(
                $bar,
                $item,
                $dataset,
                $item->key,
                $item->value,
                $label_shown,
            );
        }

        if ($this->getOption('show_context_menu')) {
            $this->setContextMenu($bar, $dataset, $item, $label_shown);
        }

        $round = max($this->getItemOption('bar_round', $dataset, $item), 0);

        if ($round > 0) {
            // don't allow the round corner to be more than 1/2 bar width or height
            $bar['rx'] = $bar['ry'] = min($round, $bar['width'] / 2, $bar['height'] / 2);
        }

        $bar_part = $this->element('rect', $bar);

        return $this->getLink($item, $item->key, $bar_part);
    }

    /**
     * Returns the space before a bar.
     *
     * @param mixed $bar_width
     */
    protected function barSpace($bar_width)
    {
        return max(0, ($this->x_axes[$this->main_x_axis]->unit() - $bar_width) / 2);
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
        $bar = [];
        $bar_x = $this->barX($item, $index, $bar, $axis, $dataset);

        if ($bar_x === null) {
            return [];
        }

        $y_pos = $this->barY($item->value, $bar, $start, $axis);

        if ($y_pos === null) {
            return [];
        }

        return $bar;
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

        $bar['x'] = $bar_x + $this->calculated_bar_space;
        $bar['width'] = $this->calculated_bar_width;

        return $bar_x;
    }

    /**
     * Fills in the y and height of a bar.
     *
     * @param mixed      $value
     * @param mixed      $bar
     * @param null|mixed $start
     * @param null|mixed $axis
     *
     * @return number unclamped bar position
     */
    protected function barY($value, &$bar, $start = null, $axis = null)
    {
        if ($start) {
            $value += $start;
        }

        $startpos = $start === null ? $this->originY($axis) :
          $this->gridY($start, $axis);

        if ($startpos === null) {
            $startpos = $this->originY($axis);
        }

        $pos = $this->gridY($value, $axis);

        if ($pos === null) {
            $bar['height'] = 0;
        } else {
            $l1 = $this->clampVertical($startpos);
            $l2 = $this->clampVertical($pos);
            $bar['y'] = min($l1, $l2);
            $bar['height'] = abs($l1 - $l2);
        }

        return $pos;
    }

    /**
     * Override to check minimum space requirement.
     *
     * @param mixed      $dataset
     * @param mixed      $index
     * @param mixed      $element
     * @param mixed      $item
     * @param mixed      $x
     * @param mixed      $y
     * @param mixed      $w
     * @param mixed      $h
     * @param null|mixed $content
     * @param mixed      $duplicate
     */
    protected function addDataLabel(
        $dataset,
        $index,
        &$element,
        &$item,
        $x,
        $y,
        $w,
        $h,
        $content = null,
        $duplicate = true,
    ) {
        if ($h < $this->getOption(['data_label_min_space', $dataset])) {
            return false;
        }

        return parent::addDataLabel(
            $dataset,
            $index,
            $element,
            $item,
            $x,
            $y,
            $w,
            $h,
            $content,
            $duplicate,
        );
    }
}
