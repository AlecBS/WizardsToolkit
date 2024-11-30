<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgSQL =<<<SQLVAR
SELECT `UID`, `LookupType`, `LookupValue`, `LookupDisplay`
  FROM `wtkLookups`
ORDER BY `LookupType` ASC, `LookupValue` ASC
SQLVAR;

// next two lines are optional and will make Export buttons/feature show
$gloShowExport = true;
$gloShowExportXML = true;

wtkFillBrowsePage($pgSQL,[],'','','N','demoMPA.htm');
?>
