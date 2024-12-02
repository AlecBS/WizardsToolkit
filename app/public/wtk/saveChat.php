<?PHP
define('_RootPATH', '../');
require('wtkLogin.php');

$pgToUser = wtkGetParam('to');
$pgMsg = wtkGetParam('msg');
$pgSQL =<<<SQLVAR
INSERT INTO `wtkChat`
 (`SendByUserUID`, `SendToUserUID`, `Message`)
 VALUES (:UserUID, :SendToUserUID, :Message)
SQLVAR;
$pgSqlFilter = array (
    'UserUID' => $gloUserUID,
    'SendToUserUID' => $pgToUser,
    'Message' => $pgMsg
);
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgHtm =<<<htmVAR
<div class="content-left left">
    <div class="triangles"></div>
    <span>$pgMsg</span>
</div><br>
htmVAR;
echo $pgHtm;
exit; // no display needed, handled via JS and spa.htm
?>
