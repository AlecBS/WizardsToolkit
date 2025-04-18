INSERT INTO "wtkCompanySettings" ("CoName", "DomainName", "AppVersion", "EnableLockout")
  VALUES ('Your Company', 'https://your-company.com', '1.0.0', 'Y');

INSERT INTO "wtkUsers" ("DelDate","FirstName","LastName","Address","Title","Email","WebPassword","SecurityLevel","StaffRole","CanPrint","CanExport","FilePath","NewFileName","NewPassHash")
 VALUES (NULL,'Your','Name','Some Address', 'Admin', 'admin@email.com', NULL, 99, 'Tech', 'Y', 'Y',NULL,NULL,'needToSet'),
   (NOW(), 'Your', 'Server', 'web server', 'Internal Processing', 'server@yourdomain.com', NULL, 99, 'Tech', 'N', 'N',NULL,NULL,NULL);
UPDATE "wtkUsers" SET "UID" = 0 WHERE "UID" = 2;

INSERT INTO "wtkEcommerce" ("PaymentProvider", "EcomWebsite")
  VALUES ('Checkout.com','www.checkout.com');

INSERT INTO "wtkEmailTemplate" ("EmailCode", "AutomationOnly", "EmailType", "Subject", "EmailBody", "InternalNote") VALUES
  ('invite', 'Y', 'A', 'Welcome to @CompanyName@', '<p>Welcome to our website.</p>
<p>Log in at @website@ using your email and password.</p>
<p>If you do not know your password you can request a password reset on our website.</p>', 'this template is called from the User List by clicking the "Send Invite" button'),
  ('WelcomePIN', 'Y', 'A', 'Welcome to @CompanyName@', '<p>Welcome to our website.</p>
<p>Your PIN is: <span style="font-family: ''Courier New'';"><b>@PIN@</b></span></p>
<p>Thank you for joining our website!</p>', 'this template is called from PIN Registration process');

INSERT INTO "wtkReports" ("ViewOrder", "SecurityLevel", "TestMode", "HideFooter", "RptType", "RptName", "RptNotes", "URLredirect", "RptSelect", "SelTableName", "SelValueColumn", "SelDisplayColumn", "SelWhere", "AddLink", "EditLink", "AlignCenter", "AlignRight", "FieldSuppress",
     "ChartSuppress", "SortableCols", "TotalCols", "TotalMoneyCols", "DaysAgo", "StartDatePrompt", "StartDateCol", "EndDatePrompt", "EndDateCol", "GraphRpt", "RegRpt", "BarChart", "LineChart", "AreaChart", "PieChart")
 VALUES
(10, 80, 'N', 'N', 'An', 'User Activity', 'This shows user activity on site.  It tracks logins, page views, report views and data updates.', NULL,
'SELECT CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",
  L."LookupDisplay" AS "StaffRole",
   (SELECT COUNT(L."UID") FROM "wtkLoginLog" L WHERE  L."UserUID" = wu."UID") AS "Logins",
   COUNT(h."UID") AS "PageViews",
   (SELECT COUNT(r."UID") FROM "wtkReportCntr" r WHERE  r."UserUID" = wu."UID") AS "ReportViews",
   (SELECT COUNT(u."UID") FROM "wtkUpdateLog" u WHERE  u."UserUID" = wu."UID") AS "Updates"
  FROM "wtkUsers" wu
    INNER JOIN "wtkUserHistory" h ON h."UserUID" = wu."UID"
    INNER JOIN "wtkLookups" L ON L."LookupType" = ''StaffRole'' AND L."LookupValue" = wu."StaffRole"
 WHERE wu."DelDate" IS NULL
GROUP BY wu."UID", L."LookupDisplay"
ORDER BY COUNT(h."UID") DESC, wu."FirstName" ASC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PageViews, Logins, ReportViews, Updates', NULL, 'StaffRole', NULL, 'PageViews, Logins, ReportViews, Updates', NULL, NULL, NULL, NULL, NULL, NULL, 'Y', 'Y', 'Y', NULL, 'Y', 'Y'),
(20, 80, 'N', 'N', 'An', 'Page Views by User', 'This shows how many page views and logins each user has had.', NULL,
'SELECT CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",
  COUNT(h."UID") AS "PageViews",
    (SELECT COUNT(L."UID") FROM "wtkLoginLog" L WHERE  L."UserUID" = wu."UID") AS "Logins"
    FROM "wtkUsers" wu INNER JOIN "wtkUserHistory" h ON h."UserUID" = wu."UID" WHERE wu."DelDate" IS NULL
GROUP BY wu."UID"
ORDER BY COUNT(h."UID") DESC, wu."FirstName" ASC', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PageViews, Logins', NULL, NULL, NULL, 'PageViews, Logins', NULL, NULL, NULL, NULL, NULL, NULL, 'Y', NULL, 'Y', 'Y', NULL, NULL),
(10, 1, 'N', 'N', 'Core', 'User Contact Information', 'Contact information for all users.', NULL, 'SELECT wu."UID",
   CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",
   L."LookupDisplay" AS "SecurityLevel",
   "fncContactIcons"(wu."Email", wu."Phone",0,0,''Y'',wu."UID",wu."SMSEnabled",''Y'','''') AS "Contact"
 FROM "wtkUsers" wu
  LEFT OUTER JOIN "wtkLookups" L
   ON L."LookupType" = ''SecurityLevel'' AND CAST(L."LookupValue" AS smallint) = wu."SecurityLevel"
 WHERE wu."DelDate" IS NULL
 ORDER BY wu."FirstName" ASC, wu."LastName" ASC', NULL, NULL, NULL, NULL, 'userEdit', 'userEdit', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL),
(20, 99, 'N', 'N', 'Core', 'User History Last Few Days', 'This is a demo for the Days Limit filter feature. It starts with showing page history for last @DaysPast@ days.', NULL, 'SELECT h."UID",
  CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",
  to_char(h."AddDate",''MM/DD/YYYY "at" HH12:MI AM'') AS "VisitDate",
  h."PageURL", h."OtherUID" AS "PassedId", h."SecondsTaken"
 FROM "wtkUserHistory" h
LEFT OUTER JOIN "wtkUsers" wu ON wu."UID" = h."UserUID"
WHERE h."AddDate" >= (CURRENT_TIMESTAMP - INTERVAL ''@DaysPast@ DAYS'')
ORDER BY h."UID" DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'PassedId,SecondsTaken', NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL),
(30, 80, 'N', 'N', 'Core', 'User History with Date Range', 'Website page history of users.', NULL, 'SELECT h."UID",
   CONCAT(wu."FirstName", '' '', COALESCE(wu."LastName",'''')) AS "User",
   to_char(h."AddDate",''MM/DD/YYYY "at" HH12:MI AM'') AS "VisitDate",
   h."PageURL", h."OtherUID" AS "PassedId", h."SecondsTaken"
  FROM "wtkUserHistory" h
   LEFT OUTER JOIN "wtkUsers" wu ON wu."UID" = h."UserUID"
WHERE h."AddDate" >= ''@StartDate@'' AND
  to_char(h."AddDate", ''YYYY-MM-DD'') <= ''@EndDate@''
  AND h."UserUID" = @RptFilter@
ORDER BY h."UID" DESC', 'wtkUsers', 'UID', 'FirstName', '"DelDate" IS NULL', NULL, NULL, 'PassedId,SecondsTaken', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Visit Date', NULL, 'Visit Date', NULL, 'N', NULL, NULL, NULL, NULL, NULL),
(10, 80, 'N', 'N', 'Money', 'Money Stats', 'This uses the moneyStats file as an external report as a demo that you can have any hand-coded report / page added to the report list.', '/admin/moneyStats.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL),
(20, 80, 'N', 'Y', 'Money', 'Payments by Ecommerce Provider', NULL, NULL, 'SELECT
 CONCAT(''<a onclick="JavaScript:rpt('', 8, '','' , e."UID", '')">'', e."PaymentProvider",''</a>'') AS "PaymentProvider",
  COUNT(r."UID") AS "Count",
    CONCAT(''$'', to_char(SUM(r."GrossAmount"),''99G999D99'')) AS "Amount"
  FROM "wtkRevenue" r
    INNER JOIN "wtkEcommerce" e ON e."UID" = r."EcomUID"
WHERE r."AddDate" >= ''@StartDate@'' AND
  to_char(r."AddDate", ''YYYY-MM-DD'') <= ''@EndDate@''
GROUP BY e."PaymentProvider", e."UID"
ORDER BY e."PaymentProvider" DESC', NULL, NULL, NULL, NULL, NULL, NULL, 'Count', 'Amount', NULL, NULL, NULL, 'Count', 'Amount', NULL, 'Purchase Date', NULL, 'Purchase Date', NULL, 'N', NULL, NULL, NULL, NULL, NULL),
(30, 80, 'N', 'N', 'Money', 'Revenue List', NULL, NULL,
'SELECT r."UID", to_char(r."AddDate",''MM/DD/YYYY "at" HH12:MI AM'') AS "AddDate",
    CONCAT(''<a onclick="JavaScript:ajaxGo(''''/wtk/userEdit'''','',r."UserUID",'')">'',
       COALESCE(u."FirstName",''''), '' '', COALESCE(u."LastName",''''),''</a><br>'',u."Email") AS "Buyer",
    CONCAT(''<a class="btn-floating" onclick="JavaScript:rpt(22,'',r."UserUID",'')">'',
           ''<i class="material-icons">format_list_numbered</i></a>'',
           ''<a onclick="JavaScript:ajaxGo(''''/admin/userLogins'''',0,'',
               r."UserUID",'');" class="btn btn-floating btn-small">'',
               ''<i class="material-icons" alt="Click to User Logins" title="Click to User Logins">beenhere</i></a>''
       ) AS "Reports",
    CONCAT(''<a onclick="JavaScript:wtkModal(''''/admin/ecomEdit'''',''''MODAL'''','',r."EcomUID",'')">'',
       e."PaymentProvider",''</a>'') AS "PaymentProvider",
    r."PaymentStatus",
    CASE WHEN r."CurrencyCode" = ''USD'' THEN ''''
      ELSE CONCAT(''<a target="_blank" href="https://www.xe.com/currencyconverter/convert/?Amount='',
            r."GrossAmount",''&From='',r."CurrencyCode",''&To=USD">'',r."GrossAmount",''</a>'')
    END AS "GrossAmount",
    r."MerchantFee", r."CurrencyCode"
FROM "wtkRevenue" r
  INNER JOIN "wtkEcommerce" e ON e."UID" = r."EcomUID"
  INNER JOIN "wtkUsers" u ON u."UID" = r."UserUID"
WHERE r."EcomUID" = @RptFilter@
  AND r."AddDate" >= ''@StartDate@'' AND
  to_char(r."AddDate", ''YYYY-MM-DD'') <= ''@EndDate@''
ORDER BY r."UID" DESC', 'wtkEcommerce', 'UID', 'PaymentProvider', '"DelDate" IS NULL', NULL, NULL, 'CurrencyCode', 'GrossAmount,MerchantFee', NULL, NULL, NULL, NULL, NULL, NULL, 'Purchase Date', NULL, 'Purchase Date', NULL, 'N', NULL, NULL, NULL, NULL, NULL),
(40, 80, 'N', 'Y', 'Money', 'Revenue Monthly', 'Income comparison for last 6 months', NULL, 'SELECT to_char("AddDate",''MM'') AS "Month" ,
    CONCAT(''$'', to_char(SUM("GrossAmount"),''99G999D99'')) AS "TotalIncome"
  FROM "wtkRevenue"
GROUP BY to_char("AddDate",''YYYY-MM''), "AddDate"
ORDER BY to_char("AddDate",''YYYY-MM'') DESC LIMIT 6', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TotalIncome', NULL, NULL, NULL, NULL, 'TotalIncome', NULL, NULL, NULL, NULL, NULL, 'Y', 'Y', 'Y', 'N', 'Y', 'N'),
(50, 80, 'N', 'Y', 'Money', 'Revenue Yearly', 'Income comparison for last 5 years', NULL, 'SELECT to_char("AddDate",''YYYY'') AS "Year",
    CONCAT(''$'',to_char(SUM("GrossAmount"),''99G999D99'')) AS "TotalIncome"
  FROM "wtkRevenue"
GROUP BY to_char("AddDate",''YYYY''), "AddDate"
ORDER BY to_char("AddDate",''YYYY'') DESC LIMIT 5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'TotalIncome', NULL, NULL, NULL, NULL, 'TotalIncome', NULL, NULL, NULL, NULL, NULL, 'Y', 'Y', 'Y', NULL, 'Y', NULL);


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
  ('Ad Tracking', 'adList', '/admin/'),
  ('Lookups', 'lookupList', '/admin/'),
  ('User History', 'userHistory', '/admin/'),
  ('Landing Pages', 'linkList', '/admin/'),
  ('Language', 'languageList', '/admin/'),
  ('My Profile', 'user', '/wtk/'),
  ('Report Wizard', 'reportList', '/admin/'),
  ('Users', 'userList', '/admin/'),
  ('Help', 'helpList', '/admin/'),
  ('Emails', 'emailTemplates', '/admin/'),
  ('Reports Viewer', 'reportViewer', '/wtk/'),
  ('Polls', 'polls', '/admin/'),
  ('User Edit', 'userEdit', '/wtk/'),
  ('Forums', 'forumList', '/wtk/'),
  ('Messages', 'messageList', '/wtk/'),
  ('Menu Groups', 'menuGroupList', '/admin/'),
  ('Emails Sent', 'emailHistory', '/admin/'),
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
  ('CSV Importer', 'pickDataTable', '/admin/'),
  ('History', 'moneyHistory', '/admin/'),
  ('Downloads', 'downloadList', '/admin/');
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

INSERT INTO "wtkMenuItems" ("MenuGroupUID", "ShowDividerAbove", "PgUID")
  VALUES
  (2, 'N', 16),
  (2, 'N', 8),
  (2, 'Y', 34),
  (2, 'N', 28),
  (2, 'N', 19),
  (2, 'Y', 1),
  (3, 'N', 18),
  (3, 'N', 36),
  (3, 'N', 37),
  (3, 'N', 33),
  (3, 'Y', 31),
  (3, 'N', 35),
  (3, 'N', 40),
  (4, 'N', 7),
  (4, 'N', 15),
  (4, 'N', 30),
  (4, 'N', 29),
  (4, 'Y', 2),
  (4, 'N', 3),
  (4, 'Y', 10),
  (4, 'N', 17),
  (4, 'N', 13),
  (4, 'Y', 39),
  (5, 'N', 4),
  (5, 'N', 11),
  (5, 'N', 5),
  (5, 'Y', 6),
  (5, 'N', 27),
  (5, 'Y', 25),
  (5, 'N', 32);

INSERT INTO "wtkHelp" ("HelpIndex", "HelpTitle", "HelpText")
  VALUES
  ('reportEdit.php', 'SQL Report Wizard',
'<h4>Filtering @Tokens@</h4>
<p>On this page you will see which tokens can be added within your SQL SELECT or WHERE and they will be automatically replaced by data and passed parameters.</p>
<p>For example, if you put in something like:<br>
<code>WHERE `UserUID` = @UserUID@</code><br>
that will automatically replace the @UserUID@ with the currently logged in user''s UID (wtkUsers.UID).
This can be very useful if you want to create a report that only shows a user data related to their account.</p>
<br>
<h4>Sorting Functionality</h4>
<p>Each column that you want to sort should be on a separate line in the "Sorting" box.  This function can take 1, 2 or 3 parameters.
Note, as usual spaces will be automatically be inserted for WordCaps or snake_case.  For example, ''FirstName'' will be changed
to ''First Name''.</p>
<br>
<h5>One Parameter</h5>
<code>Count</code><br>
<p>This uses column named `Count` and leaves headers as "Count" and sorts by this column.</p>
<br><h5>Two Parameters</h5>
<code>LookupDisplay, USA State</code><br>
<p>This uses column named `LookupDisplay` but shows the header as "USA State".  It sorts by the `LookupDisplay` column.</p>
<br><h5>Three Parameters</h5>
<code>DOB, Birthday, u.`BirthDate`</code><br>
<p>This uses column named `DOB` but shows the header as "Birthday".  It sorts using u.`BirthDate` column. This is really important when formatting causes problem with sort order.
 For example if your date format is ''Mon DD, YYYY '' then sorting by that would not give the results you want.</p>
<br><h4>Example</h4>
<p>Here is an example SQL query and the associated Sort Options.</p>
<code>SELECT p.`UID`, u.`FirstName` AS `Owner`, p.`PetName`, p.`City`, to_char(p.`BirthDate`,''Mon DD, YYYY '') AS `DOB`<br>
  FROM `pets` p<br>
  INNER JOIN `wtkUsers` u ON u.`UID` = p.`UserUID`
</code><br><br>
<h5>Sortable Columns</h5><code>Owner<br>City, Town<br>DOB, Birthday, p.`BirthDate`</code>');

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
   ('ClientStatus', 'T', 'Trial'),
   ('ClientStatus', 'A', 'Active'),
   ('ClientStatus', 'I', 'Inactive'),
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
   ('LangPref', 'eng', 'English'),
   ('LangPref', 'esp', 'Espa&ntilde;ol'),
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
    ('Country','CI','Cote D`ivoire'),
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

INSERT INTO "wtkWidgetGroup" ("WidgetGroupName", "StaffRole", "SecurityLevel", "UseForDefault")
 VALUES ('DevOps', 'Tech', 99, 'Y'),
        ('Personal - leave blank, autofilled','Emp', 1, 'N'),
        ('Marketing','Mgr', 30, 'Y');

UPDATE "wtkWidgetGroup" SET "UID" = ("UID" - 1) WHERE "UID" < 4;

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
WHERE to_char("AddDate", ''YYYY-MM-DD'') = to_char(CURRENT_DATE, ''YYYY-MM-DD'')
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
 ('Active Users',30,'Count',NULL,'success-gradient',NULL,NULL,
'SELECT COUNT("UID") as "Count"
FROM "wtkUsers"
WHERE "DelDate" IS NULL',NULL,'N'),
    ('Emails Sent',80,'Count',NULL,'info-gradient',NULL,'how many emails sent in last 24 hours',
'SELECT COUNT("UID")
 FROM "wtkEmailsSent"
WHERE "AddDate" > (NOW() - INTERVAL ''1 DAY'') ',NULL,'N'),
    ('Reports Viewed',80,'Count',NULL,'danger-gradient',NULL,'how many reports viewed in last 24 hours',
'SELECT COUNT("UID")
 FROM "wtkReportCntr"
WHERE "AddDate" > (NOW() - INTERVAL ''1 DAY'')',NULL,'N'),
('Last 5 Users', 30, 'List', NULL, NULL, 'Y', 'Last 5 unique users that accessed website.',
'SELECT wu."FirstName" AS "User",
    to_char(h."AddDate", ''Mon DD, YYYY at FMHH:MI am'') AS "LastAccess",
    h."PageURL"
   FROM "wtkUserHistory" h
     INNER JOIN (
         SELECT MAX("UID") AS "MaxUID"
           FROM "wtkUserHistory"
         GROUP BY "UserUID") latest ON h."UID" = latest."MaxUID"
     INNER JOIN "wtkUsers" wu ON wu."UID" = h."UserUID"
     WHERE wu."DelDate" IS NULL
     ORDER BY h."UID" DESC LIMIT 5', NULL, 'N'),
('Weekly Income', 80, 'Chart', 'Area', NULL, NULL, 'weekly income summaries',
'SELECT to_char(DATE_TRUNC(''week'', "AddDate") + INTERVAL ''6 days'', ''Mon DD'') AS "WeekEnding",
   COALESCE(SUM("GrossAmount"), 0) AS "Income"
FROM "wtkRevenue"
WHERE "PaymentStatus" IN (''Paid'', ''Authorized'')
GROUP BY DATE_TRUNC(''week'', "AddDate")
ORDER BY DATE_TRUNC(''week'', "AddDate") DESC', '/admin/moneyStats', 'N'),
('Unique Visitors', 80, 'Count', NULL, 'info-gradient', NULL, 'visitors to marketing site',
'SELECT COUNT(DISTINCT("IPaddress")) as "Count"
FROM "wtkVisitors"
WHERE "AddDate" > (NOW() - INTERVAL ''7 DAY'')', '/admin/visitorStats', 'N'),
('Affiliates', 80, 'Count', NULL, 'success-gradient', NULL, NULL,
'SELECT COUNT(*) FROM "wtkAffiliates" WHERE "DelDate" IS NULL', '/admin/affiliateList', 'N'),
('Prospects', 80, 'Count', NULL, 'info-gradient', NULL, NULL,
'SELECT COUNT(*) FROM "wtkProspects" WHERE "DelDate" IS NULL', '/admin/prospectList', 'N');

INSERT INTO "wtkWidgetGroup_X_Widget" ("WidgetGroupUID", "WidgetUID", "WidgetPriority")
 VALUES
(0, 15, 10),
(0, 16, 20),
(0, 17, 30),
(0, 18, 40),
(0, 13, 50),
(0, 1, 60),
(0, 12, 70),
(0, 9, 80),
(0, 10, 90),
(0, 11, 100),
(0, 14, 110),
(2, 4, 10),
(2, 5, 20),
(2, 6, 30),
(2, 21, 40),
(2, 22, 50),
(2, 23, 60),
(2, 16, 70),
(2, 17, 80),
(2, 8, 90),
(2, 7, 100),
(2, 20, 110);

/* Example Data for initial CRON Job Testing */
INSERT INTO "wtkBackgroundActions" ("TriggerTime", "ActionType", "ForUserUID", "Param1UID", "Param2UID", "Param1Str", "Param2Str")
 VALUES (NOW(), 'Thank4Order', '1', NULL, NULL, 'SKU123', 'support');
