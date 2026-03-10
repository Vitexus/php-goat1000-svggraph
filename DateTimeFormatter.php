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
 * Class for formatting date/time values.
 */
class DateTimeFormatter
{
    protected $timezone;
    protected $localize = false;
    protected $idf;

    public function __construct()
    {
        $this->timezone = new \DateTimeZone(date_default_timezone_get());

        // see if output needs localization
        if (\extension_loaded('intl')) {
            $locale = setlocale(\LC_TIME, 0);

            if ($locale && $locale !== 'C' && $locale !== 'POSIX'
              && !str_contains($locale, 'en_')) {
                $this->localize = true;
                $this->idf = new \IntlDateFormatter(
                    $locale,
                    \IntlDateFormatter::FULL,
                    \IntlDateFormatter::FULL,
                );
            }
        }
    }

    /**
     * Returns the formatted, localized date/time.
     *
     * @param mixed $dt
     * @param mixed $fmt
     * @param mixed $strip_offset
     */
    public function format($dt, $fmt, $strip_offset = false)
    {
        $datetime = clone $dt;
        $datetime->setTimezone($this->timezone);

        if ($strip_offset) {
            $offset = $this->timezone->getOffset($datetime);

            if ($offset < 0) {
                $datetime->modify($offset.' second');
            } else {
                $datetime->modify('+'.$offset.' second');
            }
        }

        if (!$this->localize) {
            return $datetime->format($fmt);
        }

        // DateTime class doesn't do localization, so these fields are passed to
        // IntlDateFormatter instead
        $map = [
            'D' => 'E',
            'l' => 'EEEE',
            'M' => 'MMM',
            'F' => 'MMMM',
        ];

        $result = '';
        $unixtime = $datetime->format('U');

        for ($i = 0; $i < \strlen($fmt); ++$i) {
            $char = $fmt[$i];

            if (isset($map[$char])) {
                $this->idf->setPattern($map[$char]);
                $result .= $this->idf->format($unixtime);
            } else {
                $result .= $datetime->format($fmt[$i]);
            }
        }

        return $result;
    }

    /**
     * Returns the list of day names.
     */
    public function getLongDays()
    {
        return $this->getDateStrings('l', 'd');
    }

    /**
     * Returns the list of abbreviated day names.
     */
    public function getShortDays()
    {
        return $this->getDateStrings('D', 'd');
    }

    /**
     * Returns the list of month names.
     */
    public function getLongMonths()
    {
        return $this->getDateStrings('F', 'm');
    }

    /**
     * Returns the list of abbreviated month names.
     */
    public function getShortMonths()
    {
        return $this->getDateStrings('M', 'm');
    }

    /**
     * Returns a list of day or month strings using IntlDateFormatter to localize.
     *
     * @param mixed $fmt
     * @param mixed $inc
     */
    private function getDateStrings($fmt, $inc)
    {
        // 1978 started on a Sunday
        $dt = new \DateTime('1978-01-01T12:00:00Z');

        if ($inc === 'm') {
            $count = 12;
            $offset = 'month';
        } else {
            $count = 7;
            $offset = 'day';
        }

        $strings = [];

        for ($i = 0; $i < $count; ++$i) {
            if ($i) {
                $dt->modify('+1 '.$offset);
            }

            $strings[] = $this->format($dt, $fmt);
        }

        return $strings;
    }
}
