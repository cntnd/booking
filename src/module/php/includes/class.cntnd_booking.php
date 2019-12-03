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
    $daterange = DateTimeUtil::getDaterange($this->daterange);
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
      echo '<tr class="'.$class.'"">';
      echo '<th scope="row"><nobr class="cntnd_booking-date" data-date="'.$date[0].'">'.$date[1].'</nobr></th>';
      foreach ($timerange as $time) {
        $timestamp = strtotime($date[0].' '.$time[1]);
        echo '<td class="free">';
        echo '<label for="'.$timestamp.'" class="res_checkbox">';
        echo '<input id="'.$timestamp.'" type="checkbox" value="'.$timestamp.'" />';
        echo '</label>';
        echo '</td>';
      }
      echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
  }
}
?>
