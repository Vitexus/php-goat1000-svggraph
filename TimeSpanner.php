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
 * Class to make start/end times match units.
 */
class TimeSpanner
{
    private $start_func = 'start_days';
    private $end_func = 'end_days';
    private $start_time = 0;

    public function __construct($units)
    {
        switch ($units) {
            case 'minute':
                $this->start_func = 'start_minutes';
                $this->end_func = 'end_minutes';

                break;
            case 'hour':
                $this->start_func = 'start_hours';
                $this->end_func = 'end_hours';

                break;
        }
    }

    /**
     * Returns the start time clamped to unit
     * $d = unix timestamp.
     *
     * @param mixed $d
     */
    public function start($d)
    {
        $e = new \DateTime('@'.$d);
        $this->{$this->start_func}($e);
        $this->start_time = $e->format('U');

        return $this->start_time;
    }

    /**
     * Returns the end time clamped to at least one unit after start
     * $d = unix timestamp.
     *
     * @param mixed $d
     */
    public function end($d)
    {
        if ($d < $this->start_time) {
            $d = $this->start_time;
        }

        $e = new \DateTime('@'.$d);
        $this->{$this->end_func}($e, $d - $this->start_time);

        return $e->format('U');
    }

    public function start_minutes(\DateTime $e): void
    {
        $h = (int) $e->format('H');
        $m = (int) $e->format('i');
        $e->setTime($h, $m, 0);
    }

    public function end_minutes(\DateTime $e, $diff): void
    {
        if ($diff < 60) {
            $e->modify('+1 minute');
        }

        $h = (int) $e->format('H');
        $m = (int) $e->format('i');
        $e->setTime($h, $m, 0);

        // prevent ending at 00:00 on next day
        if ($h === 0 && $m === 0) {
            $e->modify('-1 second');
        }
    }

    public function start_hours(\DateTime $e): void
    {
        $h = (int) $e->format('H');
        $e->setTime($h, 0, 0);
    }

    public function end_hours(\DateTime $e, $diff): void
    {
        if ($diff < 3600) {
            $e->modify('+1 hour');
        }

        $h = (int) $e->format('H');
        $e->setTime($h, 0, 0);

        // prevent ending at 00:00 on next day
        if ($h === 0) {
            $e->modify('-1 second');
        }
    }

    public function start_days(\DateTime $e): void
    {
        $e->setTime(0, 0, 0);
    }

    public function end_days(\DateTime $e, $diff): void
    {
        $e->setTime(23, 59, 59);
    }
}
