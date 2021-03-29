<?php
cInclude('module', 'includes/class.datetime.php');
/**
 * cntnd_simple_booking Class
 */
class CntndSimpleBooking {

  private $daterange;
  private $mailto;
  private $subject;
  private $blocked_days;

  private $db;
  private $client;
  private $lang;
  private $idart;

  private $config;
  private $debug = false;

  private static $_vars = array(
    "db"=> array(
        "config"=>"cntnd_simple_booking_config",
        "bookings"=>"cntnd_simple_booking"
    )
  );

  function __construct($daterange, $config_reset, $mailto, $subject, $blocked_days, $lang, $client, $idart) {
    $this->daterange=$daterange;
    $this->mailto=$mailto;
    $this->subject=$subject;
    $this->blocked_days=$blocked_days;

    $this->db = new cDb;
    $this->client = $client;
    $this->lang = $lang;
    $this->idart = $idart;

    $this->config = $this->config($config_reset);
  }

  private function config($config_reset=false){
    if (!$config_reset) {
      $sql = "SELECT * FROM :table WHERE idart = :idart";
      $values = array(
          'table' => self::$_vars['db']['config'],
          'idart' => $this->idart);
      $result = $this->db->query($sql, $values);
      if ($result->num_rows > 0) {
        $config = array();
        while ($this->db->nextRecord()) {
          $rs = $this->db->toObject();
          $config[DateTimeUtil::getIndexFromDate($rs->date)][$rs->id] = array(
              'time' => DateTimeUtil::getReadableTimeFromDate($rs->time),
              'slots' => $rs->slots,
              'comment' => $rs->comment);
        }
        return $config;
      }
    }
    else {
      $this->configReset($config_reset);
    }
    return NULL;
  }

  private function configReset($config_reset){
    $blocked_days = json_decode(base64_decode($config_reset), true);
    foreach ($blocked_days as $day => $blocked_day){
      if ($blocked_day){
        $this->removeConfig($day);
      }
    }
    return $this->config();
  }

  private function removeConfig($day){
    $sql = "DELETE FROM :table WHERE day = :day AND idart = :idart";
    $values = array(
      'table' => self::$_vars['db']['config'],
      'day' => $day,
      'idart' => $this->idart);
    $this->db->query($sql, $values);
  }

  public function hasConfig(){
    return !is_null($this->config());
  }

  public function renderConfig(){
    $config = $this->config();
    $daterange = DateTimeUtil::getDaterange($this->daterange,$this->blocked_days);

    foreach ($daterange as $date) {
      $index = DateTimeUtil::getIndexFromDate($date[0]);
      echo '<h5>'.$date[1].'</h5>';
      echo '<table class="table order-list date__'.$index.'">';
      echo '<thead><tr>';
      echo '<th>Zeit</th>';
      echo '<th>Anzahl Slots</th>';
      echo '<th colspan="2">Bemerkung (wird angezeigt)</th>';
      echo '</tr></thead>';

      echo '<tbody>';

      $i=0;
      if (!is_null($config) && array_key_exists($index, $config)){
        foreach ($config[$index] as $id => $dateConfig){
          echo '<tr data-row="'.$id.'">';
          echo '<td><input type="time" name="config['.$index.']['.$id.'][time]" class="form-control" placeholder="Zeit (HH:mm)" value="'.$dateConfig['time'].'" required/></td>';
          echo '<td><input type="number" name="config['.$index.']['.$id.'][slots]" class="form-control" placeholder="Anzahl Slots" value="'.$dateConfig['slots'].'" required/></td>';
          echo '<td><input type="text" name="config['.$index.']['.$id.'][comment]" class="form-control" placeholder="Bemerkung" value="'.$dateConfig['comment'].'" /></td>';
          echo '<td><button type="button" class="btn btn-sm cntnd_booking-config-delete">Löschen</button></td>';
          echo '</tr>';

          $i = $id + 1;
        }
      }

      echo '<tr data-row="'.$i.'">';
      echo '<td><input type="time" name="config['.$index.']['.$i.'][time]" class="form-control" placeholder="Zeit (HH:mm)" required/></td>';
      echo '<td><input type="number" name="config['.$index.']['.$i.'][slots]" class="form-control" placeholder="Anzahl Slots" required/></td>';
      echo '<td><input type="text" name="config['.$index.']['.$i.'][comment]" class="form-control" placeholder="Bemerkung"/></td>';
      echo '<td><button type="button" class="btn btn-sm cntnd_booking-config-delete">Löschen</button></td>';
      echo '</tr>';

      echo '</tbody>';

      echo '<tfoot><tr>';
      echo '<td colspan="4">';
      echo '<button type="button" class="btn btn-sm btn-light cntnd_booking-config-add" data-date="'.$index.'">Zeit hinzufügen</button>&nbsp;';
      echo '<button type="button" class="btn btn-sm btn-primary cntnd_booking-config-save">Speichern</button>';
      echo '</td>';
      echo '</tr></tfoot>';

      echo '</table>';
    }
  }

