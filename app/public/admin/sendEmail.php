<?PHP
$pgSecurityLevel = 90;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
if ($gloId == ''):
    $gloId = wtkGetGet('id'); // for View call
endif;

if (preg_match("/^'\d+'$/", $gloId)):
    $gloId = trim($gloId, "'");
endif;

$pgSQL =<<<SQLVAR
SELECT `Subject`, `EmailBody`
 FROM `wtkEmailTemplate`
 WHERE `UID` = :UID
SQLVAR;
$pgSqlFilter = array (
    'UID' => $gloId
);
wtkSqlGetRow($pgSQL, $pgSqlFilter);

$pgSubject = wtkSqlValue('Subject');
$pgSubject = wtkTokenToValue($pgSubject);
$pgEmailBody = wtkSqlValue('EmailBody');
$pgEmailBody = wtkTokenToValue($pgEmailBody);
$pgSaveArray = array (
    'EmailUID' => $gloId,
    'FromUID' => 0
);

$pgMode = wtkGetParam('Mode');
$pgHtm = '';
switch ($pgMode):
    case 'OneUser':
        $pgSQL =<<<SQLVAR
SELECT u.`UID` AS `UserUID`, CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `User`
  FROM `wtkUsers` u
    LEFT OUTER JOIN `wtkEmailsSent`e ON e.`SendToUserUID` = u.`UID` AND e.`EmailUID` = :EmailUID
  WHERE e.`UID` IS NULL
    AND u.`OptInEmails` = :OptInEmails
  ORDER BY u.`FirstName` ASC, u.`LastName` ASC
SQLVAR;
        $pgSQL  = wtkSqlPrep($pgSQL);
        $pgSqlFilter = array (
            'EmailUID' => $gloId,
            'OptInEmails' => 'Y'
        );
        $gloWTKmode = 'ADD';
        $pgForm  = wtkFormSelect('wtkUsers', 'UID', $pgSQL, $pgSqlFilter, 'User', 'UserUID', 'Pick User', 's12');
        $pgForm  = wtkReplace($pgForm, 'class="input-field col s12"','id="UserUIDDIV" class="input-field col s12 hide"');
        $pgForm .= wtkFormHidden('id', $gloId);
        $pgForm .= wtkFormHidden('Mode', 'SendOne');
        $pgForm .= wtkFormWriteUpdField();
        if ($gloDbConnection == 'Live'):
            $pgDevNote = '';
            $pgDevDisable = '';
        else:
            $pgDevNote  = '<p class="blue-text">Since DB connection is ' . $gloDbConnection;
            $pgDevNote .= ' this will be sent to<br><b>$gloTechSupport</b> shown above.';
            $pgDevNote .= ' Change $gloDbConnection to "Live" to be able to change email address or select from User drop list.</p><br>';
            $pgDevDisable = 'disabled';
        endif;

        $pgHtm =<<<htmVAR
<div class="modal-content">
    <p>Choose who to send "<strong>$pgSubject</strong>" email to:</p>
    <form id="FemailResults" name="FemailResults" class="card content b-shadow">
        <div class="switch">
            <label>To User
                <input type="checkbox" $pgDevDisable checked value="Email" id="UserOrEmail" name="UserOrEmail" onclick="JavaScript:showEmailOrUser(this.value)">
                <span class="lever"></span>
                Non-User Email
            </label>
        </div>
        <br><br>
        <div class="row">
            $pgForm
            <div id="ToEmailDIV" class="input-field col s12">
                <input $pgDevDisable id="ToEmail" name="ToEmail" type="email" value="$gloTechSupport" class="validate" onchange="JavaScript:wtkValidate(this,'EMAIL')">
                <label for="ToEmail" class="active">Email</label>
            </div>
            <div class="input-field col s12">
                <input id="Template" name="Template" type="text" required value="emailLight.htm">
                <label for="Template" class="active">Email Template</label>
            </div>
            <div class="col s12">
                <p>This test sending does not swap client-specific @tokens@.</p>
                $pgDevNote
            </div>
        </div>
    </form>
</div>
<div id="modFooter" class="modal-footer right">
    <a class="btn-small black b-shadow waves-effect waves-light modal-close" onclick="JavaScript:wtkFixSideNav()">Close</a> &nbsp;&nbsp;
    <a class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="JavaScript:adminValidateEmail()">Send</a>
</div>
htmVAR;
        break;
    case 'SendOne':
        $pgUserOrEmail = wtkGetPost('UserOrEmail');
        $pgTemplate = wtkGetParam('Template','email' . $gloDarkLight . '.htm');
        if ($pgUserOrEmail == 'Email'):
            $pgToEmail = wtkGetPost('ToEmail');
            $pgEmailBody = wtkReplace($pgEmailBody, '@FirstName@', $pgToEmail);
            $pgEmailBody = wtkReplace($pgEmailBody, '@FullName@', $pgToEmail);
        else:
            $pgUserUID = wtkGetPost('wtkwtkUsersUID');
            $pgSQL =<<<SQLVAR
