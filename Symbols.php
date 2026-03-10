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

class Symbols
{
    private $graph;
    private $symbols = [];
    private $use_count = [];
    private $empty_use;

    public function __construct(&$graph)
    {
        $this->graph = &$graph;
        $this->empty_use = $graph->getOption('empty_use');
    }

    /**
     * Defines a symbol, returning its ID.
     *
     * @param mixed $content
     */
    public function define($content)
    {
        // if this is a duplicate, return existing ID
        foreach ($this->symbols as $id => $def) {
            if ($def === $content) {
                return $id;
            }
        }

        $id = $this->graph->newID();
        $this->symbols[$id] = $content;

        return $id;
    }

    /**
     * Uses an existing symbol.
     *
     * @param mixed      $id
     * @param mixed      $attr
     * @param null|mixed $style
     */
    public function useSymbol($id, $attr, $style = null)
    {
        if (!isset($this->symbols[$id])) {
            throw new \Exception('Symbol '.$id.' not defined');
        }

        if (isset($this->use_count[$id])) {
            ++$this->use_count[$id];
        } else {
            $this->use_count[$id] = 1;
        }

        $uattr = array_merge($attr, ['xlink:href' => '#'.$id]);

        return $this->graph->element(
            'use',
            $uattr,
            $style,
            $this->empty_use ? '' : null,
        );
    }

    /**
     * Returns symbol use count.
     *
     * @param mixed $id
     */
    public function useCount($id)
    {
        if (isset($this->use_count[$id])) {
            return $this->use_count[$id];
        }

        return 0;
    }

    /**
     * Outputs the list of used definitions.
     */
    public function definitions()
    {
        $defs = '';

        foreach ($this->use_count as $id => $count) {
            $defs .= $this->graph->element(
                'symbol',
                null,
                null,
                $this->graph->element(
                    'g',
                    ['id' => $id],
                    null,
                    $this->symbols[$id],
                ),
            );
        }

        return $defs;
    }
}
