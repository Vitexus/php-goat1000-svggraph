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

class ExplodedSemiDonutGraph extends SemiDonutGraph
{
    use ExplodedPieGraphTrait {
        dataLabelPosition as protected traitDLP;
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

        return $this->traitDLP(
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
