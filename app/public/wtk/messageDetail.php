<?php
define('_RootPATH', '../');
require('wtkLogin.php');

$pgSQL =<<<SQLVAR
SELECT e.`EmailAddress`, e.`HtmlTemplate`,
    DATE_FORMAT(e.`AddDate`, '$gloSqlDateTime') AS `SentDate`,
    DATE_FORMAT(e.`EmailDelivered`, '$gloSqlDateTime') AS `EmailDelivered`,
    DATE_FORMAT(e.`EmailOpened`, '$gloSqlDateTime') AS `EmailOpened`,
    DATE_FORMAT(e.`EmailLinkClicked`, '$gloSqlDateTime') AS `EmailLinkClicked`,
    e.`Subject`,
 REPLACE(e.`EmailBody`,' href="', ' href="#" title="') AS `MsgText`,
 CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `FromName`,
 e.`SendByUserUID` AS `FromId`, u.`FilePath`, u.`NewFileName`
 FROM wtkEmailsSent e
  INNER JOIN wtkUsers u ON u.`UID` = e.`SendByUserUID`
WHERE e.`UID` = ?
GROUP BY e.`UID`
SQLVAR;
wtkSqlGetRow($pgSQL, [$gloId]);

$pgLinkClicked = wtkSqlValue('EmailLinkClicked');
if ($pgLinkClicked != ''):
    $pgLinkClicked = '<br>Link Clicked: ' . $pgEmailLinkClicked;
endif;
$pgHtm =<<<htmVAR
        <div class="row">
            <div class="col m10 offset-m1 s12">
                <div class="card">
                    <div class="card-content">
                        <h5>Subject: @Subject@</h5>
                        <p>Sent: @SentDate@<br>
                        Delivered: @EmailDelivered@<br>
                        Opened: @EmailOpened@
                        $pgLinkClicked
                        </p>
                    </div>
                </div>
            </div>
        </div>
		<p style="color: #ccc6c6;">@MsgText@</p>
htmVAR;

$pgHtmlTemplate = wtkSqlValue('HtmlTemplate');
$pgMsgText = wtkSqlValue('MsgText');
if ($pgHtmlTemplate != ''):
    $pgEmailAddress = wtkSqlValue('EmailAddress');
    $pgMsgText = wtkUseEmailTemplate($pgMsgText, $pgEmailAddress, $pgHtmlTemplate);
endif;

$pgHtm = wtkReplace($pgHtm, '@MsgText@', $pgMsgText);
// $pgHtm = wtkDisplayData('MsgText', $pgHtm);
$pgHtm = wtkReplace($pgHtm, '<a ', '<span '); // ABS 12/16/20
$pgHtm = wtkReplace($pgHtm, '</a>', '</span>');

$pgHtm = wtkDisplayData('Subject', $pgHtm);
$pgHtm = wtkDisplayData('SentDate', $pgHtm);
$pgHtm = wtkDisplayData('EmailDelivered', $pgHtm);
$pgHtm = wtkDisplayData('EmailOpened', $pgHtm);

$pgFromId = wtkSqlValue('FromId');
if ($pgFromId == 0):
    $pgHtm = wtkReplace($pgHtm, 'id="techBox" class="content"','class="hide"');
else:
    $pgHtm = wtkDisplayData('FromName', $pgHtm);
    $pgHtm = wtkDisplayData('FromId', $pgHtm);
    $pgHtm = wtkReplace($pgHtm, 'class="note"','class="hide"');
    $pgNewFileName = wtkSqlValue('NewFileName');
    if ($pgNewFileName != ''):
        $pgFilePath = wtkSqlValue('FilePath');
        $pgHtm = wtkReplace($pgHtm, '/wtk/imgs/noPhotoAvail.png', $pgFilePath . $pgNewFileName);
    endif;
endif;

echo $pgHtm;
wtkAddUserHistory();
exit;
?>
