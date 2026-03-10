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
 * Class for standard data.
 */
class Data implements \ArrayAccess, \Countable, \Iterator
{
    public $error;
    private $datasets = 0;
    private $data;
    private $assoc;
    private $datetime;
    private $min_value = [];
    private $max_value = [];
    private $min_key = [];
    private $max_key = [];

    public function __construct(&$data, $force_assoc, $datetime_keys)
    {
        if (empty($data[0])) {
            $this->error = 'No data';

            return;
        }

        $this->data = $data;
        $this->datasets = \count($data);

        if ($force_assoc) {
            $this->assoc = true;
        }

        if ($datetime_keys) {
            if ($this->rekey('Goat1000\\SVGGraph\\Graph::dateConvert')) {
                $this->datetime = true;
                $this->assoc = false;

                return;
            }

            $this->error = 'Too many date/time conversion errors';
        }
    }
    #[\ReturnTypeWillChange]
    public function current(): void
    {
        self::notIterator();
    }
    #[\ReturnTypeWillChange]
    public function key(): void
    {
        self::notIterator();
    }
    #[\ReturnTypeWillChange]
    public function next(): void
    {
        self::notIterator();
    }
    #[\ReturnTypeWillChange]
    public function rewind(): void
    {
        self::notIterator();
    }
    #[\ReturnTypeWillChange]
    public function valid(): void
    {
        self::notIterator();
    }

    /**
     * ArrayAccess methods.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return \array_key_exists($offset, $this->data);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return new DataIterator($this->data, $offset);
    }

    /**
     * Don't allow writing to the data.
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new \Exception('Read-only');
    }
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new \Exception('Read-only');
    }

    /**
     * Countable method.
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return $this->datasets;
    }

    /**
     * Returns minimum data value for a dataset.
     *
     * @param mixed $dataset
     */
    public function getMinValue($dataset = 0)
    {
        if (!isset($this->min_value[$dataset])) {
            $this->min_value[$dataset] = null;

            if (\count($this->data[$dataset])) {
                $this->min_value[$dataset] = Graph::min($this->data[$dataset]);
            }
        }

        return $this->min_value[$dataset];
    }

    /**
     * Returns maximum data value for a dataset.
     *
     * @param mixed $dataset
     */
    public function getMaxValue($dataset = 0)
    {
        if (!isset($this->max_value[$dataset])) {
            $this->max_value[$dataset] = null;

            if (\count($this->data[$dataset])) {
                $this->max_value[$dataset] = max($this->data[$dataset]);
            }
        }

        return $this->max_value[$dataset];
    }

    /**
     * Returns the minimum key value.
     *
     * @param mixed $dataset
     */
    public function getMinKey($dataset = 0)
    {
        if (!isset($this->min_key[$dataset])) {
            $this->min_key[$dataset] = null;

            if (\count($this->data[$dataset])) {
                $this->min_key[$dataset] = $this->associativeKeys() ? 0 :
                  min(array_keys($this->data[$dataset]));
            }
        }

        return $this->min_key[$dataset];
    }

    /**
     * Returns the maximum key value.
     *
     * @param mixed $dataset
     */
    public function getMaxKey($dataset = 0)
    {
        if (!isset($this->max_key[$dataset])) {
            $this->max_key[$dataset] = null;

            if (\count($this->data[$dataset])) {
                $this->max_key[$dataset] = $this->associativeKeys() ?
                  \count($this->data[$dataset]) - 1 :
                  max(array_keys($this->data[$dataset]));
            }
        }

        return $this->max_key[$dataset];
    }

    /**
     * Returns the key at a given index.
     *
     * @param mixed $index
     * @param mixed $dataset
     */
    public function getKey($index, $dataset = 0)
    {
        if (!$this->associativeKeys()) {
            return $index;
        }

        // round index to nearest integer, or PHP will floor() it
        $index = (int) round($index);

        if ($index >= 0) {
            $slice = \array_slice($this->data[$dataset], $index, 1, true);

            // use foreach to get key and value
            foreach ($slice as $k => $v) {
                return $k;
            }
        }

        return null;
    }

    /**
     * Returns TRUE if the keys are associative.
     */
    public function associativeKeys()
    {
        if ($this->assoc !== null) {
            return $this->assoc;
        }

        foreach (array_keys($this->data[0]) as $k) {
            if (!\is_int($k)) {
                return $this->assoc = true;
            }
        }

        return $this->assoc = false;
    }

    /**
     * Returns the number of data items.
     *
     * @param mixed $dataset
     */
    public function itemsCount($dataset = 0)
    {
        if ($dataset < 0) {
            $dataset = 0;
        }

        return \count($this->data[$dataset]);
    }

    /**
     * Returns the min and max sum values.
     *
     * @param mixed      $start
     * @param null|mixed $end
     */
    public function getMinMaxSumValues($start = 0, $end = null)
    {
        if ($start !== 0 || ($end !== null && $end !== 0)) {
            throw new \Exception('Dataset not found');
        }

        // structured data is used for multi-data, so just
        // return the min and max
        return [$this->getMinValue(), $this->getMaxValue()];
    }

    /**
     * Returns the min/max sum values for an array of datasets.
     *
     * @param mixed $datasets
     */
    public function getMinMaxSumValuesFor($datasets)
    {
        // Data class can't handle multiple datasets
        if (\count($datasets) > 1) {
            throw new \InvalidArgumentException('Multiple datasets not supported');
        }

        $d = array_pop($datasets);

        if ($d < 0 || $d >= $this->datasets) {
            throw new \Exception('Dataset not found');
        }

        return [$this->getMinValue($d), $this->getMaxValue($d)];
    }

    /**
     * Returns TRUE if the item exists, setting the $value.
     *
     * @param mixed $index
     * @param mixed $name
     * @param mixed $value
     */
    public function getData($index, $name, &$value)
    {
        // base class doesn't support this, so always return false
        return false;
    }

    /**
     * Doesn't return a structured data item.
     *
     * @param mixed $index
     * @param mixed $dataset
     */
    public function getItem($index, $dataset = 0)
    {
        return null;
    }

    /**
     * Transforms the keys using a callback function.
     *
     * @param mixed $callback
     */
    public function rekey($callback)
    {
        $new_data = [];
        $count = $invalid = 0;

        for ($d = 0; $d < $this->datasets; ++$d) {
            $new_data[$d] = [];

            foreach ($this->data[$d] as $key => $value) {
                $new_key = $callback($key);

                // if the callback returns null, skip the value
                if ($new_key === null) {
                    ++$invalid;

                    continue;
                }

                $new_data[$d][$new_key] = $value;
            }

            ++$count;
        }

        // if too many invalid, probably a format error
        if ($count && $invalid / $count > 0.05) {
            return false;
        }

        $this->data = $new_data;
        // forget previous min/max
        $this->min_key = [];
        $this->max_key = [];

        return true;
    }

    /**
     * Implement Iterator interface to prevent iteration...
     */
    private static function notIterator(): void
    {
        throw new \Exception('Cannot iterate '.__CLASS__);
    }
}
