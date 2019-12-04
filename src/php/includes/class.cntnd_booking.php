<?php
cInclude('module', 'includes/class.datetime.php');
/**
 * cntnd_booking Class
 */
class CntndBooking {

  private $daterange;
  private $show_daterange;
  private $interval;
  private $timerange_from;
  private $timerange_to;
  private $mailto;
  private $blocked_days;

  private $db;
  private $client;
  private $lang;

  function __construct($daterange, $show_daterange, $interval, $timerange_from, $timerange_to, $mailto, $blocked_days, $lang, $client) {
    $this->daterange=$daterange;
    $this->show_daterange=$show_daterange;
    $this->interval=$interval;
    $this->timerange_from=$timerange_from;
    $this->timerange_to=$timerange_to;
    $this->mailto=$mailto;
    $this->blocked_days=$blocked_days;

    $this->db = new cDb;
    $this->client = $client;
    $this->lang = $lang;
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
      $this->confirmationEmail($post,$dates);
      return true;
    }
    return false;
  }

  private function confirmationEmail($post,$dates){
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

  public function admin(){
    /*
    // ADMIN MODUS
    $sql = "SELECT * FROM cntnd_reservation WHERE datum >= '".date("Y-m-d")."' ORDER BY datum, time_von";
    */
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
}
?>
