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

class PolyLine extends Shape
{
    protected $element = 'polyline';
    protected $required = ['points'];

    public function __construct(&$attrs, $depth)
    {
        parent::__construct($attrs, $depth);

        if (!\is_array($this->attrs['points'])) {
            $this->attrs['points'] = explode(' ', $this->attrs['points']);
        }

        $count = \count($this->attrs['points']);

        if ($count < 4 || $count % 2 === 1) {
            throw new \Exception('Shape must have at least 2 pairs of points');
        }
    }

    /**
     * Override to transform pairs of points.
     *
     * @param mixed $attributes
     */
    protected function transformCoordinates(&$attributes): void
    {
        $count = \count($attributes['points']);

        for ($i = 0; $i < $count; $i += 2) {
            $x = $attributes['points'][$i];
            $y = $attributes['points'][$i + 1];
            $coords = $this->coords->transformCoords($x, $y);
            $attributes['points'][$i] = $coords[0];
            $attributes['points'][$i + 1] = $coords[1];
        }
    }

    /**
     * Override to build the points attribute.
     *
     * @param mixed $graph
     * @param mixed $attributes
     */
    protected function drawElement(&$graph, &$attributes)
    {
        $attributes['points'] = implode(' ', $attributes['points']);

        return parent::drawElement($graph, $attributes);
    }
}
