CREATE TABLE "wtkUsers" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "Title" varchar(40),
  "FirstName" varchar(30),
  "LastName" varchar(35),
  "Address" varchar(45),
  "Address2" varchar(30),
  "City" varchar(30),
  "State" varchar(2),
  "Zipcode" varchar(10),
  "CountryCode" char(2),
  "Phone" varchar(20),
  "CellPhone" varchar(20),
  "PersonalURL" varchar(120),
  "LangPref" char(3),
  "UseSkype" char(1) default 'N',
  "SMSEnabled" char(1) default 'N',
  "OptInEmails" char(1) default 'Y',
  "Email" varchar(80),
  "AltEmail" varchar(80),
  "LoginCode" varchar(40),
  "WebPassword" varchar(255),
  "LoginTimeout" smallint DEFAULT 60,
  "SecurityLevel" smallint DEFAULT 1,
  "StaffRole" varchar(4) DEFAULT NULL,
  "MenuSet" varchar(20) DEFAULT NULL,
  "EmailAlerts" char(1) default 'Y',
  "PhoneAlerts" char(1) default 'Y',
  "CanPrint" char(1) default 'N',
  "CanExport" char(1) default 'N',
  "CanEditHelp" char(1) default 'N',
  "CanUnlock" char(1) default 'N',
  "SSN" varchar(11),
  "UseSkype" char(1) default 'N',
  "SMSEnabled" char(1) default 'N',
  "OptInEmails" char(1) default 'N',
  "PaymentInstructions" TEXT NULL,
  "CurrencyCode" CHAR(3) NOT NULL DEFAULT 'USD',
  "Payee" varchar(120),
  "BankAddress" varchar(240),
  "AccountNumber" varchar(80),
  "InternalNote" text,
  "Biography" text,
  "IPAddress" varchar(15),
  "EmailOKDate" timestamp without time zone,
  "SignedDate" timestamp without time zone,
  "PromoCode" varchar(24),
  "ExpiresDate" date,
  "FilePath" varchar(30) NULL,
  "NewFileName" varchar(12) NULL,
  "NewPassHash" varchar(140)
);
CREATE INDEX "ix_wtkUsers_Email" ON "wtkUsers" ("Email");
CREATE INDEX "ix_wtkUsers_LastNameFirstName" ON "wtkUsers" ("LastName", "FirstName");
CREATE INDEX "ix_wtkUsers_FirstNameLastName" ON "wtkUsers" ("FirstName", "LastName");

CREATE TABLE "wtkAds" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "LastModByUserUID" int,
  "AdName" varchar(60),
  "AdVendor" varchar(60),
  "AdType" varchar(1),
  "AdText" text,
  "AdNote" text,
  "VisitCounter" int DEFAULT 0,
  "LastVisitDate" timestamp without time zone DEFAULT now(),
  CONSTRAINT "fk_wtkAds_LastModByUserUID"
    FOREIGN KEY ("LastModByUserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkAffiliates" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "SignedDate" timestamp without time zone,
  "CompanyName" varchar(255),
  "ContactName" varchar(120),
  "CountryCode" char(2),
  "TimeZone"    varchar(40),
  "WebPasscode" varchar(80),
  "LinkToURL" VARCHAR(120),
  "AffiliateHash" varchar(60),
  "Email" varchar(100),
  "MainPhone" varchar(40),
  "Website" varchar(255),
  "DiscountPercentage" tinyint COMMENT 'link gives discount on sale items',
  "AffiliateRate" decimal(5,2) COMMENT 'commission',
  "PaymentInstructions" text,
  "InternalNote" varchar(250)
);
CREATE INDEX "ix_wtkAffiliates_CompanyName" ON "wtkAffiliates" ("CompanyName");
CREATE INDEX "ix_wtkAffiliates_ContactName" ON "wtkAffiliates" ("ContactName");
CREATE INDEX "ix_wtkAffiliates_AffiliateHash" ON "wtkAffiliates" ("AffiliateHash");

