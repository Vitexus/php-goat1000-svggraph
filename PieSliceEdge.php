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
 * The PieSliceEdge class calculates and draws the 3D slice edges.
 */
class PieSliceEdge
{
    public const SCALE = 2000.0;
    public $y;
    public $slice;

    // types: 0 => start, 1 => end, 2 => curve,
    // 3 => second curve (if it exists), -1 = no edge
    protected $type;
    protected $a1;
    protected $a2;

    /**
     * $slice is the slice details array
     * $s_angle is the start angle in radians.
     *
     * @param mixed $graph
     * @param mixed $type
     * @param mixed $slice
     * @param mixed $s_angle
     */
    public function __construct(&$graph, $type, $slice, $s_angle)
    {
        $this->type = -1;
        $this->slice = $slice;
        $tau = \M_PI * 2.0;

        $start_angle = $slice['angle_start'] + $s_angle;
        $end_angle = $slice['angle_end'] + $s_angle;

        if (isset($slice['single_slice']) && $slice['single_slice']
          && !is_numeric($graph->end_angle)) {
            // if end_angle is not set, then single_slice is full pie
            $start_angle = 0.0;
            $end_angle = \M_PI;
        } elseif ($graph->getOption('reverse')) {
            // apply reverse now to save thinking about it later
            $s = \M_PI * 4.0 - $end_angle;
            $e = \M_PI * 4.0 - $start_angle;
            $start_angle = $s;
            $end_angle = $e;
        }

        $this->a1 = fmod($start_angle, $tau);
        $this->a2 = fmod($end_angle, $tau);

        if ($this->a2 < $this->a1) {
            $this->a2 += $tau;
        }

        switch ($type) {
            case 0:
                // flat edge at a1
                $this->a2 = $this->a1;

                break;
            case 1:
                // flat edge at a2
                $this->a1 = $this->a2;

                break;
            case 2:
                // bottom edge
                if ($this->a1 > \M_PI && $this->a2 < $tau) {
                    return;
                }

                // truncate curves to visible area
                if ($this->a1 <= \M_PI && $this->a2 >= \M_PI) {
                    $this->a2 = \M_PI;
                } elseif ($this->a1 > \M_PI && $this->a2 > $tau) {
                    $this->a1 = $tau;
                }

                if ($this->a2 > \M_PI * 3.0) {
                    $this->a2 = \M_PI * 3.0;
                }

                break;
            case 3:
                // type 3 edges are where the slice starts bottom, goes through top and ends at bottom
                if ($this->a2 < $tau || $this->a2 > \M_PI * 3.0 || $this->a1 >= \M_PI) {
                    return;
                }

                $this->a1 = $tau;

                break;
        }

        $this->setupSort();
        $this->type = $type;
    }

    /**
     * Returns a array of edges for the slice.
     *
     * @param mixed $graph
     * @param mixed $slice
     * @param mixed $start_angle
     * @param mixed $flat
     */
    public static function getEdges(&$graph, $slice, $start_angle, $flat)
    {
        $class = static::class;
        $start = $flat ? 0 : 2;
        $end = $class::getEdgeTypes();
        $edges = [];

        for ($e = $start; $e <= $end; ++$e) {
            $edge = new $class($graph, $e, $slice, $start_angle);

            if ($edge->visible()) {
                $edges[] = $edge;
            }
        }

        return $edges;
    }

    /**
     * Returns TRUE if the edge faces forwards.
     */
    public function visible()
    {
        // type -1 is for non-existent edges
        if ($this->type === -1) {
            return false;
        }

        // the flat edges are visible left or right
        if ($this->type === 0) {
            // start on right not visible
            if ($this->a1 < \M_PI * 0.5 || $this->a1 > \M_PI * 1.5) {
                return false;
            }

            return true;
        }

        $a2 = fmod($this->a2, \M_PI * 2.0);

        if ($this->type === 1) {
            // end on left not visible
            if ($a2 > \M_PI * 0.5 && $a2 < \M_PI * 1.5) {
                return false;
            }

            return true;
        }

        // if both ends are at top and slice angle < 180, not visible
        if ($this->a1 >= \M_PI && $this->a2 <= \M_PI * 2.0
          && $this->a2 - $this->a1 < \M_PI * 2.0) {
            return false;
        }

        return true;
    }

