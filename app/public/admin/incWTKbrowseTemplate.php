<?PHP
$pgSecurityLevel = @SecLevel@;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `@GUID@`, @BrSQL@
  FROM `@Table@`
WHERE @Where@
SQLVAR;
@incFilterWhere@@incFilter2Where@
$pgSQL .= ' ORDER BY @OrderBy@ ASC';

$gloEditPage = '@UpPHPfilename@';
$gloAddPage  = $gloEditPage;
$gloDelPage  = '@Table@DelDate'; // have DelDate at end if should DelDate instead of DELETE

// If you want phone version to show less columns...
// if ($gloDeviceType == 'phone'):
//     $pgSQL = wtkReplace($pgSQL, ', `ExtraColumns`','');
// endif;

// put in columns you want sortable here:
//wtkSetHeaderSort('ColumnName', 'Column Header');
//wtkFillSuppressArray('ColumnName');

//$gloColumnAlignArray = array (
//    'Priority'   => 'center'
//);

/*
$gloMoreButtons = array(
    'User Logins' => array(
            'act' => 'pageName',
            'img' => 'arrow-right'
            )
    );
*/
$pgHtm =<<<htmVAR
<div class="container">
    <h4>@BrowseTitle@</h4>
@incFilterBox@
    <div class="wtk-list card b-shadow">
htmVAR;
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], '@Table@', '/admin/@FileName@.php');
//$pgHtm  = wtkReplace($pgHtm, 'No data.','no @Table@ data yet');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
