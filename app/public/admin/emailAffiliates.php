<?PHP
require('incEmailPrep.php');
$pgTemplate = wtkGetPost('EmailHTM','email' . $gloDarkLight);

switch ($pgMode):
    case 'SendOne':
        $pgSQL =<<<SQLVAR
SELECT fncWTKhash(a.`UID`) AS `Hash`, a.`Email`,
    COALESCE(a.`CompanyName`, a.`ContactName`, a.`Email`) AS `ToName`,
    COALESCE(a.`CompanyName`, a.`ContactName`) AS `CompanyName`,
    COALESCE(a.`ContactName`, a.`CompanyName`) AS `ContactName`,
    a.`WebPasscode`, a.`DiscountPercentage`, a.`AffiliateRate`
 FROM `wtkAffiliates` a
WHERE a.`UID` = :UID
SQLVAR;
        $pgSqlFilter = array('UID' => $gloId );
        wtkSqlGetRow($pgSQL, $pgSqlFilter);
        $pgToEmail = wtkSqlValue('Email');
        $pgBody = wtkReplace($pgEmailBody, '@CompanyName@', wtkSqlValue('CompanyName'));
        $pgBody = wtkReplace($pgBody, '@ContactName@', wtkSqlValue('ContactName'));
        $pgBody = wtkReplace($pgBody, '@ToName@', wtkSqlValue('ToName'));
        $pgBody = wtkReplace($pgBody, '@hash@', wtkSqlValue('Hash'));
        $pgBody = wtkReplace($pgBody, '@DiscountPercentage@', wtkSqlValue('DiscountPercentage'));
        $pgAffiliateRate = wtkSqlValue('AffiliateRate');
        $pgAffiliateRate = wtkReplace($pgAffiliateRate, '.00','');
        $pgBody = wtkReplace($pgBody, '@AffiliateRate@', $pgAffiliateRate);

        $pgBody = wtkTokenToValue($pgBody);
        $pgBody = wtkReplace($pgBody, '@WebPasscode@', wtkSqlValue('WebPasscode'));
        $pgSaveArray['OtherUID'] = $gloId;
        $pgTmp = wtkNotifyViaEmail($pgSubject, $pgBody, $pgToEmail, $pgSaveArray,'',$pgTemplate . '.htm');
        $pgHtm =<<<htmVAR
<div class="row">
    <div class="col s12"><br>
        <div class="card">
            <div class="card-content">
                <h2>Email Sent</h2>
                <p><strong>$pgSubject</strong> email sent email to:<br>$pgToEmail</p>
            </div>
        </div>
    </div>
</div>
htmVAR;
        break;
    case 'SendAll':
        $gloBulkEmailing = true;
        $pgSQLstart =<<<SQLVAR
SELECT a.`UID`, fncWTKhash(a.`UID`) AS `Hash`, a.`Email`,
    COALESCE(a.`CompanyName`, a.`ContactName`, a.`Email`) AS `ToName`,
    COALESCE(a.`CompanyName`, a.`ContactName`) AS `CompanyName`,
    COALESCE(a.`ContactName`, a.`CompanyName`) AS `ContactName`,
    a.`WebPasscode`, a.`DiscountPercentage`, a.`AffiliateRate`
SQLVAR;

        $pgSQL =<<<SQLVAR
 FROM `wtkAffiliates` a
    LEFT OUTER JOIN `wtkEmailsSent`e ON e.`OtherUID` = a.`UID` AND e.`EmailUID` = :EmailUID
WHERE e.`UID` IS NULL AND a.`DelDate` IS NULL
    AND a.`Email` IS NOT NULL
