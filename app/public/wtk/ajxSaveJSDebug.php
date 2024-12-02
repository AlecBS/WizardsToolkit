<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgDebugLog = wtkGetPost('DebugLog');
$pgDebugLog = wtkReplace($pgDebugLog, '<br>',"\n");
$pgSQL =<<<SQLVAR
INSERT INTO `wtkErrorLog` (`UserUID`, `ErrType`, `ErrMsg`, `FromPage`, `ReferralPage`)
  VALUES (:UserUID, :ErrType, :ErrMsg, :CurrentPage, :ReferPage)
SQLVAR;

$pgReferPage = wtkGetServer('HTTP_REFERER');
if ($pgReferPage != ''):
    $pgReferPage = trim(substr($pgReferPage, 0, 120));
    $pgReferPage = wtkReplace(trim(substr($pgReferPage, 0, 120)), "'", "''");
else:   // Not $pgReferPage != ''
    $pgReferPage = 'NULL';
endif;  // $pgReferPage != ''

$pgFilter = array (
    'UserUID' => $gloUserUID,
    'ErrType' => 'JS Debug Log',
    'ErrMsg' =>  $pgDebugLog,
    'CurrentPage' => 'ajxSaveJSDebug.php',
    'ReferPage' => isset($pgReferPage) ? $pgReferPage : null
);
wtkSqlExec($pgSQL, $pgFilter);

echo '{"result":"success"}';
exit; // no display needed, handled via JS and spa.htm
?>
