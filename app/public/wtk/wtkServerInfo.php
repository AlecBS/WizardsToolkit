<?PHP
//=========================================================================
/*      Set connection information here for all pages to use             */
//=========================================================================
date_default_timezone_set('America/Phoenix'); // America/Phoenix  is MST with no DST

switch ($_SERVER['SERVER_NAME']):
    case '127.0.0.1':
    case 'dev.wtk.com':
        $gloDbConnection = 'WTKdocker';
        break;
    case 'localhost':
    case 'localhost:8888':
        $gloDbConnection = 'localhost';
        break;
    default:
        $pgTmp = getenv('INSTANCE_UNIX_SOCKET');
        if ($pgTmp != ''):
            $gloDbConnection = 'GCP';
        else:
            if(!isset($gloDbConnection)){$gloDbConnection='Live';}
        endif;
endswitch;
/*
Add your own connection info here within the switch case statement
for local testing, development, production, etc.
Then the only line that will need to be changed to allow switching
database structures is the $gloDbConnection above.

MySQL is our default SQL database.
PostgreSQL works equally well with Wizard's Toolkit.
If you want a different database we will provide that within 30 days
of purchasing a 5-year Wizard's Toolkit subscription.
*/
$gloDriver1 = 'mysql';
$gloDbConnection = getenv('CONNECTION');
if ($gloDbConnection == ''):
    $gloDbConnection = 'localhost';
endif;
// $gloDbConnection = 'WTKdockerTST';
switch ($gloDbConnection):
    case 'WTKdocker' :
        $gloDriver1             = getenv('DATABASE_DRIVER');
        $gloServer1             = getenv('DATABASE_ENDPOINT');
        $gloServerRO            = getenv('DATABASE_READER_ENDPOINT');
        $gloDb1                 = getenv('DATABASE');
        $gloUser1               = getenv('DATABASE_LOGIN');
        $gloPassword1           = getenv('DATABASE_PASSWORD');
        $gloWebBaseURL          = getenv('URL');
        // below are for Checkout.com integration
        $gloEcomServer          = getenv('ECOM_SERVER');
        $gloEcomKey             = getenv('ECOM_KEY');
        $gloEcomChannel         = getenv('ECOM_CHANNEL');

        $gloTechPhone           = getenv('TECH_PHONE');
        $gloTechSupport         = getenv('TECH_SUPPORT');

        $gloEmailMethod         = getenv('EMAIL_METHOD');
        $gloEmailHost           = getenv('EMAIL_HOST');
        $gloEmailPort           = getenv('EMAIL_PORT');
        $gloEmailFromAddress    = getenv('EMAIL_FROM_ADDRESS');
        $gloEmailUserName       = getenv('EMAIL_USER_NAME');
        $gloEmailPassword       = getenv('EMAIL_PASSWORD');

        $gloTwilioPhone         = getenv('TWILIO_PHONE');
        $gloTwilioSID           = getenv('TWILIO_SID');
        $gloTwilioToken         = getenv('TWILIO_TOKEN');

        $gloExtRegion           = getenv('EXT_REGION');
        $gloExtBucket           = getenv('EXT_BUCKET');
        $gloExtAccountId        = getenv('EXT_ACCOUNT_ID');
        $gloExtEndPoint         = getenv('EXT_END_POINT');
        $gloExtAccessKeyId      = getenv('EXT_ACCESS_KEY_ID');
        $gloExtAccessKeySecret  = getenv('EXT_ACCESS_KEY_SECRET');

        $gloExt2Region          = getenv('EXT_2_REGION');
        $gloExt2Bucket          = getenv('EXT_2_BUCKET');
        $gloExt2AccountId       = getenv('EXT_2_ACCOUNT_ID');
        $gloExt2EndPoint        = getenv('EXT_2_END_POINT');
        $gloExt2AccessKeyId     = getenv('EXT_2_ACCESS_KEY_ID');
        $gloExt2AccessKeySecret = getenv('EXT_2_ACCESS_KEY_SECRET');
        break;
    case 'WTKdockerTST' :
        $gloServer1 = 'wtk_db_mysql';
        $gloDb1 = 'wiztools';
        $gloUser1 = 'wtkdba';
        $gloPassword1 = 'LowCodeViaWTK';

