<?PHP
$pgSecurityLevel = 50;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgPhone = $gloRNG;

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col s12"><br>
        <h3>Send SMS Text <span class="right">
        <button type="button" class="btn-small black b-shadow waves-effect waves-light modal-close">Cancel</button>
        &nbsp;&nbsp;
        <button id="sendSmsBtn" type="button" class="btn-primary btn-small b-shadow waves-effect waves-light modal-close" onclick="Javascript:wtkSendSMS($gloId)">Send</button>
        </span></h3>
        <br>
        <div class="content b-shadow">
            <form id="smsForm" method="POST">
                <input type="hidden" id="CharCntr" name="CharCntr" value="Y">
                <input type="hidden" id="smsPhone" name="smsPhone" value="$pgPhone">
                <div class="row">
                    <div class="input-field col s12">
                      <textarea required id="smsMsg" name="smsMsg" class="materialize-textarea char-cntr" data-length="255" maxlength="255"></textarea>
                      <label for="smsMsg">SMS Message</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
htmVAR;
echo $pgHtm;
wtkAddUserHistory();
exit;
?>
