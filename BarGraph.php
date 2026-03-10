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

class BarGraph extends GridGraph
{
    use BarGraphTrait;

    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        // backwards compatibility
        if (isset($settings['show_bar_labels']) && !isset($settings['show_data_labels'])) {
            $settings['show_data_labels'] = $settings['show_bar_labels'];
        }

        $fs = ['label_centre' => !isset($settings['datetime_keys'])];
        $fs = array_merge($fs, $fixed_settings);
        parent::__construct($w, $h, $settings, $fs);
    }
}
