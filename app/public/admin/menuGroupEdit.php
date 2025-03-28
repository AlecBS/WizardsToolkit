<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `MenuUID`, `GroupName`, `GroupURL`
  FROM `wtkMenuGroups`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/menuGroupEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
    $pgMenuUID = wtkSqlValue('MenuUID');
else:
    $pgMenuUID = $gloRNG;
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Menu Group</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
                <div class="col s12">
                    <p>&ldquo;Menu Grouping&rdquo; will be shown across the top navbar.</p>
                    <p>If &ldquo;Group URL&rdquo; is left empty (which is usually desired),
                      then clicking the &ldquo;Menu Grouping&rdquo; will show drop list of menu items
                      that you can choose from the
                      <a onclick="JavaScript:ajaxGo('/admin/pageList')">Page List</a>.</p>
                    <p>If you want the top navbar &ldquo;Menu Grouping&rdquo; to go directly to a page,
                       then fill in the &ldquo;Group URL&rdquo; with the path and page
                       name excluding the `.php`,
                       <br>for example &ldquo;/admin/helpList&rdquo;</p>
                    <br><hr><br>
                </div>
htmVAR;

$pgHtm .= wtkFormText('wtkMenuGroups','GroupName','text','Menu Grouping');

$pgHtm .= wtkFormText('wtkMenuGroups','GroupURL','url','Group URL','s12','N','use exactly `dashboard` or `logout` to activate those features');

$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/menuGroupList.php');
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('rng', $pgMenuUID);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormPrimeField('wtkMenuGroups', 'MenuUID', $gloRNG);
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
