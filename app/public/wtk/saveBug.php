<?PHP
//$gloLoginRequired = false; // uncomment this line if called from pages that do not require logging in
define('_RootPATH', '../');
require('wtkLogin.php');
$pgIPaddress = wtkGetIPaddress();

$pgBrowser = safe_get_browser();
if ($pgBrowser == ''):
    $pgInternalNote = 'get_browser note working in PHP';
    $pgBrowserName  = 'unknown';
    $pgBrowserVer   = 'unknown';
else:
    // Handle failure gracefully
    $pgInternalNote = 'Browser says OS: ' . trim(substr($pgBrowser['platform'], 0, 25));
    $pgBrowserName  = trim(substr($pgBrowser['browser'], 0, 20));
    $pgBrowserVer   = trim(substr($pgBrowser['version'], 0, 12));
endif;

$pgBugMsg = wtkGetPost('bugMsg');
$pgBugMsg = wtkEscapeStringForDB($pgBugMsg);

if (!isset($pgLoginAppVer)):
    $pgLoginAppVer = 'n/a';
endif;

$pgSQL =<<<SQLVAR
INSERT INTO `wtkBugReport`
 (`CreatedByUserUID`, `IpAddress`, `AppVersion`, `Browser`, `BrowserVer`, `DeviceType`, `BugMsg`, `InternalNote`)
 VALUES (:UserUID, :IpAddress, :AppVersion, :Browser, :BrowserVer, :DeviceType, :BugMsg, :InternalNote)
SQLVAR;
$pgSqlFilter = array (
    'UserUID' => $gloUserUID,
    'IpAddress' => $pgIPaddress,
    'Browser' => $pgBrowserName,
    'BrowserVer' => $pgBrowserVer,
    'AppVersion' => $pgLoginAppVer,
    'DeviceType' => $gloDeviceType,
    'BugMsg' => $pgBugMsg,
    'InternalNote' => $pgInternalNote
);
wtkSqlExec($pgSQL, $pgSqlFilter);
$pgSqlFilter = array('UserUID' => $gloUserUID);

$pgSQL = "SELECT `UID` FROM `wtkBugReport` WHERE `CreatedByUserUID` = :UserUID ORDER BY `UID` DESC LIMIT 1";
$pgUID = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);

if ($gloUserUID == 0):
    $pgFirstName = 'Person not signed in';
    $pgEmail = 'unknown email';
else:
    $pgSQL =<<<SQLVAR
SELECT `FirstName`, `Email`
 FROM `wtkUsers`
WHERE `UID` = :UserUID
SQLVAR;
    wtkSqlGetRow($pgSQL, $pgSqlFilter);
    $pgFirstName = wtkSqlValue('FirstName');
    $pgEmail = wtkSqlValue('Email');
endif;

$pgEmailMsg  = "<h3>Question from $gloCoName app</h3>";
$pgEmailMsg .= "<p>$pgFirstName ($pgEmail) has reported:</p><hr>";
$pgEmailMsg .= nl2br($pgBugMsg) . '<hr>';
$pgEmailMsg .= '<p>More technical details are stored in wtkBugReport table. Check it out at: ' . $gloWebBaseURL . '/admin/bugView.php?id=' . $pgUID . ' .</p>';
$pgSaveArray = array (
    'FromUID' => $gloUserUID
);

wtkNotifyViaEmail('Bug Report', $pgEmailMsg, $gloTechSupport, $pgSaveArray, '', 'default', $pgEmail);
exit; // no display needed, handled via JS and spa.htm
?>
