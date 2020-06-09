<?php
cInclude('module', 'includes/class.cntnd_util.php');
cInclude('module', 'includes/class.datetime.php');

/**
 * cntnd_calendar_booking Class
 */
class CntndCalendarBooking {

  private $mailto;

  private $db;
  private $client;
  private $lang;

  function __construct($mailto, $lang, $client) {
    $this->mailto=$mailto;

    $this->db = new cDb;
    $this->client = $client;
    $this->lang = $lang;
  }

  public function load($daterange){
    $start = DateTimeUtil::getDateFromRange($daterange);
    $sql = "SELECT * FROM cntnd_calendar_booking WHERE start_date >= ':datum_von' ORDER BY start_date, end_date";
    $values = array(
      'datum_von' => $start->format('Y-m-d')
    );
    $this->db->query($sql, $values);
    $data=[];
    while ($this->db->next_record()) {
      $data = $this->generateDates($data, $this->db->f("start_date"), $this->db->f("end_date"), $this->db->f("uname"));
    }
    return $data;
  }

  private function generateDates($data, $startDate, $endDate, $uname){
    $date = new DateTime($startDate);
    $start = DateTimeUtil::dateToInt($startDate);
    $end = DateTimeUtil::dateToInt($endDate);
    while($start<=$end){
      $isStartDate = ($start==DateTimeUtil::dateToInt($startDate));
      $isEndDate = ($start==$end);
      $description = $uname;

      if (array_key_exists($date->format('Y-m-d'), $data)){
        $isEndDate = $data[$date->format('Y-m-d')]["endDate"];
        $description = $data[$date->format('Y-m-d')]["description"]." & ".$uname;
      }

      $data[$date->format('Y-m-d')] = array(
          "date" =>  $date->format('Y-m-d'),
          "startDate" =>  $isStartDate,
          "endDate" =>  $isEndDate,
          "isBooking" =>  true,
          "description" =>  $description
      );

      $date->modify('+1 day');
      $start = DateTimeUtil::dateToInt($date->format('Y-m-d'));
    }

    return $data;
  }

  public function store($post){
    /*
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
    */
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
    ->setFrom($this->mailto)
    ->setTo($post['email'])
    ->setBody($body, 'text/html');

    // Send the message
    $result = $mailer->send($mail);
    return $result;
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
    /*
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
    */
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
    ->setFrom($this->mailto)
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
    ->setFrom($this->mailto)
    ->setTo($post['email'])
    ->setBody($body, 'text/html');

    // Send the message
    $result = $mailer->send($mail);
    return $result;
  }
}
?>
