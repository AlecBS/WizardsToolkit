<?PHP
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
$gloSkipConnect = 'Y';
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

$pgEmailsGoingTo = '<p>Since $gloDbConnection is set to "' . $gloDbConnection . '"';
if ($gloDbConnection != 'Live'):
    $pgEmailsGoingTo .= ' all emails will go to<br>your $gloTechSupport ';
    $pgEmailsGoingTo .= '(' . $gloTechSupport . ') email account.';
else:
    $pgEmailsGoingTo .= ' emails sent to actual addresses.</p>';
endif;

$pgServerName = $_SERVER['SERVER_NAME'];
$pgServerVar  = '$_SERVER[\'SERVER_NAME\']';

$pgIonCubeLib = __DIR__ . '/' . _WTK_RootPATH . 'lib/';
$pgIonCubeLib = wtkReplace($pgIonCubeLib,'devUtils/../','');
$pgIonCubeFiles  = $pgIonCubeLib . 'Browse.php:';
$pgIonCubeFiles .= $pgIonCubeLib . 'Encrypt.php:';
$pgIonCubeFiles .= $pgIonCubeLib . 'Save.php';

$pgServerFile = _WTK_RootPATH . 'wtkServerInfo.php';
$pgWtkPath = _WTK_RootPATH;

if (($gloEmailMethod == 'PostmarkApp') && ($gloPostmarkToken == 'yourPostmarkToken')):
    $pgWarning = '<p class="red-text">Your $gloEmailMethod is set to "PostmarkApp" but you have not assigned your $gloPostmarkToken !</p>';
else:
    $pgWarning = '';
endif;

$pgWTKpath = substr(_WTK_RootPATH, 0, -1);

if ((file_exists(_WTK_RootPATH . 'css/wtkGlobal.css')) && (file_exists(_WTK_RootPATH . 'js/wtkLibrary.js'))):
    $pgFilesExist = '<p class="green-text text-darken-2">Core JS and CSS files are recognized.</p>';
else:
    $pgFilesExist  = '<p class="red-text text-darken-3"><strong>Core JS or CSS files not found!</strong></p>';
    if (!file_exists(_WTK_RootPATH . 'css/wtkGlobal.css')):
        $pgFilesExist .= '<p>wtkGlobal.css and other WTK CSS files should be in ' . _WTK_RootPATH . 'css directory.</p>';
    endif;
    if (!file_exists(_WTK_RootPATH . 'js/wtkLibrary.js')):
        $pgFilesExist .= '<p>wtkLibrary.js and other WTK JS files should be in ' . _WTK_RootPATH . 'js directory.</p>';
    endif;
endif;

// BEGIN Display variables and cookies
$pgServerVarsList = '';
foreach ($_SERVER as $key => $value):
    if (is_array($value)):
        foreach ($value as $key2 => $value2):
            $pgServerVarsList .= "<tr><td>SubArray: $key2</td><td>$value2</td></tr>" . "\n";
        endforeach;
    else:
        $pgServerVarsList .= "<tr><td>$key</td><td>$value</td></tr>" . "\n";
    endif;
endforeach;
$pgSesVarsList = '';
foreach ($_SESSION as $key => $value):
    if (is_array($value)):
        foreach ($value as $key2 => $value2):
            $pgSesVarsList .= "<tr><td>SubArray: $key2</td><td>$value2</td></tr>" . "\n";
        endforeach;
    else:
        $pgSesVarsList .= "<tr><td>$key</td><td>$value</td></tr>" . "\n";
    endif;
endforeach;
$pgCookiesList = '';
foreach ($_COOKIE as $key => $value):
    $pgCookiesList .= "<tr><td>$key</td><td>$value</td></tr>" . "\n";
endforeach;
$pgEnvVarsList = '';
foreach ($_ENV as $key => $value):
    if (is_array($value)):
        foreach ($value as $key2 => $value2):
            $pgEnvVarsList .= "<tr><td>SubArray: $key2</td><td>$value2</td></tr>" . "\n";
        endforeach;
    else:
        $pgEnvVarsList .= "<tr><td>$key</td><td>$value</td></tr>" . "\n";
    endif;
endforeach;
//  END  Display variables and cookies

