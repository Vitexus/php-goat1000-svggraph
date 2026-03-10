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

class ExplodedSemiDonut3DGraph extends SemiDonut3DGraph
{
    use ExplodedPieGraphTrait;

    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        $fs = ['draw_flat_sides' => true];
        $fs = array_merge($fs, $fixed_settings);
        parent::__construct($w, $h, $settings, $fs);
    }

    /**
     * Returns an edge markup.
     *
     * @param mixed $edge
     * @param mixed $x_centre
     * @param mixed $y_centre
     * @param mixed $depth
     * @param mixed $overlay
     */
    protected function getEdge($edge, $x_centre, $y_centre, $depth, $overlay)
    {
        [$xo, $yo] = $this->pie_exploder->getExplode(
            $edge->slice['item'],
            $edge->slice['angle_start'] + $this->s_angle,
            $edge->slice['angle_end'] + $this->s_angle,
        );

        return parent::getEdge(
            $edge,
            $x_centre + $xo,
            $y_centre + $yo,
            $depth,
            $overlay,
        );
    }
}
