<?PHP
$pgSecurityLevel = 80;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgCustomFromEmail = 'your@personal-email.com'; // use this for your custom email From alternative address
//$pgCustomFromEmail = 'alec@programminglabs.com'; // use this for your custom email From alternative address

$pgHtm = '';
$pgAltFromSection = '';
$pgSendTo = '';
if ($pgCustomFromEmail != 'your@personal-email.com'):
    $pgAltFromSection =<<<htmVAR
<div class="col s12">
    <p>Choose From Email:</p>
    <div class="switch">
        <label>From: $gloEmailFromAddress
            <input type="checkbox" value="Y" id="AlternateFromAddress" name="AlternateFromAddress">
            <span class="lever"></span>
            Or: $pgCustomFromEmail
        </label>
    </div>
</div>
htmVAR;
endif;

$pgMode = wtkGetParam('Mode');
switch ($pgMode):
    case 'Start':
        $pgHideToEmail = '';
        if ($gloDbConnection == 'Live'):
            $pgDevNote = '';
            $pgDevDisable = '';
            $pgDevMsg = '';
            $pgToEmail = '';
        else:
            $pgDevNote  = '<p class="blue-text">Since DB connection is ' . $gloDbConnection;
            $pgDevNote .= ' this will be sent to <b>' . $gloTechSupport . '</b>.';
            $pgDevNote .= ' Change $gloDbConnection to "Live" to be able to change email address or select from User drop list.</p><br>';
            $pgDevDisable = 'disabled';
            $pgDevMsg = '&nbsp;&nbsp; <em class="red-text">disabled because not Live site</em>';
            $pgToEmail = $gloTechSupport;
        endif;
        // BEGIN check to see if company prefers WYSIWYG
        $pgWYSIWYG = wtkSqlGetOneResult('SELECT `PreferWYSIWYG` FROM `wtkCompanySettings` WHERE `UID` = 1', []);
        if ($pgWYSIWYG == 'Y'):
            $pgLabelActive = ' class="active"';
            $pgTinyMCE = '<input type="hidden" id="HasModalTinyMCE" name="HasModalTinyMCE" value="textarea#EmailMsg">';
        else:
            $pgLabelActive = '';
            $pgTinyMCE = '';
        endif;
        //  END  check to see if company prefers WYSIWYG
        // BEGIN allow choosing HTML template
        $gloWTKmode = 'ADD';
        $pgSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = :LookupType AND `DelDate` IS NULL
ORDER BY `UID` ASC
SQLVAR;
        $pgSqlFilter = array('LookupType' => 'EmailHTM');
        $pgForm = wtkFormSelect('wtkEmailTemplate', 'EmailHTM', $pgSQL, $pgSqlFilter, 'LookupDisplay', 'LookupValue', 'Pick HTML Template', 's12" style="margin-top:27px');
//        $pgForm = wtkReplace($pgForm, '<div class="input-field col s12">', '<div class="input-field col s12"><br><br>');
        $pgSelectHTM = wtkReplace($pgForm, 'wtkwtkEmailTemplateEmailHTM','EmailHTM');
        //  END  allow choosing HTML template
        if ($gloId == 0):
            $pgSQL =<<<SQLVAR
SELECT COALESCE(u.`Email`, u.`AltEmail`) AS `ReplyToEmail`
  FROM `wtkUsers` u
WHERE u.`UID` = :UserUID
SQLVAR;
            $pgSqlFilter = array(
                'UserUID' => $gloUserUID
            );
            $pgReplyToEmail = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);

            $gloWTKmode = 'ADD';
            $pgBugMsg = '';
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
            $pgForm  = wtkFormSelect('wtkUsers', 'UID', $pgSQL, $pgSqlFilter, 'User', 'UserUID', 'Pick User', 's12');
            $pgSendTo =<<<htmVAR
            <div class="col s12">
                <p>Choose who to send email to:</p>
                <div class="switch">
                    <label>To User
                        <input type="checkbox" $pgDevDisable checked value="Email" id="UserOrEmail" name="UserOrEmail" onclick="JavaScript:showEmailOrUser(this.value)">
                        <span class="lever"></span>
                        Non-User Email $pgDevMsg
                    </label>
                </div>            
            </div>            
htmVAR;
        else:
            $gloWTKmode = 'EDIT';
            $pgDevDisable = 'disabled';
            $pgHideToEmail = 'hide';
            $pgSQL =<<<SQLVAR
SELECT CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `User`, u.`Email`, b.`BugMsg`,
       COALESCE(u2.`Email`, u2.`AltEmail`) AS `ReplyToEmail`
  FROM `wtkUsers` u, `wtkBugReport` b, `wtkUsers` u2
