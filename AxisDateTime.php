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
 * Class for calculating date/time axis measurements.
 */
class AxisDateTime extends Axis
{
    protected $grid_space;
    protected $grid_split = 0;
    protected $start = 0;
    protected $end = 0;
    protected $duration = 1;
    protected $grid_units;
    protected $grid_unit_count;
    protected $label_callback;
    protected $axis_text_format = 'Y-m-d';
    protected $timezone;
    protected $formatter;
    protected $div;
    protected $division;
    protected $levels;
    protected static $week_start = 'monday';
    protected static $weekdays = [
        'sunday' => 0,
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
    ];

    /**
     * The list of possible divisions. Fields are:
     * 0 - division unit
     * 1 - number of units in duration
     * 2 - array of division indices for subdivision
     */
    protected static $divisions = [
        // the indices are numbered for clarity
        0 => ['second', 1],
        1 => ['second', 2, [0]],
        2 => ['second', 5, [0]],
        3 => ['second', 10, [0, 1, 2]],
        4 => ['second', 15, [0, 2]],
        5 => ['second', 20, [0, 1, 2, 3]],
        6 => ['second', 30, [0, 1, 2, 3, 4]],
        7 => ['minute', 1, [3, 4, 5, 6]],
        8 => ['minute', 2, [6, 7]],
        9 => ['minute', 5, [7]],
        10 => ['minute', 10, [7, 8, 9]],
        11 => ['minute', 15, [7, 9]],
        12 => ['minute', 20, [7, 8, 9, 10]],
        13 => ['minute', 30, [8, 9, 10, 11]],
        14 => ['hour', 1, [9, 10, 11, 12, 13]],
        15 => ['hour', 2, [11, 13, 14]],
        16 => ['hour', 3, [13, 14]],
        17 => ['hour', 4, [13, 14, 15]],
        18 => ['hour', 6, [14, 15, 16]],
        19 => ['hour', 8, [14, 15, 17]],
        20 => ['hour', 12, [14, 15, 16, 17, 18, 19]],
        21 => ['day', 1, [14, 18, 20]],
        22 => ['day', 7, [21]],
        23 => ['day', 14, [21, 22]],
        24 => ['month', 1, [21]],
        25 => ['month', 2, [21, 24]],
        26 => ['month', 3, [24]],
        27 => ['month', 6, [24, 25, 26]],
        28 => ['year', 1, [24, 25, 26, 27]],
        29 => ['year', 2, [27, 28]],
        30 => ['year', 5, [28]],
        31 => ['year', 10, [28, 29, 30]],
        32 => ['year', 20, [28, 29, 30, 31]],
        33 => ['year', 50, [30, 31]],
        34 => ['year', 100, [31, 32, 33]],
        35 => ['year', 500, [34]],
        36 => ['year', 1000, [34, 35]],
        37 => ['year', 10000],
        38 => ['year', 100000],
        39 => ['year', 1000000],
    ];

    /**
     * The size of each unit in seconds.
     */
    protected static $unit_sizes = [
        'second' => 1,
        'minute' => 60,
        'hour' => 3600,
        'day' => 86400,
        'month' => 2629800, // avg year / 12
        'year' => 31557600,  // avg year = 365.25 days (ignoring leap centuries)
    ];

    /**
     * Default format strings for each unit size.
     */
    protected static $formats = [
        'second' => 'Y-m-d H:i:s',
        'minute' => 'Y-m-d H:i',
        'hour' => 'Y-m-d H:i',
        'day' => 'Y-m-d',
        'month' => 'Y-m',
        'year' => 'Y',
    ];

    /**
     * Multi-level format strings.
     */
    protected static $formats_level = [
        'second' => ['H:i:s', 'd', 'F', 'Y'],
        'minute' => ['H:i', 'd', 'F', 'Y'],
        'hour' => ['H:i', 'd', 'F', 'Y'],
        'day' => ['d', 'M', 'Y'],
        'month' => ['M', 'Y'],
        'year' => ['Y'],
    ];

