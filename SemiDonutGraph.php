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

class SemiDonutGraph extends DonutGraph
{
    /**
     * Override to set the options for semi-ness.
     *
     * @param mixed $w
     * @param mixed $h
     */
    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        $reverse = isset($settings['reverse']) && $settings['reverse'];
        $flipped = isset($settings['flipped']) && $settings['flipped'];

        $start = 180;

        if ($flipped) {
            $start = 0;
            $reverse = !$reverse;
        }

        if ($reverse) {
            $start += 180;
        }

        $fixed = [
            'reverse' => $reverse,
            'start_angle' => $start,
            'end_angle' => $start + 180,
            'slice_fit' => true,
        ];
        $fixed_settings = array_merge($fixed, $fixed_settings);
        parent::__construct($w, $h, $settings, $fixed_settings);
    }

    /**
     * Overridden to keep inner text in the middle.
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
        if ($dataset === 'innertext') {
            if ($this->getOption('flipped')) {
                $y_offset = new Number($label_h / 2);
            } else {
                $y_offset = new Number($label_h / -2);
            }

            return ['centre middle 0 '.$y_offset, [$x, $y]];
        }

        return parent::dataLabelPosition(
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
    }
}
