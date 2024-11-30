-- How to delete a user by first deleting all data in other associated tables
-- for multiple users at a time
DELETE FROM `wtkEmailsSent`
WHERE `SendByUserUID` IN (184,161,163)
   OR `SendToUserUID` IN (184,161,163);

DELETE FROM `wtkLoginLog` WHERE `UserUID` IN (184,161,163);
DELETE FROM `wtkWidgetGroup_X_Widget` WHERE `UserUID` IN (184,161,163);
DELETE FROM `wtkUserHistory` WHERE `UserUID` IN (184,161,163);
DELETE FROM `wtkChat` WHERE `SendByUserUID` IN (184,161,163)
   OR `SendToUserUID` IN (184,161,163);

DELETE FROM `wtkForumMsgs` WHERE `UserUID` IN (184,161,163);
DELETE FROM `wtkForum` WHERE `CreatedByUserUID` IN (184,161,163);
-- If above has problems, look up FormUID and determine if can delete those wtkForm rows like:
    -- DELETE FROM `wtkForumMsgs` WHERE `ForumUID` IN (21,22,23);

-- DELETE FROM `wtkRevenueDemo` WHERE `UserUID` IN (184,161,163);

DELETE FROM `wtkReportCntr` WHERE `UserUID` IN (184,161,163);
DELETE FROM `wtkUpdateLog` WHERE `UserUID` IN (184,161,163);
DELETE FROM `wtkBugReport` WHERE `CreatedByUserUID` IN (184,161,163);
DELETE FROM `wtkErrorLog` WHERE `UserUID` IN (184,161,163);
DELETE FROM `wtkUsers` WHERE `UID` IN (184,161,163);

-- How to delete a user by first deleting all data in other associated tables
-- for single user
DELETE FROM `wtkEmailsSent`
WHERE `SendByUserUID` = 58
   OR `SendToUserUID` = 58;
DELETE FROM `wtkLoginLog` WHERE `UserUID` = 58;
DELETE FROM `wtkUserHistory` WHERE `UserUID` = 58;
DELETE FROM `wtkErrorLog` WHERE `UserUID` = 58;
DELETE FROM `wtkUsers` WHERE `UID` = 58;
