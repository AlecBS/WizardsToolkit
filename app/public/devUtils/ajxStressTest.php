<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

wtkPageProtect('wtk4LowCode');
session_write_close();

$pgResults = '';
$pgType = wtkGetParam('Type');
$pgCallsPerSec = wtkGetParam('Count');

if ($pgType == 'start'):
    $pgId = wtkSqlGetOneResult('SELECT `UID` FROM `wtkStressTest` ORDER BY `UID` DESC LIMIT 1', [],0);
    echo '{"id":"' . $pgId . '"}';
    exit;
endif;

$pgSQL =<<<SQLVAR
INSERT INTO `wtkStressTest` (`TestType`, `CallsPerSec`)
 VALUES (:TestType, :CallsPerSec)
SQLVAR;
$pgSqlFilter = array (
    'TestType' => $pgType,
    'CallsPerSec' => $pgCallsPerSec
);
wtkSqlExec($pgSQL, $pgSqlFilter);
$pgLastUID = wtkSqlGetOneResult('SELECT `UID` FROM `wtkStressTest` WHERE `TestType` = :TestType AND `CallsPerSec` = :CallsPerSec ORDER BY `UID` DESC LIMIT 1', $pgSqlFilter);
/*
This page will be called once per second for each type of test.

Determine how many microseconds should wait between calls.  A microseconds is one millionth of a second.
*/
switch ($pgType):
    case 'wp':
        $pgResults = 'Web Pages opened';
        $pgUrl = $gloWebBaseURL . '/devUtils/ajxPage.php?Type=wp&Random=';
        break;
    case 'ins':
        $pgResults = 'SQL INSERTs';
        if ($gloDriver1 == 'pgsql'):
            $pgSQL =<<<SQLVAR
INSERT INTO `wtkUsersTST` (`FirstName`, `LastName`, `Phone`)
  VALUES (`generate_fname`(), `generate_lname`(), CONCAT('(',ROUND((RANDOM()*900)+100),') 555-',ROUND((RANDOM()*999)+1000)) )
SQLVAR;
        else:
            $pgSQL =<<<SQLVAR
INSERT INTO `wtkUsersTST` (`FirstName`, `LastName`, `Phone`)
  VALUES (generate_fname(), generate_lname(), CONCAT('(',ROUND((RAND()*900)+100),') 555-',ROUND((RAND()*999)+1000)) )
SQLVAR;
        endif;
        break;
    case 'upd':
        $pgResults = 'SQL UPDATEs';
        break;
    case 'sel40':
        $pgResults = 'SQL SELECT calls (40 rows)';
        $gloRowsPerPage = 40;
        $pgSQL = 'SELECT `UID`, `FirstName`, `LastName`, `Email` FROM `wtkUsersTST` ORDER BY `UID` ASC LIMIT 40 OFFSET :OFFSET';
        break;
    case 'sel250':
        $pgResults = 'SQL SELECT calls (250 rows)';
        $gloRowsPerPage = 250;
        $pgSQL = 'SELECT `UID`, `FirstName`, `LastName`, `Email` FROM `wtkUsersTST` ORDER BY `UID` ASC LIMIT 250 OFFSET :OFFSET';
        break;
    case 'del':
        $pgResults = 'SQL DELETE calls (1 row)';
        break;
    default:
        $pgResults = 'unknown call';
        break;
endswitch;
$pgMaxOffset = round((10000 / $gloRowsPerPage),0);

$pgLoopStart = microtime(true);
$pgPageTime = round(microtime(true) - $gloPageStart,4);
$pgWaitTime = round(((1000000 - $pgPageTime) / $pgCallsPerSec),0) - 5;
$pgOrigWait = $pgWaitTime;
$pgCounter = 0;

