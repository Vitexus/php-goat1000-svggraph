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
 * Class for axis grid points.
 */
class GridPoint
{
    public $position;
    public $value;
    public $item;
    protected $text = [];

    public function __construct($position, $text, $value, $item = null)
    {
        $this->position = $position;
        $this->value = $value;
        $this->item = $item;

        if (!\is_array($text)) {
            $text = [(string) $text];
        }

        foreach ($text as $t) {
            $this->text[] = (string) $t;
        }
    }

    /**
     * Returns a value from the item, or NULL.
     *
     * @param mixed $field
     */
    public function __get($field)
    {
        if ($this->item === null) {
            return null;
        }

        if (isset($this->item->{$field})) {
            return $this->item->{$field};
        }

        if ($this->item->axis_text_class) {
            $tc = new TextClass($this->item->axis_text_class, 'axis_text_');

            return $tc->{$field};
        }

        return null;
    }

    /**
     * Returns the grid point text for an axis level.
     *
     * @param mixed $level
     */
    public function getText($level = 0)
    {
        return $this->text[$level] ?? '';
    }

    /**
     * Returns true when the text is empty.
     *
     * @param mixed $level
     */
    public function blank($level = 0)
    {
        return !isset($this->text[$level]) || $this->text[$level] === '';
    }
}