SELECT `Email`, COALESCE(`FirstName`,`Email`) AS `FirstName`,
    CONCAT(COALESCE(`FirstName`,''), ' ', COALESCE(`LastName`,'')) AS `FullName`
  FROM `wtkUsers`
 WHERE `UID` = ?
SQLVAR;
            wtkSqlGetRow($pgSQL, [$pgUserUID]);
            $pgToEmail = wtkSqlValue('Email');
            $pgFirstName = wtkSqlValue('FirstName');
            $pgFullName = wtkSqlValue('FullName');
            $pgEmailBody = wtkReplace($pgEmailBody, '@FirstName@', $pgFirstName);
            $pgEmailBody = wtkReplace($pgEmailBody, '@FullName@', $pgFullName);
        endif;
        $pgEmailBody = wtkReplace($pgEmailBody, '@email@', $pgToEmail);
        $pgTmp = wtkNotifyViaEmail($pgSubject, $pgEmailBody, $pgToEmail, $pgSaveArray,'',$pgTemplate);
        $pgHtm .= '<span class="chip green white-text">Sent "' . $pgSubject . '" email to: ' . $pgToEmail . '</span>';
        break;
    case 'Test':
        $pgEmailBody = wtkReplace($pgEmailBody, '@email@', trim($gloTechSupport)); // urlencode(trim($fncEmail)));
        $pgEmailBody = wtkReplace($pgEmailBody, '@Header@', $pgSubject);
        $pgEmailBody = wtkReplace($pgEmailBody, '@FirstName@', 'John');
        $pgEmailBody = wtkReplace($pgEmailBody, '@FullName@', 'John Smith');

        $pgTmp = wtkNotifyViaEmail($pgSubject, $pgEmailBody, $gloTechSupport, $pgSaveArray);
        // 2VERIFY review in Dark mode: style="background: #3c3838 !important"
        $pgHtm .=<<<htmVAR
<div class="modal-content">
    <h2>Email Sent</h2>
    <br>
    <p>Test email sent to $gloTechSupport</p>
    <p><strong>Subject:</strong> TST: $pgSubject</p>
</div>
<div id="modFooter" class="modal-footer right">
    <a class="btn $gloIconSize modal-close waves-effect" onclick="JavaScript:wtkFixSideNav()">Close</a>
</div>
htmVAR;
        break;
    case 'VerifyBulk':
        $pgSQL =<<<SQLVAR
SELECT COUNT(*)
  FROM `wtkUsers` u
    LEFT OUTER JOIN `wtkEmailsSent`e ON e.`SendToUserUID` = u.`UID` AND e.`EmailUID` = :EmailUID
  WHERE e.`UID` IS NULL AND u.`OptInEmails` = :OptInEmails
    AND COALESCE(u.`Email`,'') <> ''
SQLVAR;
        $pgSqlFilter = array (
            'EmailUID' => $gloId,
            'OptInEmails' => 'Y'
        );
        $pgCount = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);
        if ($pgCount == 0):
            $pgForm = '';
            $pgList = '';
            $pgSaveBtn = '';
            $pgFooter =<<<htmVAR
 All users already received this email template. &nbsp;&nbsp;
  <a class="btn-small black b-shadow waves-effect waves-light modal-close">Cancel</a>
 &nbsp;&nbsp;
htmVAR;
        else:
            $pgSaveBtn = ' &nbsp;&nbsp;' . "\n";
            $pgFormattedCount = number_format($pgCount);
            $pgExtraMsg = '';
            if ($pgCount > 50):
                $pgExtraMsg = '(50 at a time)';
            endif;
            $pgFooter =<<<htmVAR
    <div class="left" style="margin-left: 27px"><strong>Are you ready to
        send $pgFormattedCount $pgExtraMsg emails?</strong></div>
    <div class="right">
        <a class="btn-small black b-shadow waves-effect waves-light modal-close">Cancel</a>
         &nbsp;&nbsp;
         <a class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="JavaScript:modalSave('sendEmail','emailResults')">Send</a>
         &nbsp;&nbsp;
    </div>
htmVAR;

            $pgSQL =<<<SQLVAR
SELECT u.`UID`, u.`Email`
  FROM `wtkUsers` u
    LEFT OUTER JOIN `wtkEmailsSent`e ON e.`SendToUserUID` = u.`UID` AND e.`EmailUID` = :EmailUID
  WHERE e.`UID` IS NULL
    AND u.`OptInEmails` = :OptInEmails
    AND COALESCE(u.`Email`,'') <> ''
  ORDER BY u.`UID` ASC LIMIT 20
