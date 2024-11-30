<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgHtm  = '<h4>Browse from Stored Procedure</h4>' . "\n";
$pgHtm .= wtkBuildDataBrowse('call st_DemoRpt()');

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, 'Stored Proc Demo', '../wtk/htm/minibox.htm');
/*
Above shows how any stored procedure that returns a list of results can be
called same as a SQL SELECT.  Here's an example stored procedure:

DELIMITER $$
CREATE PROCEDURE `st_DemoRpt`()
  BEGIN
    SELECT FirstName, LastName, Email
      FROM `wtkUsers`
    ORDER BY `UID` DESC;
  END
$$
*/
?>
