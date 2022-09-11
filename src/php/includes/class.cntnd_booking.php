<?php

cInclude('module', 'includes/class.datetime.php');
cInclude('module', 'includes/class.cntnd_util.php');

/**
 * cntnd_booking Class
 */
class CntndBooking
{

    private $daterange;
    private $mailto;
    private $subject;
    private $blocked_days;
    private $one_click;
    private $show_daterange;
    private $show_past;

    private $db;
    private $client;
    private $lang;
    private $idart;

    private $config;
    private $debug = false;

    private static $_vars = array(
        "db" => array(
            "config" => "cntnd_booking_config",
            "bookings" => "cntnd_booking"
        )
    );

    function __construct($daterange, $config_reset, $mailto, $subject, $blocked_days, $one_click, $show_daterange, $show_past, $interval_slots, $timerange_from, $timerange_to, $lang, $client, $idart)
    {
        $this->daterange = $daterange;
        $this->mailto = $mailto;
        $this->subject = $subject;
        $this->blocked_days = $blocked_days;
        $this->one_click = $one_click;
        $this->show_daterange = $show_daterange;
        $this->show_past = $show_past;

        $this->db = new cDb;
        $this->client = $client;
        $this->lang = $lang;
        $this->idart = $idart;

        $this->config = $this->intervalConfig($interval_slots, $timerange_from, $timerange_to);
    }

    private function intervalConfig($time_slots, $from, $to)
    {
        $max = ($to - $from) / $time_slots;
        $intervalConfig = array();
        for ($i = 0; $i < $max; $i++) {
            $slot = $i * $time_slots;
            $slot_from = $from + $slot;
            $slot_to = $slot_from + $time_slots;
            $time_from = date('H:i', mktime(0, $slot_from));
            $time_to = date('H:i', mktime(0, $slot_to));

            $intervalConfig[$i]['time'] = $time_from;
            $intervalConfig[$i]['time_until'] = $time_to;
            $intervalConfig[$i]['slots'] = 1;
            $intervalConfig[$i]['comment'] = "";
            $intervalConfig[$i]['recurrent'] = 1;
        }
        return $intervalConfig;
    }

    private function config()
    {
        $config = array();
        foreach ($this->blocked_days as $day => $blocked) {
            if (!$blocked) {
                $config['config'][$day] = $this->config;
            }
        }
        return $config;
    }

    public function renderConfig($recurrent)
    {
        if ($recurrent) {
            $this->recurrentConfig();
        } else {
            $this->customConfig();
        }
    }

    private function reccurentIndexByWeekday($weekday)
    {
        $index_date = DateTimeUtil::getIndexFromWeekday($weekday);
        $index = DateTimeUtil::getIndexFromDate(DateTimeUtil::getDateFromDaterange($this->daterange, $index_date));
        return $index;
    }

    private function recurrentConfig()
    {
        $config = $this->config();

        foreach ($this->blocked_days as $day => $blocked) {
            if (!$blocked) {
                $index = $this->reccurentIndexByWeekday($day);

                echo '<h5>' . DateTimeUtil::getLongWeekdayByIndex($day) . '</h5>';
                echo '<table class="table order-list date__' . $index . '">';
                echo '<thead><tr>';
                echo '<th>Zeit</th>';
                echo '<th>Anzahl Slots</th>';
                echo '<th colspan="2">Bemerkung (wird angezeigt)</th>';
                echo '</tr></thead>';

                echo '<tbody>';

                $i = 0;
                if (!is_null($config) && array_key_exists($index, $config)) {
                    foreach ($config[$index] as $id => $dateConfig) {
                        echo '<tr data-row="' . $id . '">';
                        echo '<td>';
                        echo '<input type="time" name="config[' . $index . '][' . $id . '][time]" class="form-control" placeholder="Zeit von (HH:mm)" value="' . CntndUtil::emptyIfNull($dateConfig['time']) . '" required/>';
                        echo '<input type="time" name="config[' . $index . '][' . $id . '][time_until]" class="form-control" placeholder="Zeit bis (HH:mm)" value="' . CntndUtil::emptyIfNull($dateConfig['time_until']) . '" />';
                        echo '</td>';
                        echo '<td><input type="number" name="config[' . $index . '][' . $id . '][slots]" class="form-control" placeholder="Anzahl Slots" value="' . $dateConfig['slots'] . '" required/></td>';
                        echo '<td><input type="text" name="config[' . $index . '][' . $id . '][comment]" class="form-control" placeholder="Bemerkung" value="' . $dateConfig['comment'] . '" /></td>';
                        echo '<td><button type="button" class="btn btn-sm cntnd_booking-config-delete">Löschen</button></td>';
                        echo '</tr>';

                        $i = $id + 1;
                    }
                }

                echo '<tr data-row="' . $i . '">';
                echo '<td>';
                echo '<input type="time" name="config[' . $index . '][' . $i . '][time]" class="form-control" placeholder="Zeit von (HH:mm)" required/>';
                echo '<input type="time" name="config[' . $index . '][' . $i . '][time_until]" class="form-control" placeholder="Zeit bis (HH:mm)" />';
                echo '</td>';
                echo '<td><input type="number" name="config[' . $index . '][' . $i . '][slots]" class="form-control" placeholder="Anzahl Slots" required/></td>';
                echo '<td><input type="text" name="config[' . $index . '][' . $i . '][comment]" class="form-control" placeholder="Bemerkung"/></td>';
                echo '<td><button type="button" class="btn btn-sm cntnd_booking-config-delete">Löschen</button></td>';
                echo '</tr>';

                echo '</tbody>';

                echo '<tfoot><tr>';
                echo '<td colspan="4">';
                echo '<button type="button" class="btn btn-sm btn-light cntnd_booking-recurrent-config-add" data-date="' . $index . '">Zeit hinzufügen</button>&nbsp;';
                echo '<button type="button" class="btn btn-sm btn-primary cntnd_booking-config-save">Speichern</button>';
                echo '</td>';
                echo '</tr></tfoot>';

                echo '</table>';
            }
        }
    }