// PostgreSQL TESTING
        $gloDriver1 = 'pgsql';
        $gloServer1 = 'wtk_db_pg';
        $gloDb1 = 'pgwiztools';
        $gloUser1 = 'wizdba';
        $gloPassword1 = 'LowCodeViaWTK';

        $gloWebBaseURL = 'http://dev.wtk.com'; // $_SERVER['URL']
        $gloEcomServer = 'https://api.sandbox.checkout.com/';
        // below are for Checkout.com integration
        $gloEcomKey  = 'cURL Header: Authorization: Bearer'; // get from checkout.com
        $gloEcomChannel = 'Used-For-processing_channel_id';  // get from checkout.com
        break;
    case 'GCP' :
        $gloDbConnection = 'Live';
        $gloDriver1 = 'mysql';
        $gloDb1 = getenv('DB_NAME');
        $gloUser1 = getenv('DB_USER');
        $gloPassword1 = getenv('DB_PASS');
        $gloUnixSocket = getenv('INSTANCE_UNIX_SOCKET');
        $gloWebBaseURL = 'https://yourdomain.com';
        break;
    case 'localhost' :
        $gloServer1 = 'localhost';
        $gloDb1 = 'wiztools';
        $gloUser1 = 'root';
        $gloPassword1 = 'root'; // change to your PW
        $gloWebBaseURL = 'http://localhost:8888';
        $gloEcomServer = 'https://sandbox.paypal.com';
        $gloEcomServer = 'https://api.sandbox.checkout.com/';
        // below are for Checkout.com integration
        $gloEcomKey  = 'cURL Header: Authorization: Bearer'; // get from checkout.com
        $gloEcomChannel = 'Used-For-processing_channel_id';  // get from checkout.com
        break;
    case 'Live' :
        $gloServer1 = 'localhost';
        $gloDb1 = 'yourDB';
        $gloUser1 = 'yourUser';
        $gloPassword1 = 'yourDBpassword';
        $gloWebBaseURL = 'https://yourdomain.com';
        $gloEcomServer = 'https://www.paypal.com';
        $gloEcomServer = 'https://api.checkout.com/';
        // below are for Checkout.com integration
        $gloEcomKey  = 'cURL Header: Authorization: Bearer'; // get from checkout.com
        $gloEcomChannel = 'Used-For-processing_channel_id';  // get from checkout.com
        break;
endswitch; // $gloDbConnection
$gloServerRO = $gloServer1; // if using AWS RDS or other service that has a Read-Only DB access option, then set it here
            // otherwise WTK will use the same DB connection for both
if ($gloDriver1 == 'pgsql'):
    $gloSqlDate = 'Mon DD, YYYY';  // use for SQL SELECT calls to retrieve preferred format
    $gloSqlDateTime = 'Mon DD, YYYY at FMHH:MIam';  // use for SQL SELECT calls to retrieve preferred format
else: // assume mySQL
    $gloSqlDate = '%c/%e/%Y'; // use for SQL SELECT calls to retrieve preferred format
    $gloSqlDateTime = '%c/%e/%Y at %l:%i %p';  // use for SQL SELECT calls to retrieve preferred format
endif;
$gloDateQuote = "'";
$gloPhpDateTime = 'M jS, Y';
$gloConnType = 'PDO'; // PDO is recommended; contact info@wizardstoolkit.com if you want to use ADO

$gloPHPLocale = 'en_US';  // determines number formatting
$gloCurrencyCode = 'USD'; // determines currency code in number formatting
$gloMaxFileSize = 20971520; // 20MB: maximum file size allowed to upload to server - managed in wtk/lib/Save.php and wtk/fileUpload.php