WHERE u.`UID` = :UserUID AND b.`UID` = :BugUID AND u2.`UID` = :LoggedInUser
SQLVAR;
            $pgSQL = wtkSqlPrep($pgSQL);
            $pgSqlFilter = array(
                'UserUID' => $gloId,
                'LoggedInUser' => $gloUserUID,
                'BugUID' => $gloRNG
            );
            wtkSqlGetRow($pgSQL, $pgSqlFilter);
            $pgUserName = wtkSqlValue('User');
            $pgToEmail = wtkSqlValue('Email');
            $pgReplyToEmail = wtkSqlValue('ReplyToEmail');
            $pgBugMsg = wtkSqlValue('BugMsg');
            if ($pgWYSIWYG == 'Y'):
                $pgBugMsg = '<br><br><hr>' . nl2br($pgBugMsg);
            else:
                $pgBugMsg = "\n\n\n------\n$pgBugMsg";
            endif;
            $pgLabelActive = ' class="active"';
            $pgForm .= '<div class="col s12"><strong>To:</strong> ' . "$pgUserName ($pgToEmail)</div>";
            $pgForm .= wtkFormHidden('wtkwtkUsersUID', $gloId);
            $gloWTKmode = 'ADD';
        endif;
        $pgForm  = wtkReplace($pgForm, 'class="input-field col s12"','id="UserUIDDIV" class="input-field col s12 hide"');
        $pgForm .= wtkFormHidden('Mode', 'SendOne');
        $pgForm .= wtkFormWriteUpdField();

        $pgHtm =<<<htmVAR
<div class="modal-content">
    <input type="hidden" id="HasTextArea" name="HasTextArea" value="EmailMsg">
    <form id="FemailResults" name="FemailResults" class="card content b-shadow">
        <div class="row">
            $pgAltFromSection
            $pgSendTo
            <div class="col s12">
                <p>Reply To:</p>
                <div class="switch">
                    <label>Company Email: $gloEmailFromAddress
                        <input type="checkbox" value="$pgReplyToEmail" id="EmailReplyTo" name="EmailReplyTo">
                        <span class="lever"></span>
                        Your Email: $pgReplyToEmail
                    </label>
                </div>
            </div>
            $pgSelectHTM
        </div>        
        <hr><br>
        <div class="row">
            $pgForm
            <div id="ToEmailDIV" class="input-field col s12">
                <input $pgDevDisable id="ToEmail" name="ToEmail" type="email" value="$pgToEmail" class="validate $pgHideToEmail" onchange="JavaScript:wtkValidate(this,'EMAIL')">
                <label for="ToEmail" class="active $pgHideToEmail">To Email</label>
            </div>
            <div class="input-field col s12">
                <input type="text" required id="Subject" name="Subject" value="">
                <label id="labelSubject" for="Subject">Subject</label>
            </div>
            <div class="input-field col s12">
                $pgTinyMCE
                <textarea required id="EmailMsg" name="EmailMsg" class="materialize-textarea">$pgBugMsg</textarea>
                <label id="labelEmailMsg" for="EmailMsg"$pgLabelActive>Email Message</label>
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
    <a id="sendEmailBtn" class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="JavaScript:adminValidateEmail('emailWriter')">Send</a>
</div>
htmVAR;
        break;
    case 'SendOne':
        $pgUserOrEmail = wtkGetPost('UserOrEmail');
        $pgSubject = wtkGetPost('Subject');
        $pgEmailBody = wtkGetPost('EmailMsg');
        $pgHasTinyMCE = wtkGetPost('HasModalTinyMCE');
        if ($pgHasTinyMCE == ''):
            $pgEmailBody = nl2br($pgEmailBody);
        endif;

        $pgTemplate = wtkGetParam('EmailHTM','email' . $gloDarkLight . '.htm');
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
        $pgAlternateFromAddress = wtkGetPost('AlternateFromAddress');
        if ($pgAlternateFromAddress == 'Y'):
            $gloEmailFromAddress = $pgCustomFromEmail;
        endif;
        $pgEmailReplyTo = wtkGetPost('EmailReplyTo');
        $pgEmailBody = wtkReplace($pgEmailBody, '@email@', $pgToEmail);
        $pgTmp = wtkNotifyViaEmail($pgSubject, $pgEmailBody, $pgToEmail, [],'',$pgTemplate . '.htm',$pgEmailReplyTo);
        $pgHtm .= '<span class="chip green white-text">Sent "' . $pgSubject . '" email to: ' . $pgToEmail . '</span>';
        break;
endswitch;
$pgHtm = wtkReplace($pgHtm, 'btn  btn', 'btn btn');

echo $pgHtm;
wtkAddUserHistory();
exit;
?>