SQLVAR;
        if ($pgTimeZone != ''):
            $pgSQL .= " AND (a.`TimeZone` IS NULL OR a.`TimeZone` = '$pgTimeZone')";
        endif;
        $pgSQLend = ' GROUP BY a.`UID`' . "\n";
        $pgSQLend .= ' ORDER BY a.`UID` ASC';
        if ($gloDbConnection == 'Live'):
            $pgSQLend .= ' LIMIT 50' . "\n";
        else:
            $pgSQLend .= ' LIMIT 1' . "\n";
        endif;
        $pgSqlFilter = array (
            'EmailUID' => $pgEmailUID
        );
        $pgSelSQL = wtkSqlPrep($pgSQLstart . $pgSQL . $pgSQLend);
        $pgPDO = $gloWTKobjConn->prepare($pgSelSQL);
        $pgPDO->execute($pgSqlFilter);
        $pgCnt = 0;
        $pgList = '';
        while ($pgRow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
            $pgCnt ++;
            $pgToEmail = $pgRow['Email'];
            $pgUserUID = $pgRow['UID'];
            $pgSaveArray['OtherUID'] = $pgUserUID;

            $pgBody = wtkReplace($pgEmailBody, '@CompanyName@', $pgRow['CompanyName']);
            $pgBody = wtkReplace($pgBody, '@ContactName@', $pgRow['ContactName']);
            $pgBody = wtkReplace($pgBody, '@ToName@', $pgRow['ToName']);
            $pgBody = wtkReplace($pgBody, '@hash@', $pgRow['Hash']);
            $pgBody = wtkReplace($pgBody, '@WebPasscode@', $pgRow['WebPasscode']);
            $pgBody = wtkReplace($pgBody, '@DiscountPercentage@', $pgRow['DiscountPercentage']);
            $pgAffiliateRate = $pgRow['AffiliateRate'];
            $pgAffiliateRate = wtkReplace($pgAffiliateRate, '.00','');
            $pgBody = wtkReplace($pgBody, '@AffiliateRate@', $pgAffiliateRate);
            $pgBody = wtkTokenToValue($pgBody);

            $pgTmp = wtkNotifyViaEmail($pgSubject, $pgBody, $pgToEmail, $pgSaveArray,'',$pgTemplate . '.htm');
            if ($pgCnt < 21):
                // if (strlen($pgToEmail) > 18):
                    $pgList .= '<div class="col m4 s6">';
                // else:
                //     $pgList .= '<div class="col m3 s4">';
                // endif;
                $pgList .= $pgUserUID . ': ' . $pgToEmail . '</div>' . "\n";
            endif;
        endwhile;
        $pgAddS = 's';
        if ($pgCnt > 21):
            $pgList .= '<div class="col m3 s4">and more...</div>' . "\n";
        elseif ($pgCnt == 1):
            $pgAddS = '';
        endif;
        $pgPageTime = round(microtime(true) - $gloPageStart,4);
        $pgRemainingCount = wtkSqlGetOneResult('SELECT COUNT(DISTINCT(a.`UID`)) ' . $pgSQL, $pgSqlFilter);
        if ($pgRemainingCount == 0):
            $pgAfterMsg = '<p>All eligible emails have been sent.</p>';
        else:
            $pgAfterMsg = "<p>There are $pgRemainingCount remaining affiliates to email.</p>";
        endif;
        $pgHtm =<<<htmVAR
<div class="card bg-second">
    <div class="card-content">
        <p>Using <strong>$pgTemplate</strong> template</p>
        <h5>"$pgSubject" <small><br>email sent to:</small></h5><br>
        <div class="row">
            $pgList
        </div>
        <div class="center">
            $pgAfterMsg
        </div>
    </div>
	<div class="card-action">Finished sending $pgCnt email$pgAddS in $pgPageTime seconda.</div>
</div>
htmVAR;
        break;
    default: // View
        $pgSQL =<<<SQLVAR
SELECT fncWTKhash(a.`UID`) AS `Hash`, a.`Email`,
    COALESCE(a.`CompanyName`, a.`ContactName`, a.`Email`) AS `ToName`,
    COALESCE(a.`CompanyName`, a.`ContactName`) AS `CompanyName`,
    COALESCE(a.`ContactName`, a.`CompanyName`) AS `ContactName`,
    a.`WebPasscode`, a.`DiscountPercentage`, a.`AffiliateRate`
 FROM `wtkAffiliates` a
WHERE a.`UID` = :UID
SQLVAR;
        $pgSqlFilter = array('UID' => $gloId );
        wtkSqlGetRow($pgSQL, $pgSqlFilter);
        $pgBody = wtkLoadInclude(_RootPATH . 'wtk/htm/' . $pgTemplate . '.htm');
        $pgBody = wtkReplace($pgBody, '@wtkContent@', $pgEmailBody);

        $pgToEmail = wtkSqlValue('Email');
        $pgBody = wtkReplace($pgEmailBody, '@CompanyName@', wtkSqlValue('CompanyName'));
        $pgBody = wtkReplace($pgBody, '@ContactName@', wtkSqlValue('ContactName'));
        $pgBody = wtkReplace($pgBody, '@ToName@', wtkSqlValue('ToName'));
        $pgBody = wtkReplace($pgBody, '@hash@', wtkSqlValue('Hash'));
        $pgBody = wtkReplace($pgBody, '@DiscountPercentage@', wtkSqlValue('DiscountPercentage'));
        $pgAffiliateRate = wtkSqlValue('AffiliateRate');
        $pgAffiliateRate = wtkReplace($pgAffiliateRate, '.00','');
        $pgBody = wtkReplace($pgBody, '@AffiliateRate@', $pgAffiliateRate);

        $pgBody = wtkTokenToValue($pgBody);
        $pgBody = wtkReplace($pgBody, '@WebPasscode@', wtkSqlValue('WebPasscode'));
        $pgHtm  = $pgBody . "\n";
        break;
endswitch;
$pgHtm = wtkReplace($pgHtm, 'btn  btn', 'btn btn');

echo $pgHtm;
exit;
?>
