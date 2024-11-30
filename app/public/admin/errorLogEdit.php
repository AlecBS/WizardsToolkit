<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
$pgDate = wtkSqlDateFormat('e.`AddDate`','AddDate',$gloSqlDateTime);

$pgSQL  = "SELECT e.`UID`, $pgDate," . "\n";
$pgSQL .= ' e.`ReferralPage`, e.`FromPage`, e.`ErrType`, e.`ErrMsg`, e.`ErrNotes`,e.`LineNum`,' . "\n";
$pgSQL .= " CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `User`" . "\n";
$pgSQL .= ' FROM `wtkErrorLog` e' . "\n";
$pgSQL .= ' LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = e.`UserUID`' . "\n";
$pgSQL .= ' WHERE e.`UID` = ?';
wtkSqlGetRow($pgSQL, [$gloId]);

$pgUserName = wtkSqlValue('User');
if ($pgUserName == ' '):
    $pgUser = '';
else:
    $pgUser = "<h5>Error discovered by: $pgUserName</h5><br>";
endif;

if ($gloRNG == 'widget'):
    $pgBtns  = wtkModalUpdateBtns('../wtk/lib/Save', 'errorLogDIV', 'N');
    $pgHtm  =<<<htmVAR
<div id="errorLogDIV">
    <div class="modal-content">
    <h3>Error Detail</h3>
    $pgUser
htmVAR;
else:
    $pgBtns = wtkUpdateBtns('FerrorLogDIV');
    $pgHtm =<<<htmVAR
<div class="container">
    <h4>Error Detail</h4><br>
    $pgUser
    <div class="card content b-shadow">
htmVAR;
endif;
$pgHtm .=<<<htmVAR
        <form id="FerrorLogDIV" name="FerrorLogDIV" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;
$gloForceRO = true;

$pgHtm .= wtkFormText('wtkErrorLog', 'AddDate', 'text','','m3 s12');
$pgHtm .= wtkFormText('wtkErrorLog', 'ReferralPage', 'text','','m9 s12');
$pgHtm .= wtkFormText('wtkErrorLog', 'FromPage', 'text','','m9 s12');
$pgHtm .= wtkFormText('wtkErrorLog', 'LineNum', 'text','','m3 s12');
$pgHtm .= wtkFormText('wtkErrorLog', 'ErrType', 'text', 'Error Type');
$pgHtm .= wtkFormTextArea('wtkErrorLog', 'ErrMsg', 'Error Message', 'm12 s12');
$gloForceRO = false;

$pgHtm .= wtkFormTextArea('wtkErrorLog', 'ErrNotes', 'Error Notes', 'm12 s12');
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/errorLogList.php');
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
if ($gloRNG == 'widget'):
    $pgHtm .= wtkFormHidden('rng', 'widget');
else:
    $pgHtm .= $pgBtns . "\n";
endif;
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
            </div>
        </form>
htmVAR;
if ($gloRNG == 'widget'):
    $pgHtm .=<<<htmVAR
    </div>
    <div id="modFooter" class="modal-footer right">
    $pgBtns
    </div>
</div>
htmVAR;
    $pgHtm = wtkReplace($pgHtm, 'wtkGoBack()',"wtkModalUpdate('/admin/errorLogList',0,'widget')");
else:
    $pgHtm .= '</div></div>' . "\n";
endif;
echo $pgHtm;
exit;
?>