$pgHtm  = '';
$pgStep = wtkGetParam('Step');
$pgHtm .=<<<htmVAR
<h2>Wizard&rsquo;s Toolkit Configuration Testing</h2><br>
<!-- results -->
<ul class="collapsible">
  <li class="active">
    <div class="collapsible-header"><i class="material-icons">filter_1</i>Verify UI and paths</div>
    <div class="collapsible-body"><p>This verifies whether paths are working to CSS and JS files.
        This page should look good.</p>
        <p>$pgFilesExist</p></div>
  </li>
  <li id="vars">
    <div class="collapsible-header"><i class="material-icons">filter_2</i>Verify Variables</div>
    <div class="collapsible-body">
        <h5>Server Cookies</h5>
        <p>These are the currently recognized Cookies.
            Click on headers to sort.</p>
        <table id="cookiesTable">
            <thead>
                <th onclick="sortTable('cookiesTable', 0)">Cookie Name</th>
                <th onclick="sortTable('cookiesTable', 1)">Value</th>
            </thead>
            <tbody>
          $pgCookiesList
            </tbody>
        </table>
        <br><h5>SESSION Variables</h5>
        <p>These are the currently recognized SESSION Variables.
            Click on headers to sort.</p>
        <table id="sesVarsTable">
            <thead>
                <th onclick="sortTable('sesVarsTable', 0)">SESSION Variable</th>
                <th onclick="sortTable('sesVarsTable', 1)">Value</th>
            </thead>
            <tbody>
          $pgSesVarsList
            </tbody>
        </table>
        <br><h5>SERVER Variables</h5>
        <p>These are the currently recognized SERVER Variables.
            Click on headers to sort.</p>
        <table id="serverTable">
            <thead>
                <th onclick="sortTable('serverTable', 0)">SERVER Variable</th>
                <th onclick="sortTable('serverTable', 1)">Value</th>
            </thead>
            <tbody>
          $pgServerVarsList
            </tbody>
        </table>
        <br><h5>Environment Variables</h5>
        <p>These are the currently recognized Environment Variables.
            Click on headers to sort.</p>
        <table id="envVarsTable">
            <thead>
                <th onclick="sortTable('envVarsTable', 0)">Environment Variable</th>
                <th onclick="sortTable('envVarsTable', 1)">Value</th>
            </thead>
            <tbody>
          $pgEnvVarsList
            </tbody>
        </table>
    </div>
  </li>
  <li id="ionCube">
    <div class="collapsible-header"><i class="material-icons">filter_3</i>Verify ionCube Configuration</div>
    <div class="collapsible-body">
        <p>For your <strong>php.ini</strong> settings you will need to set <strong>zend_extension</strong> and
         <strong>ioncube.loader.encoded_paths</strong>.<br>
         The <strong>zend_extension</strong> setting is shown at bottom of
         <a href="ioncube/loader-wizard.php" target="_blank">ionCube loader wizard</a> and if necessary
         more details are available on page 83 of
         <a target="_blank" href="https://www.ioncube.com/sa/USER-GUIDE.pdf">ionCube User Guide</a>.</p>

        <p>For example your setting may look something like this:</p>
        <code class="wtk-code">
