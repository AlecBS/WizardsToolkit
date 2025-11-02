<?PHP
$pgSecurityLevel = @SecLevel@;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `@GUID@`, @UpSQL@
  FROM `@Table@`
WHERE `@GUID@` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/@FormFileName@.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

$pgHtm =<<<htmVAR
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <div class="flex items-center gap-2">
                <h4 class="text-2xl font-semibold text-gray-800 whitespace-nowrap">
                    @FormTitle@
                </h4>
            </div>
        </div>

        <form method="post" name="wtkForm" id="wtkForm" role="search" class="bg-white p-4 rounded-lg shadow-md mb-6">
        <div class="grid grid-cols-1 md:grid-cols-@ColumnCount@ gap-4">
htmVAR;

@FormData@
$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('@GUID@'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/@BrowseFileName@.php');
//$pgHtm .= wtkFormPrimeField('@Table@', 'ParentUID', $gloRNG);
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
// change to below if you want Copy button also
// $pgHtm .= wtkUpdateBtns('wtkForm', '/wtk/lib/Save', 'Y'); // third parameter adds Copy button
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </form>
    </div>
htmVAR;
echo $pgHtm;
exit;
?>
