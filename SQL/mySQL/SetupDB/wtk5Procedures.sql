-- USE wiztools;

-- MySQL version of PROCEDUREs
DELIMITER $$

CREATE PROCEDURE `st_LanguageSwap`(
    IN  fncLang         char(3),
    IN  fncPrimaryText  varchar(120),
    OUT fncNewText      varchar(240)
  )
  BEGIN
  -- find language equivalent for passed text
    DECLARE fncCount INT;
    SELECT COUNT(*) INTO fncCount FROM `wtkLanguage` WHERE `PrimaryText` = fncPrimaryText AND `Language` = fncLang AND `NewText` IS NOT NULL;

    IF (fncCount > 0) THEN
        SELECT `NewText` INTO fncNewText FROM `wtkLanguage` WHERE `PrimaryText` = fncPrimaryText AND `Language` = fncLang;
    ELSE
        SET fncNewText = fncPrimaryText;
        IF ((SELECT COUNT(*) FROM `wtkLanguage` WHERE `PrimaryText` = fncPrimaryText AND `Language` = fncLang AND `NewText` IS NULL ) = 0) THEN
            INSERT INTO `wtkLanguage` (`Language`, `PrimaryText`) VALUES ( fncLang, fncPrimaryText);
        END IF;
    END IF;
  END;
$$

CREATE PROCEDURE `st_Replicate`(
     IN fncTableName    varchar(40),
     IN fncTableUID		int unsigned
  )
  BEGIN
   DECLARE fncCount INT;
 /*
   If row does not exist in table, add it.
   If row does exist in table and ReplicateStatus is = 2, then set to 0.
   If row does exist in table and ReplicateStatus is < 2, then do nothing.
 */
    SELECT COUNT(*) INTO fncCount
      FROM `wtkReplicate`
    WHERE `TableName` = fncTableName AND `TableUID` = fncTableUID ;

    IF (fncCount = 0) THEN
        INSERT INTO `wtkReplicate` (`TableName`, `TableUID`)
           VALUES (fncTableName, fncTableUID);
    ELSE
        SELECT COUNT(*) INTO fncCount
          FROM `wtkReplicate`
        WHERE `TableName` = fncTableName AND `TableUID` = fncTableUID
            AND `ReplicateStatus` = 2;

        IF (fncCount = 1) THEN
            UPDATE `wtkReplicate`
              SET `ReplicateStatus` = 0
            WHERE `TableName` = fncTableName AND `TableUID` = fncTableUID ;
        END IF;
    END IF;
  END
$$

CREATE PROCEDURE `st_LogAccess`(
     IN fncTableName    varchar(60),
     IN fncAction		char(3)
  )
  BEGIN
    INSERT INTO `wtkTableTracking` (`TableName`, `Action`)
       VALUES (fncTableName, fncAction);
  END
$$

-- Usage: call make_widget(1);
-- Used to create widgets showing summary statistics
CREATE PROCEDURE make_widget(fncDays SMALLINT)
BEGIN
    DECLARE fncuid INT DEFAULT 0;

    CREATE TEMPORARY TABLE IF NOT EXISTS tmpResults (
        UID       INT AUTO_INCREMENT PRIMARY KEY,
        tDescrip  VARCHAR(40),
        tCount    INT
    );

    DELETE FROM tmpResults;

    -- Number of Users logged in, Page Views, Updates, Reports Viewed
    INSERT INTO tmpResults (tDescrip, tCount)
        SELECT 'Number of Users logged in', COUNT(DISTINCT UserUID)
        FROM wtkLoginLog
        WHERE LastLogin > (NOW() - INTERVAL fncDays DAY);

    SELECT UID INTO fncuid
    FROM wtkUpdateLog
    WHERE AddDate < (NOW() - INTERVAL fncDays DAY)
    ORDER BY UID DESC
    LIMIT 1;

    INSERT INTO tmpResults (tDescrip, tCount)
        SELECT 'Data Updates', COUNT(UID)
        FROM wtkUpdateLog
        WHERE UID > fncuid;

    SELECT UID INTO fncuid
    FROM wtkReportCntr
    WHERE AddDate < (NOW() - INTERVAL fncDays DAY)
    ORDER BY UID DESC
    LIMIT 1;

    INSERT INTO tmpResults (tDescrip, tCount)
        SELECT 'Reports Viewed', COUNT(UID)
        FROM wtkReportCntr
        WHERE UID > fncuid;

    INSERT INTO tmpResults (tDescrip, tCount)
        SELECT 'Page Views', COUNT(UID)
        FROM wtkUserHistory
        WHERE AddDate > (NOW() - INTERVAL fncDays DAY);

    SELECT tDescrip AS `Description`, tCount AS `Count`
     FROM tmpResults
    ORDER BY UID ASC;

    -- Clean up the temporary table
    DROP TEMPORARY TABLE IF EXISTS tmpResults;
END
$$

CREATE PROCEDURE get_recent_user_history()
-- Calling method: CALL get_recent_user_history();
BEGIN
    CREATE TEMPORARY TABLE IF NOT EXISTS recent_history_ids (
        MaxUID INT
    );
    DELETE FROM recent_history_ids;

    INSERT INTO recent_history_ids (MaxUID)
    SELECT MAX(UID) AS MaxUID
      FROM wtkUserHistory
    WHERE UserUID IS NOT NULL
    GROUP BY UserUID
    ORDER BY MaxUID DESC
    LIMIT 10;

    SELECT CONCAT(u.FirstName, ' ', COALESCE(u.LastName, ''),
                  '<br>', L.LookupDisplay) AS `User`,
           DATE_FORMAT(h.AddDate, '%M %e, %Y at %H:%i') AS LastAccess,
           h.PageURL AS Page
    FROM recent_history_ids r
    INNER JOIN wtkUserHistory h ON h.UID = r.MaxUID
    INNER JOIN wtkUsers u ON u.UID = h.UserUID
    INNER JOIN wtkLookups L ON L.LookupType = 'SecurityLevel'
                            AND CAST(L.LookupValue AS DECIMAL) = u.SecurityLevel
    ORDER BY r.MaxUID DESC;

    DROP TABLE recent_history_ids;
END
$$

DELIMITER ;
