<?PHP
require('cronTop.php');

$pgHtm .= '<br><h3>Monthly CRON Jobs</h3>' . "\n";
// Should be triggered on 1st of the month

// if there was no income, it will insert a row with $0 Income and Refunds
$pgSQL =<<<SQLVAR
INSERT INTO `wtkIncomeByMonth`
   (`YearTracked`, `Quarter`, `MonthInYear`, `GrossIncome`, `Refunds`)
SELECT
    COALESCE(t.`Year`, DATE_FORMAT(NOW(), '%Y')) AS `Year`,
    COALESCE(t.`Quarter`, QUARTER(NOW())) AS `Quarter`,
    COALESCE(t.`Month`, DATE_FORMAT(NOW(), '%m')) AS `Month`,
    COALESCE(SUM(IF(r.`PaymentStatus` = 'Authorized', r.`GrossAmount`, 0)), 0) AS `GrossIncome`,
    COALESCE(SUM(IF(r.`PaymentStatus` = 'Refund', r.`GrossAmount`, 0)), 0) AS `Refunds`
FROM (
    SELECT
        DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y') AS `Year`,
        QUARTER(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AS `Quarter`,
        DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%m') AS `Month`
) t
LEFT JOIN `wtkRevenue` r ON DATE_FORMAT(r.`AddDate`, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m')
    AND r.`PaymentStatus` IN ('Authorized', 'Refund')
GROUP BY t.`Year`, t.`Quarter`, t.`Month`
SQLVAR;
wtkSqlExec($pgSQL, []);

$pgHtm .= '<p>wtkIncomeByMonth has had totals calculated for prior month.</p>' . "\n";

require('cronEnd.php');
?>
