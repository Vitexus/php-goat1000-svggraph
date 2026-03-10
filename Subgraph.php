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

class Subgraph
{
    use SVGGraphTrait;
    private $type;
    private $x;
    private $y;
    private $width;
    private $height;
    private $settings;
    private $values = [];
    private $links;
    private $colours;
    private $subgraph = true;
    private $subgraphs = [];

    public function __construct($type, $x, $y, $w, $h, $settings)
    {
        $this->type = $type;
        $this->x = $x;
        $this->y = $y;
        $this->width = $w;
        $this->height = $h;

        if ($settings === null) {
            $settings = [];
        }

        $this->settings = $settings;
        $this->colours = new Colours();
    }

    /**
     * Used to duplicate parent graph's colours.
     *
     * @param mixed $colours
     */
    public function setColours($colours): void
    {
        $this->colours = $colours;
    }

    /**
     * Fetches the graph content.
     *
     * @param mixed $parent
     */
    public function fetch($parent)
    {
        // transform any non-numeric dimensions
        if (\is_string($this->x) || \is_string($this->y)
          || \is_string($this->width) || \is_string($this->height)) {
            $coords = new Coords($parent);

            [$this->x, $this->y] = $coords->transformCoords($this->x, $this->y);
            $this->width = $coords->transform($this->width, 'x');
            $this->height = $coords->transform($this->height, 'y');
        }

        // pass position as settings
        $this->settings['graph_x'] = $this->x;
        $this->settings['graph_y'] = $this->y;

        // no header, defer javascript
        $graph = $this->setup($this->type);

        return $graph->fetch(false, true);
    }
}
