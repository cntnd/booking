<?php
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
}
?>
