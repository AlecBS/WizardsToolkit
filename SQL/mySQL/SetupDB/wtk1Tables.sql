/*
Note in your my.cnf you may want to add these settings:
[mysqld]
skip-log-bin

If you receive errors like this:
which is not functionally dependent on columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by

You can fix that by following these steps:

SELECT @@sql_mode;

Then copy the result and remove the ONLY_FULL_GROUP_BY

Then reset the sql_mode variable by pasting in the new values.  Something like this:

SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';

# https://stackoverflow.com/questions/41887460/select-list-is-not-in-group-by-clause-and-contains-nonaggregated-column-inc

If you are installing Wizard's Toolkit data files into your own database or want a different DB name, just change
USE wiztools;
... to whatever DB name you want.
*/
-- USE wiztools;

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `wtkUsers` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `Title` varchar(40),
  `FirstName` varchar(30),
  `LastName` varchar(35),
  `Address` varchar(45),
  `Address2` varchar(30),
  `City` varchar(30),
  `State` varchar(2),
  `Zipcode` varchar(10),
  `CountryCode` char(2),
  `Phone` varchar(20),
  `CellPhone` varchar(20),
  `PersonalURL` varchar(120),
  `LangPref` char(3),
  `UseSkype` enum('Y','N') default 'N',
  `SMSEnabled` enum('Y','N') default 'N',
  `OptInEmails` enum('Y','N') default 'Y',
  `Email` varchar(80),
  `AltEmail` varchar(80),
  `LoginCode` varchar(40),
  `WebPassword` varchar(255),
  `LoginTimeout` smallint DEFAULT 60,
  `SecurityLevel` smallint DEFAULT 1,
  `StaffRole` varchar(4),
  `MenuSet` varchar(20),
  `CanPrint` enum('Y','N') default 'N',
  `CanExport` enum('Y','N') default 'N',
  `CanEditHelp` enum('Y','N') default 'N',
  `CanUnlock` enum('Y','N') default 'N',
  `SSN` varchar(11),
  `IPAddress` varchar(15),
  `EmailOKDate` datetime,
  `SignedDate` datetime,
  `PromoCode` varchar(24),
  `ExpiresDate` date,
  `FilePath` varchar(30) NULL,
  `NewFileName` varchar(12) NULL,
  `NewPassHash` varchar(140),
  PRIMARY KEY (`UID`),
  KEY `ix_wtkUsers_Email` (`Email`),
  KEY `ix_wtkUsers_LastNameFirstName` (`LastName`, `FirstName`),
  KEY `ix_wtkUsers_FirstNameLastName` (`FirstName`, `LastName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkAds` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `LastModByUserUID` int UNSIGNED,
  `AdName` varchar(60),
  `AdVendor` varchar(60),
  `AdType` varchar(1),
  `AdText` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `AdNote` text,
  `VisitCounter` int UNSIGNED DEFAULT 0,
  `LastVisitDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkAds_LastModByUserUID`
    FOREIGN KEY (`LastModByUserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkAffiliates` (
  `UID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DelDate` datetime DEFAULT NULL,
  `SignedDate` datetime,
  `CompanyName` varchar(255),
  `ContactName` varchar(120),
  `CountryCode` char(2),
  `TimeZone`    varchar(40),
  `WebPasscode` varchar(80),
  `LinkToURL` VARCHAR(120),
  `AffiliateHash` varchar(60),
  `Email` varchar(100),
  `MainPhone` varchar(40),
  `Website` varchar(255),
  `DiscountPercentage` tinyint COMMENT 'link gives discount on sale items',
  `AffiliateRate` decimal(5,2) COMMENT 'commission',
  `PaymentInstructions` text,
  `InternalNote` varchar(250),
  PRIMARY KEY (`UID`),
  UNIQUE KEY `ix_wtkAffiliates_AffiliateHash` (`AffiliateHash`),
  KEY `ix_wtkAffiliates_CompanyName` (`CompanyName`),
  KEY `ix_wtkAffiliates_ContactName` (`ContactName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkBackgroundActions` (
  `UID`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate`   timestamp NOT NULL default CURRENT_TIMESTAMP,
  `TriggerDate`   DATETIME NOT NULL,
  `DoneDate`   DATETIME NULL,
  `ActionType` varchar(8) NOT NULL,
  `ToUserUID` int UNSIGNED,
  `OtherUID`  int UNSIGNED,
  `DevNote`   VARCHAR(120) NULL DEFAULT NULL,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkBackgroundActions_ToUserUID`
    FOREIGN KEY (`ToUserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkBroadcast` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `AddedByUserUID` int UNSIGNED default NULL,
  `DelDate` datetime default NULL,
  `DeletedByUserUID` int UNSIGNED default NULL,
  `AudienceType` varchar(4) default NULL,
  `AudienceSubType` varchar(4) default NULL,
  `BroadcastColor` varchar(20) default NULL,
  `TextColor` varchar(20) default NULL,
  `MessageHeader` varchar(120) default NULL,
  `MessageType` varchar(10) default NULL,
  `MessageNote` text,
  `ShowOnDate` date default NULL,
  `ShowUntilDate` date default NULL,
  `AllowClose` enum('Y','N') default 'Y',
  `CloseMessage` varchar(20) default 'Close',
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkBroadcast_DeletedByUserUID`
    FOREIGN KEY (`DeletedByUserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_wtkBroadcast_AddedByUserUID` (`AddedByUserUID`),
  KEY `ix_wtkBroadcast_Dates` (`ShowOnDate`,`ShowUntilDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkBroadcast_wtkUsers` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `BroadcastUID` int UNSIGNED default NULL,
  `UserUID` int UNSIGNED default NULL,
  `IpAddress` varchar(16) default NULL,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkBroadcast_wtkUsers_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_wtkBroadcast_wtkUsers` (`UserUID`,`BroadcastUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkBugReport` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatedByUserUID` int UNSIGNED DEFAULT NULL,
  `IPaddress` varchar(15),
  `OpSystem` varchar(25),
  `Browser` varchar(20),
  `BrowserVer` varchar(12),
  `AppVersion` varchar(12),
  `DeviceType` enum('computer','tablet','phone') DEFAULT 'computer',
  `ReferralPage` varchar(120),
  `BugMsg` text,
  `InternalNote` varchar(120),
  `DevNote` varchar(255),
  `DevUserUID` int DEFAULT NULL,
  `DoneDate` datetime DEFAULT NULL,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkBugReport_CreatedByUserUID`
   FOREIGN KEY (`CreatedByUserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkChat`(
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `SendByUserUID` int UNSIGNED,
  `SendToUserUID` int UNSIGNED,
  `Message` varchar(1400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkChat_SendByUserUID`
    FOREIGN KEY (`SendByUserUID`) REFERENCES wtkUsers(`UID`),
  CONSTRAINT `fk_wtkChat_SendToUserUID`
    FOREIGN KEY (`SendToUserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_wtkChat_FromTo` (`SendByUserUID`,`SendToUserUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkClients` (
  `UID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ClientName` VARCHAR(30),
  `Address` VARCHAR(30),
  `Address2` VARCHAR(30),
  `City` VARCHAR(30),
  `State` CHAR(2),
  `Zipcode` VARCHAR(10),
  `CountryCode` char(2),
  `ClientPhone` VARCHAR(20),
  `ClientEmail` VARCHAR(60),
  `AccountEmail` VARCHAR(60),
  `StartDate` DATE DEFAULT NULL,
  `ClientStatus` CHAR(1) DEFAULT 'A' COMMENT 'Trial, Active, Inactive',
  `InternalNote` VARCHAR(250),
  PRIMARY KEY (`UID`),
  KEY `ix_wtkClients_ClientName` (`ClientName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkBlog` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `LastEditDate` datetime,
  `UserUID` int UNSIGNED NOT NULL COMMENT 'written by',
  `Slug` varchar(80),
  `PageTitle` varchar(120) COMMENT 'also used for Navigation name',
  `BlogContent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `MetaKeywords` text,
  `MetaDescription` text,
  `TwitterAcct` varchar(40),
  `OGTitle` varchar(70),
  `OGDescription` varchar(200),
  `OGFilePath` varchar(30),
  `OGImage` varchar(12),
  `MakePublic` enum('N','Y') NOT NULL DEFAULT 'N',
  `PublishDate`  datetime,
  `Views`   int UNSIGNED NOT NULL DEFAULT 0,
  `LastViewDate` datetime,
  PRIMARY KEY (`UID`),
  KEY `ix_wtkBlog_Slug` (`Slug`),
  CONSTRAINT `fk_wtkBlog_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkCompanySettings` (
  `UID`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate`   timestamp NOT NULL default CURRENT_TIMESTAMP,
  `LastModByUserUID` int UNSIGNED default NULL,
  `CoName`  varchar(120),
  `CoLogo`  varchar(40),
  `Address` varchar(40),
  `City` varchar(40),
  `State` varchar(2),
  `Zipcode` varchar(10),
  `CountryCode` char(2),
  `Phone` varchar(20),
  `Email` varchar(60),
  `MiscContactInfo` text,
  `Ecommerce` varchar(5),
  `PayPalEmail` varchar(60),
  `TaxRate` numeric(6,4),
  `ShowQty` varchar(1),
  `ShowStaffLogin` varchar(1),
  `DomainName` varchar(90),
  `AppVersion`  varchar(6),
  `EnableLockout`  char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkCompanySettings_LastModByUserUID`
    FOREIGN KEY (`LastModByUserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkDebug` (
  `UID`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate`   timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DevNote`   VARCHAR(600) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkDownloads` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `FileName` varchar(80),
  `FileDescription` text,
  `FileLocation` varchar(240),
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkDownloadTracking` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DownloadUID` int UNSIGNED,
  `IPaddress` varchar(15),
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkDownloadTracking_DownloadUID`
    FOREIGN KEY (`DownloadUID`) REFERENCES wtkDownloads(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkEcommerce` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `PaymentProvider` varchar(60),
  `EcomLogin` varchar(80),
  `EcomPassword` varchar(80),
  `EcomWebsite` varchar(120),
  `EcomPayLink` varchar(120),
  `EcomNote` varchar(240),
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkEmailTemplate` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `AutomationOnly` CHAR(1) NOT NULL DEFAULT 'N',
  `EmailType` varchar(8),
  `EmailCode` varchar(10),
  `Subject` varchar(600) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '600 in case of emojis',
  `EmailBody` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `InternalNote` text,
  PRIMARY KEY (`UID`),
  KEY `ix_wtkEmailTemplate_EmailCode` (`EmailCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkEmailsSent` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `EmailUID` int UNSIGNED,
  `EmailType` varchar(8),
  `HtmlTemplate` varchar(30),
  `OtherUID` int UNSIGNED,
  `SendByUserUID` int UNSIGNED,
  `SendToUserUID` int UNSIGNED,
  `EmailAddress` varchar(100),
  `InternalNote` varchar(250),
  `Subject` varchar(600) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '600 in case of emojis',
  `EmailBody` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `EmailMsgId` varchar(200) NULL DEFAULT NULL COMMENT 'used for PostmarkApp and AWS webhooks',
  `EmailDelivered` DATETIME NULL DEFAULT NULL,
  `EmailOpened` DATETIME NULL DEFAULT NULL,
  `EmailLinkClicked` DATETIME NULL DEFAULT NULL,
  `SpamComplaint` DATETIME NULL DEFAULT NULL,
  `Bounced` char(1),
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkEmailsSent_SendByUserUID`
    FOREIGN KEY (`SendByUserUID`) REFERENCES wtkUsers(`UID`),
  CONSTRAINT `fk_wtkEmailsSent_SendToUserUID`
    FOREIGN KEY (`SendToUserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_wtkEmailsSent_EmailType` (`EmailType`,`OtherUID`,`UID`),
  KEY `ix_wtkEmailsSent_EmailMsgId` (`EmailMsgId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkErrorLog` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `UserUID` int UNSIGNED,
  `FromPage` varchar(120),
  `ReferralPage` varchar(120),
  `ErrType` varchar(20),
  `ErrMsg` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ErrNotes` varchar(100),
  `DevID` varchar(3),
  `DelDate` datetime,
  `LineNum` smallint,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkErrorLog_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkFailedAttempts` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `FailCode` varchar(4) NOT NULL,
  `UserUID` int UNSIGNED,
  `IPaddress` varchar(15),
  `FailNote` varchar(250),
  `OpSystem` varchar(25),
  `Browser` varchar(20),
  `BrowserVer` varchar(12),
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkFailedAttempts_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_wtkFailedAttempts_FailCode` (`FailCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkFiles` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` DATETIME,
  `UserUID` int UNSIGNED COMMENT 'who uploaded',
  `TableRelation` varchar(30) NOT NULL,
  `ParentUID` int UNSIGNED COMMENT 'associated with TableRelation',
  `Description` varchar(120) NULL,
  `OrigFileName` varchar(110) NULL,
  `FilePath` varchar(30) NULL,
  `NewFileName` varchar(12) NULL,
  `FileExtension` varchar(20) NOT NULL,
  `FileSize` int,
  `CurrentLocation` char(1) DEFAULT 'L' COMMENT 'L for local, A for AWS S3, C for Cloudflare R2, X for deleted',
  `ExternalStorage` char(1) DEFAULT 'N' COMMENT 'N for Local only; Y for AWS, Cloudflare, etc.',
  `TempDownload` ENUM('N','Y') DEFAULT 'N' COMMENT 'Y for private bucket requiring internal download to view',
  `Redacted` CHAR(1) DEFAULT 'N',
  PRIMARY KEY (`UID`),
  KEY `ix_wtkFiles_TableRelation` (`TableRelation`,`ParentUID`),
  KEY `ix_wtkFiles_ExternalStorage` (`ExternalStorage`),
  CONSTRAINT `fk_wtkFiles_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkForum` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `LastEditDate` datetime,
  `DelDate` datetime,
  `CreatedByUserUID` int UNSIGNED,
  `ForumName` varchar(24) NOT NULL,
  `ForumNote` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkForum_CreatedByUserUID`
    FOREIGN KEY (`CreatedByUserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkForumMsgs` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ForumUID` int UNSIGNED,
  `UserUID` int UNSIGNED,
  `ForumMsg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkForumMsgs_ForumUID`
    FOREIGN KEY (`ForumUID`) REFERENCES wtkForum(`UID`),
  CONSTRAINT `fk_wtkForumMsgs_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkGUID` (
  `GUID` int UNSIGNED NOT NULL auto_increment,
  `TableName` varchar(30),
  PRIMARY KEY (`GUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkHelp` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT ,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP ,
  `LastEditDate` datetime,
  `LastModByUserUID` int UNSIGNED,
  `HelpIndex` varchar(60) COMMENT 'used for lookups on page',
  `HelpTitle` varchar(200),
  `HelpText` text,
  `VideoLink` varchar(100),
  PRIMARY KEY (`UID`),
  KEY `ix_wtkHelp_HelpIndex` (`HelpIndex`),
  CONSTRAINT `fk_wtkHelp_LastModByUserUID`
    FOREIGN KEY (`LastModByUserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkLanguage` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastModByUserUID` int UNSIGNED,
  `Language` char(3),
  `MassUpdateId` varchar(30) DEFAULT NULL COMMENT 'JS and HTML prepend lang to id',
  `PrimaryText` varchar(120),
  `NewText` varchar(240),
  PRIMARY KEY (`UID`),
  KEY `ix_wtkLanguage_LastModBy` (`LastModByUserUID`),
  KEY `ix_wtkLanguage` (`PrimaryText`,`Language`),
  KEY `ix_wtkLanguage_MassUpdate` (`MassUpdateId`,`Language`,`PrimaryText`,`NewText`),
  CONSTRAINT `fk_wtkLanguage_LastModByUserUID`
    FOREIGN KEY (`LastModByUserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkLinkLogin` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `ActionNote` varchar(24),
  `GoToUrl` varchar(80),
  `NewPassHash` varchar(120),
  `VisitDate` datetime,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkLockoutUntil` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `FailCode` varchar(4) NOT NULL,
  `UserUID` int UNSIGNED,
  `IPaddress` varchar(15),
  `LockUntil` date,
  `BlockedCount` INT UNSIGNED DEFAULT 0,
  PRIMARY KEY (`UID`),
  KEY `ix_wtkLockoutUntil_IP` (`IPaddress`),
  CONSTRAINT `fk_wtkLockoutUntil_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkLoginLog` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `UserUID` int UNSIGNED NOT NULL,
  `FirstLogin` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `LastLogin` datetime,
  `LogoutTime` datetime,
  `CurrentPage` varchar(150),
  `PagesVisited` INT UNSIGNED DEFAULT 1,
  `PassedId` INT UNSIGNED NULL,
  `WhichApp` VARCHAR(12),
  `AccessMethod` varchar(15),
  `AppVersion`  varchar(6),
  `apiKey` varchar(256),
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkLoginLog_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_LoginLog_apiKey` (`apiKey`),
  KEY `ix_LoginLog_Page` (`CurrentPage`,`PassedId`,`LastLogin`),
  KEY `ix_LoginLog_LastLogin` (`LastLogin`,`UserUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkLookups` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `LastModByUserUID` int UNSIGNED,
  `LookupType` varchar(15),
  `LookupValue` varchar(40),
  `LookupDisplay` varchar(50),
  `espLookupDisplay` varchar(50),
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkLookups_LastModByUserUIDC`
    FOREIGN KEY (`LastModByUserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_wtkLookups_LookupType` (`LookupType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkMenuGroups` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `MenuUID` int UNSIGNED NOT NULL,
  `GroupName` varchar(20),
  `GroupURL` varchar(140),
  `Priority` smallint NOT NULL DEFAULT 10,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkMenuItems` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `MenuGroupUID` int UNSIGNED NOT NULL,
  `Priority` smallint NOT NULL DEFAULT 10,
  `PgUID` int UNSIGNED,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkMenuSets` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `MenuName` varchar(20),
  `Description` varchar(120),
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;


CREATE TABLE `wtkNotifications` (
  `UID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `AddedByUserUID` INT unsigned DEFAULT 0,
  `DelDate` DATETIME DEFAULT NULL,
  `DeletedByUserUID` INT unsigned DEFAULT NULL,
  `StartDate` DATETIME DEFAULT NULL,
  `Audience` CHAR(1) NOT NULL DEFAULT 'U',
  `ToUID` INT(10),
  `ToStaffRole` VARCHAR(4),
  `Icon` VARCHAR(20),
  `IconColor` VARCHAR(20),
  `NoteTitle` VARCHAR(40),
  `NoteMessage` VARCHAR(480),
  `GoToUrl` VARCHAR(80),
  `GoToId` INT(10),
  `GoToRng` INT DEFAULT 0,
  `EmailAlso` CHAR(1) DEFAULT 'N',
  `SmsAlso` CHAR(1) DEFAULT 'N',
  `SeenDate` DATETIME DEFAULT NULL,
  `SeenByUserUID` INT(10),
  `CloseDate` DATETIME DEFAULT NULL,
  `CloseByUserUID` INT(10),
  `RepeatFrequency` CHAR(1) DEFAULT 'N',
  `SentDate` DATETIME DEFAULT NULL,
  PRIMARY KEY (`UID`),
  KEY `ix_wtkNotifications_Dates` (`StartDate`, `SeenByUserUID`),
  KEY `ix_wtkNotifications_WhoTo` (`StartDate`, `Audience`, `ToStaffRole`, `ToUID`),
  KEY `ix_wtkNotifications_ToType` (`ToUID`, `ToStaffRole`, `SeenByUserUID`),
  KEY `ix_wtkNotifications_GoToId` (`GoToId`, `GoToUrl`),
  CONSTRAINT `fk_wtkNotifications_AddedByUserUID`
    FOREIGN KEY (`AddedByUserUID`) REFERENCES `wtkUsers` (`UID`),
  CONSTRAINT `fk_wtkNotifications_DeletedByUserUID`
    FOREIGN KEY (`DeletedByUserUID`) REFERENCES `wtkUsers` (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `wtkPages` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `PageName` varchar(200),
  `FileName` varchar(30),
  `Path` varchar(80),
  `DevNote` varchar(250),
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkPolls` (
  `UID`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `PollName` varchar(200) NOT NULL,
  `PollText` text,
  `PollType` varchar(4),
  `Active` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkPollOptions` (
  `UID`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `PollUID` INT UNSIGNED NOT NULL,
  `OptionText` varchar(200) NOT NULL ,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkPollResults` (
  `UID`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `PollUID` INT UNSIGNED NOT NULL,
  `UserUID` INT UNSIGNED NOT NULL,
  `MyChoice` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkProspects` (
  `UID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DelDate` datetime DEFAULT NULL,
  `CompanyName` varchar(255),
  `Address1` varchar(55),
  `Address2` varchar(30),
  `City` varchar(30),
  `State` char(2),
  `Zipcode` varchar(10),
  `County` varchar(30),
  `Country` varchar(60),
  `CountryCode` char(2),
  `TimeZone`    varchar(40),
  `MainPhone` varchar(20),
  `MainEmail` varchar(60),
  `Website` varchar(120),
  `LinkedIn` varchar(80),
  `OtherSocial` varchar(80),
  `CompanySize` varchar(80),
  `AnnualSales` varchar(255),
  `FundingDate` varchar(60),
  `FundingAmount` varchar(60),
  `FundingType` varchar(120),
  `SICCode` int,
  `B2BorB2C` varchar(20),
  `Industry` varchar(255),
  `ProspectStatus` varchar(5) DEFAULT 'new',
  `Description` text,
  `InternalNote` text,
  PRIMARY KEY (`UID`),
  KEY `ix_wtkProspects_ProspectStatus` (`ProspectStatus`),
  KEY `ix_wtkProspects_Industry` (`Industry`,`ProspectStatus`),
  KEY `ix_wtkProspects_CompanyName` (`CompanyName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkProspectStaff` (
  `UID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DelDate` datetime DEFAULT NULL,
  `ProspectUID` INT UNSIGNED NOT NULL,
  `FirstName` varchar(80),
  `LastName` varchar(80),
  `StaffRole` varchar(255),
  `DirectPhone` varchar(55),
  `Email` varchar(100),
  `EmailsSent` smallint DEFAULT '0',
  `EmailsOpened` smallint DEFAULT '0',
  `LinksClicked` smallint DEFAULT '0',
  `DoNotContact` char(1) DEFAULT 'N',
  `InternalNote` text,
  PRIMARY KEY (`UID`),
  KEY `ix_wtkProspectStaff_DoNotContact` (`DoNotContact`,`ProspectUID`),
  KEY `ix_wtkProspectStaff_ProspectUID` (`ProspectUID`,`DoNotContact`),
  KEY `ix_wtkProspectStaff_Email` (`Email`),
  KEY `ix_wtkProspectStaff_Contact` (`LinksClicked`,`EmailsOpened`,`DoNotContact`),
  KEY `ix_wtkProspectStaff_DoNotContactEmailsSent` (`DoNotContact`,`EmailsSent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkReminders` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `AddedByUserUID` int UNSIGNED NOT NULL,
  `DelDate` datetime default NULL,
  `DeletedByUserUID` int UNSIGNED default NULL,
  `DeliveryMethod` char(5),
  `Frequency` char(1) default '1' COMMENT '1 time, Weekly, Monthly',
  `Audience` ENUM('Staff','Tenant','PropGroup'),
  `ToUID`   INT UNSIGNED NULL,
  `ToStaffRole` varchar(4),
  `StartDate` date NOT NULL,
  `DeliveryTime` char(5),
  `ShowUntilDate` date NOT NULL,
  `MessageHeader` varchar(240) COMMENT 'SMS msg or Email Subject',
  `EmailBody` text,
  `SentDate` datetime,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkReminders_AddedByUserUID`
    FOREIGN KEY (`AddedByUserUID`) REFERENCES wtkUsers(`UID`),
  CONSTRAINT `fk_wtkReminders_DeletedByUserUID`
    FOREIGN KEY (`DeletedByUserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_Reminders_Dates` (`StartDate`,`DeliveryTime`,`SentDate`),
  KEY `ix_Reminders_ToType` (`ToUID`,`Audience`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkReplicate` (
  `UID`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TableName` varchar(40),
  `TableUID` INT UNSIGNED DEFAULT NULL,
  `ReplicateStatus` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`UID`),
  KEY `ix_replicatestatus_tablename_tableuid` (`ReplicateStatus`,`TableName`,`TableUID`),
  KEY `ix_tableuid_tablename_replicatestatus` (`TableUID`,`TableName`,`ReplicateStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkReplicateLog` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `NumberOfTables` INT UNSIGNED DEFAULT NULL,
  `NumberOfRows` INT UNSIGNED DEFAULT NULL,
  `ZipStatus` varchar(20),
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkReplicateTables` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TableName` varchar(40),
  `PrimaryKey` varchar(9) DEFAULT 'id',
  `Priority` int NOT NULL DEFAULT 10,
  PRIMARY KEY (`UID`),
  KEY `ix_replicate_tablename` (`TableName`,`Priority`,`PrimaryKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkReports` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime default NULL,
  `LastModByUserUID` int UNSIGNED default NULL,
  `ViewOrder` smallint DEFAULT NULL,
  `SecurityLevel` smallint DEFAULT 25,
  `TestMode` char(1) NOT NULL DEFAULT 'N',
  `HideFooter` enum('Y','N') DEFAULT 'N',
  `RptType` varchar(10),
  `RptName` varchar(80) NOT NULL,
  `RptNotes` varchar(400),
  `URLredirect` varchar(45),
  `RptSelect` text,
  `SelTableName` varchar(24),
  `SelValueColumn` varchar(24),
  `SelDisplayColumn` varchar(24),
  `SelWhere` varchar(400),
  `AddLink` varchar(40),
  `EditLink` varchar(40),
  `AlignCenter` varchar(200),
  `AlignRight` varchar(200),
  `FieldSuppress` varchar(200),
  `ChartSuppress` varchar(200),
  `SortableCols` varchar(800),
  `TotalCols` varchar(200),
  `TotalMoneyCols` varchar(200),
  `DaysAgo` smallint DEFAULT NULL,
  `StartDatePrompt` varchar(60),
  `StartDateCol` varchar(30),
  `EndDatePrompt` varchar(60),
  `EndDateCol` varchar(30),
  `GraphRpt` char(1) DEFAULT 'N',
  `RegRpt`   char(1),
  `BarChart` char(1),
  `LineChart` char(1),
  `AreaChart` char(1),
  `PieChart` char(1),
  `MenuName` varchar(20) DEFAULT NULL COMMENT 'for changing shown menu',
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkReports_LastModByUserUID`
    FOREIGN KEY (`LastModByUserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkReportCntr` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NULL default CURRENT_TIMESTAMP,
  `RptUID` int UNSIGNED,
  `RptURL` varchar(40),
  `RptType` enum('web','pdf','csv','xml'),
  `UserUID` int UNSIGNED default NULL,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkReportCntr_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkRevenue` (
  `UID`         int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate`     timestamp NOT NULL default CURRENT_TIMESTAMP,
  `UserUID`     int UNSIGNED,
  `OrderUID`    int UNSIGNED,
  `EcomUID`     int UNSIGNED,
  `EcomTxnType` varchar(60),
  `EcomPayId`   varchar(60),
  `AffiliateUID`  int UNSIGNED,
  `AffiliateRate` decimal(5,2),
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
  CONSTRAINT `fk_wtkRevenue_AffiliateUID`
    FOREIGN KEY (`AffiliateUID`) REFERENCES wtkAffiliates(`UID`),
  CONSTRAINT `fk_wtkRevenue_EcomUID`
    FOREIGN KEY (`EcomUID`) REFERENCES wtkEcommerce(`UID`),
  CONSTRAINT `fk_wtkRevenue_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`),
  INDEX `ix_RevenueAddDate` (`AddDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkInboundLog` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `IPaddress` varchar(15),
  `EcomUID` INT UNSIGNED,
  `RevenueUID` INT UNSIGNED,
  `InboundText` text COMMENT 'entire POST received',
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkInboundLog_EcomUID`
    FOREIGN KEY (`EcomUID`) REFERENCES wtkEcommerce(`UID`),
  CONSTRAINT `fk_wtkInboundLog_RevenueUID`
    FOREIGN KEY (`RevenueUID`) REFERENCES wtkRevenue(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkShortURL` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LinkName` VARCHAR(40) NULL DEFAULT NULL,
  `LinkHash` VARCHAR(12) NULL DEFAULT NULL,
  `GoToUrl` VARCHAR(250) NULL DEFAULT NULL,
  `PassVisitUID` ENUM('N','Y') NOT NULL DEFAULT 'N',
  `VisitCounter` int UNSIGNED DEFAULT 0,
  `LastVisitDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UID`),
  INDEX `ix_LinkHash` (`LinkHash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkSMSsent` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `SendByUserUID` int UNSIGNED,
  `SendToUserUID` int UNSIGNED,
  `SMSPhone` varchar(20),
  `SMSSubject` varchar(40),
  `SMSText` varchar(255),
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkSMSsent_SendByUserUID`
    FOREIGN KEY (`SendByUserUID`) REFERENCES wtkUsers(`UID`),
  CONSTRAINT `fk_wtkSMSsent_SendToUserUID`
    FOREIGN KEY (`SendToUserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkTableTracking` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TableName` varchar(60),
  `Action` char(3) NOT NULL COMMENT 'INS UPD or DEL',
  PRIMARY KEY (`UID`),
  KEY `ix_wtkTableTracking` (`TableName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkUpdateLog` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `PgUID` int UNSIGNED,
  `UserUID` int UNSIGNED,
  `OtherUID` int UNSIGNED,
  `TableName` varchar(30),
  `FullSQL` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ChangeInfo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `DevNote` varchar(200),
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkUpdateLog_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkUserNote` (
  `UID` int NOT NULL auto_increment,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `UserUID` int UNSIGNED DEFAULT NULL,
  `AddedByUserUID` int UNSIGNED DEFAULT NULL,
  `SecurityLevel` smallint DEFAULT 1 COMMENT 'minimum level to view',
  `FlagImportant` enum('Y','N') DEFAULT 'N',
  `Notes` TEXT NOT NULL,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkUserNote_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_wtkUserNote_UserUID` (`UserUID`,`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkUserHistory` (
  `UID` int NOT NULL auto_increment,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `UserUID` int UNSIGNED DEFAULT NULL,
  `OtherUID` int UNSIGNED DEFAULT NULL,
  `PageTitle` varchar(80),
  `PageURL` varchar(150),
  `SecondsTaken` decimal(6,3),
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkUserHistory_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`),
  KEY `ix_wtkUserHistory_UserUID` (`UserUID`,`UID`),
  KEY `ix_wtkUserHistory_UserUID_PageTitle` (`UserUID`,`PageTitle`),
  KEY `ix_wtkUserHistory_AddDate_UserUID` (`AddDate`,`UserUID`),
  KEY `ix_wtkUserHistory_PageTitle` (`PageTitle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkUserShortURL` (
  `UID` int NOT NULL auto_increment,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `UserUID` int UNSIGNED DEFAULT NULL,
  `ShortUID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkUserShortURL_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`),
  CONSTRAINT `fk_wtkUserShortURL_ShortUID`
    FOREIGN KEY (`ShortUID`) REFERENCES wtkShortURL(`UID`),
  KEY `ix_wtkUsershortURL_UserUID` (`UserUID`,`ShortUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkVisitors` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `IPaddress` varchar(15),
  `Referer` varchar(240),
  `ReferDomain` varchar(80),
  `AdUID` int UNSIGNED,
  `AffiliateUID` int UNSIGNED,
  `ShortUID` int UNSIGNED,
  `UserUID` int UNSIGNED,
  `SignupDate` datetime,
  `ActivatedDate` datetime,
  `BuyDate` datetime,
  `PagesB4Signup` smallint DEFAULT 0,
  `PagesB4Buy` smallint DEFAULT 0,
  `PagesAfterBuy` smallint DEFAULT 0,
  `FirstSKUBought` VARCHAR(15),
  `FirstPage` VARCHAR(150),
  `LastPage` VARCHAR(150),
  `SecondsOnSite` INT DEFAULT 0,
  `DevNote` varchar(40),
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkVisitors_AdUID`
    FOREIGN KEY (`AdUID`) REFERENCES wtkAds(`UID`),
  CONSTRAINT `fk_wtkVisitors_AffiliateUID`
    FOREIGN KEY (`AffiliateUID`) REFERENCES wtkAffiliates(`UID`),
  CONSTRAINT `fk_wtkVisitors_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES wtkUsers(`UID`),
  CONSTRAINT `fk_wtkVisitors_ShortUID`
    FOREIGN KEY (`ShortUID`) REFERENCES wtkShortURL(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkVisitorHistory` (
  `UID` int NOT NULL auto_increment,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `VisitorUID` int UNSIGNED DEFAULT NULL,
  `PageTitle` varchar(80),
  `PageURL` varchar(150),
  `SecondsViewed` INT DEFAULT 0,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_wtkVisitorHistory_VisitorUID`
    FOREIGN KEY (`VisitorUID`) REFERENCES wtkVisitors(`UID`) ON DELETE CASCADE ON UPDATE RESTRICT,
  KEY `ix_wtkVisitorHistory_VisitorUID` (`VisitorUID`,`UID`),
  KEY `ix_wtkVisitorHistory_PageTitle` (`PageTitle`, `SecondsViewed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Widget related tables
CREATE TABLE `wtkWidgetGroup` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime default NULL,
  `WidgetGroupName` varchar(40),
  `StaffRole` varchar(4),
  `SecurityLevel` smallint DEFAULT 1,
  `UseForDefault` char(1),
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkWidget` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime default NULL,
  `WidgetName` varchar(40),
  `SecurityLevel` smallint DEFAULT 1,
  `WidgetType` varchar(10),
  `ChartType`  varchar(30),
  `WidgetColor` varchar(20),
  `SkipFooter` enum('Y','N') default 'N',
  `WidgetDescription` varchar(240),
  `WidgetSQL` text,
  `WidgetURL` varchar(80),
  `PassRNG` VARCHAR(20),
  `WindowModal` enum('Y','N') default 'N',
  PRIMARY KEY (`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE `wtkWidgetGroup_X_Widget` (
  `UID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `AddDate` timestamp NULL default CURRENT_TIMESTAMP,
  `WidgetGroupUID` int UNSIGNED NOT NULL,
  `UserUID` int UNSIGNED default NULL,
  `WidgetUID` int UNSIGNED NOT NULL,
  `WidgetPriority` int NOT NULL DEFAULT 10,
  PRIMARY KEY (`UID`),
  CONSTRAINT `fk_WidgetGroup_X_Widget_WGUID`
    FOREIGN KEY (`WidgetGroupUID`) REFERENCES `wtkWidgetGroup`(`UID`),
  CONSTRAINT `fk_WidgetGroup_X_Widget_UserUID`
    FOREIGN KEY (`UserUID`) REFERENCES `wtkUsers`(`UID`),
  CONSTRAINT `fk_WidgetGroup_X_Widget_WUID`
    FOREIGN KEY (`WidgetUID`) REFERENCES `wtkWidget`(`UID`),
    KEY `ix_wtkWidgetGroup_X_Widget` (`UserUID`,`WidgetGroupUID`,`WidgetPriority`,`WidgetUID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;


SET FOREIGN_KEY_CHECKS=1;
