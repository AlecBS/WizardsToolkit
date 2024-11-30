<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgDate = wtkSqlDateFormat('l.`AddDate`','AddDate',$gloSqlDateTime);

$pgSQL =<<<SQLVAR
SELECT $pgDate,
    l.`TableName`, l.`ChangeInfo`, l.`FullSQL`, l.`OtherUID`,
    CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `User`
  FROM `wtkUpdateLog` l
    LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = l.`UserUID`
WHERE l.`UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL, [$gloId]);
$pgUserName = wtkSqlValue('User');
$pgUser = "<h5>Data changed by: $pgUserName</h5><br>";
$gloForceRO = true;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>UpdateLog Detail</h4>
    <br>$pgUser
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkUpdateLog', 'AddDate', 'text', '', 'm4 s12');
if ($gloWTKmode == 'Copy'): // Copy data feature
    $pgHtm = wtkReplace($pgHtm, ' name="Origwtk', ' name="Copywtk');
    $gloWTKmode = 'ADD';
endif;
$pgHtm .= wtkFormText('wtkUpdateLog', 'OtherUID', 'text', 'UID', 'm4 s12');
$pgHtm .= wtkFormText('wtkUpdateLog', 'TableName', 'text', '', 'm4 s12');
$pgHtm .= wtkFormTextArea('wtkUpdateLog', 'ChangeInfo');
// $pgHtm .= wtkFormText('wtkUpdateLog', 'FullSQL');

$pgHtm .= wtkFormWriteUpdField();
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/updateLogView.php');
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= '            </div>' . "\n";
$pgHtm .=<<<htmVAR
<div class="row">
    <div class="col s6 offset-s3 center">
        <button type="button" class="btn-small black b-shadow waves-effect waves-light" onclick="Javascript:wtkGoBack()">Return</button>
    </div>
</div>
htmVAR;

$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