  public function saveConfig($post){
    $config = $this->config();

    if (is_array($post['config'])){
      foreach ($post['config'] as $date => $dateConfig){
        if (is_null($config) || !array_key_exists($date, $config)){
          $this->insertDateConfig($date, $dateConfig);
        }
        else {
          $this->updateDateConfig($date, $dateConfig, $config[$date]);
        }
      }
    }

    $this->config = $this->config();
  }

  private function checkDateTimeConfig($config){
    if (array_key_exists('time', $config) &&
        array_key_exists('slots', $config)){
      return (!empty($config['time']) && !empty($config['slots']));
    }
    return false;
  }

  private function insertDateConfig($date, $dateConfig){
    foreach ($dateConfig as $config){
      $this->insertDateTimeConfig($date, $config);
    }
  }

  private function insertDateTimeConfig($date, $config){
    if ($this->checkDateTimeConfig($config)) {
      $sql = "INSERT INTO :table (idart, date, time, day, slots, comment) VALUES (:idart, ':date', ':time', :day, :slots, ':comment')";
      $values = array(
          'table' => self::$_vars['db']['config'],
          'idart' => cSecurity::toInteger($this->idart),
          'date' => DateTimeUtil::getInsertDate($date),
          'time' => DateTimeUtil::getInsertDateTime($date, $config['time']),
          'day' => DateTimeUtil::getInsertDay($date),
          'slots' => cSecurity::toInteger($config['slots']),
          'comment' => $this->escape($config['comment'])
      );
      $this->db->query($sql, $values);
    }
  }

  private function updateDateConfig($date, $dateConfig, $originalConfig){
    foreach ($dateConfig as $id => $config){
      if (array_key_exists($id, $originalConfig)){
        $this->updateDateTimeConfig($id, $date, $config);
      }
      else {
        $this->insertDateTimeConfig($date, $config);
      }
    }
  }

  private function updateDateTimeConfig($id, $date, $config){
    if ($this->checkDateTimeConfig($config)) {
      $sql= "UPDATE :table SET idart = :idart, date = ':date', time = ':time', day = :day, slots = :slots, comment = ':comment' WHERE id = :uid";
      $values = array(
          'table' => self::$_vars['db']['config'],
          'uid' => cSecurity::toInteger($id),
          'idart' => cSecurity::toInteger($this->idart),
          'date' => DateTimeUtil::getInsertDate($date),
          'time' => DateTimeUtil::getInsertDateTime($date, $config['time']),
          'day' => DateTimeUtil::getInsertDay($date),
          'slots' => cSecurity::toInteger($config['slots']),
          'comment' => $this->escape($config['comment'])
      );
      $this->db->query($sql, $values);
    }
  }

  public function daterange(){
    return $this->daterange;
  }

  public function render(){
    $daterange = DateTimeUtil::getDaterange($this->daterange,$this->blocked_days);
    $data = $this->load($this->daterange);
    $config = $this->config();

    foreach ($daterange as $date) {
      $index = DateTimeUtil::getIndexFromDate($date[0]);

      if (!is_null($config) && array_key_exists($index, $config)) {
        echo '<h6>' . $date[1] . '</h6>';
        echo '<table class="table cntnd_booking-table">';
        echo '<thead><tr>';
        echo '<th width="25%">Zeit</th>';
        echo '<th>Reservation</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($config[$index] as $dateConfig){
          $dt = DateTimeUtil::getIndexFromDateAndTime($date[0], $dateConfig['time']);
          $time=substr($dt, -4);
          $bookings = array();
          if (array_key_exists($index, $data) && array_key_exists($time, $data[$index])){
            foreach ($data[$index][$time] as $slots){
              for($i=0;$i<$slots['amount'];$i++){
                $bookings[]=$slots['status'];
              }
            }
          }
          $comment = "";
          if (!empty($dateConfig['comment'])){
            $comment = "(".$dateConfig['comment'].")";
          }
          echo '<tr>';
          echo '<td><span class="cntnd_booking-date">'.$dateConfig['time'].' '.$comment.'</span></td>';
          echo '<td><div class="d-flex">';
          for ($i=0;$i<$dateConfig['slots'];$i++){
            $disabled='';
            if (empty($bookings[$i])){
              $bookings[$i]="free";
            }
            else if ($bookings[$i]!="free"){
              $disabled='disabled="disabled"';
            }
            echo '<div class="w-auto cntnd_booking__slot" data-status="'.$bookings[$i].'">';
            echo '<input class="cntnd_booking-checkbox" name="bookings['.$index.']['.$time.'][]" type="checkbox" value="reserved" '.$disabled.' />';
            echo '</div>';
          }
          echo '</div></td>';
          echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
      }
    }
  }

