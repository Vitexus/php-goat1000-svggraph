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
 * Class for structured data items.
 */
class StructuredDataItem extends DataItem
{
    public $key = 0;
    public $value;
    private $item;
    private $dataset = 0;
    private $key_field = 0;
    private $dataset_fields = [];
    private $structure;

    public function __construct($item, &$structure, $dataset, $key = null)
    {
        $this->item = $item;
        $this->key_field = $structure['key'];
        $this->dataset_fields = $structure['value'];
        $this->key = $this->key_field === null ? $key : $item[$this->key_field];

        if (isset($this->dataset_fields[$dataset], $item[$this->dataset_fields[$dataset]])
        ) {
            $this->value = $item[$this->dataset_fields[$dataset]];
        }

        $this->dataset = $dataset;
        $this->structure = &$structure;
    }

    /**
     * Getter for data fields.
     *
     * @param mixed $field
     */
    public function __get($field)
    {
        return $this->data($field);
    }

    /**
     * Tests if a field is set.
     *
     * @param mixed $field
     */
    public function __isset($field)
    {
        if (!isset($this->structure[$field])) {
            return false;
        }

        $item_field = $this->structure[$field];

        if (\is_array($item_field)) {
            if (!isset($item_field[$this->dataset])) {
                return false;
            }

            $item_field = $item_field[$this->dataset];
        }

        return isset($this->item[$item_field]);
    }

    /**
     * Constructs a new data item with a different dataset.
     *
     * @param mixed $dataset
     */
    public function newFrom($dataset)
    {
        return new self(
            $this->item,
            $this->structure,
            $dataset,
            $this->key,
        );
    }

    /**
     * Returns some extra data from item.
     *
     * @param mixed $field
     */
    public function data($field)
    {
        if (!isset($this->structure[$field])) {
            return null;
        }

        $item_field = $this->structure[$field];

        if (\is_array($item_field)) {
            if (!isset($item_field[$this->dataset])) {
                return null;
            }

            $item_field = $item_field[$this->dataset];
        }

        return $this->item[$item_field] ?? null;
    }

    /**
     * Check if extra data field exists.
     *
     * @param mixed $field
     */
    public function rawDataExists($field)
    {
        return isset($this->item[$field]);
    }

    /**
     * Returns a value from the item without translating structure.
     *
     * @param mixed $field
     */
    public function rawData($field)
    {
        return $this->item[$field] ?? null;
    }
}