zend_extension = /usr/local/lib/php/extensions/no-debug-non-zts-20210902/ioncube_loader_lin_8.1.so
ioncube.loader.encoded_paths = "$pgIonCubeFiles"
        </code>
        <p>The above <strong>ioncube.loader.encoded_paths</strong> should be correct based on your
         WTK installation.  However if you are using Windows then change colons to semicolons like:<br>
         Encrypt.php<strong class="red-text">:</strong> <br>...to<br>Encrypt.php<strong class="red-text">;</strong></p>
        <p>Once you have added the ionCube lines to your php.ini file and restarted your webserver, then
            use above <a href="ioncube/loader-wizard.php" target="_blank">ionCube wizard</a> to verify
            your environment is OK.  After that <a href="?Step=ionCube">click for WTK with ionCube</a>
            verification. If there are problems then check local
            <a target="_blank" href="ioncube/README.txt">ionCube User Guide</a>.</p>
        <p><strong>Note:</strong> if you receive a message saying "$pgWTKpath/lib/Encrypt.php is corrupt" that
            indicates the version you have is for a different version of PHP.  Contact us at
            support@programminglabs.com and let us know which version of PHP you are using.  We will
            send you out the files ASAP!</p>

        <p class="hide">Check in $pgWTKpath/lib/ folder for
            other versions of Encrypt like Encrypt56_71.php which works with PHP versions 5.6 and 7.1.
            Or Encrypt72.php which works with PHP version 7.2, etc.  Then remove the current Encrypt.php and
            rename the Encrypt{your PHP version}.php to Encrypt.php.  Make sure to do the same for
            your $pgWTKpath/lib/Browse.php and $pgWTKpath/lib/Save.php file as well since they also
            use ionCube encryption.</p>
        <p><strong>Note:</strong> the ioncube line must appear before any Zend configuration sections.
        These sections usually begin with [Zend] so they should be easy to see.  More information available at:
            <a target="_blank" href="https://stackoverflow.com/questions/33774398/php-fatal-error-ioncube-loader-the-loader-must-appear-as-the-first-entry-in-t">StackOverflow
            discussion</a>.</p>
        <p>Your $pgServerVar is currently: $pgServerName</p>
    </div>
  </li>
  <li id="db">
    <div class="collapsible-header"><i class="material-icons">filter_4</i>Verify Database Access</div>
    <div class="collapsible-body">
        <p>If you are using PostgreSQL with Docker then during initial start PostgreSQL will have
          automatically run the scripts and set up the database.  If you are not using Docker or
          are using MySQL instead of PostgreSQL, you will need to run the scripts manually.</p>
          <h4>Manually Running Scripts</h4>

        <p>First run all SQL scripts in numeric order. Scripts are located in
          \SqlSetup\MySQL or \SqlSetup\PostgreSQL folder depending on which you are using.
          Of course these should not be uploaded to your production web server.</p>
        <p>Then set the SQL configuration variables in you $pgServerFile file.</p>
        <p>Note that if you are using Docker then instead of localhost or 127.0.0.1
          you should use your SQL container ID.</p>
        <code class="wtk-code">
&dollar;gloDbConnection = '$gloDbConnection'
&dollar;gloDriver1 = '$gloDriver1'
&dollar;gloServer1 = '$gloServer1'
&dollar;gloDb1 = '$gloDb1'
&dollar;gloUser1 = '$gloUser1'
&dollar;gloPassword1 = '$gloPassword1'
        </code>
        <p>After scripts have been run and you have set the SQL configuration in your $pgServerFile
        then <a href="?Step=DB">click for DB test</a></p>
        <p><strong>Always remember:</strong> If you get an error for anything including SQL, look in the `wtkErrorLog` data table for details.</p>
    </div>
  </li>
  <li id="email">
    <div class="collapsible-header"><i class="material-icons">filter_5</i>Verify Email Configuration</div>
    <div class="collapsible-body">
        <div class="center">
            $pgWarning
            <p>Edit the PHP global variables in $pgServerFile to configure your email sending service.
              <br>Look for "$<strong>gloEmailMethod</strong>" and variables directly below it.<p>
            <br>
            <h4>Current Settings</h4>
            <table align="center" class="table-border striped" style="width:initial !important">
                <tr><td class="right"><strong>Email Method:</strong></td><td>$gloEmailMethod</td></tr>
                <tr><td class="right"><strong>Email Host:</strong></td><td>$gloEmailHost</td></tr>
                <tr><td class="right"><strong>From Address:</strong></td><td>$gloEmailFromAddress</td></tr>
                <tr><td class="right"><strong>User Name:</strong></td><td>$gloEmailUserName</td></tr>
                <tr><td class="right"><strong>Email Password:</strong></td><td>$gloEmailPassword</td></tr>
                <tr><td class="right"><strong>Email Port:</strong></td><td>$gloEmailPort (usually 465 or 587)</td></tr>
                <tr><td class="right"><strong>To (Tech) Address:</strong></td><td>$gloTechSupport</td></tr>
            </table>
            <br>$pgEmailsGoingTo
            <form method="POST">
                <input type="hidden" id="Send" name="Send" value="Y">
                <a href="?Step=RefreshEmail" class="btn waves-effect waves-light">Refresh</a> &nbsp;&nbsp;
                <a href="?Step=Email" class="btn waves-effect waves-light">Test Sending Email</a> &nbsp;&nbsp;
                <a href="?Step=Email&Verbose=Y" class="btn waves-effect waves-light">Test Email Verbose Mode</a>
            </form>
            <br><p>Go <a href="testSMS.php">here</a> to test SMS configuration using Twilio.</p>
        </div>
    </div>
  </li>
  <li>
    <div class="collapsible-header"><i class="material-icons">filter_6</i>Set Your Password</div>
    <div class="collapsible-body">
        <p>Change your initial admin password using
         <a target="_blank" href="../$pgWtkPath/passwordReset.php?u=needToSet&Debug=Y">WTK passwordReset.php</a>.</p>
        <p>Initially the admin email address used for logging in is: <strong>admin@email.com</strong>
            but you can change that in the `wtkUsers` table or via the Admin Back Office.</p>
        <p>After all the above is working, try going to either the
            <a href="../admin/?Debug=Y" target="_blank">Admin Back Office</a>
            or the <a href="../index.php?Debug=Y" target="_blank">starting</a> page.</p>
    </div>
  </li>
  <li>
    <div class="collapsible-header"><i class="material-icons">filter_7</i>Choose Color Theme</div>
    <div class="collapsible-body">
        <p>If you do not want to use our default colors, you can easily change the color theme.
            Light or dark and several colors are available.  Plus we have
            a <a target="_blank" href="../$pgWtkPath/css/index.php">CSS Maker utility</a>
            that allows you to start with one of our color themes and then change it to
            make it your own.  After you have chosen/made your CSS page, edit /$pgWtkPath/htm/spa.htm to
            use your $pgWtkPath/css/wtk{your-color-theme}.css file instead of $pgWtkPath/css/wtkBlue.css.</p>
    </div>
  </li>
  <li>
    <div class="collapsible-header"><i class="material-icons">filter_8</i>Check Out Demos</div>
    <div class="collapsible-body">
        <p>After you have created a password you can log in and test the <a target="_blank" href="../demo/">Demos</a>.
            Note that by default Wizard&rsquo;s Toolkit does not allow visitors to add, edit or delete data unless they
            are logged in.  You can manually change that but doing so can be very dangerous to your database.</p>
        <p>View how the pages work then look at the source code to see exactly how easy it is to code yet have such
            full-featured pages.</p>
    </div>
  </li>
