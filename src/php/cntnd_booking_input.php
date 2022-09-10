?><?php
// cntnd_booking_input

// input/vars
$subject = "CMS_VALUE[4]";
if (empty($subject)) {
    $subject = mi18n("DEFAULT_SUBJECT");
}
$subject_declined = "CMS_VALUE[5]";
if (empty($subject_declined)) {
    $subject_declined = mi18n("DEFAULT_SUBJECT_DECLINED");
}
$subject_reserved = "CMS_VALUE[6]";
if (empty($subject_reserved)) {
    $subject_reserved = mi18n("DEFAULT_SUBJECT_RESERVED");
}
$recurrent = (bool)"CMS_VALUE[7]";
if (!is_bool($recurrent)) {
    $recurrent = true;
}
$one_click = (bool)"CMS_VALUE[8]";
if (!is_bool($one_click)) {
    $one_click = false;
}
$show_daterange = "CMS_VALUE[9]";
$show_past = (bool)"CMS_VALUE[20]";
if (!is_bool($show_past)) {
    $show_past = false;
}
$show_past_admin = (bool)"CMS_VALUE[21]";
if (!is_bool($show_past_admin)) {
    $show_past_admin = false;
}
$booking_title = "CMS_VALUE[22]";
if (empty($booking_title)) {
    $booking_title = "booking";
}
$interval = (bool)"CMS_VALUE[30]";
if (!is_bool($interval)) {
    $interval = false;
}
$interval_slots = "CMS_VALUE[31]";
switch ($interval_slots) {
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
$timerange_from = "CMS_VALUE[32]";
$timerange_to = "CMS_VALUE[33]";

// other/vars
if (empty($interval_slots) || empty($timerange_from)) {
    $timerange_to_disabled = 'disabled="disabled"';
}

// other/vars

// includes
cInclude('module', 'includes/class.datetime.php');
cInclude('module', 'includes/script.cntnd_booking_input.php');
cInclude('module', 'includes/style.cntnd_booking_input.php');
?>
<div class="form-vertical">
    <div class="form-group">
        <label for="title"><?= mi18n("BOOKING_TITLE") ?></label>
        <input id="title" type="text" name="CMS_VAR[22]" value="<?= $booking_title ?>"/>
    </div>

    <fieldset name="daterange">
        <legend><?= mi18n("DATERANGE_TITLE") ?></legend>
        <div class="form-group">
            <label for="daterange"><?= mi18n("DATERANGE") ?></label>
            <input id="daterange" class="cntnd_booking_daterange" type="text" name="CMS_VAR[1]" value="CMS_VALUE[1]"/>
        </div>
        <div class="form-group">
            <div><?= mi18n("BLOCKED_DAYS") ?></div>
            <div class="form-check form-check-inline">
                <input id="blocked_day_mo" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[11]"
                       data-day="1" value="true" <?php if ("CMS_VALUE[11]" == 'true') {
                    echo 'checked';
                } ?> />
                <label for="blocked_day_mo" class="form-check-label">Mo.</label>
            </div>
            <div class="form-check form-check-inline">
                <input id="blocked_day_di" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[12]"
                       data-day="2" value="true" <?php if ("CMS_VALUE[12]" == 'true') {
                    echo 'checked';
                } ?> />
                <label for="blocked_day_di" class="form-check-label">Di.</label>
            </div>
            <div class="form-check form-check-inline">
                <input id="blocked_day_mi" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[13]"
                       data-day="3" value="true" <?php if ("CMS_VALUE[13]" == 'true') {
                    echo 'checked';
                } ?> />
                <label for="blocked_day_mi" class="form-check-label">Mi.</label>
            </div>
            <div class="form-check form-check-inline">
                <input id="blocked_day_do" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[14]"
                       data-day="4" value="true" <?php if ("CMS_VALUE[14]" == 'true') {
                    echo 'checked';
                } ?> />
                <label for="blocked_day_do" class="form-check-label">Do.</label>
            </div>
            <div class="form-check form-check-inline">
                <input id="blocked_day_fr" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[15]"
                       data-day="5" value="true" <?php if ("CMS_VALUE[15]" == 'true') {
                    echo 'checked';
                } ?> />
                <label for="blocked_day_fr" class="form-check-label">Fr.</label>
            </div>
            <div class="form-check form-check-inline">
                <input id="blocked_day_sa" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[16]"
                       data-day="6" value="true" <?php if ("CMS_VALUE[16]" == 'true') {
                    echo 'checked';
                } ?> />
                <label for="blocked_day_sa" class="form-check-label">Sa.</label>
            </div>
            <div class="form-check form-check-inline">
                <input id="blocked_day_so" class="form-check-input blocked_day" type="checkbox" name="CMS_VAR[10]"
                       data-day="0" value="true" <?php if ("CMS_VALUE[10]" == 'true') {
                    echo 'checked';
                } ?> />
                <label for="blocked_day_so" class="form-check-label">So.</label>
            </div>
            <input id="reset_config" type="hidden" name="CMS_VAR[2]" value=""/>
        </div>
        <div class="form-group">
            <label for="show_daterange"><?= mi18n("SHOW_DATERANGE") ?></label>
            <select id="show_daterange" name="CMS_VAR[9]" size="1">
                <option value="all"><?= mi18n("SELECT_DATERANGE") ?></option>
                <?php
                for ($i = 1; $i < 5; $i++) {
                    $selected = "";
                    $val = '+' . $i . ' week';
                    if ($val == $show_daterange) {
                        $selected = 'selected="selected"';
                    }
                    echo '<option value="' . $val . '" ' . $selected . '> ' . $i . ' Woche(n) </option>';
                }
                ?>
            </select>
        </div>
    </fieldset>

    <fieldset name="configuration">
        <legend><?= mi18n("RESERVATION_TITLE") ?></legend>
        <div class="form-group">
            <div class="form-check form-check-inline">
                <input id="recurrent" class="form-check-input" type="checkbox" name="CMS_VAR[7]" value="true" <?php if ($recurrent) { echo 'checked'; } ?> />
                <label for="recurrent" class="form-check-label"><?= mi18n("RECURRENT") ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input id="interval" class="form-check-input" type="checkbox" name="CMS_VAR[30]" value="true" <?php if ($interval) { echo 'checked'; } ?> <?php if (!$recurrent){ echo "disabled"; } ?> />
                <label for="interval" class="form-check-label"><?= mi18n("INTERVAL") ?></label>
            </div>
        </div>

        <fieldset id="interval_configuration" class="interval_configuration" <?php if (!$interval){ echo "disabled"; } ?>>
            <div class="cntnd_alert cntnd_alert-primary <?php if(empty($timerange_to_disabled)){ echo "hide"; } ?>"><?= mi18n("TIME_DISABLED") ?></div>

            <div class="d-flex">
                <div class="w-auto form-group">
                    <label for="interval_slots"><?= mi18n("INTERVAL") ?></label>
                    <select id="interval_slots" name="CMS_VAR[31]" size="1">
                        <option value=""> -</option>
                        <option value="30" <?= $check_30 ?>> 30 Minuten</option>
                        <option value="60" <?= $check_60 ?>> 1 Stunde</option>
                        <option value="120" <?= $check_120 ?>> 2 Stunden</option>
                    </select>
                </div>

                <div class="w-50 form-group">
                    <div><?= mi18n("TIME") ?></div>
                    <div class="form-check form-check-inline">
                        <label for="interval_time_from" class="form-check-label"><?= mi18n("TIME_FROM") ?></label>
                        <select id="interval_time_from" class="form-check-input" name="CMS_VAR[32]" size="1">
                            <?php
                            // todo 5 Minuten, 15, Minuten, ?? --> Timepicker
                            for ($i = 0; $i < 48; $i++) {
                                $min = $i * 30;
                                $selected = "";
                                $time = DateTimeUtil::getReadableTime($min);
                                if ($min == $timerange_from) {
                                    $selected = 'selected="selected"';
                                }
                                echo '<option value="' . $min . '" ' . $selected . '> ' . $time . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-check form-check-inline">
                        <label for="interval_time_to" class="form-check-label"><?= mi18n("TIME_TO") ?></label>
                        <select id="interval_time_to" class="form-check-input" name="CMS_VAR[33]" size="1" <?= $timerange_to_disabled ?>>
                            <?php
                            if (!empty($timerange_from) && !empty($interval_slots)) {
                                $timerange = DateTimeUtil::getTimerange($timerange_from, 1440, $interval_slots);
                                foreach ($timerange as $time) {
                                    $selected = "";
                                    if ($time[0] == $timerange_to) {
                                        $selected = 'selected="selected"';
                                    }
                                    echo '<option value="' . $time[0] . '" ' . $selected . '> ' . $time[1] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="form-group">
            <div class="form-check form-check-inline">
                <input id="one_click" class="form-check-input" type="checkbox" name="CMS_VAR[8]"
                       value="true" <?php if ($one_click) {
                    echo 'checked';
                } ?> />
                <label for="one_click" class="form-check-label"><?= mi18n("ONE_CLICK_BOOKING") ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="form-check form-check-inline">
                <input id="show_past" class="form-check-input" type="checkbox" name="CMS_VAR[20]"
                       value="true" <?php if ($show_past) {
                    echo 'checked';
                } ?> />
                <label for="show_past" class="form-check-label"><?= mi18n("SHOW_PAST") ?></label>
            </div>

            <div class="form-check form-check-inline">
                <input id="show_past_admin" class="form-check-input" type="checkbox" name="CMS_VAR[21]"
                       value="true" <?php if ($show_past_admin) {
                    echo 'checked';
                } ?> />
                <label for="show_past_admin" class="form-check-label"><?= mi18n("SHOW_PAST_ADMIN") ?></label>
            </div>
        </div>
    </fieldset>

    <fieldset name="email">
        <legend><?= mi18n("EMAIL_TITLE") ?></legend>
        <div class="form-group">
            <label for="email"><?= mi18n("EMAIL") ?></label>
            <input id="email" type="email" name="CMS_VAR[3]" value="CMS_VALUE[3]"/>
        </div>
        <div class="form-group">
            <label for="email"><?= mi18n("SUBJECT") ?></label>
            <input id="email" type="text" name="CMS_VAR[4]" value="<?= $subject ?>"/>
        </div>
        <div class="form-group">
            <label for="email"><?= mi18n("SUBJECT_DECLINED") ?></label>
            <input id="email" type="text" name="CMS_VAR[5]" value="<?= $subject_declined ?>"/>
        </div>
        <div class="form-group">
            <label for="email"><?= mi18n("SUBJECT_RESERVED") ?></label>
            <input id="email" type="text" name="CMS_VAR[6]" value="<?= $subject_reserved ?>"/>
        </div>
    </fieldset>
</div>
<?php
