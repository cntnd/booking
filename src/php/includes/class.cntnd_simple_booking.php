<?php
cInclude('module', 'includes/class.datetime.php');
/**
 * cntnd_simple_booking Class
 */
class CntndSimpleBooking {

  private $daterange;
  private $show_daterange;
  private $mailto;
  private $blocked_days;

  private $db;
  private $client;
  private $lang;
  private $idart;

  private $config;

  private $_vars = array(
    "db"=> array(
        "config"=>"cntnd_simple_booking_config",
        "bookings"=>"cntnd_simple_booking_draft"
    )
  );

  function __construct($daterange, $show_daterange, $mailto, $blocked_days, $lang, $client, $idart) {
    $this->daterange=$daterange;
    $this->show_daterange=$show_daterange;
    $this->mailto=$mailto;
    $this->blocked_days=$blocked_days;

    $this->db = new cDb;
    $this->client = $client;
    $this->lang = $lang;
    $this->idart = $idart;

    $this->config = $this->config();
  }

  private function config(){
    $sql = "SELECT * FROM :table WHERE idart = :idart";
    $values = array(
        'table' => $this->_vars['db']['config'],
        'idart' => $this->idart);
    $result = $this->db->query($sql, $values);
    if ($result->num_rows>0) {
      $config = array();
      while ($this->db->nextRecord()) {
        $rs = $this->db->toObject();
        $config[DateTimeUtil::getIndexFromDate($rs->date)][$rs->id]=array(
            'time' => DateTimeUtil::getReadableTimeFromDate($rs->time),
            'slots' => $rs->slots,
            'comment' => $rs->comment);
      }
      return $config;
    }
    return NULL;
  }

  public function hasConfig(){
    return !is_null($this->config);
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
      $sql = "INSERT INTO :table (idart, date, time, slots, comment) VALUES (:idart, ':date', ':time', :slots, ':comment')";
      $values = array(
          'table' => $this->_vars['db']['config'],
          'idart' => $this->idart,
          'date' => DateTimeUtil::getInsertDate($date),
          'time' => DateTimeUtil::getInsertDateTime($date, $config['time']),
          'slots' => $config['slots'],
          'comment' => $config['comment']
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
      $sql= "UPDATE :table SET idart = :idart, date = ':date', time = ':time', slots = :slots, comment = ':comment' WHERE id = :uid";
      $values = array(
          'table' => $this->_vars['db']['config'],
          'uid' => $id,
          'idart' => $this->idart,
          'date' => DateTimeUtil::getInsertDate($date),
          'time' => DateTimeUtil::getInsertDateTime($date, $config['time']),
          'slots' => $config['slots'],
          'comment' => $config['comment']
      );
      $this->db->query($sql, $values);
    }
  }

  public function daterange(){
    return $this->daterange;
  }

