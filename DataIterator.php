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
 * Class to iterate over standard data.
 */
class DataIterator implements \Iterator
{
    private $data = 0;
    private $dataset = 0;
    private $position = 0;
    private $count = 0;

    public function __construct(&$data, $dataset)
    {
        $this->dataset = $dataset;
        $this->data = &$data;
        $this->count = \count($data[$dataset]);
    }

    /**
     * Iterator methods.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->getItemByIndex($this->position);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    #[\ReturnTypeWillChange]
    public function next(): void
    {
        ++$this->position;
        next($this->data[$this->dataset]);
    }

    #[\ReturnTypeWillChange]
    public function rewind(): void
    {
        $this->position = 0;
        reset($this->data[$this->dataset]);
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->position < $this->count;
    }

    /**
     * Returns an item by index.
     *
     * @param mixed $index
     */
    public function getItemByIndex($index)
    {
        $slice = \array_slice($this->data[$this->dataset], $index, 1, true);

        // use foreach to get key and value
        foreach ($slice as $k => $v) {
            return new DataItem($k, $v);
        }

        return null;
    }

    /**
     * Returns an item by its key.
     *
     * @param mixed $key
     */
    public function getItemByKey($key)
    {
        if (isset($this->data[$this->dataset][$key])) {
            return new DataItem($key, $this->data[$this->dataset][$key]);
        }

        return null;
    }
}
