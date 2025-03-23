<?PHP
$pgSecurityLevel = 90;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `PaymentProvider`, `EcomLogin`, `EcomPassword`, `EcomWebsite`, `EcomPayLink`, `EcomNote`
  FROM `wtkEcommerce`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/admin/ecomEdit.php', $gloId);
    wtkSqlGetRow($pgSQL, [$gloId]);
endif;

if ($gloWTKmode == 'MODAL'):
    $gloForceRO = true;
    $pgHtm  =<<<htmVAR
<div class="modal-content">
    <h3>Payment Processor</h3><br>
    <div class="card">
        <div class="card-content">
            <div class="row">
htmVAR;
else:
    $pgHtm =<<<htmVAR
<div class="container">
    <h4>Update Payment Processor</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;
endif;

$pgHtm .= wtkFormText('wtkEcommerce', 'PaymentProvider', 'text', '','m4 s12');
$pgHtm .= wtkFormText('wtkEcommerce', 'EcomLogin','text','Login','m4 s12');
$pgHtm .= wtkFormText('wtkEcommerce', 'EcomPassword', 'password','Password','m4 s12');
$pgHtm .= wtkFormText('wtkEcommerce', 'EcomWebsite','text','Website');
$pgHtm .= wtkFormText('wtkEcommerce', 'EcomPayLink','text','Payment URL');
$pgHtm .= wtkFormTextArea('wtkEcommerce', 'EcomNote','Note');

if ($gloWTKmode == 'MODAL'):
    $pgHtm .=<<<htmVAR
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div class="center">
           <button type="button" class="btn-small black b-shadow waves-effect waves-light modal-close">Return</button>
        </div>
    </div>
</div>
htmVAR;
else:
    $pgHtm .= wtkFormHidden('ID1', $gloId);
    $pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
    $pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
    $pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/ecomList.php');
    $pgHtm .= '            </div>' . "\n";
    $pgHtm .= wtkUpdateBtns() . "\n";
    $pgHtm .= wtkFormWriteUpdField();

    $pgHtm .=<<<htmVAR
        </form>
    </div>
</div>
htmVAR;
endif;

echo $pgHtm;
exit;
?>
