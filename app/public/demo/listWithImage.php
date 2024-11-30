<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
/*
To automatially show an image in a listing with MaterializeCSS
responsive functionality, have the file path and file name aliased as 'WTKIMAGE'
like:  CONCAT(`FilePath`, `NewFileName`) AS 'WTKIMAGE'

If you want to show a "no photo" image for NULL/blanks, then use something
like:  COALESCE(CONCAT(`FilePath`, `NewFileName`),'NoImg') AS 'WTKIMAGE'

If you want a header over the column, then use 'wtkImage' instead.
*/
$pgSQL =<<<SQLVAR
SELECT CONCAT(`FilePath`, `NewFileName`) AS 'WTKIMAGE',
    CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `Name`, `City`
  FROM `wtkUsers`
WHERE `NewFileName` IS NOT NULL
 ORDER BY `FirstName` ASC
SQLVAR;
wtkSetHeaderSort('Name');
wtkSetHeaderSort('City');

$pgHtm  = '<h4>Listing with Images</h4>' . "\n";
//wtkSearchReplace('<div align="center">Image</div>','<div align="center">My Staff</div>'); // wtkImage change header demo
//wtkSearchReplace('responsive-img maxh90','responsive-img maxh90 circle'); // add circle feature to images
$pgHtm .= wtkBuildDataBrowse($pgSQL);

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
?>
