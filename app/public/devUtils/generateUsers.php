<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

wtkSearchReplace('<body ','<body class="blue" ');
wtkPageProtect('wtk4LowCode');

$pgQty = wtkGetPost('Qty');
$pgSecLvl = wtkGetPost('SecLvl');
$pgRole = wtkGetPost('StaffRole');

if ($pgQty != ''):
    if ($pgRole == ''):
        $pgRole = NULL;
    endif;
    $pgSqlFilter = array(
        'Qty' => $pgQty,
        'SecLvl' => $pgSecLvl,
        'Role' => $pgRole
    );
    $gloSkipFooter = true;
    $pgHtm = wtkBuildDataBrowse('CALL generate_wtkUsers(:Qty, :SecLvl, :Role)', $pgSqlFilter);
    echo $pgHtm;
    exit;
else:
	$pgQty = 1;
endif;

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'SecurityLevel' ORDER BY `LookupValue` ASC";
$pgSecOptions = wtkGetSelectOptions($pgSQL, [], 'LookupDisplay', 'LookupValue', $pgSecLvl);

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'StaffRole' ORDER BY `LookupValue` ASC";
$pgRoleOptions = wtkGetSelectOptions($pgSQL, [], 'LookupDisplay', 'LookupValue', $pgRole);

$pgHtm =<<<htmVAR
<h3>Generate fake wtkUsers</h3>
<br>
<p>This provides a quick and easy way to generate unlimited number of wtkUsers for testing.
 You can generate them for any Security Level and Staff Role.</p>
<p>The names, emails and phone numbers will all be fake but the street addresses
 will all be real and searchable on maps. Only 100 can be created at a time.</p>

 <form id="wtkForm" name="wtkForm" action="?" method="POST">
    <div class="row">
        <div class="input-field col m2 offset-m1 s12">
            <input id="Qty" name="Qty" type="number" min="1" max="100" value="$pgQty" class="validate">
            <label for="Qty">How Many?</label>
        </div>
        <div class="input-field col m4 s12">
            <select id="SecLvl" name="SecLvl">
                $pgSecOptions
            </select>
        </div>
        <div class="input-field col m4 s12">
            <select id="StaffRole" name="StaffRole">
                $pgRoleOptions
                <option value=''>none</option>
            </select>
        </div>
    </div>
 </form>
 <input type="hidden" id="HasSelect" name="HasSelect" value="Y">
 <div align="center">
    <br><button class="btn" type="button" onclick="JavaScript:startInserts()" id="startBtn">Start Inserts</button>
 </div>
<hr>
<div id="resultsDIV" class="hide"><br>
    <h3 class="center">Users Added</h3>
    <div class="row">
         <div class="col m12 s12">
            <div id="resultDetail"></div>
         </div>
    </div>
</div>
<script type="text/javascript">

function startInserts(){
    let fncQty = $('#Qty').val();
    if (fncQty > 100){
        wtkAlert('Maximum of 100 each time');
    } else {
        wtkDisableBtn('startBtn');
        $('#resultsDIV').removeClass('hide');
        waitLoad('on');
        let fncFormData = $('#wtkForm').serialize();
        $.ajax({
            type: 'POST',
            url:  'generateUsers.php',
            data: (fncFormData),
            success: function(data) {
                $('#resultDetail').html(data);
                waitLoad('off');
            }
        })
    }
}

</script>
htmVAR;
require('wtkinfo.php');

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, 'User Generator', '../wtk/htm/minibox.htm');
?>
