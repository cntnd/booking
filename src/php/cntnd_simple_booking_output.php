<?php
// cntnd_booking_output

// assert framework initialization
defined('CON_FRAMEWORK') || die('Illegal call: Missing framework initialization - request aborted.');

// editmode and more
$editmode = cRegistry::isBackendEditMode();

// input/vars
$daterange = "CMS_VALUE[1]";
$show_daterange = "CMS_VALUE[2]";
$mailto = "CMS_VALUE[3]";
$subject_default = "CMS_VALUE[4]";
$subject_declined = "CMS_VALUE[5]";
$subject_reserved = "CMS_VALUE[6]";
$subject = array(
    'default'=>$subject_default,
    'declined'=>$subject_declined,
    'reserved'=>$subject_reserved);

$blocked_days[1] = (empty("CMS_VALUE[11]")) ? false : true;
$blocked_days[2] = (empty("CMS_VALUE[12]")) ? false : true;
$blocked_days[3] = (empty("CMS_VALUE[13]")) ? false : true;
$blocked_days[4] = (empty("CMS_VALUE[14]")) ? false : true;
$blocked_days[5] = (empty("CMS_VALUE[15]")) ? false : true;
$blocked_days[6] = (empty("CMS_VALUE[16]")) ? false : true;
$blocked_days[0] = (empty("CMS_VALUE[10]")) ? false : true;

// includes
cInclude('module', 'includes/class.datetime.php');
cInclude('module', 'includes/class.cntnd_simple_booking.php');
if ($editmode){
  cInclude('module', 'includes/script.cntnd_simple_booking_output.php');
}

// other/vars
$smarty = cSmartyFrontend::getInstance();
$simple_booking = new CntndSimpleBooking($daterange, $show_daterange, $mailto, $subject, $blocked_days, $lang, $client, $idart);

$has_config = $simple_booking->hasConfig();

if (empty($daterange) OR !$has_config){
  echo '<div class="cntnd_alert cntnd_alert-primary">';
  if ($editmode){
    echo mi18n("NO_CONFIG");
  }
  else {
    mi18n("NO_BOOKING");
  }
  echo '</div>';
}

if ($editmode){
  // ADMIN
  if ($_POST){
    if ($_POST["cntnd_booking-config"]=="save"){
      $simple_booking->saveConfig($_POST);
    }
    else {
      if (CntndSimpleBooking::validateUpdate($_POST)){
        $admin_success=$simple_booking->update($_POST);
      }
      else {
        $admin_error=true;
      }
    }
  }

  echo '<div class="content_box cntnd_booking"><label class="content_type_label">'.mi18n("MODULE").'</label>';
  echo '<div class="cntnd_alert cntnd_alert-primary">'.mi18n("ADMIN_MODE").'</div>';
  if ($admin_success){
    echo '<hr />';
    echo '<div class="cntnd_alert cntnd_alert-primary">'.mi18n("ADMIN_SUCCESS").'</div>';
  }
  if ($admin_error){
    echo '<hr />';
    echo '<div class="cntnd_alert cntnd_alert-danger">'.mi18n("ADMIN_FAILURE").'</div>';
  }

  // TABS

  echo '<ul class="nav nav-tabs" id="simple_booking_admin" role="tablist">';
  echo '<li class="nav-item" role="presentation"><button class="nav-link '.($has_config ? "active" : "").'" id="simple_booking_admin_tab-tab" data-bs-toggle="tab" data-bs-target="#simple_booking_admin_tab" type="button" role="tab" aria-controls="simple_booking_admin_tab" aria-selected="'.($has_config ? "true" : "false").'">Admin</button></li>';
  echo '<li class="nav-item" role="presentation"><button class="nav-link '.(!$has_config ? "active" : "").'" id="simple_booking_config_tab-tab" data-bs-toggle="tab" data-bs-target="#simple_booking_config_tab" type="button" role="tab" aria-controls="simple_booking_config_tab" aria-selected="'.(!$has_config ? "true" : "false").'">Konfiguration</button></li>';
  echo '</ul>';

  // CONTENT
  echo '<div class="tab-content" id="simple_booking_admin-content">';
  // CONTENT: ADMIN
  echo '<div class="tab-pane fade '.($has_config ? "show active" : "").'" id="simple_booking_admin_tab" role="tabpanel" aria-labelledby="simple_booking_admin_tab-tab">';

  echo '<div class="d-flex m-2">';

  echo '<div class="w-50 pr-10">';
  $smarty->assign('data', $simple_booking->listAll());
  $smarty->display('admin-liste.html');
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
  // endregion

  // CONTENT: CONFIG
  echo '<div class="tab-pane fade '.(!$has_config ? "show active" : "").'" id="simple_booking_config_tab" role="tabpanel" aria-labelledby="simple_booking_config_tab-tab">';

  echo '<div class="m-2">';

  echo '<form method="post" id="cntnd_booking-config" name="cntnd_booking-config">';
  $simple_booking->renderConfig();
  echo '<input type="hidden" name="cntnd_booking-config" value="save" />';
  echo '</form>';

  echo '</div>';

  echo '</div>';
  // endregion
  echo '</div>';

  // endregion
  echo '</div>';
}
else {
  // PUBLIC
  if ($_POST){
    var_dump($_POST);
    if (CntndSimpleBooking::validate($_POST)){
      $success=$simple_booking->store($_POST);
      $error=!$success;
    }
    else {
      $failure=true;
    }
  }
  echo '<div class="cntnd_booking">';
  echo '<form method="post" id="cntnd_booking-reservation" name="cntnd_booking-reservation">';
  $simple_booking->render();
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
  $smarty->display('formular_reservation.html');
  echo '<button type="submit" class="btn btn-primary">'.mi18n("SAVE").'</button>';
  echo '<button type="reset" class="btn">'.mi18n("RESET").'</button>';
  echo '<input type="hidden" name="required" id="cntnd_booking-required" />';
  echo '<input type="hidden" name="fields" id="cntnd_booking-fields" />';
  echo '</form>';
  echo '</div>';
}
?>
