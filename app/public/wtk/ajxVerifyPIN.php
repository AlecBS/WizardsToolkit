<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$gloUserUID = wtkGetPost('userId');
$pgPIN = wtkGetPost('pin');

$pgSQL = "SELECT COUNT(*) FROM `wtkUsers` WHERE `UID` = :UID AND BINARY `NewPassHash` = :NewPassHash AND `DelDate` IS NULL";
$pgSqlFilter = array (
    'UID' => $gloUserUID,
    'NewPassHash' => $pgPIN
);
$pgCount = wtkSqlGetOneResult(wtkSqlPrep($pgSQL), $pgSqlFilter);
if ($pgCount == 0):
    $pgResult = 'wrongPIN';
    $pgApiKey = '';
else:
    //  END  Save LandingPage data
    $pgApiKey = md5(uniqid(rand(), true));
    $pgAccessMethod = wtkGetPost('AccessMethod','website');

    $pgAppVersion = wtkSqlGetOneResult('SELECT `AppVersion` FROM `wtkCompanySettings` WHERE `UID` = ?', [1]);
    $pgSQL  = 'INSERT INTO `wtkLoginLog` (`FirstLogin`, `AccessMethod`, `CurrentPage`, `UserUID`, `apiKey`, `AppVersion`)';
    $pgSQL .= "  VALUES (NOW(), :AccessMethod, :CurrentPage, :UserUID, :apiKey, :AppVersion)";
    $pgFilter = array (
        'AccessMethod' => $pgAccessMethod,
        'CurrentPage' => 'registered',
        'UserUID' => $gloUserUID,
        'apiKey' => $pgApiKey,
        'AppVersion' => $pgAppVersion
    );
    wtkSqlExec(wtkSqlPrep($pgSQL), $pgFilter);

    $pgResult = 'ok';
endif;
$pgJSON = '{"result":"' . $pgResult . '","regApiKey":"' . $pgApiKey . '"}';
echo $pgJSON;
?>