//================= Website Password Seed =======================================
$gloAuthStatus = 'yourUniqueCode';  // guarantees uniqueness for login security level checks
//==================File & URL PATHs=======================================
if (!defined('_RootPATH')) define('_RootPATH', ''); // or should default to ''
if (!defined('_WTK_RootPATH')) define('_WTK_RootPATH',_RootPATH.'wtk/');
// file system root separate from URL root so PHP file includes work from within subdirectories.
if (!defined('_CLI_ImgPATH')) define('_CLI_ImgPATH',dirname(__FILE__) . '/imgscli/');
if (!defined('DB_COL_QUOTE')): // used in lib/DataPDO.php and lib/Save.php
    if (stripos($gloDriver1, 'ysql') !== false): // MySQL
        define('DB_COL_QUOTE', '`');
    else:   // Not stripos($gloDriver1, 'ysql') !== false
        if ($gloDriver1 == 'mssql'):
            define('DB_COL_QUOTE', '');
        else:   // Not $gloDriver1 == 'mssql'
            define('DB_COL_QUOTE', '"');
        endif;  // $gloDriver1 == "mssql"
    endif;  // stripos($gloDriver1, 'ysql') !== false
endif;
//=========================================================================
$gloCoName      = 'Your Company Name';
$gloCoLogo      = '<a href="./"><img src="/wtk/imgs/Logo.jpg" alt="' . $gloCoName . '" border="0"></a>';  // define your logo needs here
$gloGoogleApiKey = 'youGoogleApiKey';

// BEGIN Twilio SMS variables
$gloTwilioPhone         = '2095551234'; // Your Twilio From Phone number
$gloTwilioSID           = 'Your-Account-SID'; // Your Account SID from www.twilio.com/user/account
$gloTwilioToken         = 'Your-Auth-Token';  // Your Auth Token from www.twilio.com/user/account
//  END  Twilio SMS variables

$gloPostHog = 'phc_yourKeyHere';

if (!isset($gloTechPhone)):
    $gloTechPhone       = ''; // phone # for developer SMS testing
endif;
if (!isset($gloTechSupport)):
    $gloTechSupport     = 'support@yourDomain.com'; // for testing and website tech support
endif;

$gloPayPalEmail         = 'buy@yourDomain.com';

// BEGIN Email configuration variables
$gloEmailMethod         = 'sendMail'; // valid options: PostmarkApp, sendMail, smtp, qmail, mail
$gloEmailFromAddress    = 'server@yourDomain.com'; // receives registration alerts
// if using PostmarkApp, this must be approved Send email account
$gloPostmarkToken       = 'yourPostmarkToken';
// if $gloEmailMethod = PostmarkApp, then do not need below $gloEmail* variables
$gloEmailHost           = 'yourDomain.com';
$gloEmailPassword       = 'notUsed'; // not used because we are using PostmarkApp instead
$gloEmailPort           = 465;
$gloEmailUserName       = 'yourUserName';
//TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
//TCP port to connect to, use 587 for `PHPMailer::ENCRYPTION_STARTTLS` above
//  `PHPMailer::ENCRYPTION_SMTPS` encouraged
$gloEmailSMTPAuth       = true;
$gloConfirmDelete       = false;
//  END  Email configuration variables

$gloAddPlaceHolder    = true;  // ABS 07/07/14  When Mobile Phone AND Add Page, then hide Label and show PlaceHolder instead

if ((isset($_REQUEST['Debug']) ? $_REQUEST['Debug'] : '') == 'Y' ):
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 1);
else:
    error_reporting(E_ERROR);
endif;

session_cache_limiter ('private, must-revalidate');  // to prevent pages with form postings from requesting repost
if (!isset($_SESSION)):
    ini_set('session.save_handler', 'files');
    ini_set('session.cookie_samesite', 'Strict');
    session_name('WizToolkit');
    session_start();
endif;

$gloCurrentPage = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
if ($gloCurrentPage != ''):
    $gloCurrentPage = $_SERVER['PHP_SELF'] . '?'. $gloCurrentPage;
else:   // Not $gloCurrentPage != ''
    $gloCurrentPage = $_SERVER['PHP_SELF'];
