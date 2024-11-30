<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSQL = 'SELECT `FirstName`, `LastName`, `City` FROM `wtkUsers`';
/*
$gloSkipFooter = true;
$gloRowsPerPage = 15;
*/
wtkFillBrowsePage($pgSQL);
//wtkFillBrowsePage($pgSQL,[],'','','N','../wtk/htm/minibox.htm'); // optionally change HTML template
?>
