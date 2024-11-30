<?PHP
$pgSecurityLevel = 30;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$gloForceRO = wtkPageReadOnlyCheck('/admin/emailView.php', $gloId);

if ($gloRNG != 0): // must be called as Modal Window
    $pgModalWin = 'Y';
else:
    $pgModalWin = 'N';
endif;

$pgSQL =<<<SQLVAR
SELECT e.`UID`, DATE_FORMAT(e.`AddDate`,'$gloSqlDateTime') AS `SentDate`,
    COALESCE(DATE_FORMAT(e.`EmailOpened`, '$gloSqlDateTime'),'not yet') AS `EmailOpened`,
  COALESCE(t.`EmailCode`, 'none') AS `EmailCode`,
  CASE
    WHEN e.`SendByUserUID` IS NULL THEN 'Server'
    ELSE CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,''))
  END AS `SentFrom`,
  CONCAT(u2.`FirstName`, ' ', COALESCE(u2.`LastName`,'')) AS `SentTo`,
  e.`Subject`, e.`EmailAddress`, e.`EmailBody`, e.`InternalNote`
FROM `wtkEmailsSent` e
  LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = e.`SendByUserUID`
  LEFT OUTER JOIN `wtkUsers` u2 ON u2.`UID` = e.`SendToUserUID`
  LEFT OUTER JOIN `wtkEmailTemplate` t ON t.`UID` = e.`EmailUID`
WHERE e.`UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
wtkSqlGetRow($pgSQL, [$gloId]);

if ($pgModalWin == 'Y'):
    $pgHtm  = '<div class="row">' . "\n";
    $pgHtm .= '    <div class="col s12">' . "\n";
else:
    $pgHtm  = '<div class="container">' . "\n";
endif;
$pgHtm .=<<<htmVAR
    <h2>Email Detail</h2><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;
$gloForceRO = true;
$pgHtm .= wtkFormText('wtkEmailsSent', 'SentFrom');
$pgHtm .= wtkFormText('wtkEmailsSent', 'EmailCode', 'text', 'Email Template');

$pgHtm .= wtkFormText('wtkEmailsSent', 'SentDate');
$pgHtm .= wtkFormText('wtkEmailsSent', 'EmailOpened');

$pgHtm .= wtkFormText('wtkEmailsSent', 'SentTo');
$pgHtm .= wtkFormText('wtkEmailsSent', 'EmailAddress');
$pgHtm .= wtkFormText('wtkEmailsSent', 'Subject', 'text', 'Subject', 's12');
$gloForceRO = false;

$pgEmailBody = wtkSqlValue('EmailBody');
$pgHtm .=<<<htmVAR
</div>
<div class="row">
    <div class="col s12" style="border:1px">
        $pgEmailBody
    </div>
</div>
<div class="row">
htmVAR;
if ($pgModalWin == 'N'):
    $pgHtm .= wtkFormTextArea('wtkEmailsSent', 'InternalNote');
endif;
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/emailHistory.php');
//$pgHtm .= wtkFormPrimeField('wtkEmailsSent', 'ParentUID', $gloRNG);
$pgHtm .= '            </div>' . "\n";
if ($pgModalWin == 'Y'):
    $pgHtm .= '<div class="center"><button type="button" class="btn-small black b-shadow waves-effect waves-light modal-close">Close</button></div>';
else:
    $pgHtm .= wtkUpdateBtns() . "\n";
endif;
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
if ($pgModalWin == 'Y'):
    $pgHtm .= '</div>' . "\n";
endif;
echo $pgHtm;
exit;
?>
