<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgResults = '';
$pgType = wtkGetParam('Type');
$pgRanNum = wtkGetParam('Random');

// wtkSqlExec("INSERT INTO `wtkDebug` (`DevNote`) VALUES ('Type: $pgType, RanNum: $pgRanNum')", []);

// echo '{"result":"' . $pgRanNum . '"}';
exit; // no display because called by cURL to emulate page
?>
