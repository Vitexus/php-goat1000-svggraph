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

class StackedBarGraph extends BarGraph
{
    use StackedBarTrait;

    public function __construct($w, $h, $settings, $fixed_settings = [])
    {
        $fixed = ['single_axis' => true];
        $fixed_settings = array_merge($fixed, $fixed_settings);
        parent::__construct($w, $h, $settings, $fixed_settings);
    }
}
