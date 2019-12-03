<?php
/**
 * DateTimeUtil Class
 */
class DateTimeUtil {

  public static function getTimerange($from, $to, $interval, $including=false){
    $range=[];
    $max = floor(($to - $from) / $interval);
    if (!$including){
      $max=$max-1;
    }
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
      $range[]=array($dates[0]->format('d.m.Y'), self::getReadableDate($dates[0]));
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

  public static function isEvenWeek($date){
    $dt = new DateTime($date);
    return ($dt->format('W') % 2 == 0);
  }

  public static function isMonday($date){
    $dt = new DateTime($date);
    return ($dt->format('w')==1);
  }

  public static function getWeekday($date){
    $wtag[0] = "So.";
    $wtag[1] = "Mo.";
    $wtag[2] = "Di.";
    $wtag[3] = "Mi.";
    $wtag[4] = "Do.";
    $wtag[5] = "Fr.";
    $wtag[6] = "Sa.";
    $dt = self::checkDateTime($date);
    return $wtag[$dt->format('w')];
  }

  public static function getReadableDate($date){
    $weekday = self::getWeekday($date);
    $dt = self::checkDateTime($date);
    return $weekday.' '.$dt->format('d.m.Y');
  }

  public static function checkDateTime($date){
    if (is_a($date,'DateTime')){
      return $date;
    }
    else {
      return new DateTime($date);
    }
  }

  public static function getToWithInterval($from,$interval){
    return self::getReadableTime($from+$interval);
  }
}
