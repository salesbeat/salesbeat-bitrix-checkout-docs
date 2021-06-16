CREATE TABLE IF NOT EXISTS `sb_delivery_order` (
  `ID` int(11) not null auto_increment,
  `ORDER_ID` varchar(255) not null,
  `SHIPMENT_ID` varchar(255) not null,
  `SB_ORDER_ID` varchar(255) not null,
  `TRACK_CODE` varchar(255) not null,
  `DATE_ORDER` DATETIME,
  `SENT_COURIER` varchar(1) not null,
  `DATE_COURIER` DATETIME,
  `TRACKING_STATUS` varchar(255) not null,
  `DATE_TRACKING` DATETIME,
  PRIMARY KEY(`id`)
)
ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;