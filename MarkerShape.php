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

class MarkerShape extends Shape
{
    protected $element = 'use';
    protected $required = ['type', 'x', 'y'];
    protected $transform = ['size' => 'y'];
    protected $transform_from = ['size' => 'y'];
    protected $transform_pairs = [['x', 'y']];

    /**
     * Override to draw a marker.
     *
     * @param mixed $graph
     * @param mixed $attributes
     */
    protected function drawElement(&$graph, &$attributes)
    {
        $markers = new Markers($graph);
        $size = $attributes['size'] ?? 10;
        $stroke_width = $attributes['stroke-width'] ?? 1;
        $opacity = $attributes['opacity'] ?? 1;
        $angle = $attributes['angle'] ?? 0;
        $id = $markers->create(
            $attributes['type'],
            $size,
            $attributes['fill'],
            $stroke_width,
            $attributes['stroke'],
            $opacity,
            $angle,
        );

        $remove = ['type', 'size', 'fill', 'stroke', 'stroke-width',
            'opacity', 'angle'];
        $use = $attributes;

        foreach ($remove as $key) {
            unset($use[$key]);
        }

        // clip-path must be applied to <g> to prevent being offset with <use>
        if (isset($use['clip-path'])) {
            $group = ['clip-path' => $use['clip-path']];
            unset($use['clip-path']);
            $e = $graph->element(
                'g',
                $group,
                null,
                $graph->defs->useSymbol($id, $use),
            );
        } else {
            $e = $graph->defs->useSymbol($id, $use);
        }

        return $e;
    }
}