  public static function validate($post, $rand){
    if (is_array($post) && $rand==$post['rand']){
      return (self::validateDates($post) && self::validateRequired($post));
    }
    return false;
  }

  public static function validateFree($post, $idart){
    $date = key($post['bookings']);
    $time = key($post['bookings'][$date]);
    $slots = count($post['bookings'][$date][$time]);

    $db = new cDb;
    $sql = "SELECT amount FROM :table WHERE idart = :idart AND time = ':time'";
    $values = array(
        'table' => self::$_vars['db']['bookings'],
        'idart' => cSecurity::toInteger($idart),
        'time' => DateTimeUtil::getInsertDateTime($date, $time));
    $result = $db->query($sql, $values);
    if ($result->num_rows > 0) {
      $max = self::availableSlots($idart, $date, $time);
      $amount=0;
      while ($db->next_record()) {
        $amount = $amount + $db->f('amount');
      }
      $free = $max - $amount;
      return ($free>=$slots);
    }
    return true;
  }

  private static function availableSlots($idart, $date, $time){
    $db = new cDb;
    $sql = "SELECT slots FROM :table WHERE idart = :idart AND time = ':time'";
    $values = array(
        'table' => self::$_vars['db']['config'],
        'idart' => cSecurity::toInteger($idart),
        'time' => DateTimeUtil::getInsertDateTime($date, $time));
    $db->query($sql, $values);
    return $db->getResultObject()->slots;
  }

  private static function validateDates($post){
    return (array_key_exists('bookings',$post) && is_array($post['bookings']));
  }

  private static function validateRequired($post){
    $valid=false;
    if (array_key_exists('required',$post)){
      $valid=true;
      $required = json_decode(base64_decode($post['required']), true);
      if (is_array($required)){
        foreach ($required as $value) {
          if (empty($post[$value])){
            $valid=false;
          }
        }
      }
    }
    return $valid;
  }

  public function store($post){
    $date = key($post['bookings']);
    $time = key($post['bookings'][$date]);
    $amount = count($post['bookings'][$date][$time]);

    $sql = "INSERT INTO :table (idart, date, time, amount, name, address, po_box, email, phone, comment) VALUES (:idart, ':date', ':time', :amount, ':name', ':address', ':po_box', ':email', ':phone', ':comment')";
    $values = array(
        'table' => self::$_vars['db']['bookings'],
        'idart' => cSecurity::toInteger($this->idart),
        'date' => DateTimeUtil::getInsertDate($date),
        'time'=> DateTimeUtil::getInsertDateTime($date, $time),
        'amount'=> cSecurity::toInteger($amount),
        'name'=> $this->escape($post['name']),
        'address'=> $this->escape($post['adresse']),
        'po_box'=> $this->escape($post['plz_ort']),
        'email'=> $this->escape($post['email']),
        'phone'=> $this->escape($post['telefon']),
        'comment'=> $this->escape($post['bemerkungen'])
    );
    if ($this->db->query($sql, $values)){
      $this->informationEmail($post, $date, $time, $amount);
      return true;
    }
    return false;
  }

  // legacy
  private function informationEmail($post, $date, $time, $amount){
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
    $body = $smarty->fetch('email_reservation.html');

    if (!$this->debug){
      $mailer = new cMailer();

      // Create a message
      $mail = Swift_Message::newInstance($this->subject['default'])
      ->setFrom($this->mailto)
      ->setTo($post['email'])
      ->setBody($body, 'text/html');

      // Send the message
      $result = $mailer->send($mail);
    }
    else {
      var_dump($body);
      $result = true;
    }
    return $result;
  }

  public function load($daterange){
    $dates = DateTimeUtil::getDatesFromDaterange($daterange);
    $sql = "SELECT * FROM :table WHERE date between ':datum_von' AND ':datum_bis' ORDER BY date, time";
    $values = array(
      'table' => self::$_vars['db']['bookings'],
      'datum_von' => $dates[0]->format('Y-m-d'),
      'datum_bis' => $dates[1]->format('Y-m-d')
    );
    $this->db->query($sql, $values);
    $data=[];
    while ($this->db->next_record()) {
      $index=DateTimeUtil::getIndexFromDate($this->db->f('date'));
      $time=DateTimeUtil::getIndexFromDateTime($this->db->f('time'));
      $data[$index][$time][$this->db->f('id')]=array(
          'amount' => $this->db->f('amount'),
          'status' => $this->db->f('status'));
    }
    return $data;
  }

