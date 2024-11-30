<?php
$gloLoginRequired = false;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
$gloSiteDesign  = 'SPA'; // MPA or SPA for Multi-Page App or Single Page App; usually set in wtkServerInfo.php

// BEGIN Generate Demo Data
$pgCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkWidgetGroup` WHERE `WidgetGroupName` = ?', ['wtkDemo']);

if ($pgCount == 0): // need to generate demo data
    $pgSQL =<<<SQLVAR
INSERT INTO `wtkWidget` (`UID`, `WidgetName`, `SecurityLevel`, `WidgetType`, `ChartType`, `SkipFooter`, `WidgetDescription`, `WidgetSQL`, `WidgetURL`, `WindowModal`)
 VALUES (999, 'Modal Save Demo', 1, 'List', 'All', 'Y', 'This demonstrates how a modal window can refresh a single widget upon saving.', 'SELECT `Address`,`City`,`State`,`Zipcode`\r\nFROM `wtkCompanySettings`\r\nWHERE `UID` = 1', '/demo/widgetModal', 'Y')
SQLVAR;
    wtkSqlExec($pgSQL, []);

    $pgSQL =<<<SQLVAR
INSERT INTO `wtkWidgetGroup` (`UID`,`WidgetGroupName`,`StaffRole`,`SecurityLevel`,`UseForDefault`)
  VALUES
    (900,'wtkDemo','Tech', 1, 'Y'),
    (901,'Management','Tech', 1, 'Y'),
    (902,'Quality Control','Tech', 1, 'Y')
SQLVAR;
    wtkSqlExec($pgSQL, []);
    // now add some Widgets to each WidgetGroup
    wtkSqlExec('ALTER TABLE `wtkWidgetGroup_X_Widget` AUTO_INCREMENT = 9900', []);
    $pgSQL =<<<SQLVAR
INSERT INTO `wtkWidgetGroup_X_Widget` (`WidgetGroupUID`,`WidgetUID`)
  VALUES
    (900,7),
    (900,4),
    (900,8),
    (900,1),
    (901,7),
    (901,12),
    (901,13),
    (901,8),
    (901,10),
    (901,1),
    (901,3),
    (901,18),
    (902,13),
    (902,14),
    (902,2),
    (902,18)
SQLVAR;
    wtkSqlExec($pgSQL, []);
else: // check to see if need to Purge demo data
    $pgStep = wtkGetPost('Step');
    if ($pgStep == 'purge'):
        wtkSqlExec('DELETE FROM `wtkWidgetGroup_X_Widget` WHERE `UID` > 9899 AND `AddDate` > DATE_SUB(NOW(), INTERVAL 3 HOUR)', []);
        wtkSqlExec("DELETE FROM `wtkWidget` WHERE `UID` = 999 AND `WidgetName` = 'Modal Save Demo'", []);
        wtkSqlExec('DELETE FROM `wtkWidgetGroup` WHERE `UID` BETWEEN 900 AND 902', []);
        $pgUID = wtkSqlGetOneResult('SELECT `UID` FROM `wtkWidgetGroup_X_Widget` ORDER BY `UID` DESC LIMIT 1', []);
        wtkSqlExec("ALTER TABLE `wtkWidgetGroup_X_Widget` AUTO_INCREMENT = $pgUID", []);
        exit;
    endif;
endif;
//  END  Generate Demo Data

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col s12">
        <table class="widget-dashboard centered">
            <tr>
                <td><h5>Dashboards:</h5></td>
                <td id="widgTD1" onclick="JavaScript:ajaxFillDiv('/wtk/widgets',1,'widgetDIV')">My Dashboard
                    <a id="myDashBtn" class="chip green" onclick="JavaScript:wtkModal('/admin/widgetMgr',0,0,1)"><i class="material-icons white-text">edit</i></a>
                </td>
                <td id="widgTD0" onclick="JavaScript:ajaxFillDiv('/wtk/widgets',0,'widgetDIV')">Dev Ops</td>
                <td id="widgTD2" onclick="JavaScript:ajaxFillDiv('/wtk/widgets',2,'widgetDIV')">Marketing</td>
                <td id="widgTD901" onclick="JavaScript:ajaxFillDiv('/wtk/widgets',901,'widgetDIV')">Management</td>
                <td id="widgTD902" onclick="JavaScript:ajaxFillDiv('/wtk/widgets',902,'widgetDIV')">Quality Control</td>
            </tr>
        </table><br>
        <div id="widgetDIV"></div>
    </div>
</div>
<div class="container">
    <div class="card mini-box">
        <div class="card-content">
            <h4>Data Setup</h4>
            <p>Upon entering this page demo data was created.  You can see it if you log in
                to the <a target="_blank" href="/admin">/admin</a> back office and look under Widget Groups in the Client section.</p>
            <p>When finished, <a onclick="JavaScript:purgeDemoData()">purge</a> the demo data.</p>
        </div>
    </div>
    <br>
</div>
<script type="text/javascript">

ajaxFillDiv('/wtk/widgets',1,'widgetDIV');
// in wtk/js/wtkLibrary.js it will automatically call this when you
// login or go to 'dashboard' if pgWidgets is set to 'Y'.
// pgWidgets is defined in wtk/js/wtkClientVars.js

function purgeDemoData(){
    $.ajax({
        type: "POST",
        url:  '/demo/widgetDashboards.php',
        data: { apiKey: pgApiKey, Step: 'purge' },
        success: function(data) {
            M.toast({html: 'The widget demo data has been purged.', classes: 'green rounded'});
        }
    })
}
</script>
htmVAR;

// if you are requiring login then you test the user's security level and affect the page
// below will not work when $gloLoginRequired = false;
// if ($gloUserSecLevel < 80): // Manager level
//     $pgHtm = wtkReplace($pgHtm, '<td id="widgTD901" onclick="JavaScript:ajaxFillDiv(\'/wtk/widgets\',901,\'widgetDIV\')">Management</td>','');
// endif;  // Manager level

echo $pgHtm;
exit;
?>
