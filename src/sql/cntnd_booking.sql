CREATE TABLE `cntnd_booking` (
    `id` int(11) NOT NULL,
    `idart` int(11) NOT NULL,
    `date` date NOT NULL,
    `time` datetime NOT NULL,
    `amount` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `address` varchar(255) DEFAULT NULL,
    `po_box` varchar(255) DEFAULT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(255) DEFAULT NULL,
    `comment` text DEFAULT NULL,
    `create_date` datetime NOT NULL DEFAULT current_timestamp(),
    `mut_date` datetime DEFAULT NULL,
    `status` varchar(10) NOT NULL DEFAULT 'blocked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cntnd_booking`
    ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `idart` (`idart`);

ALTER TABLE `cntnd_booking`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

COMMIT;
