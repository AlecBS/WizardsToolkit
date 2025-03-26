<?PHP
// this entire directory can/should be moved to a folder not available to public
$gloLoginRequired = false;
$pgStartTime = hrtime(true);
define('_RootPATH', '../');     // change path based on location
require('../wtk/wtkLogin.php'); // change path based on location
if (wtkGetParam('pw') != 'wtk' . $gloAuthStatus): // prevent robots and hackers from triggering
    wtkInsFailedAttempt('cron');
    wtkDeadPage();
endif;

if (!isset($gloDebug)): // so calling page can override our global setting
    $gloDebug = ''; // set to '' blank if do not want in Debug mode
endif;
// when set to 'Y' this will skip all emails and SMSing and will show debug info
if ($gloDebug == 'Y'):
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 1);
    $pgSQL = 'INSERT INTO `wtkDebug` (`DevNote`) VALUES (:DevNote)';
    $pgSqlFilter = array('DevNote' => $gloMyPage . ' called');
    wtkSqlExec($pgSQL, $pgSqlFilter);
endif;

$pgHtm  = '<h2>CRON Jobs</h2><hr>' . "\n";
?>
