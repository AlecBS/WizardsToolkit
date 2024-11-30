<?php
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
$gloSiteDesign = 'SPA';

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Broadcast Demo</h4>
    <br>
    <div class="card">
        <div class="card-content">
            <p>If there are any Broadcast alerts they will be shown here.
               These come from the wtkBroadcast data table.</p>
            <p>These can be set up in the
              <a target="_blank" href="/admin">/admin</a> website.</p>
        </div>
    </div>

htmVAR;
$pgHtm .= wtkBroadcastAlerts();
$pgHtm .= '<br><br><p>This is below the wtkBroadcastAlerts() call.</p>' . "\n";
$pgHtm .= '</div>' . "\n";
/**
* wtkBroadcastAlerts function is located in /wtk/lib/HTML.php
*
* This data-driven method of notifying users.  You can set up Broadcasts in the /admin back office.
*
* Broadcast messages to users and allow them to clear.  This uses wtkBroadcast and wtkBroadcast_wtkUsers tables.
* If there are eligible broadcast messages they will be returned in a <div class="row">.  Once a user has
* cleared it will not be displayed again for that user.
*
* @param string $fncMode defaults to 'display'; other option is 'count'
*    to retrieve count of broadcasts to show in tag or alert
* @global $gloPrinting boolean if count and printing then skip getting count from data
* @return html with listing of broadcast alerts
*/
wtkProtoType($pgHtm);
echo $pgHtm;
exit;
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
?>
