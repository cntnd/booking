<?php
/**
 * DateTimeUtil Class
 */
class DateTimeUtil {

  public static function getTimerange($from, $to, $interval){
    $range=[];
    $max = floor(($to - $from) / $interval);
    for ($i=0; $i <= $max; $i++) {
      $seconds = $from + ($i*$interval);
      $range[]=array($seconds, self::getReadableTime($seconds));
    }
    return $range;
  }

  public static function getDaterange($daterange){
    $range=[];
    $dates=self::getDatesFromDaterange($daterange);
    $max=self::getDaysFromDateRange($daterange);
    for ($i=0; $i <= $max; $i++) {
      $range[]=$dates[0]->format('d.m.Y');
      $dates[0]->modify('+1 day');
    }
    return $range;
  }

  public static function getDatesFromDaterange($daterange){
    $dates = self::getStringsFromDaterange($daterange);
    return array(new DateTime($dates[0]), new DateTime($dates[1]));
  }

  public static function getStringsFromDaterange($daterange){
    return explode(" - ",$daterange);
  }

  public static function getDaysFromDateRange($daterange){
    $dates = self::getStringsFromDaterange($daterange);
    $max = $dates[1] - $dates[0];
    return $max;
  }

  public static function getHourMinute($seconds){
    $hour = floor($seconds / 60);
    $minute = (($seconds / 60) - $hour) * 60;
    return array($hour, $minute);
  }

  public static function getReadableTime($seconds){
    $time = self::getHourMinute($seconds);
    return sprintf("%02d:%02d", $time[0], $time[1]);
  }
}
