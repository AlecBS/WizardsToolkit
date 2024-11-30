-- Run below scripts so all files in /demo/ folder work (only a few require below tables and data)
SET foreign_key_checks = 0;

CREATE TABLE `pets` (
  `UID` int UNSIGNED NOT NULL auto_increment,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `UserUID` int UNSIGNED NOT NULL COMMENT 'Owner',
  `PetName` varchar(40),
  `Gender` enum('M','F','U') default NULL,
  `PetType` varchar(4) DEFAULT NULL,
  `City` varchar(40),
  `State` varchar(2),
  `Zipcode` varchar(10),
  `OwnerPhone` varchar(20),
  `OwnerEmail` varchar(60),
  `CanTreat` enum('N','Y') default 'N',
  `BirthDate` date,
  `NextTime` char(8),
  `FilePath` varchar(30) NULL,
  `NewFileName` varchar(12) NULL,
  `Latitude` DECIMAL(20,14) NULL,
  `Longitude` DECIMAL(20,14) NULL,
  `Note` text NULL,
  PRIMARY KEY (`UID`),
  KEY `ix_pets_UserUID` (`UserUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `petNotes` (
  `UID` int UNSIGNED NOT NULL auto_increment,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `PetUID` int UNSIGNED NOT NULL,
  `UserUID` int UNSIGNED NOT NULL COMMENT 'who added note',
  `PetNote` varchar(120),
  PRIMARY KEY (`UID`),
  FOREIGN KEY (`PetUID`) REFERENCES pets(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

INSERT INTO `wtkLookups` (`LookupType`,`LookupValue`,`LookupDisplay`)
   VALUES ('PetType','D','Dog'),
           ('PetType','C','Cat'),
           ('PetType','R','Rabbit');

INSERT INTO `pets` (`DelDate`, `UserUID`, `PetName`, `Gender`, `PetType`, `City`, `State`, `Zipcode`, `OwnerPhone`, `OwnerEmail`, `CanTreat`, `BirthDate`, `NextTime`, `FilePath`, `NewFileName`, `Latitude`, `Longitude`, `Note`)
   VALUES
   	(NULL, 1, 'Dogbert', 'M', 'D', 'Ceres', 'CA', NULL, '(209) 555-1212', 'dude@email.com', 'Y', '2019-04-09', '04:20 PM', NULL, NULL, NULL, NULL, NULL),
   	('2022-05-27 09:16:33', 1, 'Coati deleted', 'M', 'C', NULL, 'AK', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'female'),
   	(NULL, 1, 'Teva Bunner', 'M', 'R', NULL, 'AK', NULL, NULL, NULL, 'Y', NULL, '05:15 PM', NULL, NULL, NULL, NULL, 'edit'),
   	(NULL, 1, 'Carrot Muncher', 'U', 'R', 'San Jose', 'CA', NULL, '(408) 555-6400', 'coati@email.com', 'Y', NULL, NULL, NULL, NULL, 37.32096470000000, -121.86118270000000, NULL),
   	(NULL, 1, 'Puppers', 'M', 'D', NULL, 'MT', NULL, NULL, NULL, 'N', '2016-05-04', NULL, NULL, NULL, NULL, NULL, 'edit text'),
   	(NULL, 1, 'Cwoat the Coati', 'F', 'C', NULL, 'AK', NULL, NULL, NULL, 'Y', '2022-05-03', '03:25 AM', NULL, NULL, NULL, NULL, 'added photo');

INSERT INTO `petNotes` (`PetUID`, `UserUID`, `PetNote`)
  VALUES
	(1, 1, 'likes meat mucho'),
	(1, 1, 'Loves peanut Butter!'),
	(3, 1, 'likes stuff a lot'),
	(1, 1, 'Cool!'),
	(4, 1, 'Very cute bunny'),
	(4, 1, 'And super fuzzy!'),
	(2, 1, 'name changed again');

-- Below is example of a SQL calling:
--   ajaxGo, wtkModal, outside URL, and rpt()

SELECT r.`UID`, DATE_FORMAT(r.`AddDate`,'%c/%e/%Y at %l:%i %p') AS `AddDate`,
    CONCAT('<a onclick="JavaScript:ajaxGo(\'/wtk/userEdit\',',r.`UserUID`,')">',
       COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,''),'</a><br>',u.`Email`) AS `Buyer`,

    CONCAT('<a class="btn-floating" onclick="JavaScript:rpt(22,',r.`UserUID`,')">',
           '<i class="material-icons">format_list_numbered</i></a>',

           '<a onclick="JavaScript:ajaxGo(\'/admin/userLogins\',0,'
               ,r.`UserUID`,');" class="btn btn-floating btn-small">',
          '<i class="material-icons" alt="Click to User Logins" title="Click to User Logins">beenhere</i></a>'
       ) AS `Reports`,

    CONCAT('<a onclick="JavaScript:wtkModal(\'/admin/ecomEdit\',\'MODAL\',',r.`EcomUID`,')">',
       e.`PaymentProvider`,'</a>') AS `PaymentProvider`,
    r.`PaymentStatus`,

    IF (r.`CurrencyCode` = 'USD', '',
        CONCAT('<a target="_blank" href="https://www.xe.com/currencyconverter/convert/?Amount=',
            r.`GrossAmount`,'&From=',r.`CurrencyCode`,'&To=USD">',r.`GrossAmount`,'</a>')
    ) AS `GrossAmount`,

    r.`MerchantFee`, r.`CurrencyCode`
FROM `wtkRevenueDemo` r
  INNER JOIN `wtkEcommerce` e ON e.`UID` = r.`EcomUID`
  INNER JOIN `wtkUsers` u ON u.`UID` = r.`UserUID`
WHERE r.`UserUID` IN (1,3,7)
ORDER BY r.`UID` DESC
