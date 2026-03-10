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
 * A class for the <defs> element.
 */
class Defs
{
    private $graph;
    private $defs = [];
    private $gradients;
    private $patterns;
    private $symbols;
    private $filters;
    private $elements = [];

    public function __construct(&$graph)
    {
        $this->graph = &$graph;
    }

    /**
     * Add a string to the defs block.
     *
     * @param mixed $def
     */
    public function add($def): void
    {
        $this->defs[] = $def;
    }

    /**
     * Adds an element to the defs, returning its ID, or the ID
     * of an existing def with same content.
     *
     * @param mixed $element
     * @param mixed $attrs
     * @param mixed $content
     */
    public function addElement($element, $attrs, $content = '')
    {
        $ehash = hash('md5', $element.':'.serialize($attrs).':'.$content);

        if (isset($this->elements[$ehash])) {
            return $this->elements[$ehash];
        }

        $attrs['id'] = $this->graph->newID();
        $this->elements[$ehash] = $attrs['id'];
        $this->add($this->graph->element($element, $attrs, null, $content));

        return $attrs['id'];
    }

    /**
     * Return the defs block, or an empty string if none.
     */
    public function get()
    {
        // insert gradients, patterns, symbols
        if ($this->gradients !== null) {
            $this->gradients->makeGradients($this);
        }

        if ($this->patterns !== null) {
            $this->patterns->makePatterns($this);
        }

        if ($this->symbols !== null) {
            $this->defs[] = $this->symbols->definitions();
        }

        if ($this->filters !== null) {
            $this->filters->makeFilters($this);
        }

        if (\count($this->defs) === 0) {
            return '';
        }

        return $this->graph->element('defs', null, null, implode('', $this->defs));
    }

    /**
     * Adds a gradient to the list, returning the element ID for use in url.
     *
     * @param mixed      $colours
     * @param null|mixed $key
     * @param mixed      $radial
     */
    public function addGradient($colours, $key = null, $radial = false)
    {
        if ($this->gradients === null) {
            $this->gradients = new GradientList($this->graph);
        }

        return $this->gradients->addGradient($colours, $key, $radial);
    }

    /**
     * Returns the colour at a point in the selected gradient.
     *
     * @param mixed $key
     * @param mixed $position
     */
    public function getGradientColour($key, $position)
    {
        if ($this->gradients === null) {
            return 'none';
        }

        return $this->gradients->getColour($key, $position);
    }

    /**
     * Adds a pattern, returning the element ID.
     *
     * @param mixed $pattern
     */
    public function addPattern($pattern)
    {
        if ($this->patterns === null) {
            $this->patterns = new PatternList($this->graph);
        }

        return $this->patterns->add($pattern);
    }

    /**
     * Defines a symbol.
     *
     * @param mixed $content
     */
    public function defineSymbol($content)
    {
        if ($this->symbols === null) {
            $this->symbols = new Symbols($this->graph);
        }

        return $this->symbols->define($content);
    }

    /**
     * Uses a symbol.
     *
     * @param mixed      $id
     * @param mixed      $attr
     * @param null|mixed $style
     */
    public function useSymbol($id, $attr, $style = null)
    {
        // this should not happen - Symbols class will throw anyway
        if ($this->symbols === null) {
            $this->symbols = new Symbols($this->graph);
        }

        return $this->symbols->useSymbol($id, $attr, $style);
    }

    /**
     * Returns the use count for a symbol.
     *
     * @param mixed $id
     */
    public function symbolUseCount($id)
    {
        if ($this->symbols === null) {
            return 0;
        }

        return $this->symbols->useCount($id);
    }

    /**
     * Adds a filter.
     *
     * @param mixed      $type
     * @param null|mixed $params
     */
    public function addFilter($type, $params = null)
    {
        if ($this->filters === null) {
            $this->filters = new FilterList($this->graph);
        }

        return $this->filters->add($type, $params);
    }

    /**
     * Returns id of shadow, if enabled.
     */
    public function getShadow()
    {
        if (!$this->graph->getOption('show_shadow')) {
            return null;
        }

        return $this->addFilter('shadow', [
            'opacity' => $this->graph->getOption('shadow_opacity'),
            'offset_x' => $this->graph->getOption('shadow_offset_x'),
            'offset_y' => $this->graph->getOption('shadow_offset_y'),
            'blur' => $this->graph->getOption('shadow_blur'),
        ]);
    }
}
