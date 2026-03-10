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
 * Class for algebraic functions.
 */
class Algebraic
{
    private $type = 'straight';
    private $coeffs = [0, 1];

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Returns the y value for a + bx + cx^2 ...
     *
     * @param mixed $x
     */
    public function __invoke($x)
    {
        $val = 0;

        foreach ($this->coeffs as $p => $c) {
            switch ($p) {
                case 0: $val = bcadd($val, $c);

                    break;
                case 1: $val = bcadd($val, bcmul($c, $x));

                    break;

                default:
                    $val = bcadd($val, bcmul($c, bcpow($x, $p)));

                    break;
            }
        }

        return $val;
    }

    /**
     * Sets the coefficients in order, lowest power first.
     */
    public function setCoefficients(array $coefficients): void
    {
        $this->coeffs = $coefficients;
    }

    /**
     * Creates a row of the vandermonde matrix.
     *
     * @param mixed $x
     */
    public function vandermonde($x)
    {
        $t = $this->type;

        return $this->{$t}($x);
    }

    private static function straight($x)
    {
        return [$x];
    }

    private static function quadratic($x)
    {
        return [$x, bcmul($x, $x)];
    }

    private static function cubic($x)
    {
        $res = [$x, bcmul($x, $x)];
        $res[] = bcmul($res[1], $x);

        return $res;
    }

    private static function quartic($x)
    {
        $res = self::cubic($x);
        $res[] = bcmul($res[1], $res[1]);

        return $res;
    }

    private static function quintic($x)
    {
        $res = self::cubic($x);
        $res[] = bcmul($res[1], $res[1]);
        $res[] = bcmul($res[1], $res[2]);

        return $res;
    }
}
