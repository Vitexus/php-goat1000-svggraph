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
 * A class for setting text styles from text_classes.ini file.
 */
class TextClass
{
    private static $classes;
    private static $file;
    private $prefix = '';
    private $fields = [];

    public function __construct($class_name, $prefix = '')
    {
        if (self::$classes === null) {
            if (self::$file === null) {
                self::$file = __DIR__.\DIRECTORY_SEPARATOR.'text_classes.ini';
            }

            self::load();
        }

        $this->prefix = $prefix;

        if (isset(self::$classes[$class_name])) {
            $this->fields = self::$classes[$class_name];
        }
    }

    /**
     * Returns the value of a field from the text class.
     *
     * @param mixed $field
     */
    public function __get($field)
    {
        if ($this->prefix !== '' && str_starts_with($field, $this->prefix)) {
            $field = substr($field, \strlen($this->prefix));
        }

        if (isset($this->fields[$field])) {
            return $this->fields[$field];
        }

        return null;
    }

    /**
     * Sets the classes file.
     *
     * @param mixed $filename
     */
    public static function setFile($filename): void
    {
        self::$file = $filename;
    }

    /**
     * Loads the text classes file in.
     */
    public static function load(): void
    {
        $classes = @parse_ini_file(self::$file, true);

        if ($classes === false) {
            trigger_error("Text classes file '".self::$file."' not found");
            $classes = [];
        }

        self::$classes = $classes;
    }
}
