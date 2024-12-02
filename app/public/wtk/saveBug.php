<?PHP
define('_RootPATH', '../');
require('wtkLogin.php');
$pgIPaddress = wtkGetIPaddress();
$pgBrowser = get_browser(null, true);
$pgInternalNote = 'Browser says OS: ' . trim(substr($pgBrowser['platform'], 0, 25));
$pgBrowserName  = trim(substr($pgBrowser['browser'], 0, 20));
$pgBrowserVer   = trim(substr($pgBrowser['version'], 0, 12));
$pgBugMsg = wtkGetPost('bugMsg');
$pgBugMsg = wtkEscapeStringForDB($pgBugMsg);

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

$pgSQL =<<<SQLVAR
SELECT `FirstName`, `Email`
 FROM `wtkUsers`
WHERE `UID` = :UserUID
SQLVAR;
wtkSqlGetRow($pgSQL, $pgSqlFilter);
$pgFirstName = wtkSqlValue('FirstName');
$pgEmail = wtkSqlValue('Email');

$pgEmailMsg  = "<h3>Bug reported from $gloCoName app</h3>";
$pgEmailMsg .= "<p>$pgFirstName ($pgEmail) has reported:</p><hr>";
$pgEmailMsg .= nl2br($pgBugMsg) . '<hr>';
$pgEmailMsg .= '<p>More technical details are stored in wtkBugReport table. Check it out at: ' . $gloWebBaseURL . '/admin/bugView.php?id=' . $pgUID . ' .</p>';
$pgSaveArray = array (
    'FromUID' => $gloUserUID
);

wtkNotifyViaEmail('Bug Report', $pgEmailMsg, $gloTechSupport, $pgSaveArray, '', 'default', $pgEmail);
exit; // no display needed, handled via JS and spa.htm
?>
