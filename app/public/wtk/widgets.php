<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('wtkLogin.php');

function makeWidgets($fncGroupUID, $fncUserUID = 0, $fncWidgetUID = 0){
    global $gloUserUID, $gloDeviceType, $gloWTKobjConn, $gloColumnAlignArray, $gloSkipFooter;
    $gloColumnAlignArray = array (
        'Count' => 'center',
        'Income' => 'center'
    );
    $fncTemplate = '    <div id="widget@UID@DIV" class=';
    if ($fncWidgetUID == 0):
        $fncCountTemplate = $fncTemplate . '"col m3 s6">' . "\n";
        $fncLinkTemplate = $fncTemplate . '"col m6 s12">' . "\n";
        $fncTemplate .= '"col m4 s12">' . "\n";
    else: // for single widget must have been called by modalSave so skip outter div
        $fncCountTemplate = '';
        $fncLinkTemplate = '';
        $fncTemplate  = '';
    endif;
    $fncCountTemplate .=<<<htmVAR
        <div class="card-panel widget-box @WidgetColor@ tooltipped"@Link@ id="ttip@UID@" data-position="bottom" data-tooltip="@WidgetDescription@">
            <h3>@Count@</h3>
            <h6>@WidgetName@</h6>
        </div>
htmVAR;
    $fncLinkTemplate .=<<<htmVAR
        <div class="card b-shadow tooltipped"@Link@ id="ttip@UID@" data-position="bottom" data-tooltip="@WidgetDescription@">
            <div class="card-content center">
                <h4>@WidgetName@</h4>
            </div>
        </div>
htmVAR;
    $fncTemplate .=<<<htmVAR
        <div class="card b-shadow"@Link@>
            <div class="card-content">
                <h4>@WidgetName@</h4>
                <p>@WidgetDescription@</p>
                @Content@
            </div>
        </div>
htmVAR;
    if ($fncWidgetUID == 0):
        $fncCountTemplate .= "\n" . '    </div>';
        $fncLinkTemplate .= "\n" . '    </div>';
        $fncTemplate .= "\n" . '    </div>';
    endif;
    $fncSQL =<<<SQLVAR
SELECT wg.`WidgetGroupName`, w.`WidgetName`, COALESCE(w.`WidgetURL`,'') AS `WidgetURL`,
    COALESCE(w.`WidgetDescription`,'') AS `WidgetDescription`, w.`UID`, w.`WidgetSQL`,
    w.`WidgetType`, w.`ChartType`, COALESCE(w.`WidgetColor`,'info-gradient') AS `WidgetColor`,
    COALESCE(w.`PassRNG`,0) AS `PassRNG`, w.`WindowModal`,
    COALESCE(w.`SkipFooter`,'N') AS `SkipFooter`
  FROM `wtkWidgetGroup_X_Widget` x
    INNER JOIN `wtkWidget` w ON w.`UID` = x.`WidgetUID`
    INNER JOIN `wtkWidgetGroup` wg ON wg.`UID` = x.`WidgetGroupUID`
 WHERE
SQLVAR;
    if ($fncWidgetUID != 0):
        $fncSQL .= ' w.`UID` = :WidgetUID' . "\n";
        $fncSqlFilter = array('WidgetUID' => $fncWidgetUID);
    else:
        $fncSqlFilter = array('GroupUID' => $fncGroupUID);
        $fncSQL .= ' x.`WidgetGroupUID` = :GroupUID AND w.`DelDate` IS NULL' . "\n";
        if ($fncUserUID != 0):
            $fncSQL .= ' AND x.`UserUID` = ' . $fncUserUID;
        endif;
    endif;
    $fncSQL .= ' ORDER BY x.`WidgetPriority` ASC';
    if ($fncWidgetUID != 0):
        $fncSQL .= ' LIMIT 1';
    endif;
    $fncSQL  = wtkSqlPrep($fncSQL);
    $fncHtm = '';
    $fncChartNum = 0;
    $fncColTotal = 0;
    $fncPDO = $gloWTKobjConn->prepare($fncSQL);
    $fncPDO->execute($fncSqlFilter);
    while ($fncPDOrow = $fncPDO->fetch(PDO::FETCH_ASSOC)):
        if ($fncHtm == ''):
            if ($fncWidgetUID != 0):
                $fncHtm = '<div class="row">' . "\n";
            else:
                if ($gloDeviceType == 'phone'):
                    $fncHtm = '<h5 class="center">';
                else:
                    $fncHtm = '<h4 class="center">';
                endif;
                $fncDashboardName = $fncPDOrow['WidgetGroupName'];
                if ($fncDashboardName == 'Personal - leave blank, autofilled'):
                    $fncDashboardName = 'Personal';
                endif;
                $fncHtm .= $fncDashboardName . ' Dashboard';
                if ($fncGroupUID == 1):
                    $fncHtm .= ' <a onclick="JavaScript:wtkModal(\'/admin/widgetMgr\',0,0,' . $fncGroupUID . ')";>';
                    $fncHtm .= '<i class="material-icons">widgets</i></a>';
                endif;
                if ($gloDeviceType == 'phone'):
                    $fncHtm .= '</h5>' . "\n";
                else:
                    $fncHtm .= '</h4>' . "\n";
                endif;
                $fncHtm .= '<div class="row">' . "\n";
            endif;
        endif;
        if (($fncColTotal % 12) == 0):
            $fncHtm .= '</div>' . "\n";
            $fncHtm .= '<div class="row">' . "\n";
            $fncColTotal = 0;
        endif;
        $fncWidgetType = $fncPDOrow['WidgetType'];
        switch ($fncWidgetType):
            case 'Count':
                $fncTmp  = $fncCountTemplate;
                $fncColTotal = ($fncColTotal + 3);
                break;
            case 'Link':
                $fncTmp  = $fncLinkTemplate;
                $fncColTotal = ($fncColTotal + 6);
                break;
            default:
                $fncTmp  = $fncTemplate;
                $fncColTotal = ($fncColTotal + 4);
                break;
        endswitch;
        $fncWidgetDescription = $fncPDOrow['WidgetDescription'];
        if ($fncWidgetDescription == ''):
            $fncTmp = wtkReplace($fncTmp, ' data-position="bottom" data-tooltip="@WidgetDescription@"', '');
            $fncTmp = wtkReplace($fncTmp, ' tooltipped"', '"');
        endif;
        $fncTmp = wtkReplace($fncTmp, '@WidgetDescription@', $fncWidgetDescription);

        $fncUID = $fncPDOrow['UID'];
        $fncRNG = $fncPDOrow['PassRNG'];
        $fncTmp = wtkReplace($fncTmp, '@UID@', $fncUID);
        $fncURL = $fncPDOrow['WidgetURL'];
        if ($fncURL == ''):
            $fncTmp = wtkReplace($fncTmp, '@Link@', '');
        else:
            if ($fncPDOrow['WindowModal'] == 'Y'):
                $fncTmp = wtkReplace($fncTmp, '"@Link@', " clickable\" onclick=\"Javascript:wtkModal('" . $fncURL . "','widget',$fncUID,'$fncRNG');\"");
            else:
                $fncTmp = wtkReplace($fncTmp, '"@Link@', " clickable\" onclick=\"Javascript:ajaxGo('" . $fncURL . "',0,'$fncRNG');\"");
            endif;
        endif;
        $fncTmp = wtkReplace($fncTmp, '@WidgetName@',$fncPDOrow['WidgetName']);

        if ($fncPDOrow['SkipFooter'] == 'Y'):
            $gloSkipFooter = true;
        else:
            $gloSkipFooter = false;
        endif;
        $fncWidgetSQL = $fncPDOrow['WidgetSQL'];
        $fncWidgetSQL = wtkReplace($fncWidgetSQL, '@UserUID@',$gloUserUID);
        switch ($fncWidgetType):
            case 'List':
                $fncContent = wtkBuildDataBrowse($fncWidgetSQL, [], 'widgetList' . $fncUID);
                $fncContent = wtkReplace($fncContent, '<span onClick="JavaScript:wtkBrowseBox','<span class="hide" onClick="JavaScript:alert');
                $fncContent = wtkReplace($fncContent, '</span> &nbsp; | &nbsp;<span>','</span><span class="hide">');
                $fncContent = wtkReplace($fncContent, '</span><span><i','</span><span class="hide"><i');
                break;
            case 'Link':
                $fncContent = '';
                break;
            case 'Count':
                $fncWidgetColor = $fncPDOrow['WidgetColor'];
                $fncCount = wtkSqlGetOneResult($fncWidgetSQL, [],0);
                if ($fncCount == null):
                    $fncCount = 0;
                endif;
                $fncDecodedString = html_entity_decode($fncCount);
                $fncLength = mb_strlen($fncDecodedString);
                if ($fncLength < 3):
                    $fncTmp = wtkReplace($fncTmp, '<h3>@Count@</h3>','<h2>@Count@</h2>');
                elseif ($fncLength > 6):
                    if ($gloDeviceType == 'phone'):
                        if ($fncLength > 11):
                            $fncTmp = wtkReplace($fncTmp, '<h3>@Count@</h3>','<h5>@Count@</h5>');
                        else:
                            $fncTmp = wtkReplace($fncTmp, '<h3>@Count@</h3>','<h4>@Count@</h4>');
                        endif;
                    else:
                        if ($fncLength > 11):
                            $fncTmp = wtkReplace($fncTmp, '<h3>@Count@</h3>','<h4>@Count@</h4>');
                        endif;
                    endif;
                endif;
                $fncTmp = wtkReplace($fncTmp, '@Count@',$fncCount);
                $fncTmp = wtkReplace($fncTmp, '@WidgetColor@',$fncWidgetColor);
                $fncContent = '';
                break;
            case 'Chart':
                $fncChartType = strtolower($fncPDOrow['ChartType']);
//                $fncChartOps = array('regRpt', 'bar','pie');
      // 2FIX if bar,line passed then need to make it into an array
                $fncChartOps = array($fncChartType);
                $fncContent = wtkRptChart($fncWidgetSQL, [], $fncChartOps, $fncChartNum);
                $fncChartNum ++;
                if ($fncChartType == 'pie'):
                    $fncContent = wtkReplace($fncContent, ' class="btn"',' class="hide"');
                    $fncContent = wtkReplace($fncContent, 'options: {responsive: true, aspectRatio: 3}','options: {responsive: true, aspectRatio: 3, plugins: {legend: {display: false}}}');
                    if ($gloDeviceType == 'phone'):
                        $fncContent = wtkReplace($fncContent, 'class="col s12"','style="margin-left:-36px;min-width:360px;min-height:144px"');
                    else:
                        $fncContent = wtkReplace($fncContent, 'class="col s12"','style="margin-left:-144px;min-width:540px;min-height:220px"');
                    endif;
                else:
                    $fncContent = wtkReplace($fncContent, 'class="col s12"','style="height:220px"');
                endif;
                break;
            default:
                $fncContent = 'not defined yet - talk to tech department';
                break;
        endswitch;
        $fncTmp  = wtkReplace($fncTmp, '@Content@', $fncContent);
        $fncHtm .= $fncTmp . "\n";
    endwhile;
    if ($fncHtm != ''):
        $fncHtm .= '</div>' . "\n";
    endif;
    if (strrpos($fncHtm, 'data-tooltip') !== false):
        $fncHtm .= '<input type="hidden" id="HasTooltip" name="HasTooltip" value="Y">' . "\n";
    endif;
    unset($fncPDO);
    return $fncHtm;
} // makeWidgets

