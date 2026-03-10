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

class CylinderGraph extends Bar3DGraph
{
    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        $this->bar_class = 'Goat1000\\SVGGraph\\Bar3DCylinder';
        parent::__construct($w, $h, $settings, $fixed_settings);
    }

    /**
     * Set the bar width and space.
     *
     * @param mixed $width
     * @param mixed $space
     */
    protected function setBarWidth($width, $space): void
    {
        parent::setBarWidth($width, $space);

        // translation for cylinders added to 3D bar offset
        [$sx, $sy] = $this->project(0, 0, $width);
        $this->tx += ($width + $sx) / 2;
        $this->ty += $sy / 2;
    }
}