    public function __construct(
        $length,
        $max_val,
        $min_val,
        $min_space,
        $fixed_division,
        $levels,
        $options,
    ) {
        if ($max_val < $min_val) {
            throw new \Exception('Zero length axis (min >= max)');
        }

        $this->length = $length;
        // if $min_space > $length, use $length instead
        $this->min_space = $min_space = min($length, $min_space);
        $this->uneven = false;

        // convert actual min/max to start/end times
        $start_date = new \DateTime('@'.$min_val);
        $end_date = new \DateTime('@'.$max_val);
        $this->timezone = new \DateTimeZone(date_default_timezone_get());
        $start_date->setTimezone($this->timezone);
        $end_date->setTimezone($this->timezone);
        $this->formatter = new DateTimeFormatter();

        // set the week start day before finding divisions
        if (isset($options['datetime_week_start'], self::$weekdays[$options['datetime_week_start']])
        ) {
            self::$week_start = $options['datetime_week_start'];
        }

        if (!empty($fixed_division)) {
            [$units, $count] = self::parseFixedDivisions(
                $fixed_division,
                $min_val,
                $max_val,
                $length,
            );
            $start = self::startTime($start_date, $units, $count);
            $end = self::endTime($end_date, $units, $count, $start);

            $this->start = $start->format('U');
            $this->end = $end->format('U');
            $this->duration = ($this->end - $this->start) + 1;
            $this->grid_units = $units;
            $this->grid_unit_count = $count;

            // set the division number (if it is a standard division)
            $this->division = 0;

            foreach (self::$divisions as $key => $div) {
                if ($div[0] === $units && $div[1] === $count) {
                    $this->division = $key;
                }
            }
        } else {
            // find a sensible division
            $div = self::findBestDivision(
                $start_date,
                $end_date,
                $length,
                $min_space,
            );
            $this->div = $div;
            $this->start = $div[0]->format('U');
            $this->end = $div[1]->format('U');
            $this->duration = ($this->end - $this->start) + 1;

            $this->division = $div[2];
            $this->grid_units = self::$divisions[$this->division][0];
            $this->grid_unit_count = self::$divisions[$this->division][1];
        }

        $this->label_callback = [$this, 'dateText'];

        // get the axis text format from the options, or use defaults
        $this->axis_text_format = self::$formats[$this->grid_units];

        if (is_numeric($levels) && $levels > 1) {
            $this->levels = (int) $levels;
            $this->axis_text_format = self::$formats_level[$this->grid_units];
        }

        $text_format = null;

        if (isset($options['datetime_text_format'])) {
            $fmt = $options['datetime_text_format'];

            if (\is_array($fmt) && isset($fmt[$this->grid_units])) {
                $text_format = $fmt[$this->grid_units];
            } elseif (!empty($fmt)) {
                $text_format = $fmt;
            }
        }

        if ($text_format !== null) {
            $this->axis_text_format = $text_format;
        }
    }

    /**
     * Returns the distance in pixels $u takes from $pos.
     *
     * @param mixed $pos
     * @param mixed $u
     */
    public function measureUnits($pos, $u)
    {
        $i = Coords::parseValue($pos);

        // start with a plain date
        $datetime = new \DateTime('@0');
        $datetime->setTimezone($this->timezone);

        if ($i['simple']) {
            $a = new Number($pos);
            $datetime = new \DateTime('@'.$a);
            $datetime->setTimezone($this->timezone);
        } elseif ($i['grid']) {
            if ($i['units']) {
                [$units, $unit_count] = self::parseFixedDivisions(
                    $i['value'],
                    $this->start,
                    $this->end,
                    $this->length,
                );
                $datetime->setTimezone($this->timezone);
                $uc = new Number($unit_count);
                $datetime->modify('+'.$uc.' '.$units);
            } else {
                $v = Graph::dateConvert($i['value']);
                $a = new Number($v);
                $datetime = new \DateTime('@'.$a);
                $datetime->setTimezone($this->timezone);
            }
        }

        $start_value = $datetime->format('U');
        $start_pos = $this->length * ($start_value - $this->start) / $this->duration;

        [$units, $unit_count] = self::parseFixedDivisions(
            $u,
            $this->start,
            $this->end,
            $this->length,
        );

        $datetime->setTimezone($this->timezone);
        $uc = new Number($unit_count);
        $datetime->modify('+'.$uc.' '.$units);
        $value = $datetime->format('U');
        $end_pos = $this->length * ($value - $this->start) / $this->duration;

        return $end_pos - $start_pos;
    }

    /**
     * Returns the position of a value on the axis.
     *
     * @param mixed      $index
     * @param null|mixed $item
     */
    public function position($index, $item = null)
    {
        if ($item === null) {
            $value = $index;

            // support '10 hours' type of position
            if (\is_string($index) && str_contains($index, ' ')) {
                [$units, $unit_count] = self::parseFixedDivisions(
                    $value,
                    $this->start,
                    $this->end,
                    $this->length,
                );

                // initialise with 0, not the current time/date
                $datetime = new \DateTime('@0');
                $datetime->setTimezone($this->timezone);
                $uc = new Number($unit_count);
                $datetime->modify('+'.$uc.' '.$units);
                $value = $datetime->format('U');
            }
        } else {
            $value = $item->key;
        }

        return $this->length * ($value - $this->start) / $this->duration;
    }

