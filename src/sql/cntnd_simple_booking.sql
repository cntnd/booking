CREATE TABLE `cntnd_simple_booking` (
                                          `id` int(11) NOT NULL,
                                          `uname` varchar(100) NOT NULL,
                                          `status` varchar(10) NOT NULL,
                                          `start_date` date NOT NULL,
                                          `end_date` date NOT NULL,
                                          `mut_dat` datetime NOT NULL,
                                          PRIMARY KEY (id)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;