SQLVAR;
            $pgSqlFilter = array (
                'EmailUID' => $gloId,
                'OptInEmails' => 'Y'
            );
            $pgSQL = wtkSqlPrep($pgSQL);
            $pgPDO = $gloWTKobjConn->prepare($pgSQL);
            $pgPDO->execute($pgSqlFilter);
            if ($pgCount == 1):
                $pgColM = 12;
            elseif ($pgCount == 2):
                $pgColM = 6;
            else:
                $pgColM = 4;
            endif;
            $pgCnt = 0;
            $pgList = '';
            while ($pgRow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
                $pgCnt ++;
                $pgToEmail = $pgRow['Email'];
                $pgUserUID = $pgRow['UID'];
                if ($pgCnt < 20):
                    $pgList .= '<div class="col m' . $pgColM . ' s6">';
                    $pgList .= $pgToEmail . '</div>' . "\n";
                else:
                    $pgList .= '<div class="col m8 s12"><strong>and ' . number_format($pgCount - 20) . ' more emails</strong></div>' . "\n";
                endif;
            endwhile;
            $pgForm  = wtkFormHidden('id', $gloId);
            $pgForm .= wtkFormHidden('Mode', 'SendAll');
        endif; // $pgCount > 0

        $pgHtm =<<<htmVAR
<div class="modal-content">
    <div class="card bg-second">
        <div class="card-content">
            <h5>"$pgSubject" <small><br>email will be sent to:</small></h5>
            <form id="FemailResults" name="FemailResults" class="hide">
              $pgForm
            </form>
            <div class="row">
              $pgList
            </div>
            <div id="emailResults"></div>
            <p>Note that anyone who previuosly received this email template
            is excluded and will not receive the email again.</p>
        </div>
    </div>
</div>
<div id="modFooter" class="modal-footer">
    $pgFooter
</div>
htmVAR;
        break;
    case 'SendAll':
        session_write_close();  // so rest of website still works
        $pgSQL =<<<SQLVAR
SELECT u.`UID`, u.`Email`, COALESCE(u.`FirstName`,u.`Email`) AS `FirstName`,
    CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `FullName`
  FROM `wtkUsers` u
    LEFT OUTER JOIN `wtkEmailsSent`e ON e.`SendToUserUID` = u.`UID` AND e.`EmailUID` = :EmailUID
  WHERE e.`UID` IS NULL
    AND u.`OptInEmails` = :OptInEmails
    AND COALESCE(u.`Email`,'') <> ''
  ORDER BY u.`UID` ASC
  LIMIT 50
SQLVAR;
        $pgSqlFilter = array (
            'EmailUID' => $gloId,
            'OptInEmails' => 'Y'
        );
        $pgSQL = wtkSqlPrep($pgSQL);
        $pgPDO = $gloWTKobjConn->prepare($pgSQL);
        $pgPDO->execute($pgSqlFilter);
        $pgCnt = 0;
        $pgTemplate = $pgEmailBody;
        $gloBulkEmailing = true;
        while ($pgRow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
            $pgCnt ++;
            $pgEmailBody = $pgTemplate;
            $pgEmailBody = wtkReplace($pgEmailBody, '@FirstName@',$pgRow['FirstName']);
            $pgEmailBody = wtkReplace($pgEmailBody, '@FullName@',$pgRow['FullName']);
            $pgToEmail = $pgRow['Email'];
            $pgUserUID = $pgRow['UID'];
            $pgSaveArray['ToUID'] = $pgUserUID;
// 2ENHANCE can switch from this method of one-at-a-time to changing
//  to this method: https://github.com/PHPMailer/PHPMailer/blob/master/examples/mailing_list.phps
            $pgTmp = wtkNotifyViaEmail($pgSubject, $pgEmailBody, $pgToEmail, $pgSaveArray);
        endwhile;
        $pgAddS = '';
        if ($pgCnt > 1):
            $pgAddS = 's';
        endif;
        $pgForm  = wtkFormHidden('id', $gloId);
        $pgForm .= wtkFormHidden('Mode', 'SendOne');
        $pgPageTime = round(microtime(true) - $gloPageStart,4);
        $pgHtm =<<<htmVAR
<br>
<div class="card blue">
    <div class="card-content">
	  <h5>Finished sending $pgCnt email$pgAddS in $pgPageTime seconds.</h5>
    </div>
</div>
<script type="text/javascript">
$('#modFooter').addClass('hide');
</script>
htmVAR;
        break;
    default: // View
        $pgTemplate = wtkGetParam('Template','email' . $gloDarkLight);
    //  $pgHtm = wtkLoadInclude(_RootPATH . 'wtk/htm/email' . $gloDarkLight . '.htm');
        $pgHtm = wtkLoadInclude(_RootPATH . 'wtk/htm/' . $pgTemplate . '.htm');
//        if ($gloId == 3):
            $pgHtm = wtkReplace($pgHtm, '@wtkContent@', $pgEmailBody);
        // else:
        //     $pgHtm = wtkReplace($pgHtm, '@wtkContent@', nl2br($pgEmailBody));
        // endif;
        $pgHtm = wtkTokenToValue($pgHtm);
        $pgHtm = wtkReplace($pgHtm, '@email@', trim($gloTechSupport)); // urlencode(trim($fncEmail)));
        $pgHtm = wtkReplace($pgHtm, '@Header@', $pgSubject);
        $pgHtm = wtkReplace($pgHtm, '@FirstName@', 'John');
        $pgHtm = wtkReplace($pgHtm, '@FullName@', 'John Smith');
        break;
endswitch;
$pgHtm = wtkReplace($pgHtm, 'btn  btn', 'btn btn');

echo $pgHtm;
wtkAddUserHistory();
exit;
?>
