<?PHP
$pgSecurityLevel = 80;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgDate = wtkSqlDateFormat('r.`AddDate`', 'Date', $gloSqlDateTime);
$pgSQL =<<<SQLVAR
SELECT r.`UID`, $pgDate,
    CONCAT('<a onclick="JavaScript:ajaxGo(\'/wtk/userEdit\',', r.`UserUID`, ');">',
      COALESCE(r.`FirstName`,''), ' ', COALESCE(r.`LastName`,''),'<br>',r.`PayerEmail`,'</a>') AS `Client`,
    r.`ItemName`, e.`PaymentProvider`, r.`PaymentStatus`, r.`GrossAmount`, r.`MerchantFee`, r.`CurrencyCode`
FROM `wtkRevenue` r
  INNER JOIN `wtkEcommerce` e ON e.`UID` = r.`EcomUID`
SQLVAR;
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
$pgWHERE = '';
if ($pgFilterValue != ''):
    $pgWHERE = " r.`EcomUID` = " . $pgFilterValue . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    if ($pgWHERE != ''):
        $pgWHERE .= ' AND';
    endif;
    $pgWHERE .= " r.`PaymentStatus` = '" . $pgFilter2Value . "'" . "\n";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''
if ($pgWHERE != ''):
    $pgSQL .= ' WHERE ' . $pgWHERE;
endif;
$pgSQL .= ' ORDER BY r.`UID` DESC';
$pgSQL  = wtkSqlPrep($pgSQL);
$gloEditPage = '/admin/revenueEdit';
//$gloDelPage  = 'wtkRevenue'; // have DelDate at end if should DelDate instead of DELETE

wtkSetHeaderSort('PaymentStatus', 'Payment Status');
wtkSetHeaderSort('GrossAmount', 'Gross Amount');

$pgSelSQL =<<<SQLVAR
SELECT `UID`, `PaymentProvider`
 FROM `wtkEcommerce`
ORDER BY `PaymentProvider` ASC
SQLVAR;
$pgSelOptions = wtkGetSelectOptions($pgSelSQL, [], 'PaymentProvider', 'UID', $pgFilterValue);

$pgSelSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = 'PayStatus'
ORDER BY `LookupDisplay` ASC
SQLVAR;
$pgSelPayOptions = wtkGetSelectOptions($pgSelSQL, [], 'LookupDisplay', 'LookupDisplay', $pgFilter2Value);

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Revenue
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('/admin/revenueList','wtkRevenue','$gloRNG')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <input type="hidden" id="HasSelect" name="HasSelect" value="Y">
        <div class="input-field">
           <div class="filter-width-50 input-field">
               <select id="wtkFilter" name="wtkFilter">
                   <option value="">Show All</option>
                   $pgSelOptions
               </select>
               <label for="wtkFilter" class="active">Choose Provider</label>
           </div>
           <div class="filter-width-50 input-field">
               <select id="wtkFilter2" name="wtkFilter2">
                   <option value="">Show All</option>
                   $pgSelPayOptions
               </select>
               <label for="wtkFilter2" class="active">Payment Status</label>
		   </div>
           <button onclick="Javascript:wtkBrowseFilter('/admin/revenueList','wtkRevenue')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$gloColumnAlignArray = array (
    'GrossAmount' => 'right',
    'MerchantFee' => 'right',
	'CurrencyCode' => 'center'
);
$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'wtkRevenue', 'revenueList.php','Y');
//$pgHtm  = wtkReplace($pgHtm, 'There is no data available.','no users yet');
$pgHtm .= '</div><br></div>' . "\n";

echo $pgHtm;
exit;
?>
