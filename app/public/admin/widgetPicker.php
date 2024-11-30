<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgSQL =<<<SQLVAR
SELECT w.`UID`, w.`WidgetName`, w.`WidgetDescription`,
    CASE w.`WidgetType`
        WHEN 'Chart' THEN CONCAT(w.`ChartType`, ' Chart')
        ELSE w.`WidgetType`
    END AS `WidgetType`
  FROM `wtkWidget` w
   LEFT OUTER JOIN `wtkWidgetGroup_X_Widget` x
      ON x.`WidgetGroupUID` = :GroupUID AND x.`UserUID` = :UserUID AND w.`UID` = x.`WidgetUID`
 WHERE w.`DelDate` IS NULL AND x.`UID` IS NULL
    AND w.`SecurityLevel` <= :SecurityLevel
ORDER BY w.`WidgetName` ASC
SQLVAR;
$pgSqlFilter = array (
    'GroupUID' => $gloRNG,
    'UserUID' => $gloUserUID,
    'SecurityLevel' => $gloUserSecLevel
);

$gloEditPage = '/admin/widgetMgr';

$pgHtm  = '<div class="modal-content">' . "\n";
$pgHtm .= '    <h4>Pick a Widget</h4>' . "\n";
//$pgHtm .= '    <div class="wtk-list card b-shadow">' . "\n";
$pgHtm .= '    <div class="wtk-box">' . "\n";
$pgTmp  = wtkBuildDataBrowse($pgSQL, $pgSqlFilter);
$pgTmp  = wtkReplace($pgTmp, ':ajaxGo(',':wtkModalUpdate(');
$pgHtm .= wtkReplace($pgTmp, '>edit<','>add<');
if ($gloDeviceType == 'phone'):
    $pgHtm = wtkReplace($pgHtm, '<th> Widget ','<th>');
endif;
$pgHtm .= '    </div>' . "\n";
$pgHtm .= '</div>' . "\n";

$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no other widgets available');

echo $pgHtm;
exit;
?>
