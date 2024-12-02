<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');
$gloWTKmode = 'ADD';

$pgAudienceType = wtkGetPost('p');
switch ($pgAudienceType):
    case 'D': // department, aka staff role
        $pgSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay` AS `Display`
  FROM `wtkLookups`
 WHERE `DelDate` IS NULL AND `LookupType` = :Filter
ORDER BY `UID` ASC
SQLVAR;
        $pgSqlFilter = array ('Filter' => 'StaffRole');
        $pgHtm = wtkFormSelect('wtkNotifications', 'ToStaffRole', $pgSQL, $pgSqlFilter, 'Display', 'LookupValue','Pick Department');
        break;
    case 'S': // Staff
        $pgSQL =<<<SQLVAR
SELECT `UID`, CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `Display`
  FROM `wtkUsers`
 WHERE `DelDate` IS NULL AND `SecurityLevel` > :Filter
ORDER BY `FirstName` ASC, `LastName` ASC
SQLVAR;
        $pgSqlFilter = array ('Filter' => '49');
        $pgHtm = wtkFormSelect('wtkNotifications', 'ToUID', $pgSQL, $pgSqlFilter, 'Display', 'UID','Pick Staff');
        break;
    case 'C': // Client
    case 'R': // Researcher
        $pgSQL =<<<SQLVAR
SELECT `UID`, CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `Display`
  FROM `wtkUsers`
 WHERE `DelDate` IS NULL AND `SecurityLevel` = :Filter
ORDER BY `FirstName` ASC, `LastName` ASC
SQLVAR;
        if ($pgAudienceType == 'C'):
            $pgSqlFilter = array ('Filter' => '1');
        else:
            $pgSqlFilter = array ('Filter' => '20');
        endif;
        $pgHtm = wtkFormSelect('wtkNotifications', 'ToUID', $pgSQL, $pgSqlFilter, 'Display', 'UID','Pick Person');
        break;
    default:
        $pgHtm = '<p>Error - something went wrong. Contact tech support.</p>';
        break;
endswitch;
$pgHtm = wtkReplace($pgHtm, '<select id','<select class="browser-default" id');
echo $pgHtm;
exit; // no display needed, handled via JS and spa.htm
?>
