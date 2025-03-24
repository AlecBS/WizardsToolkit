/*
Create demo Revenue data for last 3 years and 35 days.
This was used by Wizard's Toolkit demo (https://wizardstoolkit.com/wtk.php)
*/

-- wtkRevenueDemo is identical to wtkRevenue
CREATE TABLE `wtkRevenueDemo` (
  `UID`         int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate`     timestamp NOT NULL default CURRENT_TIMESTAMP,
  `UserUID`     int UNSIGNED,
  `OrderUID`    int UNSIGNED,
  `EcomUID`     int UNSIGNED,
  `EcomTxnType` varchar(60),
  `EcomPayId`   varchar(60),
  `RevType`     varchar(4),
  `IPaddress`   varchar(15),
  `PayerEmail`  varchar(60),
  `PayerId`     varchar(60),
  `FirstName`   varchar(60),
  `LastName`    varchar(60),
  `ItemName`    varchar(120),
  `ItemNumber`  varchar(60),
  `PaymentStatus` varchar(40),
  `GrossAmount`  decimal(7,2),
  `MerchantFee`  decimal(7,2),
  `CurrencyCode` char(3),
  `DevNote`      varchar(50),
  PRIMARY KEY (`UID`),
  FOREIGN KEY (`EcomUID`) REFERENCES wtkEcommerce(`UID`),
  FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`),
  INDEX `ix_RevenueDemoAddDate` (`AddDate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


DROP PROCEDURE IF EXISTS `GenerateRevenueDemo`;

DELIMITER $$
CREATE PROCEDURE `GenerateRevenueDemo`()
BEGIN

  -- Create a temporary table to properly set AddDate values spread over 3 years
  -- go back a total of just over 3 years
  CREATE TEMPORARY TABLE `demo_revenue` AS
    SELECT DATE_SUB(NOW(), INTERVAL FLOOR((RAND() * 400) + 730) DAY) AS `AddDate`
      FROM `wtkLookups`
      ORDER BY `UID` ASC LIMIT 250; -- for first year

  -- second year
  INSERT INTO `demo_revenue` (`AddDate`)
    SELECT DATE_SUB(NOW(), INTERVAL FLOOR((RAND() * 365) + 365) DAY) AS `AddDate`
      FROM `wtkLookups`
      ORDER BY `UID` ASC LIMIT 340; -- for second year

  -- third (current) year
  INSERT INTO `demo_revenue` (`AddDate`)
    SELECT DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 365) DAY) AS `AddDate`
      FROM `wtkLookups`
      ORDER BY `UID` ASC; -- for third year

  INSERT INTO `wtkRevenueDemo` (`AddDate`,`UserUID`,`EcomUID`,
      `PayerEmail`,`FirstName`,`LastName`,
      `ItemName`,`PaymentStatus`,`GrossAmount`,`CurrencyCode`)
   SELECT `AddDate`,
       (SELECT `UID` FROM `wtkUsers` ORDER BY RAND() LIMIT 1) AS `UserUID`,
       CASE FLOOR(RAND() * 10)
         WHEN 0 THEN 2
         WHEN 1 THEN 2
         WHEN 2 THEN 2
         WHEN 3 THEN 3
         ELSE 1
       END AS `EcomUID`,
       'demo@paypal.com' AS `PayerEmail`,
       `generate_fname`() AS `FirstName`,
       `generate_lname`() AS `LastName`,
        'Great Service' AS `ItemName`,
      CASE FLOOR(RAND() * 45)
        WHEN 0 THEN 'Declined'
        WHEN 1 THEN 'Pending'
        WHEN 2 THEN 'Refund'
        WHEN 3 THEN 'Requested'
        WHEN 4 THEN 'Requested'
        ELSE 'Authorized'
      END AS `PaymentStatus`,
      (FLOOR(RAND() * 601)+ 50) AS `GrossAmount`,
      CASE FLOOR(RAND() * 40)
        WHEN 0 THEN 'GBP'
        WHEN 1 THEN 'CAD'
        WHEN 2 THEN 'CAD'
        WHEN 2 THEN 'EUR'
        WHEN 3 THEN 'EUR'
        WHEN 4 THEN 'EUR'
        ELSE 'USD'
      END AS `CurrencyCode`
    FROM `demo_revenue`
   ORDER BY `AddDate` ASC;

   UPDATE `wtkRevenueDemo`
     SET `MerchantFee` = (`GrossAmount` * .03)
   WHERE `UID` > 0;

   DROP TEMPORARY TABLE IF EXISTS `demo_revenue`;

END $$

DELIMITER ;

-- TRUNCATE TABLE `wtkRevenueDemo`;
CALL `GenerateRevenueDemo`();

-- SELECT * FROM `wtkRevenueDemo` ORDER BY `UID` ASC;

-- To manually adjust AddDate
-- UPDATE `wtkRevenue` SET `AddDate` = DATE_ADD(`AddDate`, INTERVAL 36 DAY);

-- Fill wtkIncomeByMonth data for demo purposes
-- this requires `wtkRevenueDemo` to have previously been filled with test data via call `GenerateRevenueDemo`() above

INSERT INTO `wtkIncomeByMonth`
   (`YearTracked`,`Quarter`,`MonthInYear`,`GrossIncome`,`Refunds`)
  SELECT DATE_FORMAT(`AddDate`,'%Y') AS `Year`,
    QUARTER(DATE_FORMAT(`AddDate`,'%Y-%m-%d')) AS `Quarter`,
    DATE_FORMAT(`AddDate`,'%m') AS `Month`,
    SUM(IF (`PaymentStatus` = 'Authorized', `GrossAmount`,0)) AS `GrossIncome`,
    SUM(IF (`PaymentStatus` = 'Refund', `GrossAmount`,0)) AS `Refunds`
FROM `wtkRevenueDemo`
WHERE `PaymentStatus` IN ('Authorized','Refund')
GROUP BY DATE_FORMAT(`AddDate`,'%Y-%m')
ORDER BY DATE_FORMAT(`AddDate`,'%Y-%m') ASC;
