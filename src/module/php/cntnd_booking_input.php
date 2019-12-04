?><?php
// cntnd_booking_input

// input/vars
$show_daterange = "CMS_VALUE[2]";
$interval = "CMS_VALUE[3]";
switch ($interval){
    case '30':
            $check_30 = 'selected="selected"';
            break;
    case '60':
            $check_60 = 'selected="selected"';
            break;
    case '120':
            $check_120 = 'selected="selected"';
            break;
}
$timerange_from = "CMS_VALUE[4]";
$timerange_to = "CMS_VALUE[5]";

// other/vars
if (empty($interval) || empty($timerange_from)){
    $timerange_to_disabled = 'disabled="disabled"';
}

// includes
cInclude('module', 'includes/class.datetime.php');
cInclude('module', 'includes/script.cntnd_booking_input.php');
cInclude('module', 'includes/style.cntnd_booking_input.php');
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
    <label for="interval"><?= mi18n("INTERVAL") ?></label>
    <select id="interval" name="CMS_VAR[3]" size="1">
        <option value=""> - </option>
        <option value="30" <?= $check_30 ?>> 30 Minuten</option>
        <option value="60" <?= $check_60 ?>> 1 Stunde</option>
        <option value="120" <?= $check_120 ?>> 2 Stunden</option>
    </select>
  </div>

  <div class="form-group">
    <?php
    if (empty($interval) || empty($timerange_from)){
      echo '<div class="cntnd_alert cntnd_alert-primary">'.mi18n("TIME_DISABLED").'</div>';
    }
    ?>
    <div><?= mi18n("TIME") ?></div>
    <div class="form-check form-check-inline">
      <label for="blocked_day_sa" class="form-check-label"><?= mi18n("TIME_FROM") ?></label>
      <select class="form-check-input" name="CMS_VAR[4]" size="1">
      <?php
          // todo 5 Minuten, 15, Minuten, ?? --> Timepicker
          for ($i=0;$i<48;$i++){
              $min=$i*30;
              $selected = "";
              $time = DateTimeUtil::getReadableTime($min);
              if ($min==$timerange_from){
                  $selected = 'selected="selected"';
              }
              echo '<option value="'.$min.'" '.$selected.'> '.$time.'</option>';
          }
      ?>
      </select>
    </div>

    <div class="form-check form-check-inline">
      <label for="blocked_day_sa" class="form-check-label"><?= mi18n("TIME_TO") ?></label>
      <select class="form-check-input" name="CMS_VAR[5]" size="1" <?= $timerange_to_disabled ?>>
      <?php
          if (!empty($timerange_from) && !empty($interval)){
              $timerange=DateTimeUtil::getTimerange($timerange_from,1440,$interval);
              foreach ($timerange as $time) {
                $selected = "";
                if ($time[0]==$timerange_to){
                    $selected = 'selected="selected"';
                }
                echo '<option value="'.$time[0].'" '.$selected.'> '.$time[1].'</option>';
              }
          }
      ?>
      </select>
    </div>
  </div>

  <hr />

  <div class="form-group">
    <label for="email"><?= mi18n("EMAIL") ?></label>
    <input id="email" type="email" name="CMS_VAR[6]" value="CMS_VALUE[6]" />
  </div>

  <pre>
    * min. dauer
    * interval auch 15 minuten, 45, 75 (frei wählbar durch 5 teilbar)
    * pausen zwischen den intervalen
  </pre>
</div>
<?php
