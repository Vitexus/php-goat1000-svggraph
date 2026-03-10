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

class FilterList
{
    private $graph;
    private $filters = [];

    public function __construct(&$graph)
    {
        $this->graph = &$graph;
    }

    /**
     * Creates a shadow.
     *
     * @param mixed $params
     */
    public function shadow($params)
    {
        $opts = [
            'offset_x' => 10, 'offset_y' => 10,
            'opacity' => 0.5, 'blur' => 3,
            'shadow_only' => false,
        ];

        if (\is_array($params)) {
            $opts = array_merge($opts, $params);
        }

        // matrix converts to black and sets opacity
        $o = new Number(min(max($opts['opacity'], 0.005), 1.0));
        $matrix = [
            'type' => 'matrix',
            'values' => '0 0 0 0 0 '.
              '0 0 0 0 0 '.
              '0 0 0 0 0 '.
              '0 0 0 '.$o.' 0',
        ];
        $matrix = $this->graph->element('feColorMatrix', $matrix);
        $offset = $blur = '';

        // offset positions the shadow
        $offsets = [
            'dx' => new Number($opts['offset_x']),
            'dy' => new Number($opts['offset_y']),
            'result' => 'res',
        ];

        if ($offsets['dx']->value || $offsets['dy']->value) {
            $offset = $this->graph->element('feOffset', $offsets);
        }

        // blur the outline
        $gblur = [
            'stdDeviation' => new Number($opts['blur']),
            'result' => 'res',
        ];

        if ($gblur['stdDeviation']->value > 0) {
            $blur = $this->graph->element('feGaussianBlur', $gblur);
        }

        // if there is no blur and no offset, there is no shadow
        if ($blur === '' && $offset === '') {
            return null;
        }

        $content = $matrix.$offset.$blur;

        if (!$opts['shadow_only']) {
            $merged = $this->graph->element('feMergeNode', ['in' => 'res']).
              $this->graph->element('feMergeNode', ['in' => 'SourceGraphic']);
            $content .= $this->graph->element('feMerge', null, null, $merged);
        }

        $filter = [
            'id' => $this->graph->newID(),
            'filterUnits' => 'userSpaceOnUse',
        ];

        return [
            'id' => $filter['id'],
            'content' => $this->graph->element('filter', $filter, null, $content),
        ];
    }

    /**
     * Adds a filter.
     *
     * @param mixed      $type
     * @param null|mixed $params
     */
    public function add($type, $params = null)
    {
        $key = md5(serialize([$type, $params]));

        if (isset($this->filters[$key])) {
            return $this->filters[$key]['id'];
        }

        if (!method_exists($this, $type)) {
            throw new \InvalidArgumentException('Unknown filter: '.$type);
        }

        $result = $this->{$type}($params);

        if ($result === null) {
            return null;
        }

        $this->filters[$key] = $result;

        return $result['id'];
    }

    /**
     * Adds the filters to the defs.
     *
     * @param mixed $defs
     */
    public function makeFilters(&$defs): void
    {
        foreach ($this->filters as $filter) {
            $defs->add($filter['content']);
        }
    }
}
