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
 * For iterating over structured data.
 */
class StructuredDataIterator implements \Iterator
{
    private $data = 0;
    private $dataset = 0;
    private $position = 0;
    private $count = 0;
    private $structure;
    private $key_field = 0;
    private $dataset_fields = [];

    public function __construct(&$data, $dataset, $structure)
    {
        $this->dataset = $dataset;
        $this->data = &$data;
        $this->count = \count($data);
        $this->structure = $structure;

        $this->key_field = $structure['key'];
        $this->dataset_fields = $structure['value'];
    }

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
    }

    #[\ReturnTypeWillChange]
    public function rewind(): void
    {
        $this->position = 0;
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
        if (isset($this->data[$index])) {
            $key = $this->key_field === null ? $index : null;

            return new StructuredDataItem(
                $this->data[$index],
                $this->structure,
                $this->dataset,
                $key,
            );
        }

        return null;
    }

    /**
     * Returns an item by key.
     *
     * @param mixed $key
     */
    public function getItemByKey($key)
    {
        if ($this->key_field === null) {
            if (isset($this->data[$key])) {
                return new StructuredDataItem(
                    $this->data[$key],
                    $this->structure,
                    $this->dataset,
                    $key,
                );
            }

            return null;
        }

        foreach ($this->data as $item) {
            if (isset($item[$this->key_field]) && $item[$this->key_field] === $key) {
                return new StructuredDataItem(
                    $item,
                    $this->structure,
                    $this->dataset,
                    $key,
                );
            }
        }

        return null;
    }
}
