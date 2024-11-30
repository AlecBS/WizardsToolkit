<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgHtm  = 'Hello World';

wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
?>
