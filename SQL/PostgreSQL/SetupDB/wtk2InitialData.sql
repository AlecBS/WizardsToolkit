INSERT INTO "wtkCompanySettings" ("CoName", "DomainName", "AppVersion", "EnableLockout")
  VALUES ('Your Company', 'https://your-company.com', '2.0.0', 'Y');

INSERT INTO "wtkUsers" ("DelDate","FirstName","LastName","Address","Title","Email","WebPassword","SecurityLevel","StaffRole","CanPrint","CanExport","FilePath","NewFileName","NewPassHash")
 VALUES (NULL,'Your','Name','Some Address', 'Admin', 'admin@email.com', NULL, 99, 'Tech', 'Y', 'Y',NULL,NULL,'needToSet'),
   (NOW(), 'Your', 'Server', 'web server', 'Internal Processing', 'server@yourdomain.com', NULL, 99, 'Tech', 'N', 'N',NULL,NULL,NULL);
UPDATE "wtkUsers" SET "UID" = 0 WHERE "UID" = 2;

INSERT INTO "wtkEcommerce" ("PaymentProvider", "EcomWebsite")
  VALUES ('Checkout.com','www.checkout.com');

INSERT INTO "wtkEmailTemplate" ("EmailCode", "AutomationOnly", "EmailType", "Subject", "EmailBody", "InternalNote") VALUES
  ('invite', 'Y', 'A', 'Welcome to @CompanyName@', '<p>Welcome to our website.</p>\r\n<p>Log in at @website@ using your email and password.</p>\r\n<p>If you do not know your password you can request a password reset on our website.</p>', 'this template is called from the User List by clicking the \"Send Invite\" button'),
  ('WelcomePIN', 'Y', 'A', 'Welcome to @CompanyName@', '<p>Welcome to our website.</p>\r\n<p>Your PIN is: <span style="font-family: ''Courier New'';"><b>@PIN@</b></span></p>\r\n<p>Thank you for joining our website!</p>', 'this template is called from PIN Registration process');

INSERT INTO "wtkReports" ("ViewOrder", "SecurityLevel", "TestMode", "HideFooter", "RptType", "RptName", "RptNotes", "URLredirect", "RptSelect", "RptSelectEnd", "SelTableName", "SelValueColumn", "SelDisplayColumn", "SelWhere", "AddLink", "EditLink",
    "AlignCenter", "AlignRight", "FieldSuppress", "SortableCols", "TotalCols", "TotalMoneyCols", "DaysAgo", "StartDatePrompt", "StartDateCol", "EndDatePrompt", "EndDateCol", "GraphRpt", "MenuName") VALUES
