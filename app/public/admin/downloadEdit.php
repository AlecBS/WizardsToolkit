<?PHP
$pgSecurityLevel = 25;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `FileName`, `FileDescription`, `FileLocation`
  FROM `wtkDownloads`
WHERE `UID` = ?
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/downloadEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h2>Download</h2><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkDownloads', 'FileName','text','','m12 s12');
$pgHtm .= wtkFormTextArea('wtkDownloads', 'FileDescription', '', 'm12 s12');
$pgHtm .= wtkFormText('wtkDownloads', 'FileLocation','text','','m12 s12');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/downloadList.php');
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();
$pgHtm .= '</form>' . "\n";

if ($gloWTKmode == 'EDIT'):
    $pgCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkDownloadTracking` WHERE `DownloadUID` = ?', [$gloId]);
    $pgHtm .=<<<htmVAR
    <br>
    <div class="card">
        <div class="card-content">
            <h2>$pgCount downloads so far</h2>
htmVAR;
    if ($pgCount > 0):
        $pgSQL =<<<SQLVAR
SELECT DATE_FORMAT(`AddDate`, '$gloSqlDateTime') AS `Downloaded`, `IPaddress`
  FROM `wtkDownloadTracking`
WHERE `DownloadUID` = ?
ORDER BY `UID` DESC
SQLVAR;
        wtkSetHeaderSort('IPaddress', 'IP Address');
        $pgHtm .= wtkBuildDataBrowse($pgSQL, [$gloId], 'wtkDLtrack');
    endif;
    $pgHtm .= '</div></div>' . "\n";
endif;

$pgHtm .=<<<htmVAR
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
