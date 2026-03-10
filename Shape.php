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

abstract class Shape
{
    protected $depth = ShapeList::BELOW;
    protected $element = '';
    protected $link;
    protected $link_target = '_blank';
    protected $coords;
    protected $autohide = false;

    /**
     * attributes required to draw shape.
     */
    protected $required = [];

    /**
     * attributes that support coordinate transformation.
     */
    protected $transform = [];

    /**
     * map attributes to be transformed to the attribute they are relative to
     * - 'attr_relative' => 'attr_fixed'.
     */
    protected $transform_from = [];

    /**
     * coordinate pairs for dependent transforns - don't include them in
     * $transform or they will be transformed twice.
     */
    protected $transform_pairs = [];

    /**
     * default attributes for all shapes.
     */
    protected $attrs = [
        'stroke' => '#000',
        'fill' => 'none',
    ];

    /**
     * colour gradients/patterns, and whether to allow gradients.
     */
    private $colour_convert = [
        'stroke' => false,
        'fill' => true,
    ];

    public function __construct(&$attrs, $depth)
    {
        $this->attrs = array_merge($this->attrs, $attrs);
        $this->depth = $depth;

        $missing = [];

        foreach ($this->required as $opt) {
            if (!isset($this->attrs[$opt])) {
                $missing[] = $opt;
            }
        }

        if (\count($missing)) {
            throw new \Exception($this->element.' attribute(s) not found: '.
              implode(', ', $missing));
        }

        if (isset($this->attrs['href'])) {
            $this->link = $this->attrs['href'];
        }

        if (isset($this->attrs['xlink:href'])) {
            $this->link = $this->attrs['xlink:href'];
        }

        if (isset($this->attrs['target'])) {
            $this->link_target = $this->attrs['target'];
        }

        if (isset($this->attrs['autohide'])) {
            $hide = 0;
            $show = $this->attrs['opacity'] ?? 1;

            if (isset($this->attrs['autohide_opacity'])) {
                if (\is_array($this->attrs['autohide_opacity'])) {
                    [$hide, $show] = $this->attrs['autohide_opacity'];
                } else {
                    $hide = $this->attrs['autohide_opacity'];
                }
            }

            $this->autohide = [$hide, $show];
        }

        $clean = ['href', 'xlink:href', 'target', 'autohide', 'autohide_opacity'];

        foreach ($clean as $att) {
            unset($this->attrs[$att]);
        }
    }

    /**
     * returns true if the depth is correct.
     *
     * @param mixed $d
     */
    public function depth($d)
    {
        return $this->depth === $d;
    }

    /**
     * draws the shape.
     *
     * @param mixed $graph
     */
    public function draw(&$graph)
    {
        $this->coords = new Coords($graph);

        $attributes = [];

        foreach ($this->attrs as $attr => $value) {
            if ($value !== null) {
                $val = $value;

                if (isset($this->transform[$attr])) {
                    $measure_from = 0;

                    if (isset($this->transform_from[$attr])) {
                        $measure_from = $this->attrs[$this->transform_from[$attr]];
                    }

                    $val = $this->coords->transform($value, $this->transform[$attr], 0, $measure_from);
                } elseif (isset($this->colour_convert[$attr])) {
                    $val = new Colour($graph, $value, $this->colour_convert[$attr]);
                }

                $attr = str_replace('_', '-', $attr);
                $attributes[$attr] = $val;
            }
        }

        $this->transformCoordinates($attributes);

        if ($this->autohide) {
            $graph->getJavascript()->autoHide(
                $attributes,
                $this->autohide[0],
                $this->autohide[1],
            );
        }

        $element = $this->drawElement($graph, $attributes);

        if ($this->link !== null) {
            $link = ['xlink:href' => $this->link];

            if ($this->link_target !== null) {
                $link['target'] = $this->link_target;
            }

            $element = $graph->element('a', $link, null, $element);
        }

        return $element;
    }

    /**
     * Transform coordinate pairs.
     *
     * @param mixed $attr
     */
    protected function transformCoordinates(&$attr): void
    {
        if (empty($this->transform_pairs)) {
            return;
        }

        foreach ($this->transform_pairs as $pair) {
            [$x, $y] = $pair;
            $coords = $this->coords->transformCoords($attr[$x], $attr[$y]);
            [$attr[$x], $attr[$y]] = $coords;
        }
    }

    /**
     * Performs the conversion to SVG fragment.
     *
     * @param mixed $graph
     * @param mixed $attributes
     */
    protected function drawElement(&$graph, &$attributes)
    {
        return $graph->element($this->element, $attributes);
    }
}
