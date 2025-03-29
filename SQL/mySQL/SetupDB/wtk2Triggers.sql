-- USE wiztools;

-- MySQL version of Triggers
DROP TRIGGER IF EXISTS `tia_wtkForumMsgs`;
DROP TRIGGER IF EXISTS `tib_wtkBlog`;
DROP TRIGGER IF EXISTS `tub_wtkBlog`;
DROP TRIGGER IF EXISTS `tib_wtkWidgetGroup_X_Widget`;

DELIMITER $$

CREATE TRIGGER `tia_wtkForumMsgs`
    AFTER INSERT ON `wtkForumMsgs`
    FOR EACH ROW
  BEGIN
    UPDATE `wtkForum`
      SET `LastEditDate` = NOW()
     WHERE `UID` = NEW.`ForumUID`;
  END
$$

CREATE TRIGGER `tib_wtkBlog`
    BEFORE INSERT ON `wtkBlog`
    FOR EACH ROW
  BEGIN
    IF (NEW.`MakePublic` = 'Y') THEN
        SET NEW.`PublishDate` = NOW();
    END IF;
  END
$$

CREATE TRIGGER `tub_wtkBlog`
    BEFORE UPDATE ON `wtkBlog`
    FOR EACH ROW
  BEGIN
    IF (OLD.`MakePublic` = 'N') THEN
        IF (NEW.`MakePublic` = 'Y') THEN
            IF (OLD.`PublishDate` IS NULL) THEN
                SET NEW.`PublishDate` = NOW();
            END IF;
        END IF;
    END IF;
  END
$$

CREATE TRIGGER `tib_wtkMenuGroups`
    BEFORE INSERT ON `wtkMenuGroups`
    FOR EACH ROW
  BEGIN
    DECLARE fncLastPriority SMALLINT;

    SELECT COUNT(*) INTO fncLastPriority
      FROM `wtkMenuGroups`
    WHERE `MenuUID` = NEW.`MenuUID`;

    IF (fncLastPriority > 0) THEN
        SELECT `Priority` INTO fncLastPriority
          FROM `wtkMenuGroups`
        WHERE `MenuUID` = NEW.`MenuUID`
        ORDER BY `Priority` DESC LIMIT 1;
    END IF;
    SET NEW.`Priority` = (fncLastPriority + 10);
END
$$

CREATE TRIGGER `tib_wtkMenuItems`
    BEFORE INSERT ON `wtkMenuItems`
    FOR EACH ROW
  BEGIN
    DECLARE fncLastPriority SMALLINT;

    SELECT COUNT(*) INTO fncLastPriority
      FROM `wtkMenuItems`
    WHERE `MenuGroupUID` = NEW.`MenuGroupUID`;

    IF (fncLastPriority > 0) THEN
        SELECT `Priority` INTO fncLastPriority
          FROM `wtkMenuItems`
        WHERE `MenuGroupUID` = NEW.`MenuGroupUID`
        ORDER BY `Priority` DESC LIMIT 1;
    END IF;
    SET NEW.`Priority` = (fncLastPriority + 10);
END
$$

CREATE TRIGGER `tib_wtkWidgetGroup_X_Widget`
    BEFORE INSERT ON `wtkWidgetGroup_X_Widget`
    FOR EACH ROW
  BEGIN
    DECLARE  fncLastPriority SMALLINT;

    IF (NEW.`UserUID` IS NULL) THEN
        SELECT COUNT(*) INTO fncLastPriority
          FROM `wtkWidgetGroup_X_Widget`
        WHERE `WidgetGroupUID` = NEW.`WidgetGroupUID` AND `UserUID` IS NULL;
    ELSE
        SELECT COUNT(*) INTO fncLastPriority
          FROM `wtkWidgetGroup_X_Widget`
        WHERE `WidgetGroupUID` = NEW.`WidgetGroupUID` AND `UserUID` = NEW.`UserUID`;
    END IF;

    IF (fncLastPriority > 0) THEN
        IF (NEW.`UserUID` IS NULL) THEN
            SELECT `WidgetPriority` INTO fncLastPriority
              FROM `wtkWidgetGroup_X_Widget`
            WHERE `WidgetGroupUID` = NEW.`WidgetGroupUID` AND `UserUID` IS NULL
            ORDER BY `WidgetPriority` DESC LIMIT 1;
        ELSE
            SELECT `WidgetPriority` INTO fncLastPriority
              FROM `wtkWidgetGroup_X_Widget`
            WHERE `WidgetGroupUID` = NEW.`WidgetGroupUID` AND `UserUID` = NEW.`UserUID`
            ORDER BY `WidgetPriority` DESC LIMIT 1;
        END IF;
    END IF;
    SET NEW.`WidgetPriority` = (fncLastPriority + 10);
END
$$

CREATE TRIGGER `tia_wtkVisitorHistory`
    AFTER INSERT ON `wtkVisitorHistory`
    FOR EACH ROW
  BEGIN

    UPDATE `wtkVisitors`
      SET `SecondsOnSite` = TIMESTAMPDIFF(SECOND, `AddDate`, NOW()),
        `LastPage` = COALESCE(NEW.`PageTitle`,NEW.`PageURL`),
        `PagesB4Signup` = IF (`SignupDate` IS NULL, (`PagesB4Signup` + 1), `PagesB4Signup`),
        `PagesB4Buy` = IF (`BuyDate` IS NULL, (`PagesB4Buy` + 1), `PagesB4Buy`),
        `PagesAfterBuy` = IF (`BuyDate` IS NOT NULL, `PagesAfterBuy` + 1, `PagesAfterBuy`)
    WHERE `UID` = NEW.`VisitorUID`;

  END
$$

DELIMITER ;
/*
-- Run below scripts to create triggers which will log access to wtkTableTracking for any insert/update/delete

SELECT CONCAT("CREATE TRIGGER `tib_",`TABLE_NAME`, "` BEFORE INSERT ON `",
    `TABLE_NAME`, "` FOR EACH ROW BEGIN CALL `st_LogAccess`('", `TABLE_NAME`, "','INS'); END$$") AS `Scripts`
  FROM information_schema.`tables`
   WHERE `TABLE_SCHEMA` = 'slimdb'
ORDER BY `TABLE_NAME` ASC;

SELECT CONCAT("CREATE TRIGGER `tub_",`TABLE_NAME`, "` BEFORE UPDATE ON `",
    `TABLE_NAME`, "` FOR EACH ROW BEGIN CALL `st_LogAccess`('", `TABLE_NAME`, "','UPD'); END$$") AS `Scripts`
  FROM information_schema.`tables`
   WHERE `TABLE_SCHEMA` = 'slimdb'
ORDER BY `TABLE_NAME` ASC;

SELECT CONCAT("CREATE TRIGGER `tdb_",`TABLE_NAME`, "` BEFORE DELETE ON `",
    `TABLE_NAME`, "` FOR EACH ROW BEGIN CALL `st_LogAccess`('", `TABLE_NAME`, "','DEL'); END$$") AS `Scripts`
  FROM information_schema.`tables`
   WHERE `TABLE_SCHEMA` = 'slimdb'
ORDER BY `TABLE_NAME` ASC;
*/
