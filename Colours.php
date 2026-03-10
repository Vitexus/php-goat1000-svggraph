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

class Colours implements \Countable
{
    private $colours = [];
    private $dataset_count = 0;
    private $fallback = false;
    private $max_index = 1;
    private $reverse = false;

    /**
     * Constructor sets up fallback colour array in case per-dataset
     * functions are not used.
     *
     * @param null|mixed $colours
     */
    public function __construct($colours = null)
    {
        if (\is_array($colours)) {
            $this->fallback = $colours;
        } else {
            $this->fallback = [
                  '#11c', '#c11', '#cc1', '#1c1', '#c81',
                  '#116', '#611', '#661', '#161', '#631',
              ];
        }
    }

    /**
     * Setup based on graph requirements.
     *
     * @param mixed      $count
     * @param null|mixed $datasets
     * @param mixed      $reverse
     */
    public function setup($count, $datasets = null, $reverse = false): void
    {
        if ($this->fallback !== false) {
            if ($datasets !== null) {
                foreach ($this->fallback as $colour) {
                    // in fallback, each dataset gets one colour
                    $this->colours[] = new ColourArray([$colour]);
                }
            } else {
                $this->colours[] = new ColourArray($this->fallback);
            }

            $this->dataset_count = \count($this->colours);
        }

        foreach ($this->colours as $clist) {
            $clist->setup($count);
        }

        $this->max_index = $count - 1;
        $this->reverse = $reverse;
    }

    /**
     * Returns the colour for an index and dataset.
     *
     * @param mixed      $index
     * @param null|mixed $dataset
     */
    public function getColour($index, $dataset = null)
    {
        // default is for a colour per dataset
        if ($dataset === null) {
            $dataset = 0;
        }

        if ($this->reverse) {
            $index = $this->max_index - $index;
        }

        // see if specific dataset exists
        if (\array_key_exists($dataset, $this->colours)) {
            return $this->colours[$dataset][$index];
        }

        // try mod
        if (is_numeric($dataset)) {
            $dataset %= $this->dataset_count;
        }

        if (\array_key_exists($dataset, $this->colours)) {
            return $this->colours[$dataset][$index];
        }

        // just use first dataset
        reset($this->colours);
        $clist = current($this->colours);

        return $clist[$index];
    }

    /**
     * Implement Countable to make it non-countable.
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        throw new \Exception('Cannot count Colours class');

        return 0;
    }

    /**
     * Assign a colour array for a dataset.
     *
     * @param mixed $dataset
     * @param mixed $colours
     */
    public function set($dataset, $colours): void
    {
        if ($colours === null) {
            if (\array_key_exists($dataset, $this->colours)) {
                unset($this->colours[$dataset]);
            }

            return;
        }

        $this->setDataset($dataset, new ColourArray($colours));
    }

    /**
     * Set up RGB colour range.
     *
     * @param mixed $dataset
     * @param mixed $r1
     * @param mixed $g1
     * @param mixed $b1
     * @param mixed $r2
     * @param mixed $g2
     * @param mixed $b2
     */
    public function rangeRGB($dataset, $r1, $g1, $b1, $r2, $g2, $b2): void
    {
        $this->setDataset(
            $dataset,
            new ColourRangeRGB($r1, $g1, $b1, $r2, $g2, $b2),
        );
    }

    /**
     * HSL colour range, with option to go the long way.
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
    public function rangeHSL(
        $dataset,
        $h1,
        $s1,
        $l1,
        $h2,
        $s2,
        $l2,
        $reverse = false,
    ): void {
        $rng = new ColourRangeHSL($h1, $s1, $l1, $h2, $s2, $l2);

        if ($reverse) {
            $rng->reverse();
        }

        $this->setDataset($dataset, $rng);
    }

    /**
     * HSL colour range from RGB values, with option to go the long way.
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
    public function rangeRGBtoHSL(
        $dataset,
        $r1,
        $g1,
        $b1,
        $r2,
        $g2,
        $b2,
        $reverse = false,
    ): void {
        $rng = ColourRangeHSL::fromRGB($r1, $g1, $b1, $r2, $g2, $b2);

        if ($reverse) {
            $rng->reverse();
        }

        $this->setDataset($dataset, $rng);
    }

    /**
     * RGB colour range from two RGB hex codes.
     *
     * @param mixed $dataset
     * @param mixed $c1
     * @param mixed $c2
     */
    public function rangeHexRGB($dataset, $c1, $c2): void
    {
        [$r1, $g1, $b1] = $this->hexRGB($c1);
        [$r2, $g2, $b2] = $this->hexRGB($c2);
        $this->rangeRGB($dataset, $r1, $g1, $b1, $r2, $g2, $b2);
    }

    /**
     * HSL colour range from RGB hex codes.
     *
     * @param mixed $dataset
     * @param mixed $c1
     * @param mixed $c2
     * @param mixed $reverse
     */
    public function rangeHexHSL($dataset, $c1, $c2, $reverse = false): void
    {
        [$r1, $g1, $b1] = $this->hexRGB($c1);
        [$r2, $g2, $b2] = $this->hexRGB($c2);
        $this->rangeRGBtoHSL($dataset, $r1, $g1, $b1, $r2, $g2, $b2, $reverse);
    }

    /**
     * Convert a colour code to RGB array.
     *
     * @param mixed $c
     */
    public static function hexRGB($c)
    {
        // support filters, other colour formats by using Colour class
        $graph = null;
        $cc = new Colour($graph, $c, false, false, false);

        return $cc->rgb();
    }

    /**
     * Set an entry in the colours array.
     *
     * @param mixed $dataset
     * @param mixed $colours
     */
    private function setDataset($dataset, $colours): void
    {
        if ($this->fallback) {
            // use fallback for dataset 0 if not already set
            if ($dataset !== 0) {
                $this->colours[0] = new ColourArray($this->fallback);
            }

            $this->fallback = false;
        }

        $this->colours[$dataset] = $colours;
        $this->dataset_count = \count($this->colours);
    }
}
