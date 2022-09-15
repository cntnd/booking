<?php

/**
 * DateTimeUtil Class
 */
class DateTimeUtil
{

    public static function getTimerange($from, $to, $interval, $including = false)
    {
        $range = [];
        $max = floor(($to - $from) / $interval);
        if (!$including) {
            $max = $max - 1;
        }
        for ($i = 0; $i <= $max; $i++) {
            $seconds = $from + ($i * $interval);
            $range[] = array($seconds, self::getReadableTime($seconds));
        }
        return $range;
    }

    public static function getDaterange($daterange, $blocked_days = false, $past = true)
    {
        $range = [];
        $dates = self::getDatesFromDaterange($daterange, $past);
        $max = self::getDaysFromDateRange($daterange, $past);

        for ($i = 0; $i <= $max; $i++) {
            if (!self::isBlockedDay($dates[0], $blocked_days)) {
                $range[] = array($dates[0]->format('d.m.Y'), self::getReadableDate($dates[0]));
            }
            $dates[0]->modify('+1 day');
        }
        return $range;
    }

    // todo blocked days
    public static function getShowDaterange($daterange, $show_daterange)
    {
        $today = new DateTime();
        $range = self::getDatesFromDaterange($daterange);
        if ($show_daterange != "all") {
            $d = $range[0];
            if ($today > $range[0]) {
                $d = $today;
            }
            $until = $d->modify($show_daterange);
            return self::getIndexFromDate($until);
        }
        return self::getIndexFromDate($range[1]);
    }

    public static function getDatesFromDaterange($daterange, $past = true)
    {
        $dates = self::getStringsFromDaterange($daterange);
        $date_from = new DateTime($dates[0]);
        $date_now = new DateTime();
        if (!$past && $date_from < $date_now) {
            $date_from = $date_now;
        }
        return array($date_from, new DateTime($dates[1]));
    }

    public static function getDateFromDaterange($daterange, $index, $past = true)
    {
        $dates = self::getDatesFromDaterange($daterange, $past);
        if ($index > 0) {
            $date = $dates[0];
            $date->modify('+' . $index . ' day');
            return $date;
        }
        return $dates[0];
    }

    public static function getStringsFromDaterange($daterange)
    {
        return explode(" - ", $daterange);
    }

    public static function getStringsFromTimes($times)
    {
        return explode("-", $times);
    }

    public static function getDaysFromDateRange($daterange, $past = true)
    {
        $dates = self::getDatesFromDaterange($daterange, $past);
        $max = $dates[1]->diff($dates[0])->days;
        return $max;
    }

    public static function getFromDateFromDaterange($daterange)
    {
        $dates = self::getDatesFromDaterange($daterange);
        return $dates[0];
    }

    public static function getToDateFromDaterange($daterange)
    {
        $dates = self::getDatesFromDaterange($daterange);
        return $dates[1];
    }

    public static function getHourMinute($seconds)
    {
        $hour = floor($seconds / 60);
        $minute = (($seconds / 60) - $hour) * 60;
        return array($hour, $minute);
    }

    public static function getIndexFromDate($date)
    {
        $dt = $date;
        if (!$date instanceof DateTime) {
            $dt = new DateTime($date);
        }
        return ($dt->format("Ymd"));
    }

    public static function getIndexFromDateTime($dateTime)
    {
        $dt = new DateTime($dateTime);
        return ($dt->format("Hi"));
    }

    public static function getIndexFromDateAndTime($date, $time)
    {
        $d = self::checkDateTime($date)->format("d.m.Y");
        $datetime = strtotime($d . " " . $time);
        return date("YmdHi", $datetime);
    }

    public static function getRecurrentIndexFromDate($date)
    {
        $dt = new DateTime($date);
        return ($dt->format("w"));
    }

    public static function getDateFromIndexDateTime($index)
    {
        $year = substr($index, 0, 4);
        $month = substr($index, 4, 2);
        $day = substr($index, 6, 2);
        $hour = substr($index, 8, 2);
        $minute = substr($index, -2);
        return new DateTime($day . '.' . $month . '.' . $year . ' ' . $hour . ':' . $minute);
    }

    public static function getTimeFromIndexDateTime($index)
    {
        $dt = self::getDateFromIndexDateTime($index);
        return ($dt->format("H:i"));
    }

    public static function getReadableTime($seconds)
    {
        $time = self::getHourMinute($seconds);
        return sprintf("%02d:%02d", $time[0], $time[1]);
    }

