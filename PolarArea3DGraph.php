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

class PolarArea3DGraph extends Pie3DGraph
{
    use PolarAreaTrait;

    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        $fs = [
            'repeated_keys' => 'error',
            'draw_flat_sides' => true,
            // no sorting, no percentage, no slice fit
            'sort' => false,
            'show_label_percent' => false,
            'slice_fit' => false,
        ];
        $fs = array_merge($fs, $fixed_settings);
        parent::__construct($w, $h, $settings, $fs);
    }
}
