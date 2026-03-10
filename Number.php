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
 * Class for outputting numbers.
 */
class Number implements \Stringable
{
    public $value = 0;
    public $units = '';
    public $units_before = '';
    public $precision = 0;
    private $as_string = '';
    private static $default_precision = 5;
    private static $decimal_separator = '.';
    private static $thousands_separator = ',';

    public function __construct($value, $units = '', $units_before = '')
    {
        if (\is_object($value) && $value::class === 'Goat1000\\SVGGraph\\Number') {
            $this->value = $value->value;
            $this->units = $value->units;
            $this->units_before = $value->units_before;
            $this->as_string = $value->as_string;

            return;
        }

        if (!is_numeric($value)) {
            throw new \Exception((is_scalar($value) ? (string)$value : gettype($value)).' is not a number');
        }

        $this->value = $value;
        $this->units = $units;
        $this->units_before = $units_before;
        $this->as_string = '';
    }

    /**
     * Output for SVG values.
     */
    public function __toString()
    {
        if ($this->as_string !== '') {
            return $this->as_string;
        }

        $value = $this->value;

        if ($value === 0) {
            $value = '0';
        } elseif ($value === 1) {
            $value = '1';
        } elseif (\is_int($value) || $value >= 1000 || $value <= -1000) {
            $value = sprintf('%d', $value);
        } else {
            if ($this->precision) {
                $value = sprintf('%.'.$this->precision.'F', $value);
            } else {
                $value = sprintf('%.2F', $value);
            }

            $value = rtrim($value, '0');
            $value = rtrim($value, '.');
        }

        $this->as_string = $value.$this->units;

        return $this->as_string;
    }

    /**
     * Sets the formatted string options.
     *
     * @param mixed $precision
     * @param mixed $decimal
     * @param mixed $thousands
     */
    public static function setup($precision, $decimal, $thousands): void
    {
        if ($decimal === $thousands) {
            throw new \LogicException('Decimal and thousands separators using same value. Please use different settings for "thousands" and "decimal".');
        }

        self::$default_precision = $precision;
        self::$decimal_separator = $decimal;
        self::$thousands_separator = $thousands;
    }

    /**
     * Formatted output.
     *
     * @param null|mixed $decimals
     * @param null|mixed $precision
     */
    public function format($decimals = null, $precision = null)
    {
        $n = $this->value;
        $d = ($decimals === null ? 0 : $decimals);

        if (!\is_int($n)) {
            if ($precision === null) {
                $precision = self::$default_precision;
            }

            // if there are too many zeroes before other digits, round to 0
            $e = floor(log(abs($n), 10));

            if (-$e > $precision) {
                $n = 0;
            }

            // subtract number of digits before decimal point from precision
            // for precision-based decimals
            if ($decimals === null) {
                $d = $precision - ($e > 0 ? $e : 0);
            }
        }

        $s = number_format(
            $n,
            (int)$d,
            self::$decimal_separator,
            self::$thousands_separator,
        );

        if ($decimals === null && $d
          && str_contains($s, self::$decimal_separator)) {
            [$a, $b] = explode(self::$decimal_separator, $s);
            $b1 = rtrim($b, '0');
            $s = $b1 !== '' ? $a.self::$decimal_separator.$b1 : $a;
        }

        return $this->units_before.$s.$this->units;
    }

    /**
     * Converts a string with units to a value in SVG user units.
     *
     * @param mixed $value
     */
    public static function units($value)
    {
        if (is_numeric($value) || $value === null) {
            return $value;
        }

        if (!\is_string($value)) {
            throw new \InvalidArgumentException('Unit value not a string');
        }

        if (!preg_match('/^([0-9.]+)(px|in|cm|mm|pt|pc)$/', $value, $parts)) {
            throw new \InvalidArgumentException("Unit value {$value} not in supported format");
        }

        $count = (float) $parts[1];
        $units = $parts[2];
        $umap = [
            'px' => 1.0, 'in' => 96.0, 'cm' => 37.795, 'mm' => 3.7795, 'pt' => 1.3333, 'pc' => 16.0,
        ];

        return $count * $umap[$units];
    }
}
