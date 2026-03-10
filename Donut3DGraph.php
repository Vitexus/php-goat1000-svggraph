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

class Donut3DGraph extends Pie3DGraph
{
    use DonutGraphTrait;
    protected $edge_class = 'Goat1000\\SVGGraph\\DonutSliceEdge';

    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        $fs = [];

        // enable flat sides when drawing a gap
        if (isset($settings['donut_slice_gap']) && $settings['donut_slice_gap'] > 0) {
            $fs['draw_flat_sides'] = true;
        }

        $fs = array_merge($fs, $fixed_settings);
        parent::__construct($w, $h, $settings, $fs);
    }

    /**
     * Returns the gradient overlay.
     *
     * @param mixed $x_centre
     * @param mixed $y_centre
     * @param mixed $depth
     * @param mixed $clip_path
     * @param mixed $edge
     */
    protected function getEdgeOverlay($x_centre, $y_centre, $depth, $clip_path, $edge)
    {
        if (!$edge->inner()) {
            return parent::getEdgeOverlay($x_centre, $y_centre, $depth, $clip_path, $edge);
        }

        // use radius of whole pie unless slice values are set
        $radius_x = $this->radius_x;
        $radius_y = $this->radius_y;

        if ($edge->slice['radius_x'] && $edge->slice['radius_y']) {
            $radius_x = $edge->slice['radius_x'];
            $radius_y = $edge->slice['radius_y'];
        }

        $ratio = $edge->getInnerRatio();
        $radius_x *= $ratio;
        $radius_y *= $ratio;

        // clip a gradient-filled rect to the edge shape
        $cx = new Number($x_centre);
        $cy = new Number($y_centre);
        $gradient_id = $this->defs->addGradient($this->getOption('depth_shade_gradient'));
        $rect = [
            'x' => $x_centre - $radius_x,
            'y' => $y_centre - $radius_y,
            'width' => $radius_x * 2.0,
            'height' => $radius_y * 2.0 + $this->depth + 2.0,
            'fill' => 'url(#'.$gradient_id.')',
            // rotate the rect to reverse the gradient
            'transform' => "rotate(180,{$cx},{$cy})",
        ];

        // clip a group containing the rotated rect
        $g = ['clip-path' => 'url(#'.$clip_path.')'];

        return $this->element('g', $g, null, $this->element('rect', $rect));
    }
}
