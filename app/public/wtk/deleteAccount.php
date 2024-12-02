<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgHtm =<<<htmVAR
<div id="deleteModalDIV" class="modal-content">
    <h2>Are you sure?</h2><br>
    <p>If you delete your account you will no longer be able to access
        this app and any website associated with this app.</p>
    <p>This cannot be reversed or un-done.  Once you delete your account
        all your history is forever gone and unavailable.</p>
</div>
<div id="deleteModalFooter" class="modal-footer">
    <a class="btn btn-save modal-close waves-effect">Cancel</a>
    &nbsp;&nbsp;
    <a onclick="JavaScript:wtkDeleteAccount()" class="btn red waves-effect waves-light">Delete My Account</a>
</div>
<div id="deleteConfirmedDIV" class="hide">
    <h2>Confirmed</h2><br>
    <p>The process for deleting your account has started.<br>
        We will send you one final email confirmation once it has completed.<br>
        After that your email account will also be removed from our contact list.</p>
</div>
htmVAR;

echo $pgHtm;
wtkAddUserHistory();
exit; // no display needed, handled via JS and spa.htm
?>
