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

class Figures
{
    private $graph;
    private $settings;
    private $loaded = false;
    private $figure_map = [];

    public function __construct(&$graph, &$settings)
    {
        $this->graph = &$graph;
        $this->settings = &$settings;
    }

    /**
     * Load figures from options list.
     */
    public function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        if (!isset($this->settings['figure'])) {
            return;
        }

        if (!\is_array($this->settings['figure'])
          || !isset($this->settings['figure'][0])) {
            throw new \Exception('Malformed figure option.');
        }

        if (!\is_array($this->settings['figure'][0])) {
            $this->addFigure($this->settings['figure']);
        } else {
            foreach ($this->settings['figure'] as $figure) {
                $this->addFigure($figure);
            }
        }
    }

    /**
     * Returns a figure's symbol ID by name.
     *
     * @param mixed $name
     */
    public function getFigure($name)
    {
        $this->load();

        if (isset($this->figure_map[$name])) {
            return $this->figure_map[$name];
        }

        return null;
    }

    /**
     * Adds a figure to the list.
     *
     * @param mixed $figure_array
     */
    private function addFigure($figure_array): void
    {
        $name = array_shift($figure_array);

        if (isset($this->figure_map[$name])) {
            throw new \Exception('Figure ['.$name.'] defined more than once.');
        }

        $content = '';
        $shapes = $this->graph->getShapeList();

        if (!\is_array($figure_array[0])) {
            $shape = $shapes->getShape($figure_array);
            $content .= $shape->draw($this->graph);
        } else {
            foreach ($figure_array as $s) {
                $shape = $shapes->getShape($s);
                $content .= $shape->draw($this->graph);
            }
        }

        $id = $this->graph->defs->defineSymbol($content);
        $this->figure_map[$name] = $id;
    }
}
