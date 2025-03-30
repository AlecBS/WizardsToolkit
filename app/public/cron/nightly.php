<?PHP
$gloDebug = 'Y'; //2FIX comment-out before deploying; this prevents sending actual emails
require('cronTop.php');

$pgHtm .= '<h3>Nightly</h3>' . "\n";

// BEGIN if you want it to trigger something on the same day of each month
$pgDayOfMonth = date('j');
//$pgDayOfMonth = 16;  // set here to test specific day-of-month testing
if ($pgDayOfMonth == 16): // Only generate researcher payroll on 16th of each month
    // for example, generate payroll on same day of each month
    $pgYear = date('Y');
    if (date('m') == '1'): // January
        $pgYear = ($pgYear - 1);
    endif;
    $pgFromDate = date($pgYear . '-' . date('m', strtotime('last month')) . '-16');
    $pgToDate = date('Y-m-15');

    $pgDueYearMonth = date('Y-m');
    $pgEmailCount = 0;
    $pgTotalCount = 0;
    $pgTotalAmount = 0;
// ONLY FOR TESTING - DELETE NEXT 3 LINES!
    // $pgFromDate = date('Y-10-16');
    // $pgToDate = date('Y-11-15');
    // $pgDueYearMonth = date('Y-11');
//  while ():
    $pgSQL =<<<SQLVAR
SELECT `UID`, `Employee`, `Email`
  FROM `YourFile`
WHERE `YourCriteria` = :YourCriteria
ORDER BY `UID` ASC
SQLVAR;
    $pgSqlFilter = array('YourCriteria' => 'SomeParameter');
    $pgSQL = wtkSqlPrep($pgSQL);
    /*
    $pgPDO = $gloWTKobjConn->prepare($pgSQL);
    $pgPDO->execute($pgSqlFilter);
    while ($gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
        $pgTotalCount ++;
        $pgEmployee = wtkSqlValue('Employee');
        $pgEmail = wtkSqlValue('Email');
    */
        $pgEmployee = 'John Smith'; // fill with data
        $pgEmail = $gloTechSupport; // fill with data
        $pgUserUID = 1; // fill with data
        $pgAmountDue = 123.45;
        $pgMsg =<<<htmVAR
<br>
<p>Dear $pgEmployee,</p>

<p>Thank you for your services during this last month. Your invoice for
 $ $pgAmountDue has been generated, and you can review the details by
 logging into your client portal.</p>

<p>If you have any questions regarding your pay, you can email $gloTechSupport</p>

<p>Warm regards,<br>
$gloCoName</p>
htmVAR;
        $pgSaveArray = array (
            'FromUID' => 0,
            'ToUID' => $pgUserUID
        );
        if ($gloDebug == 'Y'):
            $gloDebug .= 'Emailing ' . $pgEmail . ' about ' . $pgAmountDue . '<br>' . "\n";
        else:
            if (($gloDbConnection == 'Live') || ($pgEmailCount < 2)):
                // For dev testing, only send 2 emails
                wtkNotifyViaEmail('Payroll Processed', $pgMsg, $pgEmail, $pgSaveArray, '', "email$gloDarkLight.htm");
                $pgEmailCount ++;
            endif;
        endif;
//    endwhile;
//    unset($pgPDO);
    //  END  Now loop through generated Payroll and send them all an email
    $pgHtm .= '<hr>created ' . $pgTotalCount . ' payments to be processed<br>';
    $pgHtm .= ' totaling: $' . $pgTotalAmount . '<br>';
    $pgHtm .= 'and sent ' . $pgEmailCount . ' emails<br>';
endif; // $pgDayOfMonth == 16
//  END  Generate Payroll

require('cronEnd.php');
?>