  public function loadById($id){
    $sql = "SELECT * FROM :table WHERE id = :id";
    $values = array(
        'table' => self::$_vars['db']['bookings'],
        'id' => $id);
    $this->db->query($sql, $values);
    return $this->db->getResultObject();
  }

  public function listAll(){
    $sql = "SELECT * FROM :table WHERE date >= ':datum' ORDER BY date, time";
    $values = array(
        'table' => self::$_vars['db']['bookings'],
        'datum' => date('Y-m-d'));
    $this->db->query($sql, $values);
    $data=[];
    while ($this->db->next_record()) {
      $title='';
      $newDate = DateTimeUtil::getIndexFromDate($this->db->f('date'));
      $newTime = DateTimeUtil::getIndexFromDateTime($this->db->f('time'));
      $readableTime = DateTimeUtil::getReadableTimeFromDate($this->db->f('time'));
      if ($time!=$newTime || $date!=$newDate) {
        $title = "Zeit: ".$readableTime;
      }
      $data_detail = array(
        'id'=>$this->db->f('id'),
        'time'=>$readableTime,
        'name'=>$this->db->f('name'),
        'adresse'=>$this->db->f('address'),
        'status'=>$this->db->f('status'),
        'plz_ort'=>$this->db->f('po_box'),
        'email'=>$this->db->f('email'),
        'telefon'=>$this->db->f('phone'),
        'personen'=>$this->db->f('amount'),
        'bemerkungen'=>$this->db->f('comment'),
        'title'=>$title);
      $data[date('d.m.Y',strtotime($this->db->f('date')))][]=$data_detail;
      $time = DateTimeUtil::getIndexFromDateTime($this->db->f('time'));
      $date = DateTimeUtil::getIndexFromDate($this->db->f('date'));
    }
    return $data;
  }

  public static function validateUpdate($post){
    if (is_array($post)){
      if (array_key_exists('resid',$post) && array_key_exists('action',$post)){
        return true;
      }
    }
    return false;
  }

  public function update($post){
    if ($post['action']=='delete'){
      $sql = "DELETE FROM :table WHERE id = :id";
      $values = array(
          'table' => self::$_vars['db']['bookings'],
          'id' => $post['resid']);
      $this->rejectionEmail($post);
    }
    else {
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
  private function confirmationEmail($post){
    // use template to display email
    $smarty = cSmartyFrontend::getInstance();
    $record = $this->loadById($post['resid']);
    $smarty->assign('date', DateTimeUtil::getReadableDate($record->date));
    $smarty->assign('time', DateTimeUtil::getReadableTimeFromDate($record->time));
    $smarty->assign('personen', $record->amount);
    $smarty->assign('bemerkungen', $record->comment);
    $smarty->assign('message', $post['bemerkungen']);
    $body = $smarty->fetch('email_reservation-definitiv.html');

    if (!$this->debug){
      $mailer = new cMailer();

      // Create a message
      $mail = Swift_Message::newInstance($this->subject['reserved'])
      ->setFrom($this->mailto)
      ->setTo($record->email)
      ->setBody($body, 'text/html');

      // Send the message
      $result = $mailer->send($mail);
    }
    else {
      var_dump($body);
      $result = true;
    }
    return $result;
  }

  // legacy
  private function rejectionEmail($post){
    // use template to display email
    $smarty = cSmartyFrontend::getInstance();
    $record = $this->loadById($post['resid']);
    $smarty->assign('date', DateTimeUtil::getReadableDate($record->date));
    $smarty->assign('time', DateTimeUtil::getReadableTimeFromDate($record->time));
    $smarty->assign('personen', $record->amount);
    $smarty->assign('bemerkungen', $record->comment);
    $smarty->assign('message', $post['bemerkungen']);
    $body = $smarty->fetch('email_reservation-abgelehnt.html');

    if (!$this->debug){
      $mailer = new cMailer();
      // Create a message
      $mail = Swift_Message::newInstance($this->subject['declined'])
      ->setFrom($this->mailto)
      ->setTo($record->email)
      ->setBody($body, 'text/html');

      // Send the message
      $result = $mailer->send($mail);
    }
    else {
      var_dump($body);
      $result = true;
    }
    return $result;
  }

  private function escape($string){
    $escaped = htmlentities($string, ENT_QUOTES, "UTF-8");
    return $this->db->escape($escaped);
  }
}
?>
