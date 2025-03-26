<?php
if (!isset($gloConnected)):
    $gloLoginRequired = false;
    $gloSkipConnect = 'Y';
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

if (($gloRNG != 0) && ($gloRNG != '')):
    $pgInfoNum = $gloRNG;
else:
    $pgInfoNum = wtkGetCookie('wtkInfo');
    if ($pgInfoNum == ''):
        $pgInfoNum = 1;
    endif;
endif;
$pgBackPg = ($pgInfoNum - 1);
if ($pgBackPg == 0):
    $pgBackPg = 6;
endif;
$pgNextPg = ($pgInfoNum + 1);
if ($pgNextPg == 7):
    $pgNextPg = 1;
endif;

$pgNavBtns =<<<htmVAR
<small class="right" style="margin-top:-27px">
<ul class="pagination">
    <li class="waves-effect"><a onclick="JavaScript:ajaxFillDiv('wtkinfo','','wtkInfo',$pgBackPg)"><i class="material-icons">chevron_left</i></a></li>
    <li class="waves-effect"><a onclick="JavaScript:ajaxFillDiv('wtkinfo','','wtkInfo',$pgNextPg)"><i class="material-icons">chevron_right</i></a></li>
</ul></small>
htmVAR;

switch ($pgInfoNum):
    case 1:
        $pgWTKinfo =<<<htmVAR
<h3>Wizard&rsquo;s Toolkit Low-Code Development Library $pgNavBtns</h3>
<p>This feature-rich toolkit, powered by PHP, SQL, JavaScript, and MaterializeCSS, automates
 numerous functions, promising significant time savings for developers.
 Save thousands of hours per year in development by using a low-code development library.</p>
<p>WTK code writes code! Paste in a working SQL SELECT query and choose a few options to add
 extra features like quick-filters, then press a button and PHP pages are created which will
 display data from your database and allow updating. Our WTK Web Page Wizard creates both a
 listing page and associated update page.  See a demo of the WTK Page Builder video on our Tutorials page.</p>
htmVAR;
        break;
    case 2:
        $pgWTKinfo =<<<htmVAR
<h3>Jump Start with Wizard&rsquo;s Toolkit $pgNavBtns</h3>
<p>Embark on a rapid development journey with the Wizard&rsquo;s Toolkit. Your site, powered
 by this revolutionary low-code development library, comes pre-equipped with essential
 features. No need to reinvent the wheel &mdash with the Wizard&rsquo;s Toolkit the
 new account registration, Forgot Password functionality, Login/Logout capabilities,
 and a data-driven Dashboard are seamlessly integrated from the outset.</p>
<p>Now you can direct your attention to the critical aspects of your company.
 The toolkit takes care of the foundational elements plus provides a website (on your server)
 where you can build reports and widgets by writing simple SQL scripts.</p>
<p>Unlock efficiency, save time, and elevate your development experience with
 the Wizard&rsquo;s Toolkit.</p>
htmVAR;
        break;
    case 3:
        $pgWTKinfo =<<<htmVAR
<h3>Wizard Report Manager $pgNavBtns</h3>
<p>Empower your SQL experts or staff unfamiliar with PHP with the
 Wizard&rsquo;s Report Manager. This ingenious tool simplifies the creation of web pages
 featuring intricate reports. Your SQL team can effortlessly craft a SELECT query,
 specifying column alignment (centered or right-aligned), sorting preferences,
 and desired totals. All configurations are stored in the database, generating
 web reports exportable to CSV or XML. Elevate the visual appeal with dynamic
 charts—Bar, Line, Area, and Pie charts are just a toggle away in the Report
 Manager of the Wizard&rsquo;s Toolkit. Transform data into compelling visuals
 effortlessly and enhance your reporting capabilities with this powerful tool.</p>
htmVAR;
        break;
    case 4:
        $pgWTKinfo =<<<htmVAR
<h3>Saving Data with Wizard&rsquo;s Toolkit $pgNavBtns</h3>
<p>Unlike most coders who need to create intricate pages for data storage and
 updates, the pages in the Wizard&rsquo;s Toolkit effortlessly manage all of that
 without requiring a single line of code. The Toolkit not only saves data
 effectively but also maintains a comprehensive log detailing who made changes
 or insertions, what alterations were made, and when they occurred.</p>
<p>Administrators can efficiently search through the back office, using either
 user login or data table criteria, to access detailed update logs. This
 empowers your staff to easily track changes, providing valuable insights
 into the 'who' and 'when' of any modifications.</p>
htmVAR;
        break;
    case 5:
        $pgWTKinfo =<<<htmVAR
<h3>SQL Tables for WTK $pgNavBtns</h3>
<p>Wizard&rsquo;s Toolkit has been fully tested for both MySQL and PostgreSQL
 compatibility and contains over 40 SQL tables which manage and track
 everything from users to errors to data updates.</p>
<ul class="browser-default">
    <li>User Management</li>
    <li>Error Logging</li>
    <li>Failed Access / Hacker Tracking and lockout</li>
    <li>Update Logs - who changed what and when</li>
    <li>Data-driven Menus</li>
    <li>Offsite Replication</li>
    <li>Help System</li>
    <li>Communication Tracking (Email and SMS)</li>
    <li>Report Creation and Usage Tracking</li>
    <li>Widget Creation and Grouping by Department plus Personal</li>
</ul>

<p>All tables, functions, stored procedures and triggers are available for
 both MySQL and PostgreSQL.</p>
htmVAR;
        break;
    case 6:
        $pgWTKinfo =<<<htmVAR
<h3>All the Code is on Your Servers $pgNavBtns</h3>
<p>Wizard&rsquo;s Toolkit is a low-code solution &mdash; not a no-code solution.
 You maintain all the code on your own servers.  You have 100% control and can
 edit, enhance and modify the source code as-needed. Everything is PHP, JavaScript,
 SQL and CSS with good indentation, naming conventions, and comments in the code.</p>
<p>Only 3 files are encrypted to ensure our licensing:</p>
<ul class="browser-default">
    <li>Browse.php &mdash; generates full-featured lists of data with sorting and page navigation</li>
    <li>Encrypt.php &mdash; contains 2 functions used to encode/decode for saving of data</li>
    <li>Save.php &mdash; saves data to the SQL database and logs who changed what</li>
</ul>
htmVAR;
        break;
endswitch;
$pgWTKinfo .=<<<htmVAR
<p>Visit <a target="_blank" href="https://wizardstoolkit.com/">WizardsToolkit.com</a>
  for <a target="_blank" href="https://wizardstoolkit.com/pricing.php">pricing information</a> or check out our
  <a target="_blank" href="https://wizardstoolkit.com/tutorials.php">Tutorials</a>.</p>
htmVAR;

if (($gloRNG != 0) && ($gloRNG != '')):
    echo $pgWTKinfo;
    exit;
endif;

$pgInfo =<<<htmVAR
<br>
<div class="container">
    <div class="card">
        <div class="card-content" id="wtkInfo">
            $pgWTKinfo
        </div>
    </div>
</div>
htmVAR;

if ($pgInfoNum == 6):
    $pgInfoNum = 1;
else:
    $pgInfoNum ++;
endif;
wtkSetCookie('wtkInfo', $pgInfoNum, '1week');

wtkSearchReplace('<!-- preloader -->',$pgInfo);
?>
