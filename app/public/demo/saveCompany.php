<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgCoName = wtkGetPost('wtkwtkCompanySettingsCoName');
$pgCoName = wtkReplace($pgCoName, 'Good', 'Great');

$_POST['wtkwtkCompanySettingsCoName'] = $pgCoName; // this will be used by Save.php for saving

/*
// optionally send email alert
$pgOrigCoName = wtkGetPost('OrigwtkwtkCompanySettingsCoName');
if ($pgOrigCoName != $pgCoName):
    wtkNotifyViaEmail('Name Changed?!?', "Company name changed from $pgOrigCoName to $pgCoName!", $gloTechSupport);
endif;
*/
//$gloSkipGoTo = true; // If this is uncommented, then Save.php will not use wtkGoToURL
require('../wtk/lib/Save.php'); // this will do actual saving

// this code will only trigger if above $gloSkipGoTo = true;  is uncommented
echo 'Past the Save.php';
?>
