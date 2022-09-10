CREATE TABLE `cntnd_booking` (
   `id` int(11) NOT NULL,
   `name` varchar(100) NOT NULL,
   `adresse` varchar(100) DEFAULT NULL,
   `plz_ort` varchar(100) DEFAULT NULL,
   `email` varchar(100) NOT NULL,
   `telefon` varchar(20) DEFAULT NULL,
   `personen` varchar(50) NOT NULL,
   `bemerkungen` varchar(1000) DEFAULT NULL,
   `status` varchar(10) NOT NULL,
   `datum` date NOT NULL,
   `datetime_von` datetime NOT NULL,
   `datetime_bis` datetime NOT NULL,
   `time_von` varchar(5) NOT NULL,
   `time_bis` varchar(5) NOT NULL,
   `mut_dat` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `cntnd_booking`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

COMMIT;