$pgWidgetGroupUID = wtkGetParam('p');
if ($pgWidgetGroupUID == 1):
    $pgUserUID = $gloUserUID;
else:
    $pgUserUID = 0; // admin back office
endif;

// BEGIN If user-specific widget group request, check to see if widgets exist
// for this group and if not, fill them from core set
if ($pgUserUID > 0):
    $pgSqlFilter = array (
        'WUID' => $pgWidgetGroupUID,
        'UserUID' => $pgUserUID
    );
    $pgCount = wtkSqlGetOneResult('SELECT COUNT(*) FROM `wtkWidgetGroup_X_Widget` WHERE `WidgetGroupUID` = :WUID AND `UserUID` = :UserUID', $pgSqlFilter);
    if ($pgCount == 0):
        $pgSQL =<<<SQLVAR
SELECT wg.`UID`
  FROM `wtkWidgetGroup` wg
    LEFT OUTER JOIN `wtkUsers` u ON u.`UID` = ? AND
        (u.`StaffRole` = wg.`StaffRole` OR u.`SecurityLevel` = wg.`SecurityLevel`)
 WHERE wg.`UseForDefault` = 'Y' AND wg.`SecurityLevel` <= u.`SecurityLevel`
ORDER BY
  CASE
    WHEN wg.`StaffRole` = u.`StaffRole` THEN 'A'
    WHEN wg.`SecurityLevel` = u.`SecurityLevel` THEN 'B'
    ELSE 'C'
  END ASC, wg.`SecurityLevel` DESC, wg.`UID` DESC LIMIT 1
