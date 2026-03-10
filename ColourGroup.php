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

/**
 * Class for related stroke and fill colours.
 */
class ColourGroup
{
    private $stroke;

    public function __construct(
        &$graph,
        $item,
        $key,
        $dataset,
        $stroke_opt = 'stroke_colour',
        $fill = null,
        $item_opt = null,
        $stroke_opt_is_colour = false,
    ) {
        $stroke = $stroke_opt_is_colour ? $stroke_opt :
          $graph->getItemOption($stroke_opt, $dataset, $item, $item_opt);

        if (\is_array($stroke)) {
            $this->stroke = new Colour($graph, $stroke);

            return;
        }

        [$stroke_colour, $opacity, $filters] = $this->colourParts($stroke);

        // not a fill colour?
        if ($stroke_colour !== 'fill' && $stroke_colour !== 'fillColour') {
            $this->stroke = new Colour($graph, $stroke);

            return;
        }

        $allow_grad_pat = ($stroke_colour === 'fill');

        if ($fill !== null) {
            $stroke_colour = new Colour($graph, $fill, $allow_grad_pat, $allow_grad_pat);
        } else {
            $stroke_colour = $graph->getColour(
                $item,
                $key,
                $dataset,
                $allow_grad_pat,
                $allow_grad_pat,
            );
        }

        if ($stroke_colour->isNone()) {
            $stroke_colour = new Colour($graph, 'black');
        }

        $not_solid = $stroke_colour->isGradient() || $stroke_colour->isPattern();

        // if there are no modifications to make, we're done
        if ($not_solid || ($opacity >= 1 && $filters === '')) {
            $this->stroke = $stroke_colour;

            return;
        }

        $stroke = $stroke_colour->solid();

        if ($opacity < 1) {
            $stroke .= ':'.$opacity;
        }

        if ($filters !== '') {
            $stroke .= '/'.$filters;
        }

        $this->stroke = new Colour($graph, $stroke);
    }

    public function stroke()
    {
        return $this->stroke;
    }

    /**
     * Splits a colour into parts.
     *
     * @param mixed $colour
     */
    private static function colourParts($colour)
    {
        $opacity = 1;
        $filters = '';

        if (!empty($colour)) {
            // get opacity / filters
            $colour = (string)$colour;
            $spos = strpos($colour, '/');

            if ($spos !== false) {
                $filters = substr($colour, $spos + 1);
                $colour = substr($colour, 0, $spos);
            }

            $spos = strpos($colour, ':');

            if ($spos !== false) {
                $opacity = substr($colour, $spos + 1);
                $colour = substr($colour, 0, $spos);
            }
        }

        return [$colour, $opacity, $filters];
    }
}