CREATE TABLE "wtkBackgroundActions" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "TriggerDate" timestamp without time zone,
  "DoneDate"    timestamp without time zone,
  "ActionType"  varchar(8) NOT NULL,
  "ToUserUID"   int,
  "OtherUID"    int,
  "DevNote"     varchar(120),
  CONSTRAINT "fk_wtkBackgroundActions_ToUserUID"
    FOREIGN KEY("ToUserUID")
    REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkClients" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "ClientName" VARCHAR(30),
  "Address" VARCHAR(30),
  "Address2" VARCHAR(30),
  "City" VARCHAR(30),
  "State" CHAR(2),
  "Zipcode" VARCHAR(10),
  "CountryCode" char(2),
  "ClientPhone" VARCHAR(20),
  "ClientEmail" VARCHAR(40),
  "AccountEmail" VARCHAR(60),
  "StartDate" DATE DEFAULT NULL,
  "ClientStatus" CHAR(1) DEFAULT 'A',
  "InternalNote" VARCHAR(250)
);
CREATE INDEX "ixClientName" ON "wtkClients" ("ClientName");
COMMENT ON COLUMN "wtkClients"."ClientStatus" IS 'Trial, Active, Inactive';

CREATE TABLE "wtkBlog" (
    "UID" SERIAL PRIMARY KEY,
    "AddDate" timestamp without time zone DEFAULT now(),
    "DelDate" timestamp without time zone default NULL,
    "LastEditDate" timestamp without time zone default NULL,
    "UserUID" int NULL,
    "Slug" varchar(80),
    "PageTitle" varchar(120),
    "BlogContent" text,
    "MetaKeywords" text,
    "MetaDescription" text,
    "TwitterAcct" varchar(40) DEFAULT NULL,
    "OGTitle" varchar(70) DEFAULT NULL,
    "OGDescription" varchar(200) DEFAULT NULL,
    "OGFilePath" varchar(30) DEFAULT NULL,
    "OGImage" varchar(12) DEFAULT NULL,
    "MakePublic" char(1) NOT NULL DEFAULT 'N',
    "PublishDate" timestamp without time zone default NULL,
    "Views"   int NOT NULL DEFAULT 0,
    "LastViewDate" timestamp without time zone default NULL,
    CONSTRAINT "fk_wtkBlog_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkBlog_Slug" ON "wtkBlog" ("Slug");
COMMENT ON COLUMN "wtkBlog"."PageTitle" IS 'also used for Navigation name';

CREATE TABLE "wtkBroadcast" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "AddedByUserUID" int default NULL,
  "DelDate" timestamp without time zone default NULL,
  "DeletedByUserUID" int default NULL,
  "AudienceType" varchar(4) default NULL,
  "AudienceSubType" varchar(4) default NULL,
  "BroadcastColor" varchar(20) default NULL,
  "TextColor" varchar(20) default NULL,
  "MessageHeader" varchar(120) default NULL,
  "MessageType" varchar(10) default NULL,
  "MessageNote" text,
  "ShowOnDate" date default NULL,
  "ShowUntilDate" date default NULL,
  "AllowClose" char(1) default 'Y',
  "CloseMessage" varchar(20) default 'Close',
  CONSTRAINT "fk_wtkBroadcast_UserUID"
    FOREIGN KEY ("DeletedByUserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkBroadcast_AddedByUserUID" ON "wtkBroadcast" ("AddedByUserUID");
CREATE INDEX "ix_wtkBroadcast_Dates" ON "wtkBroadcast" ("ShowOnDate","ShowUntilDate");

CREATE TABLE "wtkBroadcast_wtkUsers" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "BroadcastUID" int default NULL,
  "UserUID" int default NULL,
  "IpAddress" varchar(16) default NULL,
  CONSTRAINT "fk_wtkBroadcast_wtkUsers_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkBroadcast_wtkUsers" ON "wtkBroadcast_wtkUsers" ("UserUID","BroadcastUID");

CREATE TABLE "wtkBugReport" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "CreatedByUserUID" int DEFAULT NULL,
  "IPaddress" varchar(40) DEFAULT NULL,
  "OpSystem" varchar(25) DEFAULT NULL,
  "Browser" varchar(20) DEFAULT NULL,
  "BrowserVer" varchar(12) DEFAULT NULL,
  "AppVersion" varchar(12) DEFAULT NULL,
  "DeviceType" char(8) DEFAULT 'computer',
  "ReferralPage" varchar(120) DEFAULT NULL,
  "BugMsg" text,
  "InternalNote" varchar(120) DEFAULT NULL,
  "DevNote" varchar(255) DEFAULT NULL,
  "DevUserUID" int DEFAULT NULL,
  "DoneDate" timestamp without time zone DEFAULT NULL,
  CONSTRAINT "fk_wtkBugReport_UserUID"
    FOREIGN KEY ("CreatedByUserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkChat"(
    "UID" SERIAL PRIMARY KEY,
    "AddDate" timestamp without time zone DEFAULT now(),
    "SendByUserUID" int,
    "SendToUserUID" int,
    "Message" varchar(900),
    CONSTRAINT "fk_wtkChat_ByUserUID"
      FOREIGN KEY ("SendByUserUID") REFERENCES "wtkUsers"("UID"),
    CONSTRAINT "fk_wtkChat_ToUserUID"
      FOREIGN KEY ("SendToUserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkChat_FromTo" ON "wtkChat" ("SendByUserUID","SendToUserUID");

CREATE TABLE "wtkCompanySettings" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "LastModByUserUID" int default NULL,
  "CoName"  varchar(120),
  "CoLogo"  varchar(40),
  "Address" varchar(40),
  "City" varchar(40),
  "State" varchar(2),
  "Zipcode" varchar(10),
  "CountryCode" char(2),
  "Phone" varchar(20),
  "Email" varchar(60),
  "MiscContactInfo" text,
  "Ecommerce" varchar(5),
  "PayPalEmail" varchar(60),
  "TaxRate" numeric(6,4),
  "ShowQty" varchar(1),
  "ShowStaffLogin" varchar(1),
  "DomainName" varchar(90),
  "AppVersion"  varchar(6),
  "EnableLockout"  char(1) NOT NULL DEFAULT 'Y',
  CONSTRAINT "fk_wtkCompanySettings_UserUID"
    FOREIGN KEY ("LastModByUserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkDebug" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DevNote"   VARCHAR(240) NULL DEFAULT NULL
);

CREATE TABLE "wtkEcommerce" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "PaymentProvider" varchar(60),
  "EcomLogin" varchar(80),
  "EcomPassword" varchar(80),
  "EcomWebsite" varchar(120),
  "EcomPayLink" varchar(120),
  "EcomNote" varchar(240)
);

CREATE TABLE "wtkEmailsSent" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "EmailUID" int,
  "EmailType" varchar(8),
  "HtmlTemplate" varchar(30),
  "OtherUID" int,
  "SendByUserUID" int,
  "SendToUserUID" int,
  "EmailAddress" varchar(100),
  "InternalNote" varchar(250),
  "Subject" varchar(255),
  "EmailBody" text,
  "EmailMsgId" varchar(200) NULL DEFAULT NULL,
  "EmailDelivered" timestamp without time zone NULL DEFAULT NULL,
  "EmailOpened" timestamp without time zone NULL DEFAULT NULL,
  "EmailLinkClicked" timestamp without time zone NULL DEFAULT NULL,
  "SpamComplaint" timestamp without time zone NULL DEFAULT NULL,
  "Bounced" char(1) DEFAULT NULL,
  CONSTRAINT "fk_wtkEmailsSent_SendByUserUID"
    FOREIGN KEY ("SendByUserUID") REFERENCES "wtkUsers"("UID"),
  CONSTRAINT "fk_wtkEmailsSent_SendToUserUID"
    FOREIGN KEY ("SendToUserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkEmailsSent_EmailType" ON "wtkEmailsSent" ("EmailType","OtherUID","UID");
CREATE INDEX "ix_wtkEmailsSent_EmailMsgId" ON "wtkEmailsSent" ("EmailMsgId");

CREATE TABLE "wtkEmailTemplate" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "AutomationOnly" CHAR(1) NOT NULL DEFAULT 'N',
  "EmailType" varchar(8),
  "EmailCode" varchar(10),
  "Subject" varchar(60),
  "EmailBody" text,
  "InternalNote" text
);
CREATE INDEX "ix_wtkEmailTemplate_EmailCode" ON "wtkEmailTemplate" ("EmailCode");

CREATE TABLE "wtkErrorLog" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "UserUID" int,
  "FromPage" varchar(120),
  "ReferralPage" varchar(120),
  "ErrType" varchar(20),
  "ErrMsg" varchar(2000),
  "ErrNotes" varchar(100),
  "DevID" varchar(3),
  "DelDate" timestamp without time zone,
  "LineNum" smallint,
  CONSTRAINT "fk_wtkErrorLog_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkFailedAttempts" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "FailCode" varchar(4) NOT NULL,
  "UserUID" int,
  "IPaddress" varchar(40),
  "FailNote" varchar(250),
  "OpSystem" varchar(25),
  "Browser" varchar(20),
  "BrowserVer" varchar(12),
  CONSTRAINT "fk_wtkFailedAttempts_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkFailedAttempts_FailCode" ON "wtkFailedAttempts" ("FailCode");

CREATE TABLE "wtkFiles" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" DATETIME,
  "UserUID" int,
  "TableRelation" varchar(30) NOT NULL,
  "ParentUID" int,
  "Description" varchar(120) NULL,
  "OrigFileName" varchar(110) NULL,
  "FilePath" varchar(30) NULL,
  "NewFileName" varchar(12) NULL,
  "FileExtension" varchar(20) NOT NULL,
  "FileSize" int,
  "CurrentLocation" char(1) DEFAULT 'L',
  "ExternalStorage" char(1) DEFAULT 'N',
  "TempDownload" char(1) DEFAULT 'N',
  "Redacted" CHAR(1) DEFAULT 'N',
  CONSTRAINT "fk_wtkFiles_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkFiles_TableRelation" ON "wtkFiles" ("TableRelation","ParentUID");
CREATE INDEX "ix_wtkFiles_ExternalStorage" ON "wtkFiles" ("ExternalStorage");
COMMENT ON COLUMN "wtkFiles"."UserUID" IS 'who uploaded';
COMMENT ON COLUMN "wtkFiles"."ParentUID" IS 'associated with TableRelation';
COMMENT ON COLUMN "wtkFiles"."CurrentLocation" IS 'L for local, A for AWS S3, C for Cloudflare R2, X for deleted';
COMMENT ON COLUMN "wtkFiles"."ExternalStorage" IS 'N for Local only; Y for AWS, Cloudflare, etc.';
COMMENT ON COLUMN "wtkFiles"."TempDownload" IS 'Y for private bucket requiring internal download to view';

CREATE TABLE "wtkForum" (
    "UID" SERIAL PRIMARY KEY,
    "AddDate" timestamp without time zone DEFAULT now(),
    "LastEditDate" timestamp without time zone,
    "DelDate" timestamp without time zone,
    "CreatedByUserUID" int,
    "ForumName" varchar(24) NOT NULL,
    "ForumNote" varchar(1800),
    CONSTRAINT "fk_wtkForum_UserUID"
      FOREIGN KEY ("CreatedByUserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkForumMsgs" (
    "UID" SERIAL PRIMARY KEY,
    "AddDate" timestamp without time zone DEFAULT now(),
    "ForumUID" int,
    "UserUID" int,
    "ForumMsg" text,
    CONSTRAINT "fk_wtkForumMsgs_ForumUID"
      FOREIGN KEY ("ForumUID") REFERENCES "wtkForum"("UID"),
    CONSTRAINT "fk_wtkForumMsgs_UserUID"
      FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkGUID" (
  "GUID" SERIAL PRIMARY KEY,
  "TableName" varchar(30)
);

CREATE TABLE "wtkHelp" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "LastEditDate" timestamp without time zone,
  "LastModByUserUID" int,
  "HelpIndex" varchar(60),
  "HelpTitle" varchar(200),
  "HelpText" text,
  "VideoLink" varchar(100),
  CONSTRAINT "fk_wtkHelp_UserUID"
    FOREIGN KEY ("LastModByUserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkHelp_HelpIndex" ON "wtkHelp" ("HelpIndex");

CREATE TABLE "wtkLanguage" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "LastModByUserUID" int,
  "Language" varchar(3),
  "MassUpdateId" varchar(30) DEFAULT NULL,
  "PrimaryText" varchar(120),
  "NewText" varchar(240) DEFAULT NULL,
  CONSTRAINT "fk_wtkLanguage_wtkUsers"
    FOREIGN KEY ("LastModByUserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkLanguage" ON "wtkLanguage" ("PrimaryText","Language");
CREATE INDEX "ix_wtkLanguage_MassUpdate" ON "wtkLanguage" ("MassUpdateId","Language","PrimaryText","NewText");

CREATE TABLE "wtkLinkLogin" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "ActionNote" varchar(24),
  "GoToUrl" varchar(80),
  "NewPassHash" varchar(120),
  "VisitDate" timestamp without time zone
);

CREATE TABLE "wtkLockoutUntil" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "FailCode" varchar(4) NOT NULL,
  "UserUID" int,
  "IPaddress" varchar(40),
  "LockUntil" date,
  "BlockedCount" INT DEFAULT 0,
  CONSTRAINT "fk_wtkLockoutUntil_wtkUsers"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkLockoutUntil_IP" ON "wtkLockoutUntil" ("IPaddress");

CREATE TABLE "wtkLoginLog" (
  "UID" SERIAL PRIMARY KEY,
  "FirstLogin" timestamp without time zone DEFAULT now(),
  "UserUID" int NOT NULL,
  "LastLogin" timestamp without time zone,
  "LogoutTime" timestamp without time zone,
  "CurrentPage" varchar(150),
  "PagesVisited" INT DEFAULT 1,
  "PassedId" bigint NULL,
  "WhichApp" VARCHAR(12),
  "MobilePlatform" varchar(15),
  "AppVersion"  varchar(6),
  "apiKey" varchar(256),
  CONSTRAINT "fk_wtkLoginLog_wtkUsers"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_LoginLog_apiKey" ON "wtkLoginLog" ("apiKey");
CREATE INDEX "ix_LoginLog_Page" ON "wtkLoginLog" ("CurrentPage","PassedId","LastLogin");
CREATE INDEX "ix_LoginLog_LastLogin" ON "wtkLoginLog" ("LastLogin","UserUID");

CREATE TABLE "wtkLookups" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "LastModByUserUID" int,
  "LookupType" varchar(15),
  "LookupValue" varchar(40),
  "LookupDisplay" varchar(50),
  "espLookupDisplay" varchar(50),
  CONSTRAINT "fk_wtkLookups_wtkUsers"
    FOREIGN KEY ("LastModByUserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkLookups_LookupType" ON "wtkLookups" ("LookupType");

CREATE TABLE "wtkMenuGroups" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "MenuUID" int NOT NULL,
  "GroupName" varchar(20),
  "GroupURL" varchar(140),
  "Priority" smallint NOT NULL DEFAULT 10
);

CREATE TABLE "wtkMenuItems" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "MenuGroupUID" int NOT NULL,
  "Priority" smallint NOT NULL DEFAULT 10,
  "PgUID" int
);

CREATE TABLE "wtkMenuSets" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "MenuName" varchar(20),
  "Description" varchar(120)
);

CREATE TABLE "wtkNotifications" (
  "UID"         SERIAL PRIMARY KEY,
  "AddDate"     timestamp without time zone DEFAULT now(),
  "AddedByUserUID" int DEFAULT 0,
  "DelDate"     timestamp without time zone default NULL,
  "DeletedByUserUID" int default NULL,
  "StartDate"   timestamp without time zone,
  "Audience"    char(1) NOT NULL DEFAULT 'U',
  "ToUID"       INT,
  "ToStaffRole" varchar(4),
  "Icon"        varchar(20),
  "IconColor"   varchar(20),
  "NoteTitle"   varchar(40),
  "NoteMessage" varchar(480),
  "GoToUrl"     varchar(80),
  "GoToId"      int,
  "GoToRng"     int DEFAULT 0,
  "EmailAlso"   char(1) DEFAULT 'N',
  "SmsAlso"     char(1) DEFAULT 'N',
  "SeenDate"    timestamp without time zone,
  "SeenByUserUID" int,
  "CloseDate"   timestamp without time zone,
  "CloseByUserUID" int,
  "RepeatFrequency" char(1) default 'N',
  "SentDate"     timestamp without time zone,
  CONSTRAINT "fk_wtkNotifications_AddedByUserUID"
    FOREIGN KEY ("AddedByUserUID") REFERENCES "wtkUsers"("UID"),
  CONSTRAINT "fk_wtkNotifications_DeletedByUserUID"
    FOREIGN KEY ("DeletedByUserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkNotifications_Dates" ON "wtkNotifications" ("StartDate","SeenByUserUID");
CREATE INDEX "ix_wtkNotifications_WhoTo" ON "wtkNotifications" ("StartDate","Audience","ToStaffRole","ToUID");
CREATE INDEX "ix_wtkNotifications_ToType" ON "wtkNotifications" ("ToUID","ToStaffRole","SeenByUserUID");
CREATE INDEX "ix_wtkNotifications_GoToId" ON "wtkNotifications" ("GoToId","GoToUrl");

CREATE TABLE "wtkPages" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "PageName" varchar(200),
  "FileName" varchar(30),
  "Path" varchar(80),
  "DevNote" varchar(250)
);

CREATE TABLE "wtkReminders" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "AddedByUserUID" int NOT NULL,
  "DelDate" timestamp without time zone default NULL,
  "DeletedByUserUID" int default NULL,
  "DeliveryMethod" varchar(5),
  "Frequency" char(1) default '1',
  "Audience" varchar(10),
  "ToUID"    INT,
  "ToStaffRole" varchar(4),
  "StartDate" date NOT NULL,
  "DeliveryTime" char(5),
  "ShowUntilDate" date NOT NULL,
  "MessageHeader" varchar(240),
  "EmailBody" text,
  "SentDate" timestamp without time zone default NULL,
  CONSTRAINT "fk_wtkReminders_AddedByUserUID"
    FOREIGN KEY ("AddedByUserUID") REFERENCES "wtkUsers"("UID"),
  CONSTRAINT "fk_wtkReminders_DeletedByUserUID"
    FOREIGN KEY ("DeletedByUserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_Reminders_Dates" ON "wtkReminders" ("StartDate","DeliveryTime","SentDate");
CREATE INDEX "ix_Reminders_ToType" ON "wtkReminders" ("ToUID","Audience");
COMMENT ON COLUMN "wtkReminders"."Frequency" IS '1 time, Weekly, Monthly';
COMMENT ON COLUMN "wtkReminders"."MessageHeader" IS 'SMS msg or Email Subject';
COMMENT ON COLUMN "wtkReminders"."DeliveryMethod" IS 'Web, Email, SMS, or Push';

CREATE TABLE "wtkReports" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone default NULL,
  "LastModByUserUID" int default NULL,
  "ViewOrder" smallint DEFAULT NULL,
  "SecurityLevel" smallint DEFAULT 25,
  "TestMode" char(1) NOT NULL DEFAULT 'N',
  "HideFooter" char(1) DEFAULT 'N',
  "RptType" varchar(10) DEFAULT NULL,
  "RptName" varchar(80) NOT NULL,
  "RptNotes" varchar(400) DEFAULT NULL,
  "URLredirect" varchar(45) DEFAULT NULL,
  "RptSelect" text,
  "SelTableName" varchar(24) DEFAULT NULL,
  "SelValueColumn" varchar(24) DEFAULT NULL,
  "SelDisplayColumn" varchar(24) DEFAULT NULL,
  "SelWhere" varchar(400) DEFAULT NULL,
  "AddLink" varchar(40) DEFAULT NULL,
  "EditLink" varchar(40) DEFAULT NULL,
  "AlignCenter" varchar(200) DEFAULT NULL,
  "AlignRight" varchar(200) DEFAULT NULL,
  "FieldSuppress" varchar(200) DEFAULT NULL,
  "ChartSuppress" varchar(200) DEFAULT NULL,
  "SortableCols" varchar(800) DEFAULT NULL,
  "TotalCols" varchar(200) DEFAULT NULL,
  "TotalMoneyCols" varchar(200) DEFAULT NULL,
  "DaysAgo" smallint DEFAULT NULL,
  "StartDatePrompt" varchar(60) DEFAULT NULL,
  "StartDateCol" varchar(30) DEFAULT NULL,
  "EndDatePrompt" varchar(60) DEFAULT NULL,
  "EndDateCol" varchar(30) DEFAULT NULL,
  "GraphRpt" char(1) DEFAULT 'N',
  "RegRpt"   char(1),
  "BarChart" char(1),
  "LineChart" char(1),
  "AreaChart" char(1),
  "PieChart" char(1),
  "MenuName" varchar(20) DEFAULT NULL,
  CONSTRAINT "fk_wtkReports_wtkUsers"
    FOREIGN KEY ("LastModByUserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkReportCntr" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "RptUID" int,
  "RptURL" varchar(40),
  "RptType" char(3),
  "UserUID" int default NULL,
  CONSTRAINT "fk_wtkReportCntr_wtkUsers"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkRevenue" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "UserUID"     int,
  "OrderUID"    int,
  "EcomUID"     int,
  "EcomTxnType" varchar(60),
  "EcomPayId"   varchar(60),
  "RevType"     varchar(4),
  "IPaddress"   varchar(15),
  "PayerEmail"  varchar(60),
  "PayerId"     varchar(60),
  "FirstName"   varchar(60),
  "LastName"    varchar(60),
  "ItemName"    varchar(120),
  "ItemNumber"  varchar(60),
  "PaymentStatus" varchar(40),
  "GrossAmount"  decimal(7,2),
  "MerchantFee"  decimal(7,2),
  "CurrencyCode" char(3),
  "DevNote"      varchar(50),
  CONSTRAINT "fk_wtkRevenue_wtkEcommerce"
    FOREIGN KEY ("EcomUID") REFERENCES "wtkEcommerce"("UID"),
  CONSTRAINT "fk_wtkRevenue_wtkUsers"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_RevenueAddDate" ON "wtkRevenue" ("AddDate");

CREATE TABLE "wtkInboundLog" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "IPaddress" varchar(40),
  "EcomUID" INT,
  "RevenueUID" INT,
  "InboundText" text,
  CONSTRAINT "fk_wtkInboundLog_EcomUID"
    FOREIGN KEY ("EcomUID") REFERENCES "wtkEcommerce"("UID"),
  CONSTRAINT "fk_wtkInboundLog_RevenueUID"
    FOREIGN KEY ("RevenueUID") REFERENCES "wtkRevenue"("UID")
);

CREATE TABLE "wtkShortURL" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "LinkName" varchar(40),
  "LinkHash" varchar(12),
  "GoToUrl" varchar(250),
  "PassVisitUID" char(1) NOT NULL DEFAULT 'N',
  "VisitCounter" int DEFAULT 0,
  "LastVisitDate" timestamp without time zone DEFAULT now()
);
CREATE INDEX "ix_LinkHash" ON "wtkShortURL" ("LinkHash");

CREATE TABLE "wtkSMSsent" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "SendByUserUID" int,
  "SendToUserUID" int,
  "SMSPhone" varchar(20),
  "SMSSubject" varchar(40),
  "SMSText" varchar(255),
  CONSTRAINT "fk_wtkSMSsent_SendByUserUID"
    FOREIGN KEY ("SendByUserUID") REFERENCES "wtkUsers"("UID"),
  CONSTRAINT "fk_wtkSMSsent_SendToUserUID"
    FOREIGN KEY ("SendToUserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkUpdateLog" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "PgUID" int,
  "UserUID" int,
  "OtherUID" int,
  "TableName" varchar(30),
  "FullSQL" text NOT NULL,
  "ChangeInfo" text NOT NULL,
  "DevNote" varchar(200),
  CONSTRAINT "fk_wtkUpdateLog_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);

CREATE TABLE "wtkUserNote" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "UserUID" int DEFAULT NULL,
  "AddedByUserUID" int DEFAULT NULL,
  "SecurityLevel" smallint DEFAULT 1,
  "FlagImportant" char(1) DEFAULT 'N',
  "Notes" TEXT NOT NULL,
  CONSTRAINT "fk_wtkUserNote_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkUserNote_UserUID" ON "wtkUserNote" ("UserUID","UID");

CREATE TABLE "wtkUserHistory" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "UserUID" int DEFAULT NULL,
  "OtherUID" bigint DEFAULT NULL,
  "PageTitle" varchar(80) DEFAULT NULL,
  "PageURL" varchar(150) DEFAULT NULL,
  "SecondsTaken" decimal(6,3) DEFAULT NULL,
  CONSTRAINT "fk_wtkUserHistory_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID")
);
CREATE INDEX "ix_wtkUserHistory_UserUID" ON "wtkUserHistory" ("UserUID","UID");
CREATE INDEX "ix_wtkUserHistory_UserUID_PageTitle" ON "wtkUserHistory" ("UserUID","PageTitle");
CREATE INDEX "ix_wtkUserHistory_AddDate_UserUID" ON "wtkUserHistory" ("AddDate","UserUID");
CREATE INDEX "ix_wtkUserHistory_PageTitle" ON "wtkUserHistory" ("PageTitle");

CREATE TABLE "wtkVisitors" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "IPaddress" varchar(40),
  "Referer" varchar(240),
  "ReferDomain" varchar(80),
  "AdUID" int,
  "AffiliateUID" int,
  "ShortUID" int,
  "UserUID" int,
  "SignupDate" timestamp without time zone,
  "ActivatedDate" timestamp without time zone,
  "BuyDate" timestamp without time zone,
  "PagesB4Signup" smallint DEFAULT 0,
  "PagesB4Buy" smallint DEFAULT 0,
  "PagesAfterBuy" smallint DEFAULT 0,
  "FirstSKUBought" VARCHAR(15),
  "FirstPage" VARCHAR(150),
  "LastPage" VARCHAR(150),
  "SecondsOnSite" INT DEFAULT 0,
  "DevNote" varchar(40),
  CONSTRAINT "fk_wtkVisitors_AdUID"
    FOREIGN KEY ("AdUID") REFERENCES "wtkAds"("UID"),
  CONSTRAINT "fk_wtkVisitors_AffiliateUID"
    FOREIGN KEY ("AffiliateUID") REFERENCES "wtkAffiliates"("UID"),
  CONSTRAINT "fk_wtkVisitors_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID"),
  CONSTRAINT "fk_wtkVisitors_ShortUID"
    FOREIGN KEY ("ShortUID") REFERENCES "wtkShortURL"("UID")
);

CREATE TABLE "wtkVisitorHistory" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "VisitorUID" int,
  "PageTitle" varchar(80),
  "PageURL" varchar(150),
  "SecondsViewed" INT DEFAULT 0,
  CONSTRAINT "fk_wtkVisitorHistory_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID"),
  CONSTRAINT "fk_wtkVisitorHistory_VisitorUID"
    FOREIGN KEY ("VisitorUID") REFERENCES "wtkVisitors"("UID")
);
CREATE INDEX "ix_wtkVisitorHistory_VisitorUID" ON "wtkVisitorHistory" ("VisitorUID","UID");
CREATE INDEX "ix_wtkVisitorHistory_PageTitle" ON "wtkVisitorHistory" ("PageTitle","SecondsViewed");

-- Widget related tables
CREATE TABLE "wtkWidgetGroup" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "WidgetGroupName" varchar(40),
  "StaffRole" varchar(4) DEFAULT NULL,
  "SecurityLevel" smallint DEFAULT 1,
  "UseForDefault" char(1)
);

CREATE TABLE "wtkWidget" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "DelDate" timestamp without time zone,
  "WidgetName" varchar(40),
  "SecurityLevel" smallint DEFAULT 1,
  "WidgetType" varchar(10),
  "ChartType"  varchar(30),
  "WidgetColor" varchar(20),
  "SkipFooter"  char(1),
  "WidgetDescription" varchar(240),
  "WidgetSQL" text,
  "WidgetURL" varchar(80),
  "PassRNG" varchar(20),
  "WindowModal" char(1) NOT NULL DEFAULT 'N'
);

CREATE TABLE "wtkWidgetGroup_X_Widget" (
  "UID" SERIAL PRIMARY KEY,
  "AddDate" timestamp without time zone DEFAULT now(),
  "WidgetGroupUID" int NOT NULL,
  "UserUID" int,
  "WidgetUID" int NOT NULL,
  "WidgetPriority" int NOT NULL DEFAULT 10,
  CONSTRAINT "fk_WidgetGroup_X_Widget_WGUID"
    FOREIGN KEY ("WidgetGroupUID") REFERENCES "wtkWidgetGroup"("UID"),
  CONSTRAINT "fk_WidgetGroup_X_Widget_UserUID"
    FOREIGN KEY ("UserUID") REFERENCES "wtkUsers"("UID"),
  CONSTRAINT "fk_WidgetGroup_X_Widget_WUID"
    FOREIGN KEY ("WidgetUID") REFERENCES "wtkWidget"("UID")
);
CREATE INDEX "ix_wtkWidgetGroup_X_Widget" ON "wtkWidgetGroup_X_Widget" ("UserUID","WidgetGroupUID","WidgetPriority","WidgetUID");
