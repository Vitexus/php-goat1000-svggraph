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
 * Data class for SVG path data.
 */
class PathData implements \Stringable
{
    private $parts = '';
    private $last = '';

    /**
     * Constructs a path from another PathData, elements or an array of elements.
     */
    public function __construct()
    {
        $segments = \func_get_args();
        $num = \count($segments);

        if ($num) {
            if ($num > 1) {
                $this->add($segments);

                return;
            }

            $this->add($segments[0]);
        }
    }

    /**
     * Converts the path segments to a string.
     */
    public function __toString()
    {
        // already contains a string, this just reduces whitespace
        return preg_replace(['/ ([a-zA-Z])/', '/([a-zA-Z]) /'], '$1', $this->parts);
    }

    /**
     * Adds segments to the path.
     *
     * @param mixed $e
     */
    public function add($e): void
    {
        $segments = \func_get_args();

        if (\count($segments) < 1) {
            throw new \Exception('No segments to add');
        }

        if (\count($segments) === 1) {
            $e = $segments[0];

            // add another PathData
            if (\is_object($e) && $e::class === 'Goat1000\\SVGGraph\\PathData') {
                $this->parts .= ' '.$e->parts;
                $this->last = $e->last;

                return;
            }

            if (\is_array($e)) {
                $segments = $e;
            }
        }

        $this->addSegments($segments);
    }

    /**
     * Returns true if the path has no segments.
     */
    public function isEmpty()
    {
        return $this->parts === '';
    }

    /**
     * Clears any existing segments.
     */
    public function clear(): void
    {
        $this->parts = '';
        $this->last = '';
    }

    /**
     * Adds an array of segments.
     *
     * @param mixed $segments
     */
    private function addSegments($segments): void
    {
        $last = $this->last;
        $parts = '';

        foreach ($segments as $part) {
            if (\is_object($part)) {
                if ($part::class === 'Goat1000\\SVGGraph\\PathData') {
                    throw new \InvalidArgumentException('PathData in segment list. Use separate add() calls.');
                }

                $parts .= ' '.$part;

                continue;
            }

            if (!is_numeric($part)) {
                // skip duplicate path commands
                if ($part === $last) {
                    continue;
                }

                $last = $part;
                $parts .= $part;

                continue;
            }

            $parts .= ' '.new Number($part);
        }

        if ($parts !== '') {
            $this->last = $last;
            $this->parts .= $parts;
        }
    }
}