endif;  // $gloCurrentPage != ''
if (!isset($gloSiteDesign) || !isset($gloCSSLib)):
    $pgMpaOrSpa = isset($_POST['MpaOrSpa']) ? $_POST['MpaOrSpa'] : '';
    if ($pgMpaOrSpa != ''):
        $gloSiteDesign = $pgMpaOrSpa;
    else:
        $pgPos = strpos($gloCurrentPage, '/blog/admin/');
        if ($pgPos !== false):
            $gloSiteDesign = 'MPA'; // WTK blog site uses Multi Page App design
            $gloCSSLib     = 'MaterializeCSS';
        else:
            // Set your default on above line; below will override for WTK special folders/files
            $pgPos = strpos($gloCurrentPage, '/admin/');
            if ($pgPos !== false):
                $gloSiteDesign = 'SPA'; // WTK admin site uses Single Page App design
                $gloCSSLib     = 'MaterializeCSS';
            else:
                $pgPos = strpos($gloCurrentPage, 'wtk/reports.php');
                if ($pgPos !== false):
                    $gloSiteDesign = 'SPA'; // WTK reports.php must be called from SPA page
                    $gloCSSLib     = 'MaterializeCSS';
                endif;
            endif;
        endif;
        if (!isset($gloSiteDesign)):
            $gloSiteDesign = 'SPA'; // your default of MPA or SPA for Multi-Page App or Single Page App
        endif;
    endif;
    if (!isset($gloCSSLib)):
        $gloCSSLib = 'MaterializeCSS'; // your default CSS Library: TailwindCSS or MaterializeCSS
    endif;
endif;
require('lib/Core.php');
if (wtkGetPost('wtkDesign') != ''):
    $gloSiteDesign = wtkGetPost('wtkDesign'); // pass to Save.php non-standard design
endif;

if (wtkGetSession('HashPW') == 'passed'):
    $_SESSION['HashPW'] = '';
    $pgSecurityLevel = 0;
    $gloLoginRequired = false;
endif;
$gloPrototype = wtkGetParam('Prototype');
if ($gloPrototype != ''):
    $_SESSION['Prototype'] = $gloPrototype;
else:
    $gloPrototype = wtkGetSession('Prototype');
endif;

if (!isset($gloCSSLib)):
//  $gloCSSLib     = 'TailwindCSS'; // in development, not ready yet
    $gloCSSLib     = 'MaterializeCSS';
endif;

$gloSaveCSS           = 'btn btn-primary';
$gloCancelCSS         = 'btn';

$gloDarkLight         = 'Light'; // used for emailing emailDark or emailLight and settign in minibox.htm
$gloImgWidth          = 200; // used for browse list for image sizes
$gloImgHeight         = 140; // used for browse list for image sizes

// btn-xs, btn-sm, btn-md, btn-lg, btn-xl
if ($gloDeviceType == 'phone'):
    $gloIconSize = 'btn-small';
else:
    $gloIconSize = '';
endif;

$gloIconAsc           = '<i class="material-icons">expand_less</i>';
$gloIconDesc          = '<i class="material-icons">expand_more</i>';

$gloIconPrint         = '<i class="material-icons">print</i>';
// $gloIconExport        = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-download"/></svg>'; //  '<i class="material-icons">file_download</i>';
$gloIconExport        = 'csv';
$gloIconExportXML     = 'xml';

if ($gloCSSLib == 'TailwindCSS'):
    $gloIconAdd       = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-plus"/></svg>';
    $gloIconEdit      = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-edit"/></svg>';
    $gloIconDelete    = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-trash"/></svg>';
    $gloIconFirst     = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-first-page"/></svg>';
    $gloIconPrior     = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-chevron-left"/></svg>';
    $gloIconNext      = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-chevron-right"/></svg>';
    $gloIconLast      = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-last-page"/></svg>';
else:
    $gloIconAdd       = '<i class="material-icons">add</i>';
    $gloIconEdit      = '<i class="material-icons">edit</i>';
    $gloIconDelete    = '<i class="material-icons">delete</i>';
    $gloIconFirst     = '<i class="material-icons">first_page</i>';
    $gloIconPrior     = '<i class="material-icons">chevron_left</i>';
    $gloIconNext      = '<i class="material-icons">chevron_right</i>';
    $gloIconLast      = '<i class="material-icons">last_page</i>';
endif;

$gloRowsPerPage = 20;  // defaults to 50; reset here and can allow users to change this on a User Preference page
?>
