<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

// You define the next function in any way you like
function wtkRowFunction($fncHtmRow, $fncData){
    $fncNewRow = $fncHtmRow;
    if ($fncData['PetType'] == 'Dog'):
        $fncNewRow = wtkReplace($fncNewRow, '<tr id','<tr class="deep-purple lighten-3" id');
    endif;
    if ($fncData['CanTreat'] == 'CanTreatY'):
        $fncNewRow = wtkReplace($fncNewRow, 'CanTreatY','<i class="material-icons green-text">pets</i>');
    else:
        $fncNewRow = wtkReplace($fncNewRow, 'CanTreatN','<i class="material-icons red-text">pets</i>');
    endif;
    return $fncNewRow;
}

$pgSQL =<<<SQLVAR
SELECT p.`UID`, p.`PetName`, p.`City`,
    L.`LookupDisplay` AS `PetType`,
    CONCAT('CanTreat',p.`CanTreat`) AS `CanTreat`,
    `fncContactIcons`(p.`OwnerEmail`,p.`OwnerPhone`,p.`Latitude`,p.`Longitude`,'Y',p.`UID`,'Y','Y','') AS `OwnerContact`
  FROM `pets` p
   LEFT OUTER JOIN `wtkLookups` L ON L.`LookupType` = 'PetType' AND L.`LookupValue` = p.`PetType`
WHERE p.`DelDate` IS NULL
SQLVAR;

wtkSearchReplaceRow('@RunFunction@', 'this parameter not used for RunFunction');
// above line is what triggers wtkRowFunction to be called for every row

$gloColumnAlignArray = array (
	'CanTreat' => 'center'
);

$pgHtm =<<<htmVAR
<h4>Listing with Row Function</h4>
<p>When you really need to customize rows within your list based on complex rules, you can use the @RunFunction@ feature.</p>
<p>You define wtkRowFunction any way you like.  In /wtk/lib/BrowsePDO.php it will be called for every row
and passed both the generated HTML as well as the fncData array holding all the data results for that row.</p>

<p>Whatever you return will be used as the HTML for that table row.</p>
<p>Below is an example where all 'Dog' rows should be purple and the pet paw print should be Green if they can have Treats
 otherwise Red if they may not.</p>
<div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL);
$pgHtm .= '</div>' . "\n";

// if you have other wtkBuildDataBrowse calls on the same page
// you need to clear out the array and counter to prevent errors
unset($gloRowChangeArray);
$gloRowChangeArray = [];
$pgSearchReplaceRowCntr = 0;
// end of clearing out array/counter

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
?>
