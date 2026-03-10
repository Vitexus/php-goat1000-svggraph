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

class Image extends Shape
{
    protected $element = 'image';
    protected $required = ['src', 'x', 'y'];
    protected $transform = ['width' => 'x', 'height' => 'y'];
    protected $transform_from = ['width' => 'x', 'height' => 'y'];
    protected $transform_pairs = [['x', 'y']];
    protected $attrs = ['preserveAspectRatio' => 'xMinYMin'];

    /**
     * Override to draw an image.
     *
     * @param mixed $graph
     * @param mixed $attributes
     */
    protected function drawElement(&$graph, &$attributes)
    {
        $attributes['xlink:href'] = $attributes['src'];

        if (isset($attributes['stretch']) && $attributes['stretch']) {
            $attributes['preserveAspectRatio'] = 'none';
        }

        unset($attributes['src'], $attributes['stretch']);

        return parent::drawElement($graph, $attributes);
    }
}