    /**
     * Returns TRUE if this is a curved edge.
     */
    public function curve()
    {
        return $this->type > 1;
    }

    /**
     * Returns angle of flat path.
     */
    public function angle()
    {
        if ($this->type > 1) {
            return 0;
        }

        return $this->type === 1 ? $this->a2 : $this->a1;
    }

    /**
     * Draws the edge.
     *
     * @param mixed      $graph
     * @param mixed      $x_centre
     * @param mixed      $y_centre
     * @param mixed      $depth
     * @param null|mixed $attr
     */
    public function draw(&$graph, $x_centre, $y_centre, $depth, $attr = null)
    {
        $attr = ($attr === null ? $this->slice['attr'] :
          array_merge($this->slice['attr'], $attr));
        $attr['d'] = $this->getPath($x_centre, $y_centre, $depth);

        return $graph->element('path', $attr);
    }

    /**
     * Returns the edge as a clipPath element.
     *
     * @param mixed $graph
     * @param mixed $x_centre
     * @param mixed $y_centre
     * @param mixed $depth
     * @param mixed $clip_id
     */
    public function getClipPath(&$graph, $x_centre, $y_centre, $depth, $clip_id)
    {
        $attr = ['id' => $clip_id];
        $path = ['d' => $this->getPath($x_centre, $y_centre, $depth)];

        return $graph->element(
            'clipPath',
            $attr,
            null,
            $graph->element('path', $path),
        );
    }

    /**
     * Fills in $this->y, for sorting slices by layer depth.
     */
    protected function setupSort(): void
    {
        if ($this->a1 < \M_PI_2 && $this->a2 > \M_PI_2) {
            $ac = \M_PI_2;
        } else {
            $ac = ($this->a1 + $this->a2) / 2;
        }

        $this->y = self::SCALE * sin($ac);
    }

    /**
     * Returns the number of edge types this class supports.
     */
    protected static function getEdgeTypes()
    {
        return 3;
    }

    /**
     * Returns the correct path.
     *
     * @param mixed $x_centre
     * @param mixed $y_centre
     * @param mixed $depth
     */
    protected function getPath($x_centre, $y_centre, $depth)
    {
        if ($this->type === 0 || $this->type === 1) {
            $path = $this->getFlatPath(
                $this->type === 1 ? $this->a2 : $this->a1,
                $x_centre,
                $y_centre,
                $depth,
            );
        } else {
            $path = $this->getCurvedPath($x_centre, $y_centre, $depth);
        }

        return $path;
    }

    /**
     * Returns the path for a flat edge.
     *
     * @param mixed $angle
     * @param mixed $x_centre
     * @param mixed $y_centre
     * @param mixed $depth
     */
    protected function getFlatPath($angle, $x_centre, $y_centre, $depth)
    {
        $x1 = $x_centre + $this->slice['radius_x'] * cos($angle);
        $y1 = $y_centre + $this->slice['radius_y'] * sin($angle) + $depth;

        return new PathData(
            'M',
            $x_centre,
            $y_centre,
            'v',
            $depth,
            'L',
            $x1,
            $y1,
            'v',
            -$depth,
            'z',
        );
    }

    /**
     * Returns the path for the curved edge.
     *
     * @param mixed $x_centre
     * @param mixed $y_centre
     * @param mixed $depth
     */
    protected function getCurvedPath($x_centre, $y_centre, $depth)
    {
        $rx = $this->slice['radius_x'];
        $ry = $this->slice['radius_y'];
        $x1 = $x_centre + $rx * cos($this->a1);
        $y1 = $y_centre + $ry * sin($this->a1);
        $x2 = $x_centre + $rx * cos($this->a2);
        $y2 = $y_centre + $ry * sin($this->a2);
        $y2d = $y2 + $depth;

        $outer = 0; // edge is never > PI
        $sweep = 1;

        $path = new PathData(
            'M',
            $x1,
            $y1,
            'v',
            $depth,
            'A',
            $rx,
            $ry,
            0,
            $outer,
            $sweep,
            $x2,
            $y2d,
            'v',
            -$depth,
        );
        $sweep = $sweep ? 0 : 1;
        $path->add('A', $rx, $ry, 0, $outer, $sweep, $x1, $y1);

        return $path;
    }
}
