<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `Phone`,`CellPhone`,`Email`
  FROM `wtkUsers` u
WHERE u.`UID` = ?
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
wtkSqlGetRow($pgSQL, [$gloId]);
$pgEmail = wtkSqlValue('Email');
$pgPhone = wtkSqlValue('Phone');
$pgCellPhone = wtkSqlValue('CellPhone');

$pgHtm =<<<htmVAR
<div class="container">
    <br><h5>Contact Info</h5>
    <p><strong>Email:</strong> $pgEmail</p>
    <p><strong>Phone:</strong> $pgPhone</p>
    <p><strong>Cell Phone:</strong> $pgCellPhone</p>
    <br>
</div>
htmVAR;

echo $pgHtm;
exit;
?>
