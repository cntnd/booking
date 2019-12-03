<?php
// cntnd_booking_output

// assert framework initialization
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

// editmode and more
$editmode = cRegistry::isBackendEditMode();
$smarty = cSmartyFrontend::getInstance();
$mailer = new cMailer();

// input/vars
$daterange = "CMS_VALUE[1]";
$show_daterange = "CMS_VALUE[2]";
$interval = "CMS_VALUE[3]";
$timerange_from = "CMS_VALUE[4]";
$timerange_to = "CMS_VALUE[5]";
$mailto = "CMS_VALUE[6]";

$blocked_days[1] = (empty("CMS_VALUE[11]")) ? false : true;
$blocked_days[2] = (empty("CMS_VALUE[12]")) ? false : true;
$blocked_days[3] = (empty("CMS_VALUE[13]")) ? false : true;
$blocked_days[4] = (empty("CMS_VALUE[14]")) ? false : true;
$blocked_days[5] = (empty("CMS_VALUE[15]")) ? false : true;
$blocked_days[6] = (empty("CMS_VALUE[16]")) ? false : true;
$blocked_days[0] = (empty("CMS_VALUE[10]")) ? false : true;

// includes
cInclude('module', 'includes/class.datetime.php');
cInclude('module', 'includes/class.cntnd_booking.php');
if ($editmode){
  cInclude('module', 'includes/script.cntnd_booking_output.php');
}

// values
$booking = new CntndBooking($daterange, $show_daterange, $interval, $timerange_from, $timerange_to, $mailto, $blocked_days);
$interval_check = ($interval * 60);
switch ($show_daterange){
    case '1week':
            $anzeige_check = "+1 Week";
            break;
    case '2week':
            $anzeige_check = "+2 Week";
            break;
    case '3week':
            $anzeige_check = "+3 Week";
            break;
    case '4week':
            $anzeige_check = "+4 Week";
            break;
}

$wtag[0] = "So.";
$wtag[1] = "Mo.";
$wtag[2] = "Di.";
$wtag[3] = "Mi.";
$wtag[4] = "Do.";
$wtag[5] = "Fr.";
$wtag[6] = "Sa.";

$error=false;

if (empty($daterange) OR empty($timerange_from) OR empty($timerange_to) OR empty($interval)){
  echo '<div class="cntnd_alert cntnd_alert-primary">';
  if ($editmode){
    echo mi18n("NO_CONFIG");
  }
  else {
    mi18n("NO_BOOKING");
  }
  echo '</div>';
}

$booking->render();
?>