  public function render(){
    $timerange = DateTimeUtil::getTimerange($this->timerange_from, $this->timerange_to, $this->interval);
    $daterange = DateTimeUtil::getDaterange($this->daterange,$this->blocked_days);
    $data = $this->load($this->daterange);

    echo '<table class="table">';
    echo '<thead><tr>';
    echo '<th>Datum</th>';
    foreach ($timerange as $time) {
      $to = DateTimeUtil::getToWithInterval($time[0],$this->interval);
      echo '<th>'.$time[1].'<span class="separator">-</span>'.$to.'</th>';
    }
    echo '</tr></thead>';
    echo '<tbody>';
    foreach ($daterange as $date) {
      $class='res_hide';
      if (DateTimeUtil::isEvenWeek($date[0])){
        $class.=' even-dat';
      }
      // todo was wenn Mo = blocked??
      if (DateTimeUtil::isMonday($date[0])){
        $class.=' kw-dat';
      }
      if (!DateTimeUtil::isInShowRange($this->daterange,$this->show_daterange,$date[0])){
        $class.=' not-in-range hide';
      }
      else {
        $class.=' in-range';
      }
      echo '<tr class="'.$class.'"">';
      echo '<th scope="row"><nobr class="cntnd_booking-date" data-date="'.$date[0].'">'.$date[1].'</nobr></th>';
      foreach ($timerange as $time) {
        $disabled='';
        $timestamp = strtotime($date[0].' '.$time[1]);
        if (array_key_exists($timestamp,$data)){
          $status = $data[$timestamp]['status'];
          $until = strtotime($date[0].' '.$data[$timestamp]['time_bis']);
        }
        else if (empty($until) || $timestamp>$until){
          $status = 'free';
          unset($until);
        }
        if ($status!='free'){
          $disabled='disabled="disabled"';
        }
        echo '<td class="'.$status.'">';
        echo '<label for="'.$timestamp.'" class="res_checkbox">';
        echo '<input id="'.$timestamp.'" class="cntnd_booking-checkbox" name="dates[]" type="checkbox" value="'.$timestamp.'" '.$disabled.' />';
        echo '</label>';
        echo '</td>';
      }
      echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
  }

  public static function validate($post,$interval){
    if (is_array($post)){
      return (self::validateDates($post,$interval) && self::validateRequired($post));
    }
    return false;
  }

  private static function validateDates($post,$interval){
    $valid=false;
    if (array_key_exists('dates',$post) && is_array($post['dates'])){
      $valid=true;
      $dates = $post['dates'];
      $interval_ms = $interval*60; // interval is in sec and timestamp is in ms
      sort($dates);
      foreach ($dates as $date) {
        if (!empty($old)){
          $diff = $date-$old;
          if ($diff>$interval_ms){
            $valid=false;
          }
          if (date('d.m.Y',$old)!=date('d.m.Y',$date)){
            $valid=false;
          }
        }
        $old=$date;
      }
    }
    return $valid;
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
    $dates = DateTimeUtil::getInsertDates($post['dates']);
    $sql = "INSERT INTO cntnd_booking (idclient, idlang, name, adresse, plz_ort, email, telefon, personen, bemerkungen, status, datum, time_von, time_bis) VALUES (:idclient, :idlang, ':name', ':adresse', ':plz_ort', ':email', ':telefon', :personen, ':bemerkungen', ':status', ':datum', ':time_von', ':time_bis')";
    $values = array(
        'idclient' => cSecurity::toInteger($this->client),
        'idlang' => cSecurity::toInteger($this->lang),
        'name'=> $this->db->escape($post['name']),
        'adresse'=> $this->db->escape($post['adresse']),
        'plz_ort'=> $this->db->escape($post['plz_ort']),
        'email'=> $this->db->escape($post['email']),
        'telefon'=> $this->db->escape($post['telefon']),
        'personen'=> cSecurity::toInteger($post['personen']),
        'bemerkungen'=> $this->db->escape($post['bemerkungen']),
        'status'=> 'blocked',
        'datum'=> $dates['datum'],
        'time_von'=> $dates['time_von'],
        'time_bis'=> $dates['time_bis']
    );
    if ($this->db->query($sql, $values)){
      $this->informationEmail($post,$dates);
      return true;
    }
    return false;
  }

  private function informationEmail($post,$dates){
    $mailer = new cMailer();
    $smarty = cSmartyFrontend::getInstance();
    // use template to display email
    $time_bis = new DateTime($dates['dat_email'].' '.$dates['time_bis']);
    $time_bis->modify('+'.$this->interval.' minutes');

    $smarty->assign('dat_email', $dates['dat_email']);
    $smarty->assign('name', $post['name']);
    $smarty->assign('adresse', $post['adresse']);
    $smarty->assign('plz_ort', $post['plz_ort']);
    $smarty->assign('telefon', $post['telefon']);
    $smarty->assign('bemerkungen', $post['bemerkungen']);
    $smarty->assign('email', $post['email']);
    $smarty->assign('personen', $post['personen']);
    $smarty->assign('time_von', $dates['time_von']);
    $smarty->assign('time_bis', $time_bis->format('H:i'));
    $body = $smarty->fetch('reservation-mail.html');
    // Create a message
    // todo betreff etc
    $mail = Swift_Message::newInstance('Ihre Reservation')
    ->setFrom($mailto)
    ->setTo($post['email'])
    ->setBody($body, 'text/html');

    // Send the message
    $result = $mailer->send($mail);
    return $result;
  }

  public function load($daterange){
    $dates = DateTimeUtil::getDatesFromDaterange($daterange);
    $sql = "SELECT * FROM cntnd_booking WHERE datum between ':datum_von' AND ':datum_bis' ORDER BY datum, time_von";
    $values = array(
      'datum_von' => $dates[0]->format('Y-m-d'),
      'datum_bis' => $dates[1]->format('Y-m-d')
    );
    $this->db->query($sql, $values);
    $data=[];
    while ($this->db->next_record()) {
      $timestamp = strtotime($this->db->f('datum').' '.$this->db->f('time_von'));
      $data[$timestamp]=array('time_von'=>$this->db->f('time_von'),'time_bis'=>$this->db->f('time_bis'),'status'=>$this->db->f('status'));
    }
    return $data;
  }

  public function loadById($id){
    $sql = "SELECT * FROM cntnd_booking WHERE id = :id";
    $values = array('id' => $id);
    $this->db->query($sql, $values);
    return $this->db->getResultObject();
  }

  public function listAll(){
    $sql = "SELECT * FROM cntnd_booking WHERE datum >= ':datum' ORDER BY datum, time_von";
    $values = array('datum' => date('Y-m-d'));
    $this->db->query($sql, $values);
    $data=[];
    while ($this->db->next_record()) {
      $data_detail = array(
        'id'=>$this->db->f('id'),
        'name'=>$this->db->f('name'),
        'adresse'=>$this->db->f('adresse'),
        'status'=>$this->db->f('status'),
        'plz_ort'=>$this->db->f('plz_ort'),
        'email'=>$this->db->f('email'),
        'telefon'=>$this->db->f('telefon'),
        'personen'=>$this->db->f('personen'),
        'bemerkungen'=>$this->db->f('bemerkungen'),
        'time_von'=>$this->db->f('time_von'),
        'time_bis'=>$this->db->f('time_bis')
      );
      $data[date('d.m.Y',strtotime($this->db->f('datum')))][]=$data_detail;
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
      $sql = "DELETE FROM cntnd_booking WHERE id = :id";
      $values = array('id' => $post['resid']);
      $this->rejectionEmail($post);
    }
    else {
      $sql = "UPDATE cntnd_booking SET status = ':status', mut_dat = NOW() WHERE id = :id";
      $values = array(
        'status' => 'reserved',
        'id' => $post['resid']);
      $this->confirmationEmail($post);
    }
    return $this->db->query($sql, $values);
  }

  private function confirmationEmail($post){
    $mailer = new cMailer();
    $smarty = cSmartyFrontend::getInstance();
    // use template to display email
    $record = $this->loadById($post['resid']);
    $smarty->assign('datum', DateTimeUtil::getReadableDate($record->datum));
    $smarty->assign('time_von', DateTimeUtil::getReadableTimeFromDate($record->time_von));
    $smarty->assign('time_bis', DateTimeUtil::getReadableTimeFromDate($record->time_bis));
    $smarty->assign('bemerkungen', $record->bemerkungen);
    $smarty->assign('message', $post['bemerkungen']);
    $body = $smarty->fetch('reservation-definitiv-mail.html');
    // Create a message
    $mail = Swift_Message::newInstance('Ihre Reservationsbestätigung')
    ->setFrom($mailto)
    ->setTo($post['email'])
    ->setBody($body, 'text/html');

    // Send the message
    $result = $mailer->send($mail);
    return $result;
  }

  private function rejectionEmail($post){
    $mailer = new cMailer();
    $smarty = cSmartyFrontend::getInstance();
    // use template to display email
    $record = $this->loadById($post['resid']);
    $smarty->assign('datum', DateTimeUtil::getReadableDate($record->datum));
    $smarty->assign('time_von', DateTimeUtil::getReadableTimeFromDate($record->time_von));
    $smarty->assign('time_bis', DateTimeUtil::getReadableTimeFromDate($record->time_bis));
    $smarty->assign('bemerkungen', $record->bemerkungen);
    $smarty->assign('message', $post['bemerkungen']);
    $body = $smarty->fetch('reservation-abgelehnt-mail.html');
    // Create a message
    $mail = Swift_Message::newInstance('Ablehnung ihrer Reservation')
    ->setFrom($mailto)
    ->setTo($post['email'])
    ->setBody($body, 'text/html');

    // Send the message
    $result = $mailer->send($mail);
    return $result;
  }
}
?>
