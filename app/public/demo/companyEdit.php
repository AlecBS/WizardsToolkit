<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
    $gloForceRO = wtkPageReadOnlyCheck('/demo/companyEdit.php', 1);
    // in this page last parameter is hard coded to 1; usually it should be the passed $gloId
else:
    $gloFormMsg = 'Your data has been saved.';
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `CoName`, `PayPalEmail`, `DomainName`, `AppVersion`, `EnableLockout`
  FROM `wtkCompanySettings`
WHERE `UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL, [1]);

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Company Setting</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;
if ($gloFormMsg == 'Your data has been saved.'):
    $pgHtm = wtkReplace($pgHtm, 'class="red-text"','class="green-text"');
endif;

$pgHtm .= wtkFormText('wtkCompanySettings', 'CoName', 'text', 'Company Name');
if ($gloWTKmode == 'Copy'): // Copy data feature
    $pgHtm = wtkReplace($pgHtm, ' name="Origwtk', ' name="Copywtk');
    $gloWTKmode = 'ADD';
endif;
$pgTmp  = wtkFormText('wtkCompanySettings', 'PayPalEmail', 'email');
$pgHtm .= wtkReplace($pgTmp, 'Pay Pal','PayPal');
$pgHtm .= wtkFormText('wtkCompanySettings', 'DomainName');
$pgHtm .= wtkFormText('wtkCompanySettings', 'AppVersion');

$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
);
$pgHtm .= wtkFormCheckbox('wtkCompanySettings', 'EnableLockout', '', $pgValues);
$pgHtm .= wtkFormHidden('ID1', 1);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= '            </div>';

// $pgHtm .= wtkUpdateBtns();
// $pgHtm .= wtkFormHidden('wtkGoToURL', '../../demo/companyEdit.php'); // path to this should be based on how it should be called from wtk/lib/Save.php
// use either above 2 lines or below 2 lines
$pgHtm .= wtkFormHidden('wtkGoToURL', 'companyEdit.php'); // path to this should be based on how it should be called from saveCompany.php in current folder
$pgHtm .= wtkUpdateBtns('wtkForm','saveCompany');

$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </form>
    </div>
htmVAR;

// below is only Feature description and is not necessary for working page
$pgHtm .=<<<htmVAR
<br>
<div class="card">
    <div class="card-content">
        <h4>Features on this Page</h4>
        <br>
        <h5>wtkUpdateBtns() and wtkModalUpdateBtns()</h5>
        <p>These PHP functions are defined in /wtk/lib/Materialize.php and build
            the HTML for Cancel, Save and the optional Copy buttons.
          wtkUpdateBtns accepts the following optional parameters.</p>
         <ul class="browser-default">
           <li>form - defaults to 'wtkForm'</li>
           <li>page to post to - defaults to '/wtk/lib/Save'</li>
           <li>copy flag - defaults to '' blank</li>
         </ul>
         <p>wtkUpdateBtns can be called without passing any values, like this page
            does, and just accept the defaults.  If you do pass a value to the
            second parameter, the Post from the save button will post to your page.
            You can then handle the posted values and then include the /wtk/lib/Save.php
            or not, as you choose.</p>
         <p>wtkModalUpdateBtns provides HTML and CSS specific for forms in
            modal windows.  The parameters and methodology is somewhat different
            and will be outlined in a different demo file.</p>
        <br><hr><br>
        <h5>wtkGoToURL Parameter</h5>
        <p>Set a hidden input field to determine where to go after the save
            is finished.</p>
        <br><hr><br>
        <h5>wtk/lib/Save.php</h5>
        <p>After saving, the Save.php page will do one of the following:</p>
        <p>If a hidden field of 'Debug' has a value of 'Y' then instead of saving, the page
            will generate the SQL for the INSERT/UPDATE and provide it for review.
            It will be hidden so anyone seeing the page will just see
            "WTK Save is in debug mode.  Notify developers."
            but if you look at the raw HTML you will see debug information.</p>
        <p>If you have a PHP page which includes the '/wtk/lib/Save.php' then you can set
            the global PHP variable to<br>
            <code>&dollar;gloSkipGoTo = true;</code><br>
            In that case it will process the Save logic but then continue.
            This way you can have code you personally code both before and after the Save process.</p>
        <p>If &dollar;gloSkipGoTo is not set, or is set to false, then after the
            Save.php is complete the page will <code>require</code> the wtkGoToURL
            so that code is executed also.</p>
        <br><hr><br>
        <h5>Enable Lockout</h5>
        <p>When this is checked every page that has code which checks the
        wtkPageReadOnlyCheck() function will be read-only if another user is currently in
        that page.  This is very useful for edit pages so you do not have two users
        editing the data at the same time.  At the top of the page simply add this line:
        <pre><code>&dollar;gloForceRO = wtkPageReadOnlyCheck('/demo/companyEdit.php', &dollar;gloId);</code></pre>
        </p>
        <br><hr><br>
        <h5>Code Examples</h5>
        <p>Look in this file and the optional /demo/saveCompany.php file for code
          example of how this all works.  This file (/demo/companyEdit.php) has
          two methods demonstrated.</p>
        <br>
        <h6>Default Saving Method</h6>
        <p>This is what you will use 90% of the time.  It allows wtkUpdateBtns
          to use the default values for all three parameters.  Make sure your <form>
          has an id of "wtkForm", otherwise you will need to pass your form id as the
          first parameter.</p>
<pre><code>
&dollar;pgHtm .= wtkUpdateBtns();
&dollar;pgHtm .= wtkFormHidden('wtkGoToURL', '../../demo/companyEdit.php');
// path to this should be based on how it should be called from wtk/lib/Save.php
</code></pre>
        <br>
        <h6>Optional Saving Method <small class="green-text">active</small></h6>
        <p>This method shows calling a custom PHP page so you can do extra coding,
            then using the WTK Save.php to handle the regular processing and
            saving of the data.</p>
<pre><code>
&dollar;pgHtm .= wtkFormHidden('wtkGoToURL', 'companyEdit.php'); // path to this should be based on how it should be called from saveCompany.php in current folder
&dollar;pgHtm .= wtkUpdateBtns('wtkForm','saveCompany');
</code></pre>
        <p>In the /demo/saveCompany.php page you will find very little code but it
            gives you unlimited flexibility.  Feel free to experiment.  In this
            example, it will replace "Good" with "Great" in the Company Name.
            You can also uncomment code to have it email you an alert. Or
            uncomment the <code>&dollar;gloSkipGoTo = true;</code> if you want to
            do custom coding after the Save.php is complete.</p>
        <br>
        <h6>Full saveCompany.php Code</h6>
<pre><code>
<&quest;PHP
&dollar;pgSecurityLevel = 1;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

&dollar;pgCoName = wtkGetPost('wtkwtkCompanySettingsCoName');
&dollar;pgCoName = wtkReplace(&dollar;pgCoName, 'Good', 'Great');

&dollar;_POST['wtkwtkCompanySettingsCoName'] = &dollar;pgCoName; // this will be used by Save.php for saving

// optionally send email alert
&dollar;pgOrigCoName = wtkGetPost('OrigwtkwtkCompanySettingsCoName');
if (&dollar;pgOrigCoName != &dollar;pgCoName):
    wtkNotifyViaEmail('Name Changed?!?', "Company name changed from &dollar;pgOrigCoName to &dollar;pgCoName!", &dollar;gloTechSupport);
endif;

//&dollar;gloSkipGoTo = true; // If this is uncommented, then Save.php will not use wtkGoToURL
require('../wtk/lib/Save.php'); // this will do actual saving

// this code will only trigger if above &dollar;gloSkipGoTo = true;  is uncommented
echo 'Past the Save.php';
?>

</code></pre>

    </div>
</div>
<br>
htmVAR;

$pgHtm .= '</div>' . "\n";

echo $pgHtm;
exit;
?>