</ul>
<p>Get <a href="?Step=phpinfo">phpinfo</a>. &nbsp;&nbsp; Make sure not to leave this file on your production server.
  Your local Wizard&rsquo;s Toolkit documentation is <a target="_blank" href="/docs">here</a>.
  Also check out our <a target="_blank" href="https://wizardstoolkit.com/wiki">online Wiki</a> for more insights
  on how to get the most from Wizard&rsquo;s Toolkit.</p>
<p>If you have any problems with Wizard&rsquo;s Toolkit configuration,
    contact <a href="mailto:support@programminglabs.com">support@programminglabs.com</a> </p>
htmVAR;
$pgHtm = wtkReplace($pgHtm, $pgWtkPath . '/', $pgWtkPath);
$pgHtm = wtkReplace($pgHtm, 'value="' . $gloEmailMethod . '"', 'value="' . $gloEmailMethod . '" selected');

$pgResults = '';
switch ($pgStep):
    case 'RefreshEmail':
        $pgHtm = wtkReplace($pgHtm, '<li class="active">','<li>');
        $pgHtm = wtkReplace($pgHtm, '<li id="email">','<li class="active">');
        break;
    case 'DB':
        $pgResults = '<h3>Database Test: <small class="green-text">Success!</small></h3>' . "\n";
        $pgHtm = wtkReplace($pgHtm, '<li class="active">','<li>');
        $pgHtm = wtkReplace($pgHtm, '<li id="db">','<li class="active">');
        wtkConnectToDB();
        $pgSQL = 'SELECT `FirstName`, `LastName`, `Email` AS `EmailLogin` FROM `wtkUsers` ORDER BY `LastName` ASC LIMIT 3';
        $gloSkipFooter = true;
        $pgList = wtkBuildDataBrowse($pgSQL, [], 'wtkUsers', 'testWTK.php?Step=DB');
        $pgResults .=<<<htmVAR
        <div class="card">
            <div class="card-content">
                $pgList
            </div>
        </div><br>