    /**
     * Returns the position by key, which is a datetime string.
     *
     * @param mixed $key
     */
    public function positionByKey($key)
    {
        // ignore grid-relative positions
        if (\in_array($key, ['t', 'l', 'b', 'r', 'h', 'w', 'cx', 'cy'], true)) {
            return null;
        }

        $value = Graph::dateConvert($key);

        if ($value) {
            return $this->position($value);
        }

        return null;
    }

    /**
     * Returns the value at a position on the axis.
     *
     * @param mixed $position
     */
    public function value($position)
    {
        return $this->start + $position * $this->duration / $this->length;
    }

    /**
     * Returns the position of the origin.
     */
    public function origin()
    {
        // time started before whatever date the graph starts with
        return 0;
    }

    /**
     * Returns the unit size.
     */
    public function unit()
    {
        $u = self::$unit_sizes[$this->grid_units];
        $w = $this->length * $u / $this->duration;

        return max(1, $w);
    }

    /**
     * Not actually 0, but the position of the axis.
     */
    public function zero()
    {
        return 0;
    }

    /**
     * Returns the grid points as an array of GridPoints.
     *
     * @param mixed $start
     */
    public function getGridPoints($start)
    {
        if ($start === null) {
            return;
        }

        $c = $pos = 0;
        $dlength = $this->length + 1; // allow 1 pixel overflow

        $units = $this->grid_units;
        $unit_count = $this->grid_unit_count;
        $value = $this->start;

        // prevent too many grid points if something goes wrong
        $limit = 1000;

        $points = [];

        while (floor($pos) < $dlength && ++$c < $limit) {
            $position = $start + ($pos * $this->direction);
            $points[] = $this->getGridPoint($position, $value);

            $datetime = new \DateTime('@'.$this->start);
            $datetime->setTimezone($this->timezone);
            $offset = new Number($c * $unit_count);
            $datetime->modify('+'.$offset.' '.$units);
            $value = $datetime->format('U');
            $pos = $this->position($value);
        }

        return $points;
    }

    /**
     * Returns the grid subdivision points as an array.
     *
     * @param mixed $min_space
     * @param mixed $min_unit
     * @param mixed $start
     * @param mixed $fixed
     */
    public function getGridSubdivisions($min_space, $min_unit, $start, $fixed)
    {
        $subdivs = [];

        if (!empty($fixed)) {
            [$units, $unit_count] = self::parseFixedDivisions(
                $fixed,
                $this->start,
                $this->end,
                $this->length,
            );
        } else {
            // if the main division is the lowest level, there is no subdivision
            if ($this->division === 0) {
                return $subdivs;
            }

            $start_date = new \DateTime('@'.$this->start);
            $start_date->setTimezone($this->timezone);
            $end_date = new \DateTime('@'.$this->end);
            $end_date->setTimezone($this->timezone);

            $div = self::findBestDivision(
                $start_date,
                $end_date,
                $this->length,
                $min_space,
                $this->division,
            );

            // if no divisions found, stop now
            if ($div === null) {
                return $subdivs;
            }

            $division = $div[2];

            $units = self::$divisions[$division][0];
            $unit_count = self::$divisions[$division][1];
        }

        $value = $this->start;

        // get the main divisions, turn them into a map of where not to put a
        // subdivision
        $main_divisions = $this->getGridPoints($start);
        $not_here = [];

        foreach ($main_divisions as $d) {
            $not_here[floor($d->position)] = $d->value;
        }

        // prevent too many grid points if something goes wrong
        $limit = 1000;

        $c = $pos = 0;
        $dlength = $this->length + 1; // allow 1 pixel overflow
        $text = '';

        while (floor($pos) < $dlength && ++$c < $limit) {
            $position = $start + ($pos * $this->direction);

            if (!isset($not_here[floor($position)])
              && !isset($not_here[ceil($position)])) {
                $subdivs[] = new GridPoint($position, $text, $value);
            }

            $datetime = new \DateTime('@'.$this->start);
            $datetime->setTimezone($this->timezone);
            $offset = new Number($c * $unit_count);
            $datetime->modify('+'.$offset.' '.$units);
            $value = $datetime->format('U');
            $pos = $this->position($value);
        }

        return $subdivs;
    }

