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
 * Right-click (or long touch) context menu.
 */
class ContextMenu
{
    private $graph;
    private $js;
    private $function_added = false;
    private $callback;
    private $use_structure = false;
    private $namespace = '';

    /**
     * Constructor sets up options and root menu.
     *
     * @param mixed $graph
     * @param mixed $javascript
     */
    public function __construct(&$graph, &$javascript)
    {
        if (!$graph->getOption('show_context_menu')) {
            return;
        }

        $this->graph = &$graph;
        $this->js = &$javascript;

        $this->callback = $graph->getOption('context_callback');
        $structure = $graph->getOption('structure');

        if (\is_array($structure) && isset($structure['context_menu'])) {
            $this->use_structure = true;
        }

        if ($graph->getOption('namespace')) {
            $this->namespace = 'svg:';
        }

        $global = $graph->getOption('context_global');

        if ($global !== false) {
            if ($global === null) {
                $global = [[SVGGraph::VERSION, null]];
            }

            $entries = '';

            foreach ($global as $entry) {
                $attr = ['name' => $entry[0], 'link' => $entry[1]];
                $entries .= $graph->element('svggraph:menuitem', $attr);
            }

            $menu = $graph->element('svggraph:menu', null, null, $entries);
            $xml = $graph->element(
                'svggraph:data',
                ['xmlns:svggraph' => 'http://www.goat1000.com/svggraph'],
                null,
                $menu,
            );
            $graph->defs->add($xml);
        }
    }

    /**
     * Adds the javascript function.
     */
    public function addFunction(): void
    {
        $this->js->addFuncs(
            'getE',
            'finditem',
            'newel',
            'newtext',
            'svgNode',
            'setattr',
            'getData',
            'svgCursorCoords',
        );
        $this->js->addInitFunction('contextMenuInit');

        $opts = ['link_target', 'link_underline', 'stroke_width', 'round', 'font',
            'font_weight', 'document_menu', 'spacing', 'min_width',
            'shadow_opacity', 'mouseleave'];
        $colours = ['colour', 'link_colour', 'link_hover_colour', 'back_colour'];
        $vars = [];

        foreach ($opts as $opt) {
            $vars[$opt] = $this->graph->getOption('context_'.$opt);
        }

        $vars['font_size'] = Number::units($this->graph->getOption('context_font_size'));

        foreach ($colours as $opt) {
            $vars[$opt] = new Colour($this->graph, $this->graph->getOption('context_'.$opt));
        }

        $svg_text = new Text($this->graph, $vars['font']);
        [, $text_height] = $svg_text->measure('Test', $vars['font_size']);
        $text_baseline = $svg_text->baseline($vars['font_size']);

        $vars['pad_x'] = $this->graph->getOption('context_padding_x', 'context_padding');
        $vars['pad_y'] = $this->graph->getOption('context_padding_y', 'context_padding');
        $vars['text_start'] = $vars['pad_y'] + $text_baseline;
        $vars['rect_start'] = $vars['pad_y'] - $vars['spacing'] / 2;
        $vars['spacing'] += $text_height;

        $vars['round_part'] = $vars['mouseleave'] = $vars['underline_part'] = '';

        if ($vars['link_underline']) {
            $vars['underline_part'] = ", 'text-decoration': 'underline'";
        }

        if ($vars['round']) {
            $rnum = new Number($vars['round']);
            $vars['round_part'] = ', rx:"'.$rnum.'px", ry:"'.$rnum.'px"';
        }

        $cmoffs = 0;
        $half_stroke = $vars['stroke_width'] / 2;
        $vars['pad_x'] += $half_stroke;
        $vars['pad_y'] += $half_stroke;

        $vars['off_right'] = $vars['stroke_width'];
        $vars['off_bottom'] = $vars['stroke_width'];

        if (is_numeric($vars['shadow_opacity'])) {
            $cmoffs = 4;
            $vars['off_right'] += $cmoffs;
            $vars['off_bottom'] += $cmoffs;
        }

        $vars['cmoffs'] = $cmoffs;

        if ($vars['document_menu']) {
            $this->js->insertFunction(
                'rootContextMenu',
                "function rootContextMenu(){closeContextMenu();}\n",
            );
        } else {
            $this->js->insertTemplate('rootContextMenu');
        }

        if ((int) $vars['mouseleave'] > 0) {
            $mlnum = new Number($mouseleave);
            $vars['mouseleave'] = 'e[c].addEventListener("mouseleave",function(e) {'.
              'setTimeout(closeContextMenu,'.$mlnum.');}, false);';
        }

        $vars['namespace'] = $this->namespace;
        $this->js->insertTemplate('contextMenu', $vars);
        $this->function_added = true;
    }

    /**
     * Adds context menu for item.
     *
     * @param mixed $element
     * @param mixed $dataset
     * @param mixed $item
     * @param mixed $duplicate
     */
    public function setMenu(&$element, $dataset, &$item, $duplicate = false): void
    {
        $menu = null;

        if (\is_callable($this->callback)) {
            $menu = ($this->callback)($dataset, $item->key, $item->value);
        } elseif ($this->use_structure) {
            $menu = $item->context_menu;
        }

        if (\is_array($menu)) {
            if (!isset($element['id'])) {
                $element['id'] = $this->graph->newID();
            }

            $var = json_encode($menu);
            $this->js->insertVariable('menus', $element['id'], $var, false);

            if ($duplicate) {
                $this->js->addOverlay($element['id'], $this->graph->newID());
            }
        } else {
            // add a placeholder to make sure the variable exists
            $ignore_id = $this->graph->newID();
            $this->js->insertVariable('menus', $ignore_id, "''", false);
        }

        // set up menus after duplication
        if (!$this->function_added) {
            $this->addFunction();
        }
    }
}
