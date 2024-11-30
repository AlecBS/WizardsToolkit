-- USE wiztools;

-- MySQL version of VIEWs
DROP VIEW IF EXISTS `wtkDashboardView`;

DELIMITER $$
CREATE VIEW `wtkDashboardView` AS
-- SQL programmer can change this to affect the dashboard counts
-- without needing to get into PHP
SELECT (SELECT COUNT(*) FROM `wtkUsers` WHERE `DelDate` IS NULL) AS `Widget1`,
    (SELECT COUNT(*) FROM `wtkLoginLog`) AS `Widget2`,
    (SELECT COUNT(*) FROM `wtkUserHistory`) AS `Widget3`,
    (SELECT COUNT(*) FROM `wtkReportCntr`) AS `Widget4`
$$

DELIMITER ;

DROP VIEW IF EXISTS `wtkPollSummaryView`;

DELIMITER $$
CREATE VIEW `wtkPollSummaryView` AS
    SELECT p.`UID`, count(r.`PollUID`) AS `Votes`
     FROM (`wtkPolls` p LEFT JOIN `wtkPollResults` r ON ((r.`PollUID` = p.`UID`)))
    GROUP BY p.`UID`
$$

DELIMITER ;
