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

class SVGGraph
{
    use SVGGraphTrait;
    public const VERSION = 'SVGGraph 3.20';
    protected static $last_instance;
    private $width = 100;
    private $height = 100;
    private $settings = [];
    private $values = [];
    private $links;
    private $colours;
    private $subgraph = false;
    private $subgraphs = [];

    public function __construct($w, $h, $settings = null, $subgraph = false)
    {
        $this->subgraph = $subgraph;
        $this->width = $w;
        $this->height = $h;

        if (\is_array($settings)) {
            // structured_data, when FALSE disables structure
            if (isset($settings['structured_data']) && !$settings['structured_data']) {
                unset($settings['structure']);
            }

            $this->settings = $settings;
        }

        $this->colours = new Colours();
    }

    /**
     * Prevent direct access to members.
     *
     * @param mixed $name
     * @param mixed $val
     */
    public function __set($name, $val): void
    {
        if ($name === 'values' || $name === 'links' || $name === 'colours') {
            throw new \BadMethodCallException('Modifying $graph->'.$name.
              ' directly is not supported - please use the $graph->'.$name.
              '() function.');
        }
    }

    /**
     * Fetch the content.
     *
     * @param mixed $class
     * @param mixed $header
     * @param mixed $defer_js
     */
    public function fetch($class, $header = true, $defer_js = true)
    {
        self::$last_instance = $this->setup($class);

        return self::$last_instance->fetch($header, $defer_js);
    }

    /**
     * Pass in the type of graph to display.
     *
     * @param mixed $class
     * @param mixed $header
     * @param mixed $content_type
     * @param mixed $defer_js
     */
    public function render(
        $class,
        $header = true,
        $content_type = true,
        $defer_js = false,
    ) {
        self::$last_instance = $this->setup($class);

        return self::$last_instance->render($header, $content_type, $defer_js);
    }

    /**
     * Fetch the Javascript for ALL graphs that have been Fetched.
     *
     * @param null|mixed $nonce
     */
    public static function fetchJavascript($nonce = null)
    {
        if (self::$last_instance === null) {
            return '';
        }

        return self::$last_instance->fetchJavascript(true, true, $nonce);
    }
}
