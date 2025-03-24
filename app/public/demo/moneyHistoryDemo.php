<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgRowCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkIncomeByMonth`', []);

if ($pgRowCount == 0):
    $pgHtm =<<<htmVAR
<div class="row">
    <div class="col m6 offset-m3 s12">
        <div class="card">
            <div class="card-content">
                <h3>Missing Income by Month Data</h3>
                <p>This demo page require `wtkIncomeByMonth` data.</p>
                <p>To generate the data run the following scripts:
                    <ul class="browser-default">
                        <li>\SQL\mySQL\Utils\NameGeneration.sql</li>
                        <li>\SQL\mySQL\Utils\GenerateRevenueDemo.sql</li>
                    </ul>
                </p>
                <p>Make sure to TRUNCATE `wtkIncomeByMonth` after done reviewing.</p>
            </div>
        </div>
    </div>
</div>
htmVAR;
    echo $pgHtm;
else:
    include('../admin/moneyHistory.php');
endif;
exit;
?>
