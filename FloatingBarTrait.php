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

trait FloatingBarTrait
{
    private $min_value;
    private $max_value;

    /**
     * Returns the maximum bar end.
     */
    public function getMaxValue()
    {
        if ($this->max_value !== null) {
            return $this->max_value;
        }

        $max = null;

        foreach ($this->values[0] as $item) {
            $s = $item->value;
            $e = $item->end;

            if ($s === null || $e === null) {
                continue;
            }

            $m = max($s, $e);

            if ($max === null || $m > $max) {
                $max = $m;
            }
        }

        return $this->max_value = $max;
    }

    /**
     * Returns the minimum bar end.
     */
    public function getMinValue()
    {
        if ($this->min_value !== null) {
            return $this->min_value;
        }

        $min = null;

        foreach ($this->values[0] as $item) {
            $s = $item->value;
            $e = $item->end;

            if ($s === null || $e === null) {
                continue;
            }

            $m = min($s, $e);

            if ($min === null || $m < $min) {
                $min = $m;
            }
        }

        return $this->min_value = $min;
    }

    /**
     * Returns TRUE if the item is visible on the graph.
     *
     * @param mixed $item
     * @param mixed $dataset
     */
    public function isVisible($item, $dataset = 0)
    {
        if ($item->value === null || $item->end === null) {
            return false;
        }

        return $item->end - $item->value !== 0;
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

        $start = $item->value;
        $value = $item->end - $start;
        $y_pos = $this->barY($value, $bar, $start, $axis);

        if ($y_pos === null) {
            return [];
        }

        return $bar;
    }

    /**
     * Override to replace value.
     *
     * @param mixed      $element
     * @param mixed      $item
     * @param mixed      $dataset
     * @param mixed      $key
     * @param null|mixed $value
     * @param mixed      $duplicate
     */
    protected function setTooltip(
        &$element,
        &$item,
        $dataset,
        $key,
        $value = null,
        $duplicate = false,
    ) {
        $value = $item->end - $item->value;

        return parent::setTooltip($element, $item, $dataset, $key, $value, $duplicate);
    }
}
