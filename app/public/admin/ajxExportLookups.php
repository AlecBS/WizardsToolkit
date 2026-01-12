<?PHP
$pgSecurityLevel = 95;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgFilterValue = wtkGetPost('p');
if ($pgFilterValue == ''):
	$pgFilterValue = wtkFilterRequest('wtkFilter');
endif;

$pgSQL =<<<SQLVAR
SELECT GROUP_CONCAT(CONCAT('(\'', `LookupType`, '\',\'', `LookupValue`, '\',\'', `LookupDisplay`, '\')')) AS `Values`
FROM `wtkLookups`
WHERE `DelDate` IS NULL AND lower(`LookupType`) LIKE lower('%$pgFilterValue%')
ORDER BY `LookupType` ASC, `UID` ASC
SQLVAR;
$pgValues = wtkSqlGetOneResult($pgSQL, []);
$pgValues = wtkReplace($pgValues, '),(', "),\n(");

$pgHtm =<<<htmVAR
<pre style='padding-left:27px'><code class="code-text">
INSERT INTO `wtkLookups` (`LookupType`, `LookupValue`, `LookupDisplay`)
 VALUES
$pgValues   
</code></pre>
htmVAR;

echo $pgHtm;
exit; // no display needed, handled via JS and spa.htm
?>
