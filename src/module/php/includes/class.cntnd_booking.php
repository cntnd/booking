<?php
cInclude('module', 'includes/class.datetime.php');
/**
 * cntnd_booking Class
 */
class CntndBooking {

  private $daterange;
  private $show_daterange;
  private $interval;
  private $timerange_from;
  private $timerange_to;
  private $mailto;
  private $blocked_days;

  function __construct($daterange, $show_daterange, $interval, $timerange_from, $timerange_to, $mailto, $blocked_days) {
    $this->daterange=$daterange;
    $this->show_daterange=$show_daterange;
    $this->interval=$interval;
    $this->timerange_from=$timerange_from;
    $this->timerange_to=$timerange_to;
    $this->mailto=$mailto;
    $this->blocked_days=$blocked_days;
  }

  public function daterange(){
    return $this->daterange;
  }

  public function render(){
    $timerange = DateTimeUtil::getTimerange($this->timerange_from, $this->timerange_to, $this->interval);
    $daterange = DateTimeUtil::getDaterange($this->daterange,$this->blocked_days);

    echo '<table class="table">';
    echo '<thead><tr>';
    echo '<th>Datum</th>';
    foreach ($timerange as $time) {
      $to = DateTimeUtil::getToWithInterval($time[0],$this->interval);
      echo '<th>'.$time[1].'<span class="separator">-</span>'.$to.'</th>';
    }
    echo '</tr></thead>';
    echo '<tbody>';
    foreach ($daterange as $date) {
      $class='res_hide';
      if (DateTimeUtil::isEvenWeek($date[0])){
        $class.=' even-dat';
      }
      if (DateTimeUtil::isMonday($date[0])){
        $class.=' kw-dat';
      }
      if (!DateTimeUtil::isInShowRange($this->daterange,$this->show_daterange,$date[0])){
        $class.=' not-in-range hide';
      }
      else {
        $class.=' in-range';
      }
      echo '<tr class="'.$class.'"">';
      echo '<th scope="row"><nobr class="cntnd_booking-date" data-date="'.$date[0].'">'.$date[1].'</nobr></th>';
      foreach ($timerange as $time) {
        $timestamp = strtotime($date[0].' '.$time[1]);
        echo '<td class="free">';
        echo '<label for="'.$timestamp.'" class="res_checkbox">';
        echo '<input id="'.$timestamp.'" class="cntnd_booking-checkbox" name="dates[]" type="checkbox" value="'.$timestamp.'" />';
        echo '</label>';
        echo '</td>';
      }
      echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
  }

  public static function validate($post,$interval){
    if (is_array($post)){
      var_dump(self::validateRequired($post));
      return (self::validateDates($post,$interval) && self::validateRequired($post));
    }
    return false;
  }

  private static function validateDates($post,$interval){
    $valid=false;
    if (array_key_exists('dates',$post) && is_array($post['dates'])){
      $valid=true;
      $dates = $post['dates'];
      $interval_ms = $interval*60; // interval is in sec and timestamp is in ms
      sort($dates);
      foreach ($dates as $date) {
        if (!empty($old)){
          $diff = $date-$old;
          if ($diff>$interval_ms){
            $valid=false;
          }
          if (date('d.m.Y',$old)!=date('d.m.Y',$date)){
            $valid=false;
          }
        }
        $old=$date;
      }
    }
    return $valid;
  }

  private static function validateRequired($post){
    $valid=false;
    if (array_key_exists('required',$post)){
      $valid=true;
      $required = json_decode(base64_decode($post['required']), true);
      if (is_array($required)){
        foreach ($required as $value) {
          if (empty($post[$value])){
            $valid=false;
          }
        }
      }
    }
    return $valid;
  }
}
?>
