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

trait SVGGraphTrait
{
    /**
     * Instantiate the correct class.
     *
     * @param mixed $class
     */
    private function setup($class)
    {
        if (!strstr($class, '\\')) {
            $class = __NAMESPACE__.'\\'.$class;
        }

        if (!class_exists($class)) {
            throw new \InvalidArgumentException('Unknown graph type: '.$class);
        }

        if (!is_subclass_of($class, '\\Goat1000\\SVGGraph\\Graph')) {
            throw new \InvalidArgumentException('Not a graph class: '.$class);
        }

        $g = new $class($this->width, $this->height, $this->settings);
        $g->subgraph = $this->subgraph;
        $g->values($this->values);
        $g->links($this->links);
        $g->colours($this->colours);
        $g->subgraphs($this->subgraphs);

        return $g;
    }

    /**
     * Assign values, either from an array or from numeric arguments.
     *
     * @param mixed $values
     */
    public function values($values): void
    {
        $this->values = \is_array($values) ? $values : \func_get_args();
    }

    /**
     * Assign links to data items.
     *
     * @param mixed $links
     */
    public function links($links): void
    {
        $this->links = \is_array($links) ? $links : \func_get_args();
    }

    /**
     * Assign a single colour set for use across datasets.
     *
     * @param mixed $colours
     */
    public function colours($colours): void
    {
        $this->colours = new Colours($colours);
    }

    /**
     * Sets colours for a single dataset.
     *
     * @param mixed $dataset
     * @param mixed $colours
     */
    public function colourSet($dataset, $colours): void
    {
        $this->colours->set($dataset, $colours);
    }

    /**
     * Sets up RGB colour range.
     *
     * @param mixed $dataset
     * @param mixed $r1
     * @param mixed $g1
     * @param mixed $b1
     * @param mixed $r2
     * @param mixed $g2
     * @param mixed $b2
     */
    public function colourRangeRGB($dataset, $r1, $g1, $b1, $r2, $g2, $b2): void
    {
        $this->colours->rangeRGB($dataset, $r1, $g1, $b1, $r2, $g2, $b2);
    }

    /**
     * RGB colour range from hex codes.
     *
     * @param mixed $dataset
     * @param mixed $c1
     * @param mixed $c2
     */
    public function colourRangeHexRGB($dataset, $c1, $c2): void
    {
        $this->colours->rangeHexRGB($dataset, $c1, $c2);
    }

    /**
     * Sets up HSL colour range.
     *
     * @param mixed $dataset
     * @param mixed $h1
     * @param mixed $s1
     * @param mixed $l1
     * @param mixed $h2
     * @param mixed $s2
     * @param mixed $l2
     * @param mixed $reverse
     */
    public function colourRangeHSL(
        $dataset,
        $h1,
        $s1,
        $l1,
        $h2,
        $s2,
        $l2,
        $reverse = false,
    ): void {
        $this->colours->rangeHSL($dataset, $h1, $s1, $l1, $h2, $s2, $l2, $reverse);
    }

    /**
     * HSL colour range from hex codes.
     *
     * @param mixed $dataset
     * @param mixed $c1
     * @param mixed $c2
     * @param mixed $reverse
     */
    public function colourRangeHexHSL($dataset, $c1, $c2, $reverse = false): void
    {
        $this->colours->rangeHexHSL($dataset, $c1, $c2, $reverse);
    }

    /**
     * Sets up HSL colour range from RGB values.
     *
     * @param mixed $dataset
     * @param mixed $r1
     * @param mixed $g1
     * @param mixed $b1
     * @param mixed $r2
     * @param mixed $g2
     * @param mixed $b2
     * @param mixed $reverse
     */
    public function colourRangeRGBtoHSL(
        $dataset,
        $r1,
        $g1,
        $b1,
        $r2,
        $g2,
        $b2,
        $reverse = false,
    ): void {
        $this->colours->rangeRGBtoHSL(
            $dataset,
            $r1,
            $g1,
            $b1,
            $r2,
            $g2,
            $b2,
            $reverse,
        );
    }

    /**
     * Returns a sub-graph.
     *
     * @param mixed      $type
     * @param mixed      $x
     * @param mixed      $y
     * @param mixed      $w
     * @param mixed      $h
     * @param null|mixed $settings
     * @param null|mixed $extra
     */
    public function subgraph(
        $type,
        $x,
        $y,
        $w,
        $h,
        $settings = null,
        $extra = null,
    ) {
        if (!\is_string($x) && $x < 0) {
            $x = $this->width + $x;
        }

        if (!\is_string($y) && $y < 0) {
            $y = $this->height + $y;
        }

        if (!\is_string($w) && $w <= 0) {
            $w = $this->width - $x + $w;
        }

        if (!\is_string($h) && $h <= 0) {
            $h = $this->height - $y + $h;
        }

        if ($settings === null) {
            $settings = $this->settings;
        }

        if (\is_array($extra)) {
            $settings = array_merge($settings, $extra);
        }

        $sg = new Subgraph($type, $x, $y, $w, $h, $settings);
        $sg->setColours(clone $this->colours);
        $this->subgraphs[] = $sg;

        return $sg;
    }

    /**
     * Replaces the list of subgraphs.
     *
     * @param mixed $list
     */
    public function setSubgraphs($list): void
    {
        $this->subgraphs = $list;
    }
}
