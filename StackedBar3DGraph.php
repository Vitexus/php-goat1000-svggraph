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

class StackedBar3DGraph extends Bar3DGraph
{
    use StackedBarTrait {
        setBarVisibility as traitSetBarVis;
    }

    public function __construct($w, $h, $settings, $fixed_settings = [])
    {
        $fixed = ['single_axis' => true];
        $fixed_settings = array_merge($fixed, $fixed_settings);
        parent::__construct($w, $h, $settings, $fixed_settings);
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
        $this->traitSetBarVis($dataset, $item, $top, $top || $item->value !== 0);
    }
}
