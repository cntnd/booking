?><?php
// cntnd_booking_input

// input/vars
$show_daterange = "CMS_VALUE[2]";

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
      <input id="blocked_day_mo" class="form-check-input" type="checkbox" name="CMS_VAR[11]" value="true" <?php if("CMS_VALUE[11]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_mo" class="form-check-label">Mo.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_di" class="form-check-input" type="checkbox" name="CMS_VAR[12]" value="true" <?php if("CMS_VALUE[12]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_di" class="form-check-label">Di.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_mi" class="form-check-input" type="checkbox" name="CMS_VAR[13]" value="true" <?php if("CMS_VALUE[13]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_mi" class="form-check-label">Mi.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_do" class="form-check-input" type="checkbox" name="CMS_VAR[14]" value="true" <?php if("CMS_VALUE[14]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_do" class="form-check-label">Do.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_fr" class="form-check-input" type="checkbox" name="CMS_VAR[15]" value="true" <?php if("CMS_VALUE[15]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_fr" class="form-check-label">Fr.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_sa" class="form-check-input" type="checkbox" name="CMS_VAR[16]" value="true" <?php if("CMS_VALUE[16]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_sa" class="form-check-label">Sa.</label>
    </div>
    <div class="form-check form-check-inline">
      <input id="blocked_day_so" class="form-check-input" type="checkbox" name="CMS_VAR[10]" value="true" <?php if("CMS_VALUE[10]"=='true'){ echo 'checked'; } ?> />
      <label for="blocked_day_so" class="form-check-label">So.</label>
    </div>
  </div>

  <div class="form-group">
    <label for="show_daterange"><?= mi18n("SHOW_DATERANGE") ?></label>
    <select id="show_daterange" name="CMS_VAR[2]" size="1">
        <option value="all">- ganzer Zeitraum anzeigen -</option>
        <?php
            for ($i=1;$i<5;$i++){
                $selected = "";
                $val='+'.$i.' week';
                if ($val==$show_daterange){
                    $selected = 'selected="selected"';
                }
                echo '<option value="'.$val.'" '.$selected.'> '.$i.' Woche(n) </option>';
            }
        ?>
        </select>
  </div>

  <hr />

  <div class="form-group">
    <label for="email"><?= mi18n("EMAIL") ?></label>
    <input id="email" type="email" name="CMS_VAR[3]" value="CMS_VALUE[3]" />
  </div>
</div>
<?php
