<?php
// This page requires special WTK library that blocks hackers and will only be available to paid subscribers
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$gloId = wtkGetPost('p');

$pgHeader = '';
$pgResult = '';
switch ($gloId):
    case 'list':
        $pgHeader = '<h4>Cloudflare IP Firewall</h4><br>';
        $pgResult = wtkListCloudflareFirewallRules();
        break;
    case 'block':
        $pgHeader = "<h4>Cloudflare Blocked IP $gloRNG</h4>";
        $pgResult = wtkAddToCloudflareBlocklist($gloRNG);
        $pgResult = "<pre><code>$pgResult</code></pre>";
        // BEGIN If not already in wtkLockoutUntil, add it now
        $pgCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkLockoutUntil` WHERE `IPaddress` = ?', [$gloRNG]);
        if ($pgCount == 0):
            $pgSQL =<<<SQLVAR
INSERT INTO `wtkLockoutUntil` (`IPaddress`,`LockUntil`,`FailCode`)
  VALUES (:IPaddress, :LockUntil, 'Hack')
SQLVAR;
            $pgLockUntil = date('Y-m-d', strtotime('+2 years'));
            $pgSqlFilter = array(
                'IPaddress' => $gloRNG,
                'LockUntil' => $pgLockUntil
            );
            wtkSqlExec($pgSQL, $pgSqlFilter);
        endif;
        //  END  If not already in wtkLockoutUntil, add it now
        break;
    case 'unblock':
        $pgHeader = "<h4>Cloudflare removed IP $gloRNG from block list</h4>";
        $pgResult = wtkRemoveFromCloudflareBlocklist($gloRNG);
        $pgResult = "<pre><code>$pgResult</code></pre>";
        break;
    case 'abuse':
        $pgHeader = "<h4>Reported IP $gloRNG to AbuseIPDB</h4>";
        $pgResult = wtkReportAbuseByIP($gloRNG, 21, 'attempted to hack web server');
        $pgResult = "<pre><code>$pgResult</code></pre>";
        break;
endswitch;
if ($gloId != ''):
    echo $pgResult;
    exit;
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <div class="card b-shadow">
        <div class="card-content">
            <div class="row">
                <div class="col m10 offset-m1 s12">
                    <h3 class="center">Cloudflare Firewall Manager</h3>
                    <br>
                    <p><a onclick="JavaScript:ajaxFillDiv('/admin/cfMgr','list','resultsDIV',0)">List</a> current IPs blocked by Cloudflare Firewall.</p>
                    <br>
                </div>
                <div class="input-field col m6 s12">
                    <input id="IPaddress" type="text">
                    <label for="IPaddress">IP Address</label>
                </div>
                <div class="input-field col m2 s6 center">
                    <a onclick="JavaScript:wtkBadIPaddress('block')" class="btn red">Block IP</a>
                </div>
                <div class="input-field col m2 s6 center">
                    <a onclick="JavaScript:wtkBadIPaddress('unblock')" class="btn blue">Un-Block IP</a>
                </div>
                <div class="input-field col m2 s6 center">
                    <a onclick="JavaScript:wtkBadIPaddress('abuse')" class="btn blue">Abuse IPDB</a>
                </div>
            </div>
            <div class="row">
                <div id="resultsDIV" class="col m10 offset-m1 s12">
            $pgHeader
            $pgResult
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function wtkBadIPaddress(fncMode) {
    let fncIPaddress = $('#IPaddress').val();
    ajaxFillDiv('/admin/cfMgr',fncMode,'resultsDIV',fncIPaddress);
}
</script>
htmVAR;
echo $pgHtm;
exit;
?>