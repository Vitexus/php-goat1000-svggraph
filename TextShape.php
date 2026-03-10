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

class TextShape extends Shape
{
    protected $element = 'text';
    protected $required = ['text', 'x', 'y'];
    protected $transform_pairs = [['x', 'y']];

    /**
     * Override default attributes from Shape.
     */
    protected $attrs = [
        'fill' => '#000',
        'font_size' => 14,
        'font' => 'Arial',
    ];

    /**
     * Override to draw text element.
     *
     * @param mixed $graph
     * @param mixed $attributes
     */
    protected function drawElement(&$graph, &$attributes)
    {
        $content = $attributes['text'];
        $font = $attributes['font'];
        $attributes['font-size'] = Number::units($attributes['font-size']);
        $spacing = isset($attributes['line-spacing']) ?
          Number::units($attributes['line-spacing']) : $attributes['font-size'];
        $align = $attributes['text-align'] ?? '';

        // remove SVGGraph's shape options
        $unset_list = ['text', 'font', 'line-spacing', 'text-align'];

        foreach ($unset_list as $a) {
            unset($attributes[$a]);
        }

        $t = new Text($graph, $font);
        $align_map = ['right' => 'end', 'centre' => 'middle'];

        if (isset($align_map[$align])) {
            $attributes['text-anchor'] = $align_map[$align];
        }

        $attributes['font-family'] = $font;

        return $t->text($content, $spacing, $attributes);
    }
}