    private function customConfig()
    {
        $config = $this->config();
        $daterange = DateTimeUtil::getDaterange($this->daterange, $this->blocked_days);

        foreach ($daterange as $date) {
            $index = DateTimeUtil::getIndexFromDate($date[0]);
            echo '<h5>' . $date[1] . '</h5>';
            echo '<table class="table order-list date__' . $index . '">';
            echo '<thead><tr>';
            echo '<th>Zeit</th>';
            echo '<th>Anzahl Slots</th>';
            echo '<th colspan="2">Bemerkung (wird angezeigt)</th>';
            echo '</tr></thead>';

            echo '<tbody>';

            $i = 0;
            if (!is_null($config) && array_key_exists($index, $config)) {
                foreach ($config[$index] as $id => $dateConfig) {
                    echo '<tr data-row="' . $id . '">';
                    echo '<td><input type="time" name="config[' . $index . '][' . $id . '][time]" class="form-control" placeholder="Zeit (HH:mm)" value="' . $dateConfig['time'] . '" required/></td>';
                    echo '<td><input type="number" name="config[' . $index . '][' . $id . '][slots]" class="form-control" placeholder="Anzahl Slots" value="' . $dateConfig['slots'] . '" required/></td>';
                    echo '<td><input type="text" name="config[' . $index . '][' . $id . '][comment]" class="form-control" placeholder="Bemerkung" value="' . $dateConfig['comment'] . '" /></td>';
                    echo '<td><button type="button" class="btn btn-sm cntnd_booking-config-delete">Löschen</button></td>';
                    echo '</tr>';

                    $i = $id + 1;
                }
            }

            echo '<tr data-row="' . $i . '">';
            echo '<td><input type="time" name="config[' . $index . '][' . $i . '][time]" class="form-control" placeholder="Zeit (HH:mm)" required/></td>';
            echo '<td><input type="number" name="config[' . $index . '][' . $i . '][slots]" class="form-control" placeholder="Anzahl Slots" required/></td>';
            echo '<td><input type="text" name="config[' . $index . '][' . $i . '][comment]" class="form-control" placeholder="Bemerkung"/></td>';
            echo '<td><button type="button" class="btn btn-sm cntnd_booking-config-delete">Löschen</button></td>';
            echo '</tr>';

            echo '</tbody>';

            echo '<tfoot><tr>';
            echo '<td colspan="4">';
            echo '<button type="button" class="btn btn-sm btn-light cntnd_booking-config-add" data-date="' . $index . '">Zeit hinzufügen</button>&nbsp;';
            echo '<button type="button" class="btn btn-sm btn-primary cntnd_booking-config-save">Speichern</button>';
            echo '</td>';
            echo '</tr></tfoot>';

            echo '</table>';
        }
    }

