<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgParentUID = wtkGetPost('ParentUID');
if ($pgParentUID != ''):
    $gloRNG = $pgParentUID;
else:
    if ($gloRNG == 0): // not refreshing from Priority change
        $gloRNG = $gloId;
    else:
        $pgParentUID = 'priority change'; // this triggers showing list without rest of page
    endif;
endif;

$pgSQL =<<<SQLVAR
SELECT x.`UID`, w.`WidgetName`,
  L.`LookupDisplay` AS `SecurityLevel`, w.`WidgetType`, w.`ChartType`,
  CONCAT('<a class="btn btn-floating wtkdrag" draggable="true"',
      ' data-id="', x.`UID`, '"',
      ' data-pos="', ROW_NUMBER() OVER(ORDER BY x.`WidgetPriority`), '"',
      ' ondragstart="wtkDragStart(this);" ondrop="wtkDropId(this)" ondragover="wtkDragOver(event)">',
      '<i class="material-icons" alt="drag to change priority" title="drag to change priority">drag_handle</i></a>')
      AS `Prioritize`
  FROM `wtkWidgetGroup_X_Widget` x
     INNER JOIN `wtkWidget` w ON w.`UID` = x.`WidgetUID`
     INNER JOIN `wtkLookups` L ON L.`LookupType` = 'SecurityLevel'
       AND CAST(L.`LookupValue` AS DECIMAL) = w.`SecurityLevel`
  WHERE x.`UserUID` IS NULL AND x.`WidgetGroupUID` = :WidgetGroupUID
ORDER BY x.`WidgetPriority` ASC, x.`UID` ASC
SQLVAR;

$gloEditPage = 'widgetGroupXWidgetEdit';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'wtkWidgetGroup_X_Widget';
$gloColumnAlignArray = array (
	'Prioritize' => 'center'
);
if ($gloWTKmode == 'ADD'):
    $pgWidgetList = '<p>Save Widget Group then return to add Widgets.</p>';
else:
    if ($gloId != 1):
        $pgSqlFilter = array('WidgetGroupUID' => $gloRNG);
        $pgList = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkWidgetGroup_X_Widget','','Y');
        if ($pgParentUID != ''):
            echo $pgList;
            exit;
        endif;
        $pgWidgetList =<<<htmVAR
<p>The widgets will show in this order, using responsive design to fit on the user&rsquo;s page.</p>
<div id="widDIV">
$pgList
</div>
htmVAR;
    endif;
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `WidgetGroupName`, `SecurityLevel`, `StaffRole`, `UseForDefault`
  FROM `wtkWidgetGroup`
WHERE `UID` = ?
SQLVAR;
$pgSQL  = wtkSqlPrep($pgSQL);
if ($gloWTKmode != 'ADD'):
    $gloForceRO = wtkPageReadOnlyCheck('/widgetGroupEdit.php', $gloRNG);
    wtkSqlGetRow($pgSQL, [$gloRNG]);
endif;

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Widget Group</h4><br>
    <div class="content card b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST">
            <span id="formMsg" class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

$pgHtm .= wtkFormText('wtkWidgetGroup', 'WidgetGroupName','text','','s12');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'StaffRole' ORDER BY `LookupValue` ASC";
$pgHtm .= wtkFormSelect('wtkWidgetGroup', 'StaffRole', $pgSQL, [], 'LookupDisplay', 'LookupValue','Staff Role for Defaults','m4 s12','Y');

$pgSQL  = "SELECT `LookupValue`, `LookupDisplay` FROM `wtkLookups` WHERE `LookupType` = 'SecurityLevel' ORDER BY `LookupValue` ASC";
$pgHtm .= wtkFormSelect('wtkWidgetGroup', 'SecurityLevel', $pgSQL, [], 'LookupDisplay', 'LookupValue','Security Level for Defaults','m4 s12');

$pgValues = array(
    'checked' => 'Y',
    'not' => 'N'
    );
$pgHtm .= wtkFormCheckbox('wtkWidgetGroup', 'UseForDefault', 'Use for Personal Dashboard default',$pgValues,'m4 s12');

$pgHtm .= wtkFormHidden('ID1', $gloRNG);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/widgetGroupList.php');
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkUpdateBtns() . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
        </form>
    </div><br>
    <div class="card">
        <div class="card-content">
            <h4>Widgets in this Group</h4>
htmVAR;

if ($gloId == 1):
    $pgHtm .=<<<htmVAR
    <br>
    <p>You do not assign widgets to the <b>Personal</b> widget group.
     Instead these will automatically be filled in the first time a person
     accesses the widget dashboard.  It will select the widget group based
     on the person&rsquo;s Staff Role or Security Level.</p>
    <p>If there is a Widget Group with a matching Staff Role to the person
     and the &ldquo;Use for Personal Dashboard default&rdquo; is checked,
     it will use that one.  If there is not a match, then it will use a
     widget group with a matching Security Level if the &ldquo;Use for
     Personal Dashboard default&rdquo; is checked.</p>
    <p>The Widget Group UID is <b>1</b> for the &ldquo;Personal Dashboard&rdquo;.
     Users are allowed to modify their personal dashboard including reordering
     widgets, adding new ones, and removing widgets.</p>
htmVAR;
else:
    $pgHtm .=<<<htmVAR
    <input type="hidden" id="wtkDragTable" value="wtkWidgetGroup_X_Widget">
    <input type="hidden" id="wtkDragColumn" value="WidgetPriority">
    <input type="hidden" id="wtkDragFilter" value="$gloRNG">
    <input type="hidden" id="wtkDragRefresh" value="/admin/widgetGroupEdit">
    <input type="hidden" id="wtkDragLocation" value="table">
    $pgWidgetList
htmVAR;
endif;
$pgHtm .=<<<htmVAR
        </div>
    </div>
    <br>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
