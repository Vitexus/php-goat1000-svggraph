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
 * Arbitrary shapes for adding to graphs.
 */
class ShapeList
{
    public const ABOVE = 1;
    public const BELOW = 0;
    private $graph;
    private $shapes = [];

    public function __construct(&$graph)
    {
        $this->graph = &$graph;
    }

    /**
     * Load shapes from options list.
     *
     * @param mixed $settings
     */
    public function load(&$settings): void
    {
        if (!isset($settings['shape'])) {
            return;
        }

        if (!\is_array($settings['shape']) || !isset($settings['shape'][0])) {
            throw new \Exception('Malformed shape option');
        }

        if (!\is_array($settings['shape'][0])) {
            $this->addShape($settings['shape']);

            return;
        }

        foreach ($settings['shape'] as $shape) {
            $this->addShape($shape);
        }
    }

    /**
     * Draw all the shapes for the selected depth.
     *
     * @param mixed $depth
     */
    public function draw($depth)
    {
        $content = [];

        foreach ($this->shapes as $shape) {
            if ($shape->depth($depth)) {
                $content[] = $shape->draw($this->graph);
            }
        }

        return implode('', $content);
    }

    /**
     * Returns a shape class.
     *
     * @param mixed $shape_array
     */
    public function getShape(&$shape_array)
    {
        $shape = $shape_array[0];
        unset($shape_array[0]);

        $class_map = [
            'circle' => 'Goat1000\\SVGGraph\\Circle',
            'ellipse' => 'Goat1000\\SVGGraph\\Ellipse',
            'rect' => 'Goat1000\\SVGGraph\\Rect',
            'line' => 'Goat1000\\SVGGraph\\Line',
            'polyline' => 'Goat1000\\SVGGraph\\PolyLine',
            'polygon' => 'Goat1000\\SVGGraph\\Polygon',
            'path' => 'Goat1000\\SVGGraph\\Path',
            'marker' => 'Goat1000\\SVGGraph\\MarkerShape',
            'figure' => 'Goat1000\\SVGGraph\\FigureShape',
            'image' => 'Goat1000\\SVGGraph\\Image',
            'text' => 'Goat1000\\SVGGraph\\TextShape',
        ];

        if (isset($class_map[$shape]) && class_exists($class_map[$shape])) {
            $depth = self::BELOW;

            if (isset($shape_array['depth']) && $shape_array['depth'] === 'above') {
                $depth = self::ABOVE;
            }

            if (isset($shape_array['clip_to_grid']) && $shape_array['clip_to_grid']
              && method_exists($this->graph, 'gridClipPath')) {
                $clip_id = $this->graph->gridClipPath();
                $shape_array['clip-path'] = 'url(#'.$clip_id.')';
            }

            unset($shape_array['depth'], $shape_array['clip_to_grid']);

            return new $class_map[$shape]($shape_array, $depth);
        }

        throw new \Exception('Unknown shape ['.$shape.']');
    }

    /**
     * Adds a shape from config array.
     *
     * @param mixed $shape_array
     */
    private function addShape(&$shape_array): void
    {
        $this->shapes[] = $this->getShape($shape_array);
    }
}