while ($pgCounter < $pgCallsPerSec):
    $pgWhileStart = microtime(true);
    $pgCounter ++;
    switch ($pgType):
        case 'wp':
            $pgGoTo = $pgUrl . rand(1,99999);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $pgGoTo);
            $pgTmp = curl_exec($ch);
            curl_close($ch);
            break;
        case 'ins':
            wtkSqlExec($pgSQL, []);
            break;
        case 'upd':
            $pgRanNum = rand(1,11);
            $pgSqlFilter = array('UID' => $pgRanNum);
            wtkSqlExec('UPDATE `wtkUsersTST` SET `FirstName` = generate_fname() WHERE `UID` = :UID', $pgSqlFilter);
            break;
        case 'del':
            if ($gloDriver1 == 'pgsql'):
                $pgUIDresult = 0;
                while ($pgUIDresult == 0):
                    $pgRanNum = rand(1,11000); // may need to have top value increase if have done lots of inserts and deletes
                    $pgSqlFilter = array('UID' => $pgRanNum);
                    $pgUIDresult = wtkSqlGetOneResult('DELETE FROM "wtkUsersTST" WHERE "UID" = :UID RETURNING "UID"', $pgSqlFilter,0);
                endwhile;
            else:
                $pgRanNum = rand(2500,13000); // may need to have top value increase if have done lots of inserts and deletes
                $pgSqlFilter = array('UID' => $pgRanNum);
                wtkSqlExec('DELETE FROM `wtkUsersTST` WHERE `UID` <= :UID ORDER BY `UID` DESC LIMIT 1', $pgSqlFilter);
            endif;
            break;
        case 'sel40':
        case 'sel250':
            $pgRanNum = rand(2,$pgMaxOffset);
            $pgOffset = (($pgRanNum - 1) * $gloRowsPerPage);
            $pgSqlFilter = array('OFFSET' => $pgOffset);
            $pgTmp = wtkGetSelectOptions($pgSQL, $pgSqlFilter, 'FirstName', 'UID', 9);
            break;
        default:
            $pgResults = 'unknown call';
            break;
    endswitch;
    if ($pgCounter == 1): // adjust wait time based on how long first action took
        $pgLoopTime = (microtime(true) - $pgWhileStart); // how long it took to do above process
        $pgProcessingTime = ($pgLoopTime * $pgCallsPerSec);
        $pgTimeToSplit = (1000000 - $pgProcessingTime);
        $pgWaitTime = round(($pgTimeToSplit / $pgCallsPerSec),0);
    else:
        $pgLoopTime = round(microtime(true) - $pgLoopStart,4);
        // this is percentage of a second that has elapsed shown like .1435 would be 14.35%
        // value * 100 to get percentage
        // then know what percentage of second is remaining which could be displayed as .8565 of a second remaining
        // divide that by remaining count to process to determine wait between each count
        // but multiply it by 100 for USLEEP()
        // and take into consideration how much time spent on loop?  Maybe not since recalculating with each pass
        $pgTimeRemaining = (1 - $pgLoopTime);
        if ($pgCounter != $pgCallsPerSec):
            $pgLoopTime = (microtime(true) - $pgWhileStart); // how long it took to do above process
            $pgWaitTime = round((($pgTimeRemaining / ($pgCallsPerSec - $pgCounter)) * 1000000),0);
        endif;
    endif;
    if ($pgWaitTime > 1):
        usleep($pgWaitTime); // wait before doing it again
    endif;
    //    wtkSqlExec("INSERT INTO `wtkDebug` (`DevNote`) VALUES ('Type: $pgType, RanNum: $pgRanNum')", []);
endwhile;
$pgResults .= ': ' . $pgCounter;

$pgPageTime = round(microtime(true) - $gloPageStart,4);
$pgSqlFilter = array (
    'UID' => $pgLastUID,
    'SecondsTaken' => $pgPageTime
);
wtkSqlExec('UPDATE `wtkStressTest` SET `SecondsTaken` = :SecondsTaken WHERE `UID` = :UID', $pgSqlFilter);

echo '{"result":"' . $pgResults . '"}';
// ',"pageStart":"' . $gloPageStart . '","pageTime":"' . $pgPageTime . '","origWait":"' . $pgOrigWait . '","newWait":"' . $pgWaitTime . '"}';
exit; // no display needed, handled via JS
?>