    /**
     * Formats the axis text.
     *
     * @param mixed $f
     */
    public function dateText($f)
    {
        $dt = new \DateTime('@'.$f);
        $dt->setTimezone($this->timezone);

        if (!\is_array($this->axis_text_format)) {
            return $this->formatter->format($dt, $this->axis_text_format);
        }

        $strings = [];

        foreach ($this->axis_text_format as $fmt) {
            $strings[] = $this->formatter->format($dt, $fmt);
        }

        return $strings;
    }

    /**
     * Returns the format in use.
     *
     * @param mixed $level
     */
    public function getFormat($level = 0)
    {
        if (\is_array($this->axis_text_format)) {
            return $this->axis_text_format[$level];
        }

        return $this->axis_text_format;
    }

    /**
     * Returns the formatted, localized date/time.
     *
     * @param mixed $dt
     * @param mixed $fmt
     */
    public function format($dt, $fmt)
    {
        return $this->formatter->format($dt, $fmt);
    }

    /**
     * Finds the best division for the given start and end date/time.
     *
     * @param DateTime $start
     * @param DateTime $end
     * @param number   $length
     * @param number   $min_space
     * @param number   $subdivision
     *                              Returns array($start, $end, $div_index, $div_count) or NULL if there is no
     *                              subdivision possible
     */
    private static function findBestDivision(
        $start,
        $end,
        $length,
        $min_space,
        $subdivision = false,
    ) {
        $max_divisions = floor($length / $min_space);
        $duration_s = $end->format('U') - $start->format('U');
        $avg_duration = ceil($duration_s / $max_divisions);

        $choice = null;
        $divisions = 1;
        $subdivide = false;

        if ($subdivision === false) {
            $d_list = array_keys(self::$divisions);
        } else {
            // give up now if this can't be subdivided
            if (!isset(self::$divisions[$subdivision][2])) {
                return null;
            }

            $d_list = self::$divisions[$subdivision][2];
            $subdivide = true;
        }

        foreach ($d_list as $i) {
            $d = self::$divisions[$i];
            $div_duration = $d[1] * self::$unit_sizes[$d[0]];

            if ($div_duration >= $avg_duration) {
                $divisions = floor($duration_s / $div_duration);
                $unit = $d[0];
                $nunits = $d[1];

                // get the updated start and end times to fit with the spacing
                $new_start = self::startTime($start, $unit, $nunits);
                $new_end = self::endTime($end, $unit, $nunits, $new_start);
                $new_duration = $new_end->format('U') - $new_start->format('U');
                $new_avg_duration = (int) ceil($new_duration / $max_divisions);

                if ($div_duration >= $new_avg_duration) {
                    $choice = $d;

                    break;
                }
            }
        }

        if ($choice === null) {
            if ($subdivide) {
                return null;
            }

            throw new \Exception('Unable to find divisions for DateTime axis');
        }

        return [$new_start, $new_end, $i, $divisions];
    }

    /**
     * Returns the start of the current $n $units of $time.
     *
     * @param mixed $time
     * @param mixed $unit
     * @param mixed $n
     */
    private static function startTime($time, $unit, $n)
    {
        $formats = [
            'year' => '00:00:00 January 1',
            'month' => '00:00:00 first day of',
            'day' => '00:00:00',
        ];
        $datetime = clone $time;

        if ($n === 1 && isset($formats[$unit])) {
            $datetime->modify($formats[$unit]);
        } else {
            switch ($unit) {
                case 'year':
                    $y = $time->format('Y');
                    $y -= $y % $n;
                    $datetime->setDate($y, 1, 1);
                    $datetime->setTime(0, 0);

                    break;
                case 'month':
                    $y = $time->format('Y');
                    $m = $time->format('n') - 1;
                    $m -= $m % $n;
                    $datetime->setDate($y, $m + 1, 1);
                    $datetime->setTime(0, 0);

                    break;
                case 'day':
                    $day = $datetime->format('w'); // 0-6, Sun-Sat
                    $dow = self::$weekdays[self::$week_start];

                    // always start on the right weekday
                    if ($day === $dow) {
                        $datetime->modify('00:00:00');
                    } else {
                        $datetime->modify('00:00:00 last '.self::$week_start);
                    }

                    break;
                case 'hour':
                    $h = $datetime->format('H');

                    if ($n > 1) {
                        $h -= ($h % $n);
                    }

                    $newtime = sprintf('%02d:00:00', $h);
                    $datetime->modify($newtime);

                    break;
                case 'minute':
                    $m = $datetime->format('i');

                    if ($n > 1) {
                        $m -= ($m % $n);
                    }

                    $newtime = $datetime->format(sprintf('H:%02d:00', $m));
                    $datetime->modify($newtime);

                    break;
                case 'second':
                    $s = $datetime->format('s');

                    if ($n > 1) {
                        $s -= ($s % $n);
                    }

                    $newtime = $datetime->format(sprintf('H:i:%02d', $s));
                    $datetime->modify($newtime);

                    break;
            }
        }

        return $datetime;
    }

