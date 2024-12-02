<?PHP
$pgSecurityLevel = 50;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgEmailUID = '';
$pgOtherUID = '';
$pgSubject = '';
$pgEmailBody = '';
$pgLabelActive = '';
$pgOtherHid = '';
$pgEmailCode = wtkGetParam('Mode');
if (substr($pgEmailCode,0,8) == 'OtherUID'):
    $pgOtherUID = wtkReplace($pgEmailCode,'OtherUID','');
    $pgOtherUID = '<input type="hidden" id="OtherUID" name="OtherUID" value="' . $pgOtherUID . '">';
else: // Not passing OtherUID
    if ($pgEmailCode != ''):
        $pgSqlFilter = array (
            'EmailCode' => $pgEmailCode
        );
        $pgCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkEmailTemplate` WHERE `EmailCode` = :EmailCode', $pgSqlFilter);
        if ($pgCount > 0):
            $pgSQL =<<<SQLVAR
SELECT `UID`, `Subject`, `EmailBody`
  FROM `wtkEmailTemplate`
 WHERE `EmailCode` = :EmailCode
ORDER BY `UID` DESC LIMIT 1
SQLVAR;
            wtkSqlGetRow($pgSQL, [$pgEmailCode]);
            $pgEmailUID = wtkSqlValue('UID');
            $pgSubject = wtkTokenToValue(wtkSqlValue('Subject'));
            $pgEmailBody = wtkSqlValue('EmailBody');
            if ($pgEmailBody != ''):
                $pgLabelActive = ' class="active"';
                $pgEmailBody = wtkTokenToValue($pgEmailBody);
                $pgEmailBody = wtkRemoveStyle($pgEmailBody);
            endif;
        endif;
    endif;
endif;

// BEGIN Email Template Picker
$pgEmailTemplate = '';
$pgPicker = $gloRNG;
if ($pgPicker != ''):
    $pgPicker = wtkReplace($pgPicker,'picker','');
    // make drop list based on
    $pgSelSQL =<<<SQLVAR
SELECT `UID`, CONCAT(`EmailCode`, ': ', `Subject`) AS `Subject`
FROM `wtkEmailTemplate`
WHERE `EmailType` = :EmailType
ORDER BY `UID` ASC
SQLVAR;
    $pgSqlFilter = array (
        'EmailType' => $pgPicker
    );
    $pgSelOptions = wtkGetSelectOptions($pgSelSQL, $pgSqlFilter, 'Subject', 'UID', '');
    $pgEmailTemplate =<<<htmVAR
    <div class="input-field col s12">
        <select id="wtkFilterSel" name="wtkFilterSel" onchange="JavaScript:ajxEmailTemplate(this.value)">
            <option value="0">none</option>
            $pgSelOptions
        </select>
        <label for="wtkFilterSel" class="active">Choose Email Template</label>
        <input type="hidden" id="HasSelect" name="HasSelect" value="Y">
    </div>
htmVAR;
endif;
//  END  Email Template Picker

$pgSQL =<<<SQLVAR
SELECT CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `ToName`,
    CONCAT(COALESCE(u2.`FirstName`,''), ' ', COALESCE(u2.`LastName`,'')) AS `FromName`,
    u.`Email` AS `ToEmail`, u2.`Email` AS `FromEmail`
  FROM `wtkUsers` u, `wtkUsers` u2
 WHERE u.`UID` = :ToUID AND u2.`UID` = :FromUID
SQLVAR;
$pgSqlFilter = array (
    'FromUID' => $gloUserUID,
    'ToUID' => $gloId
);
wtkSqlGetRow($pgSQL, $pgSqlFilter);
$pgToName = wtkSqlValue('ToName');
$pgFromName = wtkSqlValue('FromName');
$pgFromEmail = wtkSqlValue('FromEmail');
$pgToEmail = wtkSqlValue('ToEmail');

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col s12"><br>
        <h3>Send Email <span class="right">
        <button type="button" class="btn-small black b-shadow waves-effect waves-light modal-close">Cancel</button>
        &nbsp;&nbsp;
        <button id="sendEmailBtn" type="button" class="btn-primary btn-small b-shadow waves-effect waves-light modal-close" onclick="Javascript:wtkSendEmail($gloId)">Send</button>
        </span></h3>
        <br>
        <div class="content b-shadow">
            <form id="emailForm" method="POST">
                <input type="hidden" id="EmailUID" name="EmailUID" value="$pgEmailUID">
                <input type="hidden" id="id" name="id" value="$gloId">
                $pgOtherUID
                <input type="hidden" id="ToName" name="ToName" value="$pgToName">
                <input type="hidden" id="ToEmail" name="ToEmail" value="$pgToEmail">
                <input type="hidden" id="FromName" name="FromName" value="$pgFromName">
                <input type="hidden" id="FromEmail" name="FromEmail" value="$pgFromEmail">
                <input type="hidden" id="HasTextArea" name="HasTextArea" value="EmailMsg">
                <p>From: $pgFromName ($pgFromEmail)</p>
                <p>To: $pgToName ($pgToEmail)</p><br>
                <div class="row">
                    $pgEmailTemplate
                    <div class="input-field col s12">
                      <input type="text" required id="Subject" name="Subject" value="$pgSubject">
                      <label id="labelSubject" for="Subject"$pgLabelActive>Subject</label>
                    </div>
                    <div class="input-field col s12">
                      <textarea required id="EmailMsg" name="EmailMsg" class="materialize-textarea">$pgEmailBody</textarea>
                      <label id="labelEmailMsg" for="EmailMsg"$pgLabelActive>Email Message</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
htmVAR;
echo $pgHtm;
wtkAddUserHistory();
exit;
?>
