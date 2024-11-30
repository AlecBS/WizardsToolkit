<?php
$gloLoginRequired = false;
require('wtk/wtkLogin.php');

wtkTrackVisitor('Documentation');

// change mpa.htm below to your marketing.htm template or add your marketing HTML in this file
$pgHtm =<<<htmVAR
<br>
    <div class="card b-shadow">
        <div class="card-content">
<h3>Documentation of Features</h3>
<p>This is just a short list of some of the features available within our website pulled
 directly from our internal Help system.</p>
htmVAR;

$pgSQL =<<<SQLVAR
SELECT `HelpTitle`, `HelpText`
  FROM `wtkHelp`
WHERE `HelpTitle` != :Exclude
ORDER BY `HelpTitle` ASC
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
$pgSqlFilter = array (
    'Exclude' => 'Need to Define'
);

$pgPDO = $gloWTKobjConn->prepare($pgSQL);
$pgPDO->execute($pgSqlFilter);
$pgHelp = '';
while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
    $pgHelp .= '<hr>' . "\n";
    $pgHelp .= '<h3>' . $gloPDOrow['HelpTitle'] . '</h3>' . "\n";
    $pgHelp .= $gloPDOrow['HelpText'] . "\n";
endwhile;
unset($pgPDO);

$pgHtm .= $pgHelp . '</div></div>' . "\n";

wtkMergePage($pgHtm, $gloCoName, 'wtk/htm/mpa.htm');
?>
