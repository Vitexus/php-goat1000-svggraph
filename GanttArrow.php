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
 * A class for drawing arrows.
 */
class GanttArrow extends Arrow
{
    protected $type = 0;
    protected $vsplit = false;
    protected $space = 10;

    public function __construct(Point $a, Point $b, $wa, $ha, $wb, $hb, $type, $sp)
    {
        switch ($type) {
            case 'SS':
                $this->vsplit = ($a->x < $b->x);

                break;
            case 'FF':
                $a->x += $wa;
                $b->x += $wb;
                $this->vsplit = ($a->x > $b->x);

                break;
            case 'SF':
                $b->x += $wb;
                $this->vsplit = ($a->x < $b->x);

                break;
            case 'FS':
            default:
                $a->x += $wa;
                $this->vsplit = ($a->x > $b->x);
        }

        $a->y += $ha;

        // only start horizontal if the first element has some width
        if ($wa < 1) {
            $this->vsplit = true;
        }

        parent::__construct($a, $b);
        $this->type = $type;
        $this->space = max(5, $sp);
    }

    /**
     * Returns the PathData for an arrow line.
     */
    protected function getArrowPath()
    {
        $p = new PathData('M', $this->a);
        $dx = $this->b->x - $this->a->x;
        $dy = $this->b->y - $this->a->y;

        if ($dx && $this->vsplit) {
            $v1 = new Number($dy - $this->space);
            $v2 = new Number($this->space);
            $p->add('v', $v1);
            $p->add('h', new Number($dx));
            $p->add('v', $v2);
        } else {
            // if horizontal very small, ignore it
            if (abs($dx) > 0.1) {
                $p->add('h', new Number($dx));
            }

            $p->add('v', new Number($this->b->y - $this->a->y));
        }

        return $p;
    }
}