    public function saveConfig($post, $interval = false)
    {
        $config = $this->config();

        if (is_array($post['config'])) {
            foreach ($post['config'] as $date => $dateConfig) {
                if (is_null($config) || !array_key_exists($date, $config)) {
                    $this->insertDateConfig($date, $dateConfig);
                } else {
                    if (!$interval) {
                        $this->updateDateConfig($date, $dateConfig, $config[$date]);
                    }
                }
            }
        }

        $this->config = $this->config();
    }

    private function checkDateTimeConfig($config)
    {
        if (array_key_exists('time', $config) &&
            array_key_exists('slots', $config)) {
            return (!empty($config['time']) && !empty($config['slots']));
        }
        return false;
    }

    private function insertDateConfig($date, $dateConfig)
    {
        foreach ($dateConfig as $config) {
            $this->insertDateTimeConfig($date, $config);
        }
    }

    private function insertDateTimeConfig($date, $config)
    {
        if ($this->checkDateTimeConfig($config)) {
            $sql = "INSERT INTO :table (idart, date, time, time_until, day, slots, comment, recurrent) VALUES (:idart, ':date', ':time', ':until', :day, :slots, ':comment', :recurrent)";
            $values = array(
                'table' => self::$_vars['db']['config'],
                'idart' => cSecurity::toInteger($this->idart),
                'date' => DateTimeUtil::getInsertDate($date),
                'time' => DateTimeUtil::getInsertDateTime($date, $config['time']),
                'until' => DateTimeUtil::getInsertTimeOrNull($config['time_until']),
                'day' => DateTimeUtil::getInsertDay($date),
                'slots' => cSecurity::toInteger($config['slots']),
                'comment' => $this->escape($config['comment']),
                'recurrent' => CntndUtil::boolToInt($config['recurrent'])
            );
            $this->db->query($sql, $values);
        }
    }

    private function updateDateConfig($date, $dateConfig, $originalConfig)
    {
        foreach ($dateConfig as $id => $config) {
            if (array_key_exists($id, $originalConfig)) {
                $this->updateDateTimeConfig($id, $date, $config);
            } else {
                $this->insertDateTimeConfig($date, $config);
            }
        }
    }

    private function updateDateTimeConfig($id, $date, $config)
    {
        if ($this->checkDateTimeConfig($config)) {
            $sql = "UPDATE :table SET idart = :idart, date = ':date', time = ':time', time_until = ':until', day = :day, slots = :slots, comment = ':comment', recurrent = :recurrent WHERE id = :uid";
            $values = array(
                'table' => self::$_vars['db']['config'],
                'uid' => cSecurity::toInteger($id),
                'idart' => cSecurity::toInteger($this->idart),
                'date' => DateTimeUtil::getInsertDate($date),
                'time' => DateTimeUtil::getInsertDateTime($date, $config['time']),
                'until' => DateTimeUtil::getInsertTimeOrNull($config['time_until']),
                'day' => DateTimeUtil::getInsertDay($date),
                'slots' => cSecurity::toInteger($config['slots']),
                'comment' => $this->escape($config['comment']),
                'recurrent' => CntndUtil::boolToInt($config['recurrent'])
            );
            $this->db->query($sql, $values);
        }
    }

    public function daterange()
    {
        return $this->daterange;
    }

    private function recurrentIndexByDate($date)
    {
        $weekday = DateTimeUtil::getWeekdayIndex($date);
        return $this->reccurentIndexByWeekday($weekday);
    }

    public function renderData()
    {
        $displayData = array();
        $daterange = DateTimeUtil::getDaterange($this->daterange, $this->blocked_days, $this->show_past);
        $data = $this->load($this->daterange);
        $config = $this->config();

        foreach ($daterange as $date) {
            $dateIndex = DateTimeUtil::getIndexFromDate($date[0]);
            $index = DateTimeUtil::getWeekdayIndex($date[0]);
            $entries = array();

            if (!is_null($config)) {
                $dateConfigs = array();
                foreach ($config['config'][$index] as $dateConfig) {
                    $dt = DateTimeUtil::getIndexFromDateAndTime($date[0], $dateConfig['time']);
                    $time = substr($dt, -4);
                    $dateConfig['time_index'] = $time;
                    $dateConfig['time_value'] = $dt;
                    $bookings = array();
                    if (array_key_exists($dateIndex, $data) && array_key_exists($time, $data[$dateIndex])) {
                        foreach ($data[$dateIndex][$time] as $slots) {
                            $amount = 1;
                            for ($i = 0; $i < $amount; $i++) {
                                $bookings[] = $slots['status'];
                            }
                        }
                    }

                    for ($i = 0; $i < $dateConfig['slots']; $i++) {
                        if (empty($bookings[$i])) {
                            $bookings[$i] = "free";
                        }
                    }
                    $dateConfig['bookings'] = $bookings;
                    $dateConfig['type'] = $this->dayType($time);
                    $dateConfigs[$time] = $dateConfig;
                }

                asort($dateConfigs);

                $entries = array(
                    "title" => $date[1],
                    "dateConfigs" => $dateConfigs
                );
            }

            $displayData[] = array(
                "index" => $index,
                "dateIndex" => $dateIndex,
                "showDaterange" => DateTimeUtil::getShowDaterange($this->daterange, $this->show_daterange),
                "entries" => $entries
            );
        }

        return $displayData;
    }

