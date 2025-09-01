<?PHP
$pgSecurityLevel = 30;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgType = wtkGetPost('type');
$pgName = wtkGetPost('name');
$pgName = mb_strtolower($pgName);

$pgSqlFilter = array('Zero' => 0);

$pgSQL =<<<SQLVAR
SELECT u.`UID`, CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,''), ' (', L.`LookupDisplay`, ')') AS `Name`
 	FROM `wtkUsers` u
 	  INNER JOIN `wtkLookups` L ON L.`LookupType` = 'SecurityLevel' AND L.`LookupValue` = u.`SecurityLevel`
 WHERE u.`DelDate` IS NULL AND u.`UID` > :Zero
    AND LOWER(u.`FirstName`) LIKE '%$pgName%'
SQLVAR;
switch ($pgType):
    case 'staff':
        $pgSQL .= ' AND u.`SecurityLevel` BETWEEN 20 AND 40';
        break;
    case 'customers':
        $pgSQL .= ' AND u.`SecurityLevel` < 10';
        break;
endswitch;
$pgSQL .= ' ORDER BY u.`FirstName` ASC, u.`LastName` ASC';

$pgSQL = wtkSqlPrep($pgSQL);
$pgHtm = '';
$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);
while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
    $pgUserUID = wtkSqlValue('UID');
    $pgUserName = wtkSqlValue('Name');
    $pgUserName = wtkReplace($pgUserName, "'","&rsquo;");
    switch ($pgType):
        case 'justList':
            $pgHtm .= '<li>' . $pgUserName . '</li>' . "\n";
            break;
        default:
            $pgHtm .= '<li><a onclick="JavaScript:chooseUser(' . $pgUserUID . ', \'' . $pgUserName . '\')">' . $pgUserName . '</a></li>' . "\n";
            break;
    endswitch;
endwhile;
unset($pgPDO);

echo $pgHtm;
exit;
?>
