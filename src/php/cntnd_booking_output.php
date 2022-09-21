<?php
// cntnd_booking_output

// assert framework initialization
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

// editmode and more
$editmode = cRegistry::isBackendEditMode();

// input/vars
$booking_title = "CMS_VALUE[22]";
$daterange = "CMS_VALUE[1]";
$config_reset = "CMS_VALUE[2]";
$mailto = "CMS_VALUE[3]";
$subject_default = "CMS_VALUE[4]";
$subject_declined = "CMS_VALUE[5]";
$subject_reserved = "CMS_VALUE[6]";
$subject = array(
    'booking_title' => $booking_title,
    'default' => $subject_default,
    'declined' => $subject_declined,
    'reserved' => $subject_reserved);
$one_click = (bool)"CMS_VALUE[8]";
$show_daterange = "CMS_VALUE[9]";
$show_past = (bool)"CMS_VALUE[20]";
$show_past_admin = (bool)"CMS_VALUE[21]";
if (empty($booking_title)) {
    $booking_title = "booking";
}

$blocked_days[1] = (empty("CMS_VALUE[11]")) ? false : true;
$blocked_days[2] = (empty("CMS_VALUE[12]")) ? false : true;
$blocked_days[3] = (empty("CMS_VALUE[13]")) ? false : true;
$blocked_days[4] = (empty("CMS_VALUE[14]")) ? false : true;
$blocked_days[5] = (empty("CMS_VALUE[15]")) ? false : true;
$blocked_days[6] = (empty("CMS_VALUE[16]")) ? false : true;
$blocked_days[0] = (empty("CMS_VALUE[10]")) ? false : true;

$interval = (bool)"CMS_VALUE[30]";
$interval_slots = "CMS_VALUE[31]";
$timerange_from = "CMS_VALUE[32]";
$timerange_to = "CMS_VALUE[33]";

$email_copy_default = (bool) "CMS_VALUE[40]";
$email_copy_reserved = (bool) "CMS_VALUE[41]";
$email_copy_declined = (bool) "CMS_VALUE[42]";
$email_copy_mailto = "CMS_VALUE[43]";
if (empty($email_copy_mailto)) {
    $email_copy_mailto = $mailto;
}
$email_copy = array(
    'default'=>$email_copy_default,
    'reserved'=>$email_copy_reserved,
    'declined'=>$email_copy_declined,
    'mailto'=>$email_copy_mailto);

$bootstrap_fallback = true;

// includes
cInclude('module', 'includes/class.datetime.php');
cInclude('module', 'includes/class.cntnd_booking.php');
if ($editmode) {
    cInclude('module', 'includes/script.cntnd_booking_output.php');
    if ($bootstrap_fallback) {
        cInclude('module', 'includes/style.cntnd_booking_output-fallback.php');
    }
    cInclude('module', 'includes/style.cntnd_booking_output.php');
}

// other/vars
$smarty = cSmartyFrontend::getInstance();
$booking = new CntndBooking($daterange, $mailto, $email_copy, $subject, $blocked_days, $one_click, $show_daterange, $show_past, $interval_slots, $timerange_from, $timerange_to, $lang, $client, $idart);

if (empty($daterange)) {
    echo '<div class="cntnd_alert cntnd_alert-primary">';
    if ($editmode) {
        mi18n("NO_BOOKING");
    }
    echo '</div>';
}

