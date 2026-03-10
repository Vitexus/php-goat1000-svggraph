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

class HorizontalStackedBar3DGraph extends HorizontalBar3DGraph
{
    use StackedBarTrait;

    public function __construct($w, $h, $settings, $fixed_settings = [])
    {
        $fixed = ['single_axis' => true];
        $fixed_settings = array_merge($fixed, $fixed_settings);
        parent::__construct($w, $h, $settings, $fixed_settings);
    }

    /**
     * Trait version draws totals above or below bars, we want left and right.
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
            [$d] = explode('-', $dataset);

            if ($d === 'totalpos') {
                if (isset($this->last_position_pos[$index])) {
                    [$lpos, $l_w] = $this->last_position_pos[$index];
                    [$hpos, $vpos] = Graph::translatePosition($lpos);

                    if ($hpos === 'or') {
                        $num_offset = new Number($l_w);

                        return ['middle outside right '.$num_offset.' 0', $target];
                    }
                }

                return ['outside right', $target];
            }

            if ($d === 'totalneg') {
                if (isset($this->last_position_neg[$index])) {
                    [$lpos, $l_w] = $this->last_position_neg[$index];
                    [$hpos, $vpos] = Graph::translatePosition($lpos);

                    if ($hpos === 'ol') {
                        $num_offset = new Number(-$l_w);

                        return ['middle outside left '.$num_offset.' 0', $target];
                    }
                }

                return ['outside left', $target];
            }
        }

        if ($label_w > $w && Graph::isPositionInside($pos)) {
            $pos = str_replace(['outside left', 'outside right'], 'centre', $pos);
        }

        if ($item->value > 0) {
            $this->last_position_pos[$index] = [$pos, $label_w];
        } else {
            $this->last_position_neg[$index] = [$pos, $label_w];
        }

        return [$pos, $target];
    }

    /**
     * Returns the ordering for legend entries.
     */
    public function getLegendOrder()
    {
        // bars are stacked from left to right
        return null;
    }
}
