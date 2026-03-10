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

class Line extends Shape
{
    protected $element = 'line';
    protected $required = ['x1', 'y1', 'x2', 'y2'];
    protected $transform_pairs = [['x1', 'y1'], ['x2', 'y2']];
}
