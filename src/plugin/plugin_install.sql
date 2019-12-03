CREATE TABLE IF NOT EXISTS cntnd_reservation (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  idclient int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id of form client',
  idlang int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id of form language',
  name varchar(255) NOT NULL,
  adresse varchar(255) NOT NULL,
  plz_ort varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  telefon varchar(255) NOT NULL,
  personen int(11) NOT NULL,
  bemerkungen varchar(255),
  status varchar(20) NOT NULL,
  datum date NOT NULL,
  time_von time NOT NULL,
  time_bis time NOT NULL,
  mut_dat datetime NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARSET=utf8;