    private function dayType($value)
    {
        $time = intval($value);
        if ($time > 1200) {
            return "afternoon";
        }
        return "morning";
    }

    public static function validate($post, $rand)
    {
        if (is_array($post) && $rand == $post['rand']) {
            return (self::validateDates($post) && self::validateRequired($post));
        }
        return false;
    }

    public static function validateFree($post, $idart)
    {
        if (!self::isOneClick($post)) {
            $date = key($post['bookings']);
            $time = key($post['bookings'][$date]);
            $slots = count($post['bookings'][$date][$time]);
        } else {
            $booking = $post['booking'];
            $date = DateTimeUtil::getDateFromIndexDateTime($booking);
            $time = DateTimeUtil::getTimeFromIndexDateTime($booking);
            $slots = 1;
        }

        $db = new cDb;
        $sql = "SELECT amount FROM :table WHERE idart = :idart AND time = ':time'";
        $values = array(
            'table' => self::$_vars['db']['bookings'],
            'idart' => cSecurity::toInteger($idart),
            'time' => DateTimeUtil::getInsertDateTime($date, $time));
        $result = $db->query($sql, $values);
        if ($result->num_rows > 0) {
            $max = self::availableSlots($idart, $date, $time);
            $amount = 0;
            while ($db->next_record()) {
                $amount = $amount + $db->f('amount');
            }
            $free = $max - $amount;
            return ($free >= $slots);
        }
        return true;
    }

    private static function availableSlots($idart, $date, $time)
    {
        $db = new cDb;
        $sql = "SELECT slots FROM :table WHERE idart = :idart AND time = ':time'";
        $values = array(
            'table' => self::$_vars['db']['config'],
            'idart' => cSecurity::toInteger($idart),
            'time' => DateTimeUtil::getInsertDateTime($date, $time));
        $db->query($sql, $values);
        return $db->getResultObject()->slots;
    }

    private static function validateDates($post)
    {
        if (!self::isOneClick($post)) {
            return (array_key_exists('bookings', $post) && is_array($post['bookings']));
        } else {
            return (array_key_exists('booking', $post));
        }
    }

    private static function isOneClick($post)
    {
        if (array_key_exists('one_click_booking', $post)) {
            return (bool)$post['one_click_booking'];
        }
        return false;
    }

    private static function validateRequired($post)
    {
        $valid = false;
        if (array_key_exists('required', $post)) {
            $valid = true;
            $required = json_decode(base64_decode($post['required']), true);
            if (is_array($required)) {
                foreach ($required as $value) {
                    if (empty($post[$value])) {
                        $valid = false;
                    }
                }
            }
        }
        return $valid;
    }

    public function store($post, $recurrent, $interval)
    {
        if (!$this->one_click) {
            return $this->storeMany($post, $recurrent);
        } else {
            return $this->storeOne($post, $recurrent);
        }
    }

    private function storeMany($post, $recurrent)
    {
        $date = key($post['bookings']);
        $time = key($post['bookings'][$date]);
        $amount = count($post['bookings'][$date][$time]);
        if ($recurrent) {
            $amount = $post['personen'];
        }

        $sql = "INSERT INTO :table (idart, date, time, amount, name, address, po_box, email, phone, comment) VALUES (:idart, ':date', ':time', :amount, ':name', ':address', ':po_box', ':email', ':phone', ':comment')";
        $values = array(
            'table' => self::$_vars['db']['bookings'],
            'idart' => cSecurity::toInteger($this->idart),
            'date' => DateTimeUtil::getInsertDate($date),
            'time' => DateTimeUtil::getInsertDateTime($date, $time),
            'amount' => cSecurity::toInteger($amount),
            'name' => $this->escape($post['name']),
            'address' => $this->escape($post['adresse']),
            'po_box' => $this->escape($post['plz_ort']),
            'email' => $this->escape($post['email']),
            'phone' => $this->escape($post['telefon']),
            'comment' => $this->escape($post['bemerkungen'])
        );
        if ($this->db->query($sql, $values)) {
            $this->informationEmail($post, $date, $time, $amount);
            return true;
        }
        return false;
    }

