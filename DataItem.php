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
 * Class for single data items.
 */
class DataItem
{
    public $key;
    public $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * A getter for extra fields - there are none, so return NULL.
     *
     * @param mixed $field
     */
    public function __get($field)
    {
        return null;
    }

    /**
     * Returns NULL because standard data doesn't support extra fields.
     *
     * @param mixed $field
     */
    public function data($field)
    {
        return null;
    }
}
