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
    $max = DateTimeUtil::getDaysFromDateRange($this->daterange);
    echo '<table>';
    echo '<thead><tr>';
    echo '<th>Datum</th>';
    foreach ($timerange as $time) {
      echo '<th>'.$time[1].'</th>';
    }
    echo '</tr></thead>';
    echo '<tbody>';
    foreach ($daterange as $date) {
      echo '<tr>';
      echo '<th scope="row">'.$date.'</th>';
      foreach ($timerange as $time) {
        echo '<td><input type="checkbox" value="'.strtotime($date.' '.$time[1]).'" /></td>';
      }
      echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
  }
}
?>
