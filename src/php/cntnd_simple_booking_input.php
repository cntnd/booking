?><?php
// cntnd_booking_input

// input/vars
$subject = "CMS_VALUE[4]";
if (empty($subject)){
    $subject = mi18n("DEFAULT_SUBJECT");
}
$subject_declined = "CMS_VALUE[5]";
if (empty($subject_declined)){
    $subject_declined = mi18n("DEFAULT_SUBJECT_DECLINED");
}
$subject_reserved = "CMS_VALUE[6]";
if (empty($subject_reserved)){
    $subject_reserved = mi18n("DEFAULT_SUBJECT_RESERVED");
}

// other/vars

// includes
cInclude('module', 'includes/class.datetime.php');
cInclude('module', 'includes/script.cntnd_simple_booking_input.php');
cInclude('module', 'includes/style.cntnd_simple_booking_input.php');
?>
<div class="form-vertical">
  <div class="form-group">
    <label for="daterange"><?= mi18n("DATERANGE") ?></label>
    <input id="daterange" class="cntnd_booking_daterange" type="text" name="CMS_VAR[1]" value="CMS_VALUE[1]" />
  </div>

  <div class="form-group">
    <div><?= mi18n("BLOCKED_DAYS") ?></div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_mo" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[11]" data-day="1" value="true" <?php if("CMS_VALUE[11]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_mo" class="form-check-label">Mo.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_di" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[12]" data-day="2" value="true" <?php if("CMS_VALUE[12]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_di" class="form-check-label">Di.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_mi" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[13]" data-day="3" value="true" <?php if("CMS_VALUE[13]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_mi" class="form-check-label">Mi.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_do" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[14]" data-day="4" value="true" <?php if("CMS_VALUE[14]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_do" class="form-check-label">Do.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_fr" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[15]" data-day="5" value="true" <?php if("CMS_VALUE[15]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_fr" class="form-check-label">Fr.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_sa" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[16]" data-day="6" value="true" <?php if("CMS_VALUE[16]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_sa" class="form-check-label">Sa.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_so" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[10]" data-day="0" value="true" <?php if("CMS_VALUE[10]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_so" class="form-check-label">So.</label>
    </div>

    <input id="reset_config" type="hidden" name="CMS_VAR[2]" value="" />
  </div>

  <hr />

  <div class="form-group">
    <label for="email"><?= mi18n("EMAIL") ?></label>
    <input id="email" type="email" name="CMS_VAR[3]" value="CMS_VALUE[3]" />
  </div>

  <div class="form-group">
    <label for="email"><?= mi18n("SUBJECT") ?></label>
    <input id="email" type="text" name="CMS_VAR[4]" value="<?= $subject ?>" />
  </div>

    <div class="form-group">
        <label for="email"><?= mi18n("SUBJECT_DECLINED") ?></label>
        <input id="email" type="text" name="CMS_VAR[5]" value="<?= $subject_declined ?>" />
    </div>

    <div class="form-group">
        <label for="email"><?= mi18n("SUBJECT_RESERVED") ?></label>
        <input id="email" type="text" name="CMS_VAR[6]" value="<?= $subject_reserved ?>" />
    </div>
</div>
<?php