    public static function getReadableTimeFromDate($date)
    {
        $dt = self::checkDateTime($date);
        return $dt->format('H:i');
    }

    public static function getReadableTimeFromTime($time)
    {
        $d = self::getInsertDate(new DateTime());
        $dt = new DateTime($d . " " . $time);
        return $dt->format('H:i');
    }

    public static function isEvenWeek($date)
    {
        $dt = new DateTime($date);
        return ($dt->format('W') % 2 == 0);
    }

    public static function isMonday($date)
    {
        $dt = new DateTime($date);
        return ($dt->format('w') == 1);
    }

    public static function getWeekday($date)
    {
        $dt = self::checkDateTime($date);
        return self::getWeekdayByIndex($dt->format('w'));
    }

    public static function getWeekdayIndex($date)
    {
        $dt = self::checkDateTime($date);
        return $dt->format('w');
    }

    public static function getWeekdayByIndex($index)
    {
        $wtag[0] = "So.";
        $wtag[1] = "Mo.";
        $wtag[2] = "Di.";
        $wtag[3] = "Mi.";
        $wtag[4] = "Do.";
        $wtag[5] = "Fr.";
        $wtag[6] = "Sa.";
        return $wtag[$index];
    }

    public static function getLongWeekdayByIndex($index)
    {
        $wtag[0] = "Sonntag";
        $wtag[1] = "Montag";
        $wtag[2] = "Dienstag";
        $wtag[3] = "Mittwoch";
        $wtag[4] = "Donnerstag";
        $wtag[5] = "Freitag";
        $wtag[6] = "Samstag";
        return $wtag[$index];
    }

    public static function getIndexFromWeekday($weekday)
    {
        if ($weekday == 0) {
            return 7;
        } else {
            $index = $weekday - 1;
            return $index;
        }
    }

    public static function getReadableDate($date)
    {
        $weekday = self::getWeekday($date);
        $dt = self::checkDateTime($date);
        return $weekday . ' ' . $dt->format('d.m.Y');
    }

    public static function checkDateTime($date)
    {
        if (is_a($date, 'DateTime')) {
            return $date;
        } else {
            return new DateTime($date);
        }
    }

    public static function isBlockedDay($date, $blocked_days)
    {
        if (is_array($blocked_days)) {
            $dt = self::checkDateTime($date);
            $w = $dt->format('w');
            if (array_key_exists($w, $blocked_days)) {
                return $blocked_days[$w];
            }
        }
        return false;
    }

    public static function compare($date1, $date2)
    {
        $d1 = self::checkDateTime($date1);
        $d2 = self::checkDateTime($date2);
        return ($d1 == $d2);
    }

    public static function getInsertDate($date)
    {
        return self::checkDateTime($date)->format("Y-m-d");
    }

    public static function getInsertDateTime($date, $time)
    {
        $d = self::getInsertDate($date);
        $datetime = strtotime($d . " " . $time);
        return date("Y-m-d H:i:s", $datetime);
    }

    public static function getInsertTimeOrNull($time)
    {
        if (!empty($time)) {
            $d = self::getInsertDate(new DateTime());
            $datetime = strtotime($d . " " . $time);
            return date("Y-m-d H:i:s", $datetime);
        }
        return NULL;
    }

    public static function getInsertDay($date)
    {
        return self::checkDateTime($date)->format("w");
    }

    public static function isPast($date)
    {
        $now = new DateTime();
        return self::checkDateTime($date) < $now;
    }

    public static function getFirstMonday($dateRange)
    {
        $dates = self::getDatesFromDaterange($dateRange);
        return $dates[0]->modify("first monday of this month");
    }

    public static function getFirstWeekday($dateRange, $weekday)
    {
        $dates = self::getDatesFromDaterange($dateRange);
        return $dates[0]->modify("first ".self::weekday($weekday)." of this month");
    }

    private static function weekday($index) {
        $weekday = "monday";
        switch($index) {
            case 0: $weekday = "sunday";
                break;
            case 1: $weekday = "monday";
                break;
            case 2: $weekday = "tuesday";
                break;
            case 3: $weekday = "wednesday";
                break;
            case 4: $weekday = "thursday";
                break;
            case 5: $weekday = "friday";
                break;
            case 6: $weekday = "saturday";
                break;
        }
        return $weekday;
    }
}