    private function storeOne($post, $recurrent)
    {
        $booking = $post['booking'];
        $date = DateTimeUtil::getDateFromIndexDateTime($booking);
        $time = DateTimeUtil::getTimeFromIndexDateTime($booking);

        $amount = 1;
        if ($recurrent) {
            $amount = $post['personen'];
        }

        $sql = "INSERT INTO :table (idart, date, time, amount, name, address, po_box, email, phone, comment) VALUES (:idart, ':date', ':time', :amount, ':name', ':address', ':po_box', ':email', ':phone', ':comment')";
        $values = array(
            'table' => self::$_vars['db']['bookings'],
            'idart' => cSecurity::toInteger($this->idart),
            'date' => DateTimeUtil::getInsertDate($date),
            'time' => DateTimeUtil::getInsertDateTime($date, $time),
            'amount' => cSecurity::toInteger($amount),
            'name' => $this->escape($post['name']),
            'address' => $this->escape($post['adresse']),
            'po_box' => $this->escape($post['plz_ort']),
            'email' => $this->escape($post['email']),
            'phone' => $this->escape($post['telefon']),
            'comment' => $this->escape($post['bemerkungen'])
        );
        if ($this->db->query($sql, $values)) {
            $this->informationEmail($post, $date, $time, $amount);
            return true;
        }
        return false;
    }

    // legacy
    private function informationEmail($post, $date, $time, $amount)
    {
        // use template to display email
        $smarty = cSmartyFrontend::getInstance();
        $smarty->assign('date', DateTimeUtil::getReadableDate($date));
        $smarty->assign('time', DateTimeUtil::getReadableTimeFromDate($time));
        $smarty->assign('name', $post['name']);
        $smarty->assign('adresse', $post['adresse']);
        $smarty->assign('plz_ort', $post['plz_ort']);
        $smarty->assign('telefon', $post['telefon']);
        $smarty->assign('bemerkungen', $post['bemerkungen']);
        $smarty->assign('email', $post['email']);
        $smarty->assign('personen', $amount);
        $smarty->assign('sauna', $this->subject['booking_title']);
        $body = $smarty->fetch('email-booking.html');

        if (!$this->debug) {
            $mailer = new cMailer();

            // Create a message
            $mail = Swift_Message::newInstance($this->subject['default'])
                ->setFrom($this->mailto)
                ->setTo($post['email'])
                ->setBody($body, 'text/html');

            // Send the message
            $result = $mailer->send($mail);
        } else {
            $result = true;
        }
        return $result;
    }

    public function load($daterange)
    {
        $dates = DateTimeUtil::getDatesFromDaterange($daterange, $this->show_past);
        $datum_von = DateTimeUtil::getInsertDate($dates[0]);
        $sql = "SELECT * FROM :table WHERE idart = :idart AND date between ':datum_von' AND ':datum_bis' ORDER BY date, time";
        $values = array(
            'table' => self::$_vars['db']['bookings'],
            'idart' => $this->idart,
            'datum_von' => $datum_von,
            'datum_bis' => DateTimeUtil::getInsertDate($dates[1])
        );
        $this->db->query($sql, $values);
        $data = [];
        while ($this->db->next_record()) {
            $index = DateTimeUtil::getIndexFromDate($this->db->f('date'));
            $time = DateTimeUtil::getIndexFromDateTime($this->db->f('time'));
            $data[$index][$time][$this->db->f('id')] = array(
                'amount' => $this->db->f('amount'),
                'status' => $this->db->f('status'));
        }
        return $data;
    }

    public function loadById($id)
    {
        $sql = "SELECT * FROM :table WHERE id = :id";
        $values = array(
            'table' => self::$_vars['db']['bookings'],
            'id' => $id);
        $this->db->query($sql, $values);
        return $this->db->getResultObject();
    }