htmVAR;
        break;
    case 'Email':
        $pgHtm = wtkReplace($pgHtm, '<li class="active">','<li>');
        $pgHtm = wtkReplace($pgHtm, '<li id="email">','<li class="active">');
        $pgResults = '<h3>Email Results:' . "\n";
        if ($gloEmailFromAddress == 'server@yourDomain.com'):
            $pgResults .= ' <small class="red-text">Skipping emailing until you update $gloEmailFromAddress</small></h3>' . "\n";
            $pgResults .= '<p>That variable and other email-related variables can be found in ' . $pgServerFile . '</p>';
        else:
            if ($gloTechSupport == 'support@yourDomain.com'):
                $pgResults .= ' <small class="red-text">Skipping emailing until you update $gloTechSupport in ' . $pgServerFile . '</small></h3>' . "\n";
            else:
                if (($gloEmailMethod == 'PostmarkApp') && ($gloPostmarkToken == 'yourPostmarkToken')):
                    $pgResults .= '<small class="red-text">Skipping emailing until you add your $gloPostmarkToken</small></h3>' . "\n";
                else:
                    wtkConnectToDB();
                    $pgMsg  = '<p>Email sent via Wizard&rsquo;s Toolkit using "' . $gloEmailMethod . '" emailing method.</p>' . "\n";
                    $pgTimeStart = microtime(true);
                    $pgVerbose = wtkGetParam('Verbose');
                    if ($pgVerbose == 'Y'):
                        $pgTmp  = wtkNotifyViaEmail('Testing Email Verbosely', $pgMsg, '', [], '','default','','N',4);
                    else:
                        $pgTmp  = wtkNotifyViaEmail('Testing Email', $pgMsg);
                    endif;
                    if ($pgTmp == true):
                        $pgPageTime = round(microtime(true) - $pgTimeStart,4);
                        $pgResults .= '<small class="green-text">Email sent successfully</small></h3>' . "\n";
                        $pgResults .= "<p>It took $pgPageTime seconds to send email to $gloTechSupport .</p>" . "\n";
                    else:
                        $pgResults .= '<small class="red-text">Email failed to send</small></h3>' . "\n";
                        $pgResults .= "<p>Sent to $gloTechSupport - check for error in `wtkErrorLog` data table.</p>" . "\n";
                    endif;
                    $pgResults .= $pgMsg . "\n";
                endif;
            endif;
        endif;
        break;
    case 'ionCube':
        $pgHtm = wtkReplace($pgHtm, '<li class="active">','<li>');
        $pgHtm = wtkReplace($pgHtm, '<li id="ionCube">','<li class="active">');
        $pgResults = '<h3>Success! <small class="green-text">ionCube is working</small></h3>' . "\n";
        break;
    case 'phpinfo':
        echo '<p>When finished reviewing PHP info, go back to <a href="testWTK.php">test WTK</a> page.</p>' . "\n";
        echo phpinfo();
        exit;
        break;
    default:
        break;
endswitch;
$pgHtm = wtkReplace($pgHtm, '<!-- results -->', $pgResults);
$pgHtm .=<<<htmVAR
<script type="text/javascript">
function sortTable(fncTableId, n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById(fncTableId);
  switching = true;
  // Set the sorting direction to ascending:
  dir = "asc";
  /* Make a loop that will continue until
  no switching has been done: */
  while (switching) {
    // Start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 1; i < (rows.length - 1); i++) {
      // Start by saying there should be no switching:
      shouldSwitch = false;
      /* Get the two elements you want to compare,
      one from current row and one from the next: */
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      /* Check if the two rows should switch place,
      based on the direction, asc or desc: */
      if (dir == "asc") {
        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
          // If so, mark as a switch and break the loop:
          shouldSwitch = true;
          break;
        }
      } else if (dir == "desc") {
        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
          // If so, mark as a switch and break the loop:
          shouldSwitch = true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      /* If a switch has been marked, make the switch
      and mark that a switch has been done: */
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      // Each time a switch is done, increase this count by 1:
      switchcount ++;
    } else {
      /* If no switching has been done AND the direction is "asc",
      set the direction to "desc" and run the while loop again. */
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}
</script>
htmVAR;
if ($gloDarkLight == 'Light'):
    wtkSearchReplace('class="card b-shadow"','class="card b-shadow bg-second"');
endif;
wtkSearchReplace('col m4 offset-m4 s12','col m8 offset-m2 s12');
wtkMergePage($pgHtm, 'Wizards Toolkit Test', _WTK_RootPATH . 'htm/minibox.htm');
?>
