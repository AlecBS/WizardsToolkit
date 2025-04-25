<?PHP
$pgSecurityLevel = 80;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgCustomFromEmail = 'your@personal-email.com'; // use this for your custom email From alternative address

$pgMode = wtkGetParam('Mode');
$pgHtm = '';
switch ($pgMode):
    case 'Start':
        // BEGIN allow choosing HTML template
        $gloWTKmode = 'ADD';
        $pgSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = :LookupType
ORDER BY `LookupDisplay` ASC
SQLVAR;
        $pgSqlFilter = array('LookupType' => 'EmailHTM');
        $pgForm  = wtkFormSelect('wtkEmailTemplate', 'EmailHTM', $pgSQL, $pgSqlFilter, 'LookupDisplay', 'LookupValue', 'Pick HTML Template', 's12');
        $pgSelectHTM = wtkReplace($pgForm, 'wtkwtkEmailTemplateEmailHTM','EmailHTM');
        //  END  allow choosing HTML template
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
        $pgForm .= wtkFormHidden('Mode', 'SendOne');
        $pgForm .= wtkFormWriteUpdField();
        if ($gloDbConnection == 'Live'):
            $pgDevNote = '';
            $pgDevDisable = '';
            $pgDevMsg = '';
        else:
            $pgDevNote  = '<p class="blue-text">Since DB connection is ' . $gloDbConnection;
            $pgDevNote .= ' this will be sent to <b>' . $gloTechSupport . '</b>.';
            $pgDevNote .= ' Change $gloDbConnection to "Live" to be able to change email address or select from User drop list.</p><br>';
            $pgDevDisable = 'disabled';
            $pgDevMsg = '&nbsp;&nbsp; <em class="red-text">disabled because not Live site</em>';
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

        $pgHtm =<<<htmVAR
<div class="modal-content">
    <form id="FemailResults" name="FemailResults" class="card content b-shadow">
        <div class="row">
            <div class="col s12">
                <p>Choose who to send email to:</p>
                <div class="switch">
                    <label>To User
                        <input type="checkbox" $pgDevDisable checked value="Email" id="UserOrEmail" name="UserOrEmail" onclick="JavaScript:showEmailOrUser(this.value)">
                        <span class="lever"></span>
                        Non-User Email $pgDevMsg
                    </label>
                </div><br>
                <div class="switch">
                    <label>From $gloEmailFromAddress
                        <input type="checkbox" value="Y" id="UseProgLabs" name="UseProgLabs">
                        <span class="lever"></span>
                        From $pgCustomFromEmail
                    </label>
                </div>
            </div>
            $pgSelectHTM
        </div>
        <hr><br>
        <div class="row">
            $pgForm
            <div id="ToEmailDIV" class="input-field col s12">
                <input $pgDevDisable id="ToEmail" name="ToEmail" type="email" value="$gloTechSupport" class="validate" onchange="JavaScript:wtkValidate(this,'EMAIL')">
                <label for="ToEmail" class="active">To Email</label>
            </div>
            <div class="input-field col s12">
                <input type="text" required id="Subject" name="Subject" value="">
                <label id="labelSubject" for="Subject">Subject</label>
            </div>
            <div class="input-field col s12">
                $pgTinyMCE
                <textarea required id="EmailMsg" name="EmailMsg" class="materialize-textarea"></textarea>
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
    <a class="btn-primary btn-small b-shadow waves-effect waves-light" onclick="JavaScript:adminValidateEmail('emailWriter')">Send</a>
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
        $pgUseProgLabs = wtkGetPost('UseProgLabs');
        if ($pgUseProgLabs == 'Y'):
            $gloEmailFromAddress = $pgCustomFromEmail;
        endif;
        $pgEmailBody = wtkReplace($pgEmailBody, '@email@', $pgToEmail);
        $pgTmp = wtkNotifyViaEmail($pgSubject, $pgEmailBody, $pgToEmail, [],'',$pgTemplate . '.htm');
        $pgHtm .= '<span class="chip green white-text">Sent "' . $pgSubject . '" email to: ' . $pgToEmail . '</span>';
        break;
endswitch;
$pgHtm = wtkReplace($pgHtm, 'btn  btn', 'btn btn');

echo $pgHtm;
wtkAddUserHistory();
exit;
?>