if ($editmode) {
    // ADMIN
    if ($_POST) {
        if (CntndBooking::validateUpdate($_POST)) {
            $admin_success = $booking->update($_POST);
        } else {
            $admin_error = true;
        }
    }

    echo '<div class="content_box cntnd_simple-booking"><label class="content_type_label">' . mi18n("MODULE") . '</label>';
    echo '<div class="cntnd_alert cntnd_alert-primary">' . mi18n("ADMIN_MODE") . '</div>';
    if ($admin_success) {
        echo '<hr />';
        echo '<div class="cntnd_alert cntnd_alert-primary">' . mi18n("ADMIN_SUCCESS") . '</div>';
        echo '<script>' . "\n";
        echo '$(document).ready(function (){' . "\n";
        echo '  $("html, body").animate({' . "\n";
        echo '    scrollTop: $("[data-resid=' . $_POST['resid'] . ']").offset().top' . "\n";
        echo '  }, 100);' . "\n";
        echo '});' . "\n";
        echo '</script>' . "\n";
    }
    if ($admin_error) {
        echo '<hr />';
        echo '<div class="cntnd_alert cntnd_alert-danger">' . mi18n("ADMIN_FAILURE") . '</div>';
    }

    // TABS

    echo '<ul class="tabs" id="booking_admin" role="tablist">';
    echo '<li class="tabs__tab active" data-toggle="tabs" data-target="booking_admin-content">Admin</li>';
    echo '</ul>';

    // CONTENT
    echo '<div class="tabs__content">';
    // CONTENT: ADMIN
    echo '<div  id="booking_admin-content" class="tabs__content--pane active">';

    echo '<div class="d-flex pt-2">';

    echo '<div class="w-50 pr-10">';
    $smarty->assign('data', $booking->listAll($show_past_admin));
    $smarty->display('admin-liste.html');
    echo '</div>';

    echo '<div class="w-50 pl-10">';
    echo '<div class="cntnd_booking-admin-action">
    <h5>' . mi18n("ADMIN_ACTION") . '</h5>
    <div class="form-vertical card">
      <div class="card-body">
        <div class="cntnd_booking-admin-error cntnd_alert cntnd_alert-primary hide">' . mi18n("ADMIN_SUBMIT_ERROR") . '</div>
        <form method="post" id="cntnd_booking-admin" name="cntnd_booking-admin">
          <div class="cntnd_booking-admin-timeslot hide">
            <span class="timeslot"></span>
          </div>
          <div class="form-group">
        		<label for="bemerkungen">Bemerkungen</label>
        		<textarea name="bemerkungen" class="form-control"></textarea>
        	</div>
          <button class="btn btn-primary" type="submit">' . mi18n("SAVE") . '</button>
          <button class="btn btn-dark cntnd_booking-admin-delete" type="button">' . mi18n("DELETE") . '</button>
          <button class="btn cntnd_booking-admin-cancel" type="reset">' . mi18n("RESET") . '</button>
          <input type="hidden" name="resid" />
          <input type="hidden" name="action" value="save" />
          <div class="form-group">
            <span>' . mi18n("EMAIL") . '</span>
            <div class="form-check form-check-inline">
              <input id="email_senden" class="form-check-input" type="checkbox" name="email_senden" value="true" checked />
              <label for="email_senden" class="form-check-label">' . mi18n("EMAIL_SEND") . '</label>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>';
    echo '</div>';

    echo '</div>';

    echo '</div>';
    // endregion

    echo '</div>';

    // endregion
    echo '</div>';
} else {
    // PUBLIC
    if ($_POST) {
        if (CntndBooking::validate($_POST, $_SESSION['rand'])) {
            if (CntndBooking::validateFree($_POST, $idart) && CntndBooking::validateAvailability($_POST, $idart)) {
                $success = $booking->store($_POST);
                $error = !$success;
                $error_free = false;
            } else {
                $error_free = true;
            }
        } else {
            $failure = true;
        }
    }
    // REFRESH
    $rand = mt_rand();
    $_SESSION['rand'] = $rand;

    if ($success) {
        echo '<div class="cntnd_alert cntnd_alert-primary">' . mi18n("SUCCESS") . '</div>';
    }
    echo '<div class="cntnd_booking">';
    echo '<form method="post" id="cntnd_booking-reservation" name="cntnd_booking-reservation">';

    // display booking
    $data = $booking->renderData();
    $smarty->assign('data', $data);
    $smarty->assign('one_click', $one_click);
    $smarty->assign('pagination', ($show_daterange != "all"));
    $smarty->display('booking.html');

    // show messages
    if ($_POST && !$success) {
        echo '<div id="cntnd_booking-form"></div>';
    }
    $failureMsg = ($failure) ? '' : 'hide';
    echo '<div class="cntnd_alert cntnd_alert-danger cntnd_booking-validation ' . $failureMsg . '">';
    echo mi18n("VALIDATION");
    echo '<ul>';
    echo '<li class="cntnd_booking-validation-required">' . mi18n("VALIDATION_REQUIRED") . '</li>';
    echo '<li class="cntnd_booking-validation-dates">' . mi18n("VALIDATION_DATES") . '</li>';
    echo '<li class="cntnd_booking-validation-consecutive">' . mi18n("VALIDATION_CONSECUTIVE") . '</li>';
    echo '</ul>';
    echo '</div>';
    if ($error) {
        echo '<div class="cntnd_alert cntnd_alert-danger">' . mi18n("FAILURE") . '</li></div>';
    }
    if ($error_free) {
        echo '<div class="cntnd_alert cntnd_alert-danger">' . mi18n("VALIDATION_FREE_SLOTS") . '</div>';
    }

    // display form
    $smarty->display('form.html');

    echo '<button type="submit" class="btn btn-primary">' . mi18n("SAVE") . '</button>';
    echo '<button type="reset" class="btn">' . mi18n("RESET") . '</button>';
    echo '<input type="hidden" name="required" id="cntnd_booking-required" />';
    echo '<input type="hidden" name="fields" id="cntnd_booking-fields" />';
    echo '<input type="hidden" name="one_click_booking" value="' . $one_click . '" id="cntnd_booking-one_click_booking" />';
    echo '<input type="hidden" name="rand" value="' . $rand . '" />';
    echo '</form>';
    echo '</div>';
}
?>
