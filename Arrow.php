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
 * A class for drawing arrows.
 */
class Arrow
{
    protected $a;
    protected $b;
    protected $head_size = 7;
    protected $head_colour = '#000';

    public function __construct(Point $a, Point $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * Sets the arrow head size (min 2 pixels).
     *
     * @param mixed $size
     */
    public function setHeadSize($size): void
    {
        $this->head_size = max(2, $size);
    }

    public function setHeadColour($colour): void
    {
        $this->head_colour = $colour;
    }

    /**
     * Returns the arrow element.
     *
     * @param mixed      $graph
     * @param null|mixed $style
     */
    public function draw($graph, $style = null)
    {
        $head_id = $this->getArrowHead($graph);
        $p = $this->getArrowPath();

        $path = [
            'd' => $p,
            'marker-end' => 'url(#'.$head_id.')',
            'fill' => 'none',
        ];

        if (\is_array($style)) {
            $path = array_merge($style, $path);
        }

        return $graph->element('path', $path);
    }

    /**
     * Returns the arrow head as the ID of a <marker> element.
     *
     * @param mixed $graph
     */
    protected function getArrowHead($graph)
    {
        $sz = new Number($this->head_size);
        $point = 75; // sharpness of arrow
        $marker = [
            'viewBox' => "0 0 {$point} 100",
            'markerWidth' => $sz,
            'markerHeight' => $sz,
            'refX' => $point,
            'refY' => 50,
            'orient' => 'auto',
        ];
        $pd = new PathData('M', 0, 0, 'L', $point, 50, 'L', 0, 100, 'z');
        $path = [
            'd' => $pd,
            'stroke' => $this->head_colour,
            'fill' => $this->head_colour,
        ];
        $marker_content = $graph->element('path', $path);

        return $graph->defs->addElement('marker', $marker, $marker_content);
    }

    /**
     * Returns the PathData for an arrow line.
     */
    protected function getArrowPath()
    {
        return new PathData('M', $this->a, $this->b);
    }
}
