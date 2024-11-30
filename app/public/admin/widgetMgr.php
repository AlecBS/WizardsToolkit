<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

if ((wtkGetParam('Mode') == '') && ($gloId > 0)): // came from widgetPicker.php so insert a row
    $pgSQL =<<<SQLVAR
INSERT INTO `wtkWidgetGroup_X_Widget` (`WidgetGroupUID`,`UserUID`,`WidgetUID`)
  VALUES (:WidgetGroupUID,:UserUID,:WidgetUID)
SQLVAR;
    $pgSqlFilter = array (
        'WidgetGroupUID' => $gloRNG,
        'UserUID' => $gloUserUID,
        'WidgetUID' => $gloId
    );
    wtkSqlExec($pgSQL, $pgSqlFilter);
endif;
$pgSQL =<<<SQLVAR
SELECT x.`UID`, w.`WidgetName`, w.`WidgetDescription`,
    CASE w.`WidgetType`
        WHEN 'Chart' THEN CONCAT(w.`ChartType`, ' Chart')
        ELSE w.`WidgetType`
    END AS `WidgetType`,
    CONCAT('<a draggable="true" ondragstart="wtkDragStart(', x.`UID`,
        ',', ROW_NUMBER() OVER(ORDER BY x.`WidgetPriority`),');" ondrop="wtkDropId(', x.`UID`,
        ',', ROW_NUMBER() OVER(ORDER BY x.`WidgetPriority`),')" ondragover="wtkDragOver(event)" class="btn btn-floating ">',
        '<i class="material-icons" alt="drag to change priorty" title="drag to change priorty">drag_handle</i></a>')
        AS `Prioritize`
  FROM `wtkWidgetGroup_X_Widget` x
    INNER JOIN `wtkWidget` w ON w.`UID` = x.`WidgetUID`
    INNER JOIN `wtkUsers` u ON u.`UID` = x.`UserUID`
 WHERE x.`WidgetGroupUID` = :GroupUID AND x.`UserUID` = :UserUID
   AND w.`DelDate` IS NULL
ORDER BY x.`WidgetPriority` ASC
SQLVAR;
if ($gloDeviceType == 'phone'):
    $pgSQL = wtkReplace($pgSQL, 'w.`WidgetDescription`,','');
endif;

$pgSqlFilter = array (
    'GroupUID' => $gloRNG,
    'UserUID' => $gloUserUID
);

$gloAddPage = '/admin/widgetPicker';
$gloDelPage = 'wtkWidgetGroup_X_Widget';

$pgHtm =<<<htmVAR
<div class="modal-content">
    <h4>My Dashboard Widgets
        <small class="right">
            <a onclick="JavaScript:goHome()" class="modal-close btn">Close and Refresh</a>
        </small>
    </h4>
htmVAR;
$gloColumnAlignArray = array (
	'Prioritize' => 'center'
);

$pgHtm .= '    <div class="wtk-box">' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL, $pgSqlFilter,'wtkWidgetGroup_X_Widget','','Y');
if ($gloDeviceType == 'phone'):
    $pgHtm = wtkReplace($pgHtm, '<th> Widget ','<th>');
endif;
$pgHtm .= '    </div>' . "\n";
$pgHtm .= '</div>' . "\n";

$pgHtm .=<<<htmVAR
<input type="hidden" id="wtkDragTable" value="wtkWidgetGroup_X_Widget">
<input type="hidden" id="wtkDragColumn" value="WidgetPriority">
<input type="hidden" id="wtkDragFilter" value="$gloRNG">
<input type="hidden" id="wtkDragRefresh" value="/admin/widgetMgr">
htmVAR;
echo $pgHtm;
exit;
?>