(10, 25, 'N', 'N', 'An', 'Page Views by User', 'This shows how many page views and logins each user has had.', NULL, 'SELECT CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",\r\n  COUNT(h."UID") AS "PageViews",\r\n  (SELECT COUNT(L."UID") FROM "wtkLoginLog" L WHERE  L."UserUID" = wu."UID") AS "Logins"\r\nFROM "wtkUsers" wu\r\n  INNER JOIN "wtkUserHistory" h ON h."UserUID" = wu."UID"\r\nWHERE wu."DelDate" IS NULL', 'GROUP BY wu."UID"\r\nORDER BY COUNT(h."UID") DESC, wu."FirstName" ASC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PageViews, Logins', NULL, NULL, 'PageViews, Logins', NULL, NULL, NULL, NULL, NULL, NULL, 'Y', NULL),
(20, 25, 'N', 'N', 'An', 'User Activity', 'This shows user activity on site.  It tracks logins, page views, report views and data updates.', NULL, 'SELECT CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",\r\n   (SELECT COUNT(L."UID") FROM "wtkLoginLog" L WHERE  L."UserUID" = wu."UID") AS "Logins",\r\n   COUNT(h."UID") AS "PageViews",\r\n   (SELECT COUNT(r."UID") FROM "wtkReportCntr" r WHERE  r."UserUID" = wu."UID") AS "ReportViews",\r\n   (SELECT COUNT(u."UID") FROM "wtkUpdateLog" u WHERE  u."UserUID" = wu."UID") AS "Updates"\r\n  FROM "wtkUsers" wu\r\nINNER JOIN "wtkUserHistory" h ON h."UserUID" = wu."UID"\r\n WHERE wu."DelDate" IS NULL', 'GROUP BY wu."UID"\r\nORDER BY COUNT(h."UID") DESC, wu."FirstName" ASC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PageViews, Logins, ReportViews, Updates', NULL, NULL, 'PageViews, Logins, ReportViews, Updates', NULL, NULL, NULL, NULL, NULL, NULL, 'Y', NULL),
(10, 0, 'N', 'N', 'Core', 'User Contact Information', 'Contact information for all users.', NULL, 'SELECT wu."UID",\r\n   CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",\r\n   L."LookupDisplay" AS "SecurityLevel",\r\n   "fncContactIcons"(wu."Email", wu."Phone",0,0,''Y'') AS "Contact"\r\n FROM "wtkUsers" wu\r\n  LEFT OUTER JOIN "wtkLookups" L\r\n   ON L."LookupType" = ''SecurityLevel'' AND L."LookupValue" = wu."SecurityLevel"\r\n WHERE wu."DelDate" IS NULL', 'ORDER BY wu."FirstName" ASC, wu."LastName" ASC', NULL, NULL, NULL, NULL, 'userEdit', 'userEdit', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL),
(20, 0, 'N', 'N', 'Core', 'User History Last Few Days', 'This is a demo for the Days Limit filter feature. It starts with showing page history for last @DaysPast@ days.', NULL, 'SELECT h."UID",\r\n  CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",\r\n  DATE_FORMAT(h."AddDate",''%c/%e/%Y at %l:%i %p'') AS "VisitDate",\r\n  h."PageURL", h."OtherUID" AS "PassedId", h."SecondsTaken"\r\n FROM "wtkUserHistory" h\r\nLEFT OUTER JOIN "wtkUsers" wu ON wu."UID" = h."UserUID"\r\nWHERE h."AddDate" >= DATE_SUB(NOW(), INTERVAL @DaysPast@ DAY)', 'ORDER BY h."UID" DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'PassedId,SecondsTaken', NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 'N', NULL),
(30, 0, 'N', 'N', 'Core', 'User History by Date Range', 'This is a demo for the Date Range filter feature.', NULL, 'SELECT h."UID",\r\n   CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",\r\n   DATE_FORMAT(h."AddDate",''%c/%e/%Y at %l:%i %p'') AS "VisitDate",\r\n   h."PageURL", h."OtherUID" AS "PassedId", h."SecondsTaken"\r\n  FROM "wtkUserHistory" h\r\n   LEFT OUTER JOIN "wtkUsers" wu ON wu."UID" = h."UserUID"\r\nWHERE h."AddDate" >= ''@StartDate@'' AND\r\n  DATE_FORMAT(h."AddDate",''%Y-%m-%d'') <= ''@EndDate@''', 'ORDER BY h."UID" DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'PassedId,SecondsTaken', NULL, NULL, NULL, NULL, NULL, NULL, 'Visit Date', 'h."AddDate"', 'Visit Date', 'h."AddDate"', 'N', NULL),
(40, 0, 'N', 'N', 'Core', 'External Report Demo', 'This uses the userList file as an external report as a demo that you can have any hand-coded report / page added to the report list.', '../admin/userList.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL);

INSERT INTO "wtkPages" ("PageName", "FileName", "Path")
  VALUES
('Settings', 'companyEdit', '/admin/'),
('Page List', 'pageList', '/admin/'),
('Menu Sets', 'menuSetList', '/admin/'),
('Login Log', 'loginLogList', '/admin/'),
('Update Logs', 'updateLogList', '/admin/'),
('Error Logs', 'errorLogList', '/admin/'),
('WTK Builder', 'wtkBuilder', '/admin/'),
('Clients', 'clientList', '/admin/'),
('Lookups', 'lookupList', '/admin/'),
('User History', 'userHistory', '/admin/'),
('Landing Pages', 'linkList', '/admin/'),
('Language', 'languageList', '/admin/'),
('My Profile', 'user', '/wtk/'),
('Report Wizard', 'reportList', '/admin/'),
('Users', 'userList', '/admin/'),
('Help', 'helpList', '/admin/'),
('Email Templates', 'emailTemplates', '/admin/'),
('Reports Viewer', 'reportViewer', '/wtk/'),
('Payroll', 'payrollList', NULL),
('User Edit', 'userEdit', '/wtk/'),
('Forums', 'forumList', '/wtk/'),
('Messages', 'messageList', '/wtk/'),
('Menu Groups', 'menuGroupList', '/admin/'),
('Menu Items', 'menuItemList', '/admin/'),
('Email History', 'emailHistory', '/admin/'),
('Chats', 'chatList', '/wtk/'),
('Access Fails', 'failedAttemptList', '/admin/'),
('Broadcast List', 'broadcastList', '/admin/'),
('Widget Groups', 'widgetGroupList', '/admin/'),
('Widgets', 'widgetList', '/admin/'),
('Revenue', 'revenueList', '/admin/'),
('Feedback', 'bugList', '/admin/'),
('Visitors', 'visitorStats', '/admin/'),
('Ecom Providers', 'ecomList', '/admin/'),
('Money Stats', 'moneyStats', '/admin/'),
('Affiliates', 'affiliateList', '/admin/'),
('Prospects', 'prospectList', '/admin/'),
('Prospect Staff', 'prospectStaffList', '/admin/'),
('CSV Importer', 'pickDataTable', '/admin/');

INSERT INTO "wtkMenuSets" ("MenuName", "Description")
  VALUES ('WTK-Admin', 'administration of Wizards Toolkit');

INSERT INTO "wtkMenuGroups" ("MenuUID", "GroupName", "GroupURL", "Priority")
  VALUES
  (1, 'Dashboard', 'dashboard', 10),
  (1, 'Client Control', NULL, 20),
  (1, 'Marketing', NULL, 30),
  (1, 'Site Management', NULL, 40),
  (1, 'View Logs', NULL, 50),
  (1, 'Logout', 'logout', 90);


INSERT INTO "wtkMenuItems" ("MenuGroupUID", "Priority", "PgUID")
  VALUES
  (2, 10, 1),
  (2, 20, 8),
  (2, 30, 16),
  (2, 40, 34),
  (2, 50, 28),
  (2, 60, 18),
  (2, 70, 19),
  (2, 80, 31),
  (3, 10, 36),
  (3, 20, 37),
  (3, 30, 33),
  (3, 40, 35),
  (4, 10, 7),
  (4, 20, 15),
  (4, 30, 30),
  (4, 40, 29),
  (4, 50, 10),
  (4, 60, 2),
  (4, 70, 3),
  (4, 80, 17),
  (4, 90, 13),
  (4, 100, 39),
  (5, 10, 4),
  (5, 20, 11),
  (5, 30, 5),
  (5, 40, 6),
  (5, 50, 27),
  (5, 60, 25),
  (5, 70, 32);

INSERT INTO "wtkLookups" ("LookupType", "LookupValue", "LookupDisplay")
 VALUES
   ('SecurityLevel', '1', 'Customer'),
   ('SecurityLevel', '30', 'Staff'),
   ('SecurityLevel', '80', 'Manager'),
   ('SecurityLevel', '95', 'Owner'),
   ('SecurityLevel', '99', 'Programmer'),
   ('StaffRole', 'Mgr', 'Manager'),
   ('StaffRole', 'Emp', 'Customer Service'),
   ('StaffRole', 'Tech', 'Tech Support'),
   ('FailCode','SQL','SQL Injection'),
   ('FailCode','Hash','PW Reset'),
   ('FailCode','DDOS','DDOS Attack?'),
   ('FailCode','Hack','Hacker Attempt'),
   ('PagePath', '/wtk/', 'WTK'),
   ('PagePath', '/admin/', 'Admin'),
   ('PagePath', NULL, 'Root'),
   ('RptType', 'An', 'Analytics'),
   ('RptType', 'Core', 'Core Info'),
   ('RptType', 'Money', 'Financials'),
   ('SelTableName', 'wtkUsers', 'wtkUsers'),
   ('SelTableName', 'wtkLookups', 'wtkLookups'),
   ('SelTableName', 'wtkEcommerce', 'wtkEcommerce'),
   ('SelValueColumn', 'UID', 'UID'),
   ('SelValueColumn', 'LookupValue', 'LookupValue'),
   ('YesNoUnknown', 'U', 'Unknown'),
   ('YesNoUnknown', 'Y', 'Yes'),
   ('YesNoUnknown', 'N', 'No'),
   ('Currency', 'USD', 'USD $'),
   ('Currency', 'MXN', 'Mexican Pesos'),
   ('Language', 'eng', 'English'),
   ('Language', 'esp', 'Espa&ntilde;ol'),
   ('AudienceType', 'All', 'All'),
   ('AudienceType', 'Cust', 'Customer'),
   ('AudienceType', 'Staf', 'Staff'),
   ('BroadcastColor', 'green', 'Green'),
   ('BroadcastColor', 'blue', 'Blue'),
   ('BroadcastColor', 'blue-grey', 'Blue-Grey'),
   ('BroadcastColor', 'deep-purple darken-1', 'Purple'),
   ('BroadcastColor', 'yellow', 'Yellow'),
   ('BroadcastColor', 'orange', 'Orange'),
   ('BroadcastColor', 'pink accent-1', 'Pink'),
   ('BroadcastColor', 'red darken-3', 'Red'),
   ('TextColor', '', 'Black'),
   ('TextColor', 'white-text', 'White'),
   ('TextColor', 'blue-text', 'Blue'),
   ('TextColor', 'red-text', 'Red'),
   ('emFreq', 'N', 'Never'),
   ('emFreq', 'M', 'Monthly'),
   ('emFreq', 'W', 'Weekly'),
   ('emFreq', 'D', 'Once Daily'),
   ('EmailType', 'A', 'Everyone'),
   ('EmailType', 'Af', 'Affiliate'),
   ('EmailType', 'P', 'Prospect'),
   ('EmailType', 'C', 'Customer'),
   ('EmailType', 'S', 'Staff'),
   ('EmailHTM', 'emailLight', 'emailLight'),
   ('EmailHTM', 'emailDark', 'emailDark'),
   ('USAstate', 'AL', 'Alabama'),
   ('USAstate', 'AK', 'Alaska'),
   ('USAstate', 'AZ', 'Arizona'),
   ('USAstate', 'AR', 'Arkansas'),
   ('USAstate', 'CA', 'California'),
   ('USAstate', 'CO', 'Colorado'),
   ('USAstate', 'CT', 'Connecticut'),
   ('USAstate', 'DE', 'Delaware'),
   ('USAstate', 'FL', 'Florida'),
   ('USAstate', 'GA', 'Georgia'),
   ('USAstate', 'HI', 'Hawaii'),
   ('USAstate', 'ID', 'Idaho'),
   ('USAstate', 'IL', 'Illinois'),
   ('USAstate', 'IN', 'Indiana'),
   ('USAstate', 'IA', 'Iowa'),
   ('USAstate', 'KS', 'Kansas'),
   ('USAstate', 'KY', 'Kentucky'),
   ('USAstate', 'LA', 'Louisiana'),
   ('USAstate', 'ME', 'Maine'),
   ('USAstate', 'MD', 'Maryland'),
   ('USAstate', 'MA', 'Massachusetts'),
   ('USAstate', 'MI', 'Michigan'),
   ('USAstate', 'MN', 'Minnesota'),
   ('USAstate', 'MS', 'Mississippi'),
   ('USAstate', 'MO', 'Missouri'),
   ('USAstate', 'MT', 'Montana'),
   ('USAstate', 'NE', 'Nebraska'),
   ('USAstate', 'NV', 'Nevada'),
   ('USAstate', 'NH', 'New Hampshire'),
   ('USAstate', 'NJ', 'New Jersey'),
   ('USAstate', 'NM', 'New Mexico'),
   ('USAstate', 'NY', 'New York'),
   ('USAstate', 'NC', 'North Carolina'),
   ('USAstate', 'ND', 'North Dakota'),
   ('USAstate', 'OH', 'Ohio'),
   ('USAstate', 'OK', 'Oklahoma'),
   ('USAstate', 'OR', 'Oregon'),
   ('USAstate', 'PA', 'Pennsylvania'),
   ('USAstate', 'RI', 'Rhode Island'),
   ('USAstate', 'SC', 'South Carolina'),
   ('USAstate', 'SD', 'South Dakota'),
   ('USAstate', 'TN', 'Tennessee'),
   ('USAstate', 'TX', 'Texas'),
   ('USAstate', 'UT', 'Utah'),
   ('USAstate', 'VT', 'Vermont'),
   ('USAstate', 'VA', 'Virginia'),
   ('USAstate', 'WA', 'Washington'),
   ('USAstate', 'WV', 'West Virginia'),
   ('USAstate', 'WI', 'Wisconsin'),
   ('USAstate', 'WY', 'Wyoming'),
   ('Canada', 'AB', 'Alberta'),
   ('Canada', 'BC', 'British Columbia'),
   ('Canada', 'MB', 'Manitoba'),
   ('Canada', 'NB', 'New Brunswick'),
   ('Canada', 'NL', 'Newfoundland and Labrador'),
   ('Canada', 'NT', 'Northwest Territories'),
   ('Canada', 'NS', 'Nova Scotia'),
   ('Canada', 'NU', 'Nunavut'),
   ('Canada', 'ON', 'Ontario'),
   ('Canada', 'PE', 'Prince Edward Island'),
   ('Canada', 'QC', 'Quebec'),
   ('Canada', 'SK', 'Saskatchewan'),
   ('Canada', 'YT', 'Yukon'),
   ('Country','AF','Afghanistan'),
   ('Country','AX','Aland Islands'),
    ('Country','AL','Albania'),
    ('Country','DZ','Algeria'),
    ('Country','AS','American Samoa'),
    ('Country','AD','Andorra'),
    ('Country','AO','Angola'),
    ('Country','AI','Anguilla'),
    ('Country','AQ','Antarctica'),
    ('Country','AG','Antigua and Barbuda'),
    ('Country','AR','Argentina'),
    ('Country','AM','Armenia'),
    ('Country','AW','Aruba'),
    ('Country','AU','Australia'),
    ('Country','AT','Austria'),
    ('Country','AZ','Azerbaijan'),
    ('Country','BS','Bahamas'),
    ('Country','BH','Bahrain'),
    ('Country','BD','Bangladesh'),
    ('Country','BB','Barbados'),
    ('Country','BY','Belarus'),
    ('Country','BE','Belgium'),
    ('Country','BZ','Belize'),
    ('Country','BJ','Benin'),
    ('Country','BM','Bermuda'),
    ('Country','BT','Bhutan'),
    ('Country','BO','Bolivia'),
    ('Country','BA','Bosnia and Herzegovina'),
    ('Country','BW','Botswana'),
    ('Country','BV','Bouvet Island'),
    ('Country','BR','Brazil'),
    ('Country','IO','British Indian Ocean Territory'),
    ('Country','BN','Brunei Darussalam'),
    ('Country','BG','Bulgaria'),
    ('Country','BF','Burkina Faso'),
    ('Country','BI','Burundi'),
    ('Country','KH','Cambodia'),
    ('Country','CM','Cameroon'),
    ('Country','CA','Canada'),
    ('Country','CV','Cape Verde'),
    ('Country','KY','Cayman Islands'),
    ('Country','CF','Central African Republic'),
    ('Country','TD','Chad'),
    ('Country','CL','Chile'),
    ('Country','CN','China'),
    ('Country','CX','Christmas Island'),
    ('Country','C','Cocos (Keeling) Islands'),
    ('Country','CO','Colombia'),
    ('Country','KM','Comoros'),
    ('Country','CG','Congo'),
    ('Country','CD','Congo, Democratic Republic of the'),
    ('Country','CK','Cook Islands'),
    ('Country','CR','Costa Rica'),
    ('Country','CI','Cote D"ivoire'),
    ('Country','HR','Croatia'),
    ('Country','CU','Cuba'),
    ('Country','CY','Cyprus'),
    ('Country','CZ','Czech Republic'),
    ('Country','DK','Denmark'),
    ('Country','DJ','Djibouti'),
    ('Country','DM','Dominica'),
    ('Country','DO','Dominican Republic'),
    ('Country','EC','Ecuador'),
    ('Country','EG','Egypt'),
    ('Country','SV','El Salvador'),
    ('Country','GQ','Equatorial Guinea'),
    ('Country','ER','Eritrea'),
    ('Country','EE','Estonia'),
    ('Country','ET','Ethiopia'),
    ('Country','K','Falkland Islands (Malvinas):'),
    ('Country','FO','Faroe Islands'),
    ('Country','FJ','Fiji'),
    ('Country','FI','Finland'),
    ('Country','FR','France'),
    ('Country','GF','French Guiana'),
    ('Country','PF','French Polynesia'),
    ('Country','TF','French Southern Territories'),
    ('Country','GA','Gabon'),
    ('Country','GM','Gambia'),
    ('Country','GE','Georgia'),
    ('Country','DE','Germany'),
    ('Country','GH','Ghana'),
    ('Country','GI','Gibraltar'),
    ('Country','GR','Greece'),
    ('Country','GL','Greenland'),
    ('Country','GD','Grenada'),
    ('Country','GP','Guadeloupe'),
    ('Country','GU','Guam'),
    ('Country','GT','Guatemala'),
    ('Country','GG','Guernsey'),
    ('Country','GN','Guinea'),
    ('Country','GW','Guinea-Bissau'),
    ('Country','GY','Guyana'),
    ('Country','HT','Haiti'),
    ('Country','HM','Heard Island and Mcdonald Islands'),
    ('Country','A','Holy See (Vatican City State):'),
    ('Country','HN','Honduras'),
    ('Country','HK','Hong Kong'),
    ('Country','HU','Hungary'),
    ('Country','IS','Iceland'),
    ('Country','IN','India'),
    ('Country','ID','Indonesia'),
    ('Country','IR','Iran'),
    ('Country','IQ','Iraq'),
    ('Country','IE','Ireland'),
    ('Country','IM','Isle of Man'),
    ('Country','IL','Israel'),
    ('Country','IT','Italy'),
    ('Country','JM','Jamaica'),
    ('Country','JP','Japan'),
    ('Country','JE','Jersey'),
    ('Country','JO','Jordan'),
    ('Country','KZ','Kazakhstan'),
    ('Country','KE','Kenya'),
    ('Country','KI','Kiribati'),
    ('Country','KP','Korea, Democratic Peoples Republic of'),
    ('Country','KR','Korea, Republic of'),
    ('Country','KW','Kuwait'),
    ('Country','KG','Kyrgyzstan'),
    ('Country','LA','Lao Peoples Democratic Republic'),
    ('Country','LV','Latvia'),
    ('Country','LB','Lebanon'),
    ('Country','LS','Lesotho'),
    ('Country','LR','Liberia'),
    ('Country','LY','Libyan Arab Jamahiriya'),
    ('Country','LI','Liechtenstein'),
    ('Country','LT','Lithuania'),
    ('Country','LU','Luxembourg'),
    ('Country','MO','Macao'),
    ('Country','MK','Macedonia, The Former Yugoslav Republic of'),
    ('Country','MG','Madagascar'),
    ('Country','MW','Malawi'),
    ('Country','MY','Malaysia'),
    ('Country','MV','Maldives'),
    ('Country','ML','Mali'),
    ('Country','MT','Malta'),
    ('Country','MH','Marshall Islands'),
    ('Country','MQ','Martinique'),
    ('Country','MR','Mauritania'),
    ('Country','MU','Mauritius'),
    ('Country','YT','Mayotte'),
    ('Country','MX','Mexico'),
    ('Country','FM','Micronesia, Federated States of'),
    ('Country','MD','Moldova, Republic of'),
    ('Country','MC','Monaco'),
    ('Country','MN','Mongolia'),
    ('Country','ME','Montenegro'),
    ('Country','MS','Montserrat'),
    ('Country','MA','Morocco'),
    ('Country','MZ','Mozambique'),
    ('Country','MM','Myanmar'),
    ('Country','NA','Namibia'),
    ('Country','NR','Nauru'),
    ('Country','NP','Nepal'),
    ('Country','NL','Netherlands'),
    ('Country','AN','Netherlands Antilles'),
    ('Country','NC','New Caledonia'),
    ('Country','NZ','New Zealand'),
    ('Country','NI','Nicaragua'),
    ('Country','NE','Niger'),
    ('Country','NG','Nigeria'),
    ('Country','NU','Niue'),
    ('Country','NF','Norfolk Island'),
    ('Country','MP','Northern Mariana Islands'),
    ('Country','NO','Norway'),
    ('Country','OM','Oman'),
    ('Country','PK','Pakistan'),
    ('Country','PW','Palau'),
    ('Country','PS','Palestinian Territory, Occupied'),
    ('Country','PA','Panama'),
    ('Country','PG','Papua New Guinea'),
    ('Country','PY','Paraguay'),
    ('Country','PE','Peru'),
    ('Country','PH','Philippines'),
    ('Country','PN','Pitcairn'),
    ('Country','PL','Poland'),
    ('Country','PT','Portugal'),
    ('Country','PR','Puerto Rico'),
    ('Country','QA','Qatar'),
    ('Country','RE','Reunion'),
    ('Country','RO','Romania'),
    ('Country','RU','Russian Federation'),
    ('Country','RW','Rwanda'),
    ('Country','BL','Saint Barthelemy'),
    ('Country','SH','Saint Helena'),
    ('Country','KN','Saint Kitts and Nevis'),
    ('Country','LC','Saint Lucia'),
    ('Country','MF','Saint Martin'),
    ('Country','PM','Saint Pierre and Miquelon'),
    ('Country','VC','Saint Vincent and the Grenadines'),
    ('Country','WS','Samoa'),
    ('Country','SM','San Marino'),
    ('Country','ST','Sao Tome and Principe'),
    ('Country','SA','Saudi Arabia'),
    ('Country','SN','Senegal'),
    ('Country','RS','Serbia'),
    ('Country','SC','Seychelles'),
    ('Country','SL','Sierra Leone'),
    ('Country','SG','Singapore'),
    ('Country','SK','Slovakia'),
    ('Country','SI','Slovenia'),
    ('Country','SB','Solomon Islands'),
    ('Country','SO','Somalia'),
    ('Country','ZA','South Africa'),
    ('Country','GS','South Georgia and South Sandwich Islands'),
    ('Country','ES','Spain'),
    ('Country','LK','Sri Lanka'),
    ('Country','SD','Sudan'),
    ('Country','SR','Suriname'),
    ('Country','SJ','Svalbard and Jan Mayen'),
    ('Country','SZ','Swaziland'),
    ('Country','SE','Sweden'),
    ('Country','CH','Switzerland'),
    ('Country','SY','Syrian Arab Republic'),
    ('Country','TW','Taiwan'),
    ('Country','TJ','Tajikistan'),
    ('Country','TZ','Tanzania, United Republic of'),
    ('Country','TH','Thailand'),
    ('Country','TL','Timor-Leste'),
    ('Country','TG','Togo'),
    ('Country','TK','Tokelau'),
    ('Country','TO','Tonga'),
    ('Country','TT','Trinidad and Tobago'),
    ('Country','TN','Tunisia'),
    ('Country','TR','Turkey'),
    ('Country','TM','Turkmenistan'),
    ('Country','TC','Turks and Caicos Islands'),
    ('Country','TV','Tuvalu'),
    ('Country','UG','Uganda'),
    ('Country','UA','Ukraine'),
    ('Country','AE','United Arab Emirates'),
    ('Country','GB','United Kingdom'),
    ('Country','US','United States'),
    ('Country','UM','United States Minor Outlying Islands'),
    ('Country','UY','Uruguay'),
    ('Country','UZ','Uzbekistan'),
    ('Country','VU','Vanuatu'),
    ('Country','VE','Venezuela'),
    ('Country','VN','Viet Nam'),
    ('Country','VG','Virgin Islands, British'),
    ('Country','VI','Virgin Islands, U.S.'),
    ('Country','WF','Wallis and Futuna'),
    ('Country','EH','Western Sahara'),
    ('Country','YE','Yemen'),
    ('Country','ZM','Zambia'),
    ('Country','ZW','Zimbabwe'),
    ('WidgetType','Count','Count'),
    ('WidgetType','Chart','Chart'),
    ('WidgetType','List','List'),
    ('WidgetType','Link','Link'),
    ('WidgetColor','info-gradient','Blue Gradient'),
    ('WidgetColor','success-gradient','Green Gradient'),
    ('WidgetColor','warning-gradient','Orange Gradient'),
    ('WidgetColor','red-gradient','Red Gradient'),
    ('WidgetColor','danger-gradient','Purple-Pink Gradient'),
    ('ChartType','Bar,Line','Bar and Line'),
    ('ChartType','Line','Line'),
    ('ChartType','Area','Area'),
    ('ChartType','Pie','Pie'),
    ('ChartType','Bar','Bar'),
    ('ChartType','All','All'),
    ('ProspectStatus', 'new', 'Not contacted'),
    ('ProspectStatus', 'email', 'Emailed'),
    ('ProspectStatus', 'reply', 'Replied'),
    ('ProspectStatus', 'close', 'Closed'),
    ('ProspectStatus', 'sold', 'Client');

INSERT INTO "wtkWidget" ("WidgetName","SecurityLevel","WidgetType","ChartType","WidgetColor","SkipFooter","WidgetDescription","WidgetSQL","WidgetURL","WindowModal")
 VALUES
	 ('Users by Security Level',30,'List','Bar',NULL,'Y',NULL,
'SELECT (SELECT L."LookupDisplay" FROM "wtkLookups" L
    WHERE L."LookupType" = ''SecurityLevel'' AND CAST(L."LookupValue" AS smallint) = u."SecurityLevel")
     AS "SecurityLevel",
 COUNT(u."UID") AS "Count"
 FROM "wtkUsers" u
  WHERE u."DelDate" IS NULL
 GROUP BY u."SecurityLevel"
ORDER BY u."SecurityLevel" ASC',NULL,'N'),
	 ('Last 10 Users',30,'List',NULL,NULL,'Y','Last 10 unique users that accessed website.',
'SELECT "User", "LastAccess", "Page" FROM get_recent_user_history()',NULL,'N'),
    ('New Users - 7 Days',30,'Count',NULL,'info-gradient',NULL,'how many users signed up in last 7 days',
'SELECT COUNT("UID")
FROM "wtkUsers"
WHERE "DelDate" IS NULL AND "AddDate" > (current_date - 7)',NULL,'N'),
   ('Revenue Today',90,'Count',NULL,'success-gradient',NULL,NULL,
'SELECT CONCAT(''$'', COALESCE(SUM("GrossAmount"),0)) AS "Income"
  FROM "wtkRevenue"
WHERE to_char("AddDate", ''YYYY-MM-DD'')= to_char(CURRENT_DATE, ''YYYY-MM-DD'')
  AND "PaymentStatus" = ''Authorized''',NULL,'N'),
   ('Revenue This Week',80,'Count',NULL,'info-gradient',NULL,'income from last 7 days',
'SELECT CONCAT(''$'', SUM("GrossAmount")) AS "Income"
  FROM "wtkRevenue"
WHERE "AddDate" > (CURRENT_DATE - 7)
  AND "PaymentStatus" = ''Authorized'';',NULL,'N'),
  ('Revenue This Month',80,'Count',NULL,'warning-gradient',NULL,'income from last 30 days',
'SELECT CONCAT(''&pound;'', SUM("GrossAmount")) AS "Income"
   FROM "wtkRevenue"
 WHERE "AddDate" > (CURRENT_DATE - 30)
   AND "PaymentStatus" = ''Authorized'';',NULL,'N'),
   ('Daily Income',90,'Chart','Line',NULL,NULL,'last 7 days income summaries',
'SELECT to_char("AddDate", ''Mon FMDDth (Dy)'') AS "Day" ,
    SUM(`GrossAmount`) AS `Income`
  FROM `wtkRevenue`
WHERE `PaymentStatus` = ''Authorized''
GROUP BY to_char("AddDate", ''J''),to_char("AddDate", ''Mon FMDDth (Dy)'')
ORDER BY to_char("AddDate", ''J'') DESC LIMIT 7',NULL,'N'),
    ('Revenue by Currency Type',90,'Chart','Pie',NULL,NULL,'income by currency code over last 7 days',
'SELECT `CurrencyCode`,
 SUM(`GrossAmount`) AS `Income`
FROM `wtkRevenue`
WHERE `PaymentStatus` = ''Authorized'' AND `AddDate` > (CURRENT_DATE - 7)
GROUP BY `CurrencyCode`
ORDER BY SUM(`GrossAmount`) DESC',NULL,'N');

-- Widgets for Tech Department
INSERT INTO "wtkWidget" ("WidgetName","SecurityLevel","WidgetType","ChartType","WidgetColor","SkipFooter","WidgetDescription","WidgetSQL","WidgetURL","WindowModal")
 VALUES
    ('Activity 1 Day',80,'List',NULL,NULL,'Y','Server access statistics','SELECT * FROM make_widget(''1'')',NULL,'N'),
    ('Activity 7 Days',80,'List',NULL,NULL,'Y','Server access statistics','SELECT * FROM make_widget(''7'')',NULL,'N'),
    ('Activity 30 Days',80,'List',NULL,NULL,'Y','Server access statistics','SELECT * FROM make_widget(''30'')',NULL,'N'),
    ('Update History',80,'Chart','Bar',NULL,'N','data updates during last 5 days',
'SELECT to_char("AddDate", ''Mon FMDDth (Dy)'') AS "Day" ,
    COUNT("UID") AS "Count"
  FROM "wtkUpdateLog"
GROUP BY to_char("AddDate", ''J''),to_char("AddDate", ''Mon FMDDth (Dy)'')
ORDER BY to_char("AddDate", ''J'') DESC LIMIT 5;',NULL,'N'),
	 ('Login History',80,'Chart','Bar',NULL,'N','login activity last 5 days',
'SELECT to_char("LastLogin", ''Mon FMDDth (Dy)'') AS "Day" ,
    COUNT("UID") AS "Count"
  FROM "wtkLoginLog"
where "LastLogin" IS NOT NULL
GROUP BY to_char("LastLogin", ''J''),to_char("LastLogin", ''Mon FMDDth (Dy)'')
ORDER BY to_char("LastLogin", ''J'') DESC LIMIT 5',NULL,'N'),
	 ('Page View History',80,'Chart','Bar',NULL,'N','page views during last 5 days',
'SELECT to_char("AddDate", ''Mon FMDDth (Dy)'') AS "Day" ,
    COUNT("UID") AS "Count"
  FROM "wtkUserHistory"
GROUP BY to_char("AddDate", ''J''),to_char("AddDate", ''Mon FMDDth (Dy)'')
ORDER BY to_char("AddDate", ''J'') DESC LIMIT 5;',NULL,'N'),
    ('Errors Today',99,'Count',NULL,'warning-gradient',NULL,'how many errors in last 24 hours',
'SELECT COUNT("UID")
   FROM "wtkErrorLog"
 WHERE "DelDate" IS NULL AND "AddDate" > (NOW() - INTERVAL ''1 DAY'')',NULL,'N'),
    ('Emails Sent',80,'Count',NULL,'info-gradient',NULL,'how many emails sent in last 24 hours',
'SELECT COUNT("UID")
 FROM "wtkEmailsSent"
WHERE "AddDate" > (NOW() - INTERVAL ''1 DAY'') ',NULL,'N'),
    ('Reports Viewed',80,'Count',NULL,'danger-gradient',NULL,'how many reports viewed in last 24 hours',
'SELECT COUNT("UID")
 FROM "wtkReportCntr"
WHERE "AddDate" > (NOW() - INTERVAL ''1 DAY'')',NULL,'N'),
    ('Active Users',30,'Count',NULL,'success-gradient',NULL,NULL,
'SELECT COUNT("UID") as "Count"
FROM "wtkUsers"
WHERE "DelDate" IS NULL',NULL,'N');