    public function listAll($past = false)
    {
        $sql = "SELECT * FROM :table WHERE idart = :idart AND date >= ':datum' ORDER BY date, time";
        if ($past) {
            $sql = "SELECT * FROM :table WHERE idart = :idart ORDER BY date, time";
        }
        $values = array(
            'table' => self::$_vars['db']['bookings'],
            'idart' => cSecurity::toInteger($this->idart),
            'datum' => date('Y-m-d'));
        $this->db->query($sql, $values);
        $data = [];
        while ($this->db->next_record()) {
            $title = '';
            $is_past = false;
            if ($past) {
                $is_past = DateTimeUtil::isPast($this->db->f('date'));
            }
            $newDate = DateTimeUtil::getIndexFromDate($this->db->f('date'));
            $newTime = DateTimeUtil::getIndexFromDateTime($this->db->f('time'));
            $readableTime = DateTimeUtil::getReadableTimeFromDate($this->db->f('time'));
            if ($time != $newTime || $date != $newDate) {
                $title = "Zeit: " . $readableTime;
            }
            $data_detail = array(
                'id' => $this->db->f('id'),
                'time' => $readableTime,
                'name' => $this->db->f('name'),
                'adresse' => $this->db->f('address'),
                'status' => $this->db->f('status'),
                'plz_ort' => $this->db->f('po_box'),
                'email' => $this->db->f('email'),
                'telefon' => $this->db->f('phone'),
                'personen' => $this->db->f('amount'),
                'bemerkungen' => $this->db->f('comment'),
                'title' => $title,
                'past' => $is_past);
            $data[date('d.m.Y', strtotime($this->db->f('date')))][] = $data_detail;
            $time = DateTimeUtil::getIndexFromDateTime($this->db->f('time'));
            $date = DateTimeUtil::getIndexFromDate($this->db->f('date'));
        }
        return $data;
    }

    public static function validateUpdate($post)
    {
        if (is_array($post)) {
            if (array_key_exists('resid', $post) && array_key_exists('action', $post)) {
                return true;
            }
        }
        return false;
    }

    public function update($post)
    {
        if ($post['action'] == 'delete') {
            $sql = "DELETE FROM :table WHERE id = :id";
            $values = array(
                'table' => self::$_vars['db']['bookings'],
                'id' => $post['resid']);
            $this->rejectionEmail($post);
        } else {
            $sql = "UPDATE :table SET status = ':status', mut_date = NOW() WHERE id = :id";
            $values = array(
                'table' => self::$_vars['db']['bookings'],
                'status' => 'reserved',
                'id' => $post['resid']);
            $this->confirmationEmail($post);
        }
        return $this->db->query($sql, $values);
    }

    // legacy
    private function confirmationEmail($post)
    {
        // use template to display email
        $smarty = cSmartyFrontend::getInstance();
        $record = $this->loadById($post['resid']);
        $smarty->assign('date', DateTimeUtil::getReadableDate($record->date));
        $smarty->assign('time', DateTimeUtil::getReadableTimeFromDate($record->time));
        $smarty->assign('personen', $record->amount);
        $smarty->assign('bemerkungen', $record->comment);
        $smarty->assign('message', $post['bemerkungen']);
        $smarty->assign('sauna', $this->subject['booking_title']);
        $body = $smarty->fetch('email-reserved.html');

        if (!$this->debug) {
            $mailer = new cMailer();

            // Create a message
            $mail = Swift_Message::newInstance($this->subject['reserved'])
                ->setFrom($this->mailto)
                ->setTo($record->email)
                ->setBody($body, 'text/html');

            // Send the message
            $result = $mailer->send($mail);
        } else {
            $result = true;
        }
        return $result;
    }

    // legacy
    private function rejectionEmail($post)
    {
        // use template to display email
        $smarty = cSmartyFrontend::getInstance();
        $record = $this->loadById($post['resid']);
        $smarty->assign('date', DateTimeUtil::getReadableDate($record->date));
        $smarty->assign('time', DateTimeUtil::getReadableTimeFromDate($record->time));
        $smarty->assign('personen', $record->amount);
        $smarty->assign('bemerkungen', $record->comment);
        $smarty->assign('message', $post['bemerkungen']);
        $smarty->assign('sauna', $this->subject['booking_title']);
        $body = $smarty->fetch('email-declined.html');

        if (!$this->debug) {
            $mailer = new cMailer();
            // Create a message
            $mail = Swift_Message::newInstance($this->subject['declined'])
                ->setFrom($this->mailto)
                ->setTo($record->email)
                ->setBody($body, 'text/html');

            // Send the message
            $result = $mailer->send($mail);
        } else {
            $result = true;
        }
        return $result;
    }

    private function escape($string)
    {
        $escaped = htmlentities($string, ENT_QUOTES, "UTF-8");
        return $this->db->escape($escaped);
    }
}

?>
