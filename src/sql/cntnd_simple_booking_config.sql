CREATE TABLE `cntnd_simple_booking_config` (
  `id` int(11) NOT NULL,
  `idart` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` datetime NOT NULL,
  `day` int(1) NOT NULL,
  `slots` int(10) NOT NULL,
  `comment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cntnd_simple_booking_config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idart` (`idart`);

ALTER TABLE `cntnd_simple_booking_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;
