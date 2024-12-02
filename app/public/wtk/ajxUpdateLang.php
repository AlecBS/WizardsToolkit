<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgLanguage = wtkGetGet('Lang');
wtkSetCookie('wtkLang', $pgLanguage);

$pgSQL =<<<SQLVAR
SELECT `MassUpdateId`, `NewText`
  FROM `wtkLanguage`
WHERE `MassUpdateId` IS NOT NULL AND `Language` = :Language
    AND COALESCE(`NewText`,'') <> ''
SQLVAR;

$pgSqlFilter = array (
    'Language' => $pgLanguage
);
$pgJSON = '{';
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);
while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
    if ($pgJSON != '{'):
        $pgJSON .= ',';
    endif;
    $pgJSON .= '"' . $gloPDOrow['MassUpdateId'] . '":"' . $gloPDOrow['NewText'] . '"';
endwhile;
unset($pgPDO);
$pgJSON .= '}';

//$pgJSON = '{"langWelcome":"Yo Dude","langBye":"bye dude!","langEmail":"My Email","langPW":"My Secret"}';
echo $pgJSON;
exit;
?>
