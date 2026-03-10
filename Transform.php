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
 * Class for SVG transforms.
 */
class Transform implements \Stringable
{
    private $transforms = [];

    /**
     * Output for SVG values.
     */
    public function __toString()
    {
        $str = '';

        foreach ($this->transforms as $xform) {
            $str .= $xform[0].'(';
            $str .= implode(' ', $xform[1]);
            $str .= ')';
        }

        return $str;
    }

    /**
     * Adds another transform to this one.
     *
     * @param mixed $xform
     */
    public function add($xform): void
    {
        if (!\is_object($xform) || $xform::class !== 'Goat1000\\SVGGraph\\Transform') {
            throw new \InvalidArgumentException('Argument is not a Transform');
        }

        $this->transforms = array_merge($this->transforms, $xform->transforms);
    }

    /**
     * Translate by $x, $y.
     *
     * @param mixed $x
     * @param mixed $y
     */
    public function translate($x, $y): void
    {
        $this->transforms[] = ['translate', [new Number($x), new Number($y)]];
    }

    /**
     * Scale by $x, or by $x and $y.
     *
     * @param mixed      $x
     * @param null|mixed $y
     */
    public function scale($x, $y = null): void
    {
        $args = [new Number($x)];

        if ($y !== null) {
            $args[] = new Number($y);
        }

        $this->transforms[] = ['scale', $args];
    }

    /**
     * Rotate by $a degrees, around point $x, $y.
     *
     * @param mixed      $a
     * @param null|mixed $x
     * @param null|mixed $y
     */
    public function rotate($a, $x = null, $y = null): void
    {
        $args = [new Number($a)];

        if ($x !== null && $y !== null) {
            $args[] = new Number($x);
            $args[] = new Number($y);
        }

        $this->transforms[] = ['rotate', $args];
    }

    /**
     * Skew by $a degrees along X-axis.
     *
     * @param mixed $a
     */
    public function skewX($a): void
    {
        $this->transforms[] = ['skewX', [new Number($a)]];
    }

    /**
     * Skew by $a degrees along Y-axis.
     *
     * @param mixed $a
     */
    public function skewY($a): void
    {
        $this->transforms[] = ['skewY', [new Number($a)]];
    }
}
