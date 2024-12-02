<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgLinkToURL = 'https://wizardstoolkit.com/eoy2024.php?af='; // change this to your pricing page

$pgResult = 'ok';
$pgExtra  = '';

$pgWtkMode = wtkGetParam('wtkMode');
switch ($pgWtkMode):
    case 'EDIT':
        $gloSkipGoTo = true;
        require('lib/Save.php');
        break;
    case 'ADD':
        $pgCoName = wtkGetPost('wtkwtkAffiliatesCompanyName');
        $pgContact = wtkGetPost('wtkwtkAffiliatesContactName');
        $pgEmail = wtkGetPost('wtkwtkAffiliatesEmail');
        $pgPW = wtkGetPost('wtkwtkAffiliatesWebPasscode');
        wtkSetCookie('PgPasscode', wtkEncode($pgPW), '1year');
        wtkSetSession('AffiliateCongrats', 'Y');
        $pgSqlFilter = array(
            'CompanyName' => $pgCoName,
            'LinkToURL' => $pgLinkToURL,
            'ContactName' => $pgContact,
            'Email' => $pgEmail,
            'WebPasscode' => $pgPW
        );
        $pgSQL =<<<SQLVAR
INSERT INTO `wtkAffiliates` (`CompanyName`,`ContactName`,`Email`,`WebPasscode`,
    `LinkToURL`,`DiscountPercentage`,`AffiliateRate`)
  VALUES (:CompanyName, :ContactName, :Email, :WebPasscode, :LinkToURL, 10, 50)
SQLVAR;
        $pgSQL = wtkSqlPrep($pgSQL);
        wtkSqlExec($pgSQL, $pgSqlFilter);
        $pgSQL =<<<SQLVAR
SELECT fncWTKhash(`UID`) AS `Hash`
 FROM `wtkAffiliates`
WHERE `Email` = :Email
ORDER BY `UID` DESC LIMIT 1
SQLVAR;
        $pgSQL  = wtkSqlPrep($pgSQL);
        $pgSqlFilter = array(
            'Email' => $pgEmail
        );
        $pgHash = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);
        $pgSqlFilter = array('Hash' => $pgHash);
        wtkSqlExec("UPDATE `wtkAffiliates` SET `AffiliateHash` = CONCAT('af',`UID`) WHERE fncWTKhash(`UID`) = :Hash", $pgSqlFilter);
        $pgExtra = ',"hash":"' . $pgHash . '"';
        // BEGIN optionally send staff notice regarding new affiliate
        $pgIPaddress = wtkGetIPaddress();
        $pgBody =<<<htmVAR
<h3>New Affiliate just signed up!</h3
<br><p>IP Address: $pgIPaddress</p>
<hr>
<p><strong>Company Name:</strong> $pgCoName<br>
<strong>Contact Name:</strong> $pgContact<br>
<strong>Email:</strong> $pgEmail</p>
<p>Need to set up WizBits account for them.</p>
htmVAR;
        $pgTmp = wtkNotifyViaEmail('New Affiliate Signup', $pgBody);
        //  END  optionally send staff notice regarding new affiliate
        // 2ENHANCE send them a welcome email
        break;
    default:
        $pgResult = 'fail';
        break;
endswitch;

echo '{"result":"' . $pgResult . '"' . $pgExtra . '}';
exit; // no display needed, handled via JS and spa.htm
?>
