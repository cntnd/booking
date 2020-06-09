<?php
// cntnd_booking_output

// assert framework initialization
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

// editmode and more
$editmode = cRegistry::isBackendEditMode();

// input/vars
$mailto = "CMS_VALUE[1]";
//$range = "CMS_VALUE[2]";
$range = 2;

// includes
cInclude('module', 'includes/class.cntnd_util.php');
cInclude('module', 'includes/class.cntnd_calendar_booking.php');
if ($editmode){
  //cInclude('module', 'includes/script.cntnd_booking_output.php');
}

// other/vars
$booking = new CntndCalendarBooking($mailto, $lang, $client);
$smarty = cSmartyFrontend::getInstance();
$uuid = rand();
$formId = 'booking_'.$uuid;

// public includes
if (!$editmode){
  $data = $booking->load($range);
  echo '<script>';
  echo 'var bookings = new Map();';
  echo CntndUtil::toJSMap("bookings",$data);
  echo '</script>';
  cInclude('module', 'includes/style.cntnd_booking_output_public.php');
  cInclude('module', 'includes/script.cntnd_booking_output_public.php');
}

if ($editmode){
  // ADMIN
  /*
  if ($_POST){
    if (CntndBooking::validateUpdate($_POST)){
      $admin_success=$booking->update($_POST);
    }
    else {
      $admin_error=true;
    }
  }
  */
  echo '<div class="content_box cntnd_booking"><label class="content_type_label">'.mi18n("MODULE").'</label>';
  echo '<div class="cntnd_alert cntnd_alert-primary">'.mi18n("ADMIN_MODE").'</div>';
  /*
  if ($admin_success){
    echo '<hr />';
    echo '<div class="cntnd_alert cntnd_alert-primary">'.mi18n("ADMIN_SUCCESS").'</div>';
  }
  if ($admin_error){
    echo '<hr />';
    echo '<div class="cntnd_alert cntnd_alert-danger">'.mi18n("ADMIN_FAILURE").'</div>';
  }
  */
  echo '<div class="d-flex ">';

  echo '<div class="w-50 pr-10">';
  //$smarty->assign('data', $booking->listAll());
  //$smarty->display('admin-liste.html');
  echo '</div>';

  echo '<div class="w-50 pl-10">';
  echo '<div class="cntnd_booking-admin-action">
    <h5>'.mi18n("ADMIN_ACTION").'</h5>
    <div class="form-vertical card">
      <div class="card-body">
        <div class="cntnd_booking-admin-error cntnd_alert cntnd_alert-primary hide">'.mi18n("ADMIN_SUBMIT_ERROR").'</div>
        <form method="post" id="cntnd_booking-admin" name="cntnd_booking-admin">
          <div class="cntnd_booking-admin-timeslot hide">
            <span class="timeslot"></span>
          </div>
          <div class="form-group">
        		<label for="bemerkungen">Bemerkungen</label>
        		<textarea name="bemerkungen" class="form-control"></textarea>
        	</div>
          <button class="btn btn-primary" type="submit">'.mi18n("SAVE").'</button>
          <button class="btn btn-dark cntnd_booking-admin-delete" type="button">'.mi18n("DELETE").'</button>
          <button class="btn cntnd_booking-admin-cancel" type="reset">'.mi18n("RESET").'</button>
          <input type="hidden" name="resid" />
          <input type="hidden" name="action" value="save" />
          <div class="form-group">
            <span>'.mi18n("EMAIL").'</span>
            <div class="form-check form-check-inline">
              <input id="email_senden" class="form-check-input" type="checkbox" name="email_senden" value="true" checked />
              <label for="email_senden" class="form-check-label">'.mi18n("EMAIL_SEND").'</label>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>';
  echo '</div>';

  echo '</div>';
  echo '</div>';
}
else {
  // PUBLIC
  /*
  if ($_POST){
    if (CntndBooking::validate($_POST,$interval)){
      $success=$booking->store($_POST,$interval);
      $error=!$success;
    }
    else {
      $failure=true;
    }
  }
  */
  ?>
    <div class="cntnd_booking">
      <div class="d-flex">
        <!-- // CALENDAR -->
        <div class="w-33">
          <h2><?= mi18n("CALENDAR") ?></h2>
          <?php $smarty->display('reservation_kalender.html'); ?>
        </div>

        <!-- // RESERVATION -->
        <div class="w-33">
          <h2><?= mi18n("RESERVATION") ?></h2>
          <form data-uuid="<?= $formId ?>" id="<?= $formId ?>" name="<?= $formId ?>" method="post">
            <?php $smarty->display('reservation_reservation.html'); ?>
            <input type="text" name="uname" value="<?= $auth->auth["uname"] ?>" />
          </form>
        </div>

      </div>
    </div>
  <?php
      /*
      echo '<form method="post" id="cntnd_booking-reservation" name="cntnd_booking-reservation">';
      $booking->render();
      // show more/less
      if (!empty($show_daterange)){
        echo '<div class="cntnd_booking-pagination">';
        echo '<span class="cntnd_booking-more">'.mi18n("MORE").'</span>';
        echo '<span class="cntnd_booking-less hide">'.mi18n("LESS").'</span>';
        echo '</div>';
      }
      // show messages
      $failureMsg=($failure) ? '' : 'hide';
      echo '<div class="cntnd_alert cntnd_alert-danger cntnd_booking-validation '.$failureMsg.'">';
      echo mi18n("VALIDATION");
      echo '<ul>';
      echo '<li class="cntnd_booking-validation-required">'.mi18n("VALIDATION_REQUIRED").'</li>';
      echo '<li class="cntnd_booking-validation-dates">'.mi18n("VALIDATION_DATES").'</li>';
      echo '<li class="cntnd_booking-validation-days">'.mi18n("VALIDATION_DAYS").'</li>';
      echo '</ul>';
      echo '</div>';
      if ($success){
        echo '<div class="cntnd_alert cntnd_alert-primary">'.mi18n("SUCCESS").'</div>';
      }
      if ($error){
        echo '<div class="cntnd_alert cntnd_alert-danger">'.mi18n("FAILURE").'</div>';
      }
      // use template to display formular
      $smarty->display('reservation-formular.html');
      echo '<button type="submit" class="btn btn-primary">'.mi18n("SAVE").'</button>';
      echo '<button type="reset" class="btn">'.mi18n("RESET").'</button>';
      echo '<input type="hidden" name="required" id="cntnd_booking-required" />';
      echo '<input type="hidden" name="fields" id="cntnd_booking-fields" />';
      echo '</form>';
      */
}
?>
