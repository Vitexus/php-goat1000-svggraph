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
 * A class to hold the details of an entry in the legend.
 */
class LegendEntry
{
    public $item;
    public $text;
    public $link;
    public $style;
    public $width = 0;
    public $height = 0;

    public function __construct($item, $text, $link, $style)
    {
        $this->item = $item;
        $this->text = $text;
        $this->link = $link;
        $this->style = $style;
    }
}
