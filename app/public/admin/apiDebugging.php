<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
$gloSkipFooter = true;

if ($gloDriver1 == 'pgsql'):
    $pgDate = wtkSqlDateFormat('AddDate','Time','Mon DD FMHH:MI:SS');
else: // assume mySQL
    $pgDate = wtkSqlDateFormat('AddDate','Time','%b %D %k:%i:%s');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, $pgDate,
   `FromPage`, `ErrType` AS `ErrorType`, `ErrMsg` AS `ErrorMessage`
  FROM `wtkErrorLog`
WHERE `DelDate` IS NULL
ORDER BY `UID` DESC LIMIT 3
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$gloEditPage = '/admin/errorLogEdit';
$gloDelPage  = 'wtkErrorLogDelDate'; // have DelDate at end if should DelDate instead of DELETE
$pgErrorList = wtkBuildDataBrowse($pgSQL, [],'wtkErrorLog');
$pgErrorList = wtkReplace($pgErrorList, 'There is no data available.','no errors yet');

$pgSQL =<<<SQLVAR
SELECT $pgDate, `DevNote`
  FROM `wtkDebug`
ORDER BY `UID` DESC LIMIT 6
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$gloEditPage = '';
$gloDelPage  = '';
$pgDebugList = wtkBuildDataBrowse($pgSQL, []);
$pgDebugList = wtkReplace($pgDebugList, 'There is no data available.','no debug notes yet');

$pgSQL =<<<SQLVAR
SELECT $pgDate, `InboundSource`, `InboundText`
  FROM `wtkInboundLog`
ORDER BY `UID` DESC LIMIT 4
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$pgInboundList = wtkBuildDataBrowse($pgSQL, []);
$pgInboundList = wtkReplace($pgInboundList, 'There is no data available.','no inbound logs yet');

$pgSQL =<<<SQLVAR
SELECT $pgDate, `ActionType`, `TriggerTime`, `StartTime`, `CompletedTime`,
    `ForUserUID`,`Param1UID`,`Param2UID`,`Param1Str`,`Param2Str`
  FROM `wtkBackgroundActions`
ORDER BY `UID` DESC LIMIT 4
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$gloColumnAlignArray = array (
    'ForUserUID' => 'center',
    'Param1UID' => 'center',
    'Param2UID' => 'center',
    'Param1Str' => 'center',
    'Param2Str' => 'center'
);
$pgBackgroundList = wtkBuildDataBrowse($pgSQL, []);
$pgBackgroundList = wtkReplace($pgBackgroundList, 'There is no data available.','no background actions yet');

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col m12">
        <h4>WTK API Debugger <small class="right"><a onclick="ajaxGo('apiDebugging')">Refresh</a></small></h4>
        <p>This page helps with debugging, especially for API calls, showing the most recent rows added
            into the core tables for debugging.</p>
        <div class="wtk-list card b-shadow">
            <br><h4>Recent wtkErrorLog</h4>
            $pgErrorList
        </div>
        <div class="wtk-list card b-shadow">
            <br><h4>Recent wtkDebug</h4>
            $pgDebugList
        </div>
        <div class="wtk-list card b-shadow">
            <br><h4>Recent wtkInboundLog</h4>
            $pgInboundList
        </div>
        <div class="wtk-list card b-shadow">
            <br><h4>Recent wtkBackgroundActions</h4>
            $pgBackgroundList
        </div>
    </div>
</div>
htmVAR;

echo $pgHtm;
exit;
?>
