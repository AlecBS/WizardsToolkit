<?PHP
$pgSecurityLevel = 50;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgBtns = wtkModalUpdateBtns('../wtk/lib/Save','widDIV');

$pgSQL =<<<SQLVAR
SELECT `WidgetUID`, `WidgetPriority`
  FROM `wtkWidgetGroup_X_Widget`
WHERE `UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL, [$gloId]);

$pgHtm =<<<htmVAR
<div class="row">
    <div class="col s12"><br>
        <h3>Widget <span class="right">$pgBtns</span></h3>
        <br>
        <div class="card content b-shadow">
            <form id="FwidDIV" method="POST">
            <span class="red-text">$gloFormMsg</span>
            <div class="row">
htmVAR;

//$pgHtm .= wtkFormText('wtkWidgetGroup_X_Widget', 'WidgetPriority','number','Priority','m4 s12');
$pgWidgetUID = wtkSqlValue('WidgetUID');
if ($pgWidgetUID == ''): // must be an ADD
    $pgWidgetUID = 0;
endif;

$pgSQL  = '';
$pgSQL =<<<SQLVAR
SELECT w.`UID`, w.`WidgetName`
  FROM `wtkWidget` w
   LEFT OUTER JOIN `wtkWidgetGroup_X_Widget` x
      ON x.`WidgetGroupUID` = $gloRNG AND w.`UID` = x.`WidgetUID` AND x.`UserUID` IS NULL
 WHERE w.`UID` = $pgWidgetUID OR (w.`DelDate` IS NULL AND x.`UID` IS NULL)
ORDER BY w.`WidgetName` ASC
SQLVAR;
$pgHtm .= wtkFormSelect('wtkWidgetGroup_X_Widget', 'WidgetUID', $pgSQL, [], 'WidgetName', 'UID','Widget');

$pgHtm .= wtkFormHidden('ID1', $gloId);
$pgHtm .= wtkFormHidden('UID', wtkEncode('UID'));
$pgHtm .= wtkFormPrimeField('wtkWidgetGroup_X_Widget', 'WidgetGroupUID', $gloRNG);
$pgHtm .= wtkFormHidden('wtkMode', $gloWTKmode);
$pgHtm .= wtkFormHidden('ParentUID', $gloRNG);
$pgHtm .= wtkFormHidden('wtkGoToURL', '../../admin/widgetGroupEdit.php');
$pgHtm .= '            </div>' . "\n";
$pgHtm .= wtkFormWriteUpdField();

$pgHtm .=<<<htmVAR
            </form>
        </div>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit;
?>
