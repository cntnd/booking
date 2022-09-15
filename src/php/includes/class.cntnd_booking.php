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
    private $interval_slots;

    private $db;
    private $client;
    private $lang;
    private $idart;

    private $config;
    private $debug = false;

    private static $_vars = array(
        "db" => array(
            "bookings" => "cntnd_booking"
        )
    );

    function __construct($daterange, $mailto, $subject, $blocked_days, $one_click, $show_daterange, $show_past, $interval_slots, $timerange_from, $timerange_to, $lang, $client, $idart)
    {
        $this->daterange = $daterange;
        $this->mailto = $mailto;
        $this->subject = $subject;
        $this->blocked_days = $blocked_days;
        $this->one_click = $one_click;
        $this->show_daterange = $show_daterange;
        $this->show_past = $show_past;
        $this->interval_slots = $interval_slots;

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

    public function daterange()
    {
        return $this->daterange;
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
                    $until = str_replace(":","",$dateConfig['time_until']);
                    $time = substr($dt, -4);
                    $dateConfig['time_index'] = $time;
                    $dateConfig['time_value'] = $dt;
                    $dateConfig['time_interval'] = $time."-".$until;
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
                    "dateConfigs" => $dateConfigs,
                    "morning" => $this->isDayTypeBlocked($data[$dateIndex], "morning"),
                    "afternoon" => $this->isDayTypeBlocked($data[$dateIndex], "afternoon")
                );
            }

            $displayData['data'][] = array(
                "index" => $index,
                "dateIndex" => $dateIndex,
                "showDaterange" => DateTimeUtil::getShowDaterange($this->daterange, $this->show_daterange),
                "entries" => $entries
            );
        }

        $displayData['dateConfig'] = $this->config;

        return $displayData;
    }

    private function isDayTypeBlocked($data, $dayType) {
        $blocked = false;
        if (is_array($data)) {
            foreach($data as $key => $date) {
                $type = $this->dayType($key);
                if ($type == $dayType) {
                    $blocked = true;
                }
            }
        }
        return $blocked;
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

    public static function validateAvailability($post, $idart) {
        $available = true;
        $db = new cDb;

        $date = key($post['bookings']);
        $times = array_keys($post['bookings'][$date]);
        $check_time = DateTimeUtil::getStringsFromTimes($times[0]);
        $check_until = DateTimeUtil::getStringsFromTimes(end($times));

        $sql = "SELECT * FROM :table WHERE idart = :idart AND date = ':date' ORDER BY time";
        $values = array(
            'table' => self::$_vars['db']['bookings'],
            'idart' => cSecurity::toInteger($idart),
            'date' => DateTimeUtil::getInsertDate($date));
        $db->query($sql, $values);
        //var_dump($db->prepare($sql, $values));
        while ($db->next_record()) {
            $blocked_time = DateTimeUtil::getIndexFromDateTime($db->f('time'));
            $blocked_until = DateTimeUtil::getIndexFromDateTime($db->f('until'));
            if (
                ($blocked_time>=$check_time && $blocked_time< $check_until) ||
                ($blocked_until> $check_time && $blocked_until<=$check_until) ||
                ($blocked_time<=$check_time && $blocked_until>=$check_until)
            ){
                $available=false;
            }
        }

        return $available;
    }

    private static function availableSlots($idart, $date, $time)
    {
        $db = new cDb;
        $sql = "SELECT slots FROM :table WHERE idart = :idart AND time = ':time'";
        $values = array(
            'table' => self::$_vars['db']['bookings'],
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

    public function store($post)
    {
        if (!$this->one_click) {
            return $this->storeMany($post);
        } else {
            return $this->storeOne($post);
        }
    }

    private function storeMany($post)
    {
        $date = key($post['bookings']);
        $times = array_keys($post['bookings'][$date]);
        asort($times);
        $time = DateTimeUtil::getStringsFromTimes($times[0]);
        $until = DateTimeUtil::getStringsFromTimes(end($times));
        $amount = count($post['bookings'][$date]);

        $sql = "INSERT INTO :table (idart, date, time, until, amount, name, address, po_box, email, phone, comment) VALUES (:idart, ':date', ':time', ':until', :amount, ':name', ':address', ':po_box', ':email', ':phone', ':comment')";
        $values = array(
            'table' => self::$_vars['db']['bookings'],
            'idart' => cSecurity::toInteger($this->idart),
            'date' => DateTimeUtil::getInsertDate($date),
            'time' => DateTimeUtil::getInsertDateTime($date, $time[0]),
            'until' => DateTimeUtil::getInsertDateTime($date, $until[1]),
            'amount' => cSecurity::toInteger($amount),
            'name' => $this->escape($post['name']),
            'address' => $this->escape($post['adresse']),
            'po_box' => $this->escape($post['plz_ort']),
            'email' => $this->escape($post['email']),
            'phone' => $this->escape($post['telefon']),
            'comment' => $this->escape($post['bemerkungen'])
        );
        if ($this->db->query($sql, $values)) {
            $this->informationEmail($post, $date, $time[0], $until[1], $amount);
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
            $this->informationEmail($post, $date, $time[0], $time[1], $amount);
            return true;
        }
        return false;
    }

    // legacy
    private function informationEmail($post, $date, $time, $until, $amount)
    {
        // use template to display email
        $smarty = cSmartyFrontend::getInstance();
        $smarty->assign('date', DateTimeUtil::getReadableDate($date));
        $smarty->assign('time', DateTimeUtil::getReadableTimeFromDate($time));
        $smarty->assign('until', DateTimeUtil::getReadableTimeFromDate($until));
        $smarty->assign('name', $post['name']);
        $smarty->assign('adresse', $post['adresse']);
        $smarty->assign('plz_ort', $post['plz_ort']);
        $smarty->assign('telefon', $post['telefon']);
        $smarty->assign('bemerkungen', $post['bemerkungen']);
        $smarty->assign('email', $post['email']);
        $smarty->assign('personen', $amount);
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
            $until = DateTimeUtil::getIndexFromDateTime($this->db->f('until'));
            $slot = ($this->interval_slots/60*100);
            $max = ($until-$time)/$slot;
            for($i=0;$i<$max;$i++){
                $dataIndex = str_pad((intval($time) + ($i * $slot)), 4, "0", STR_PAD_LEFT);
                $data[$index][$dataIndex][$this->db->f('id')] = array(
                    'amount' => $this->db->f('amount'),
                    'status' => $this->db->f('status'));
            }
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
            $readableUntil = DateTimeUtil::getReadableTimeFromDate($this->db->f('until'));
            if ($time != $newTime || $date != $newDate) {
                $title = "Zeit: " . $readableTime;
            }
            $data_detail = array(
                'id' => $this->db->f('id'),
                'time' => $readableTime,
                'until' => $readableUntil,
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
        $smarty->assign('until', DateTimeUtil::getReadableTimeFromDate($record->until));
        $smarty->assign('personen', $record->amount);
        $smarty->assign('bemerkungen', $record->comment);
        $smarty->assign('message', $post['bemerkungen']);
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
        $smarty->assign('until', DateTimeUtil::getReadableTimeFromDate($record->until));
        $smarty->assign('personen', $record->amount);
        $smarty->assign('bemerkungen', $record->comment);
        $smarty->assign('message', $post['bemerkungen']);
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