SQLVAR;
        $pgMasterGroupUID = wtkSqlGetOneResult($pgSQL, [$pgUserUID], 0);
        if ($pgMasterGroupUID > 0):
            $pgSqlFilter = array (
                'WUID' => $pgMasterGroupUID,
                'UserUID' => $pgUserUID
            );
            $pgSQL =<<<SQLVAR
INSERT INTO `wtkWidgetGroup_X_Widget` (`WidgetGroupUID`, `UserUID`, `WidgetUID`, `WidgetPriority`)
SELECT $pgWidgetGroupUID, :UserUID, `WidgetUID`, `WidgetPriority`
  FROM `wtkWidgetGroup_X_Widget`
 WHERE `WidgetGroupUID` = :WUID AND `UserUID` IS NULL
ORDER BY `WidgetPriority` ASC
SQLVAR;
            wtkSqlExec($pgSQL, $pgSqlFilter);
        endif;
    endif;
endif;
//  END  If user-specific widget group request, check to see if widgets exist for this group and if not, fill them from core set

$pgWidgetUID = wtkGetParam('wuid',0);
$pgHtm = makeWidgets($pgWidgetGroupUID, $pgUserUID, $pgWidgetUID);

echo $pgHtm;
exit; // no display needed, handled via JS and spa.htm
?>
