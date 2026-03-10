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

trait ExplodedPieGraphTrait
{
    protected $pie_exploder;
    protected $explode_amount = 0;

    /**
     * Returns the position for the label.
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

        if (isset($this->slice_info[$index])) {
            [$xo, $yo] = $this->pie_exploder->getExplode(
                $item,
                $this->slice_info[$index]->start_angle + $this->s_angle,
                $this->slice_info[$index]->end_angle + $this->s_angle,
            );

            [$x1, $y1] = explode(' ', $pos);

            if (is_numeric($x1) && is_numeric($y1)) {
                $x1 += $xo;
                $y1 += $yo;
            } else {
                $x1 = $xo;
                $y1 = $yo;
            }

            // explode target position too
            $target[0] += $xo;
            $target[1] += $yo;

            $pos = new Number($x1).' '.new Number($y1);
        } else {
            $pos = 'middle centre';
        }

        return [$pos, $target];
    }

    /**
     * Calculates reduced radius of pie.
     */
    protected function calc(): void
    {
        parent::calc();
        $this->explode_amount = $this->pie_exploder->fixRadii(
            $this->radius_x,
            $this->radius_y,
        );
    }

    /**
     * Returns a single slice of pie.
     *
     * @param mixed $item
     * @param mixed $angle_start
     * @param mixed $angle_end
     * @param mixed $radius_x
     * @param mixed $radius_y
     * @param mixed $attr
     * @param mixed $single_slice
     * @param mixed $colour_index
     */
    protected function getSlice(
        $item,
        $angle_start,
        $angle_end,
        $radius_x,
        $radius_y,
        &$attr,
        $single_slice,
        $colour_index,
    ) {
        if ($single_slice) {
            return parent::getSlice(
                $item,
                $angle_start,
                $angle_end,
                $radius_x,
                $radius_y,
                $attr,
                $single_slice,
                $colour_index,
            );
        }

        // find and apply explosiveness
        [$xo, $yo] = $this->pie_exploder->getExplode($item, $angle_start +
          $this->s_angle, $angle_end + $this->s_angle);

        $translated = $attr;
        $xform = new Transform();
        $xform->translate($xo, $yo);

        if (isset($translated['transform'])) {
            $translated['transform']->add($xform);
        } else {
            $translated['transform'] = $xform;
        }

        return parent::getSlice(
            $item,
            $angle_start,
            $angle_end,
            $radius_x,
            $radius_y,
            $translated,
            $single_slice,
            $colour_index,
        );
    }

    /**
     * Checks that the data are valid.
     */
    protected function checkValues(): void
    {
        parent::checkValues();

        $largest = $this->getMaxValue();
        $smallest = $largest;

        // want smallest non-0 value
        foreach ($this->values[0] as $item) {
            if ($item->value < $smallest) {
                $smallest = $item->value;
            }
        }

        $this->pie_exploder = new PieExploder($this, $smallest, $largest);
    }
}
