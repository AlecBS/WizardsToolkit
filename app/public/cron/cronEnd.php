<?PHP
if ($gloDebug != ''):
    $pgHtm .= '<hr><h4>Debug</h4>' . $gloDebug . '<hr>' . "\n";
//  $pgHtm .= '<hr>' . $pgSQL . '<hr>' . "\n";
endif;

$pgTimePassed = (((hrtime(true) - $pgStartTime)/1e+6)/1000);
$pgHtm .= '<br><p>Time it took: ' . $pgTimePassed . ' seconds.</p>' . "\n";

wtkSearchReplace('col m4 offset-m4 s12','col m6 offset-m3 s12');
wtkMergePage($pgHtm, 'CRON Jobs', _WTK_RootPATH . '/htm/minibox.htm');
?>
