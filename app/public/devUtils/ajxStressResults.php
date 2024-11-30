<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

wtkPageProtect('wtk4LowCode');

$pgParam = wtkGetParam('p');
if ($pgParam == 'detail'):
    if ($gloDriver1 == 'pgsql'):
        $pgSQL = 'SELECT to_char(s."AddDate",\'SS.US\') AS "Time",' . "\n";
    else:
        $pgSQL = "SELECT DATE_FORMAT(s.`AddDate`,'%T.%f') AS `Time`," . "\n";
    endif;
    $pgSQL .=<<<SQLVAR
  L.`LookupDisplay` AS `TestType`, s.`CallsPerSec`, s.`SecondsTaken`
FROM `wtkStressTest` s
  INNER JOIN `wtkLookups` L ON L.`LookupType` = 'TestType' AND L.`LookupValue` = s.`TestType`
WHERE s.`UID` > :UID
ORDER BY s.`UID` DESC
SQLVAR;
    $pgBrName = 'stressDetail';
else: // summary
    $pgSQL =<<<SQLVAR
SELECT L.`LookupDisplay` AS `TestType`, SUM(s.`CallsPerSec`) AS `TotalProcessed`
  FROM `wtkStressTest` s
  INNER JOIN `wtkLookups` L ON L.`LookupType` = 'TestType' AND L.`LookupValue` = s.`TestType`
WHERE s.`UID` > :UID
GROUP BY L.`LookupDisplay`
ORDER BY L.`LookupDisplay` ASC
SQLVAR;
    $gloSkipFooter = true;
    $pgBrName = 'stressSummary';
    $gloTotalArray = array (
        'TotalProcessed' => 'SUM'
    );
endif;

$gloColumnAlignArray = array (
    'CallsPerSec' => 'center',
    'SecondsTaken' => 'center',
	'TotalProcessed' => 'center'
);
$pgSqlFilter = array ('UID' => $gloRNG);
$pgList = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, $pgBrName, 'ajxStressResults.php');
if ($pgParam == 'summary'):
    $pgList = wtkReplace($pgList,'<table','<table style="max-width: 270px;margin: 0 auto;"');
endif;
echo $pgList;
exit; // no display because called by cURL to emulate page
?>
