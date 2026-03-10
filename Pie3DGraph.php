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

class Pie3DGraph extends PieGraph
{
    protected $edge_class = 'Goat1000\\SVGGraph\\PieSliceEdge';
    protected $depth;

    public function __construct($w, $h, array $settings, array $fixed_settings = [])
    {
        $fs = [
            // for 100% pie the flat sides are all hidden
            'draw_flat_sides' => false,
        ];
        $fs = array_merge($fs, $fixed_settings);
        parent::__construct($w, $h, $settings, $fs);

        $this->depth = $this->getOption('depth');
    }

    protected function draw()
    {
        // modify pad_bottom to make PieGraph do the hard work
        $pb = $this->pad_bottom;
        $space = $this->height - $this->pad_top - $this->pad_bottom;

        if ($space < $this->depth) {
            $this->depth = $space / 2;
        }

        $this->pad_bottom += $this->depth;
        $this->calc();
        $this->pad_bottom = $pb;

        // see if flat sides are visible
        if (is_numeric($this->end_angle)) {
            $start = fmod(deg2rad($this->start_angle), \M_PI * 2.0);
            $end = fmod(deg2rad($this->end_angle), \M_PI * 2.0);

            if ($this->getOption('reverse')) {
                if (($end > \M_PI * 0.5 && $end < \M_PI * 1.5)
                  || $start < \M_PI * 0.5 || $start > \M_PI * 1.5) {
                    $this->setOption('draw_flat_sides', true);
                }
            } else {
                if (($start > \M_PI * 0.5 && $start < \M_PI * 1.5)
                  || $end < \M_PI * 0.5 || $end > \M_PI * 1.5) {
                    $this->setOption('draw_flat_sides', true);
                }
            }
        }

        return PieGraph::draw();
    }

    /**
     * Returns the SVG markup to draw all slices.
     *
     * @param mixed $slice_list
     */
    protected function drawSlices($slice_list)
    {
        $edge_list = [];

        foreach ($slice_list as $key => $slice) {
            $edges = $this->getEdges($slice);

            if (!empty($edges)) {
                $edge_list = array_merge($edge_list, $edges);
            }
        }

        // should not be empty - that would mean no sides visible
        if (empty($edge_list)) {
            return parent::drawSlices($slice_list);
        }

        usort($edge_list, static function ($a, $b) {
            if ($a->y === $b->y) {
                return 0;
            }

            return $a->y < $b->y ? -1 : 1;
        });

        $edges = [];
        $overlay = \is_array($this->getOption('depth_shade_gradient'));

        foreach ($edge_list as $edge) {
            $edges[] = $this->getEdge(
                $edge,
                $this->x_centre,
                $this->y_centre,
                $this->depth,
                $overlay,
            );
        }

        return implode('', $edges).parent::drawSlices($slice_list);
    }

    /**
     * Returns the edges for the slice that face outwards.
     *
     * @param mixed $slice
     */
    protected function getEdges($slice)
    {
        return $this->edge_class::getEdges(
            $this,
            $slice,
            $this->s_angle,
            $this->getOption('draw_flat_sides'),
        );
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
        $item = $edge->slice['item'];
        $attr = [
            'fill' => $this->getColour(
                $item,
                $edge->slice['colour_index'],
                $this->dataset,
                false,
                false,
            ),
            'id' => $this->newID(),
        ];

        if ($this->getOption('show_tooltips')) {
            $this->setTooltip($attr, $item, $this->dataset, $item->key, $item->value, true);
        }

        if ($this->getOption('show_context_menu')) {
            $this->setContextMenu($attr, $this->dataset, $item, true);
        }

        $this->addLabelClient($this->dataset, $edge->slice['original_position'], $attr);

        $content = '';

        // the gradient overlay uses a clip-path
        if ($overlay) {
            $clip_id = $this->newID();
            $this->defs->add($edge->getClipPath(
                $this,
                $x_centre,
                $y_centre,
                $depth,
                $clip_id,
            ));

            // fill without stroking
            $attr['stroke'] = 'none';
            $content = $edge->draw($this, $x_centre, $y_centre, $depth, $attr);

            // overlay
            $content .= $this->getEdgeOverlay($x_centre, $y_centre, $depth, $clip_id, $edge);

            // stroke without filling
            unset($attr['stroke']);
            $attr['fill'] = 'none';
        }

        $content .= $edge->draw($this, $x_centre, $y_centre, $depth, $attr);

        return $this->getLink($item, $item->key, $content);
    }

    /**
     * Overlays the gradient on the pie sides.
     */
    protected function pieExtras()
    {
        // removed the overlay code because it drew over the stroked edges -
        // overlays are always drawn separately now
        return '';
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
        // use radius of whole pie unless slice values are set
        $radius_x = $this->radius_x;
        $radius_y = $this->radius_y;

        if ($edge->slice['radius_x'] && $edge->slice['radius_y']) {
            $radius_x = $edge->slice['radius_x'];
            $radius_y = $edge->slice['radius_y'];
        }

        // clip a rect to the edge shape
        $rect = [
            'x' => $x_centre - $radius_x,
            'y' => $y_centre - $radius_y,
            'width' => $radius_x * 2.0,
            'height' => $radius_y * 2.0 + $this->depth + 2.0,
            'clip-path' => 'url(#'.$clip_path.')',
        ];

        $gradient_id = $this->defs->addGradient($this->getOption('depth_shade_gradient'));

        if ($edge->curve()) {
            $rect['fill'] = 'url(#'.$gradient_id.')';
        } else {
            $a = $edge->angle();

            if ($a > \M_PI) {
                $a -= \M_PI;
            }

            $pos = 50 * cos($a);

            if ($pos < 0) {
                $pos = 100 + $pos;
            }

            $fill = $this->defs->getGradientColour($gradient_id, $pos);
            $opacity = $fill->opacity();

            if ($opacity < 0.01 || $fill->isNone()) {
                return '';
            }

            if ($opacity < 0.99) {
                $rect['opacity'] = $opacity;
            }

            $rect['fill'] = $fill;
        }

        return $this->element('rect', $rect);
    }
}