    /**
     * Returns the end of the current $n $units of $time, started at $start.
     *
     * @param mixed $time
     * @param mixed $unit
     * @param mixed $n
     * @param mixed $start
     */
    private static function endTime($time, $unit, $n, $start)
    {
        $formats = [
            'year' => '23:59:59 December 31',
            'month' => '23:59:59 last day of',
            'day' => '23:59:59',
        ];
        $datetime = clone $time;

        if ($n === 1 && isset($formats[$unit])) {
            $datetime->modify($formats[$unit]);
        } else {
            switch ($unit) {
                case 'year':
                    $y = $time->format('Y');
                    $new_y = new Number($y - ($y % $n) + $n - 1);
                    $datetime->modify($new_y.'-12-31 23:59:59');

                    break;
                case 'month':
                    $datetime->modify('00:00:00 first day of');
                    $diff = $datetime->diff($start);
                    $months = ($diff->y * 12) + $diff->m;
                    $new_months = new Number($months - ($months % $n) + $n - 1);
                    $datetime = clone $start;
                    $datetime->modify('+'.$new_months.' month 23:59:59 last day of');

                    break;
                case 'day':
                    $datetime->modify('00:00:00');
                    $diff = $datetime->diff($start);
                    $days = new Number($diff->days - ($diff->days % $n) + $n - 1);
                    $datetime = clone $start;
                    $datetime->modify('+'.$days.' day 23:59:59');

                    break;
                case 'hour':
                    if ($n > 1) {
                        $diff = $datetime->diff($start);
                        $hours = ($diff->days * 24) + $diff->h;
                        $hours = new Number($hours - ($hours % $n) + $n - 1);
                        $datetime = clone $start;
                        $datetime->modify('+'.$hours.' hour 59 minute 59 second');
                    } else {
                        $h = $datetime->format('H');
                        $newtime = sprintf('%02d:59:59', $h);
                        $datetime->modify($newtime);
                    }

                    break;
                case 'minute':
                    if ($n > 1) {
                        $diff = $datetime->diff($start);
                        $minutes = ((($diff->days * 24) + $diff->h) * 60) + $diff->i;
                        $minutes = new Number($minutes - ($minutes % $n) + $n - 1);
                        $datetime = clone $start;
                        $datetime->modify('+'.$minutes.' minute 59 second');
                    } else {
                        $m = $datetime->format('i');
                        $newtime = $datetime->format(sprintf('H:%02d:59', $m));
                        $datetime->modify($newtime);
                    }

                    break;
                case 'second':
                    if ($n > 1) {
                        $diff = $datetime->diff($start);
                        $seconds = ($diff->days * 86400) + ($diff->h * 3600) +
                          ($diff->i * 60) + $diff->s;
                        $seconds = new Number($seconds - ($seconds % $n) + $n - 1);
                        $datetime = clone $start;
                        $datetime->modify('+'.$seconds.' second');
                    }

                    // if $n == 1, no modifications are required
                    break;
            }
        }

        return $datetime;
    }

    /**
     * Converts a fixed division option to a unit size and count.
     * $start_time and $end_time are unix timestamps
     * Returns array($unit, $count).
     *
     * @param mixed $fixed_opt
     * @param mixed $start_time
     * @param mixed $end_time
     * @param mixed $axis_length
     */
    private static function parseFixedDivisions(
        $fixed_opt,
        $start_time,
        $end_time,
        $axis_length,
    ) {
        if (str_contains($fixed_opt, ' ')) {
            // number and unit
            [$unit_count, $units] = explode(' ', $fixed_opt);
        } elseif (is_numeric($fixed_opt)) {
            // number without units
            $unit_count = $fixed_opt * 1;
            // make a guess at the units to use
            $min_interval = ($end_time - $start_time) / $axis_length;

            foreach (self::$unit_sizes as $unit => $size) {
                if ($size > $min_interval) {
                    break;
                }
            }

            $units = $unit;
        } else {
            // unit without number
            $unit_count = 1;
            $units = $fixed_opt;
        }

        $units = rtrim($units, 's');

        if (!isset(self::$unit_sizes[$units])) {
            throw new \Exception('Unrecognized datetime units ['.$units.']');
        }

        if (!is_numeric($unit_count) || $unit_count < 1) {
            $unit_count = 1;
        }

        return [$units, $unit_count];
    }
}
