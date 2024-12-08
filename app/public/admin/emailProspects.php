<?PHP
require('incEmailPrep.php');
$pgTemplate = wtkGetPost('EmailHTM','email' . $gloDarkLight);

switch ($pgMode):
    case 'SendOne':
        $pgSQL =<<<SQLVAR
SELECT CRC32(s.`UID`) AS `Hash`, s.`ProspectUID`, s.`Email`, s.`FirstName`,
    s.`LastName`, COALESCE(p.`CompanyName`,'') AS `ProspectName`
 FROM `wtkProspectStaff` s
   INNER JOIN `wtkProspects` p ON p.`UID` = s.`ProspectUID`
WHERE s.`UID` = :UID
SQLVAR;
        $pgSqlFilter = array('UID' => $gloId );
        wtkSqlGetRow($pgSQL, $pgSqlFilter);
        $pgToEmail = wtkSqlValue('Email');
        $pgProspectUID = wtkSqlValue('ProspectUID');
        $pgFirstName = wtkSqlValue('FirstName');
        $pgLastName = wtkSqlValue('LastName');
        $pgBody = wtkReplace($pgEmailBody, '@ProspectName@', wtkSqlValue('ProspectName'));
        $pgBody = wtkReplace($pgBody, '@FirstName@', $pgFirstName);
        $pgBody = wtkReplace($pgBody, '@LastName@', $pgLastName);
        $pgBody = wtkReplace($pgBody, '@FullName@', $pgFirstName . ' ' . $pgLastName);
        $pgBody = wtkReplace($pgBody, '@hash@', wtkSqlValue('Hash'));
        $pgBody = wtkTokenToValue($pgBody);
        $pgSaveArray['OtherUID'] = $gloId;
        $pgTmp = wtkNotifyViaEmail($pgSubject, $pgBody, $pgToEmail, $pgSaveArray,'', $pgTemplate . '.htm');
        $pgUpdSQL =<<<SQLVAR
UPDATE `wtkProspectStaff`
  SET `EmailsSent` = (`EmailsSent` + 1)
WHERE `UID` = :UID
SQLVAR;
        wtkSqlExec($pgUpdSQL, $pgSqlFilter);
        $pgSQL =<<<SQLVAR
UPDATE `wtkProspects`
  SET `ProspectStatus` = 'email'
WHERE `UID` = :UID AND `ProspectStatus` = 'new'
SQLVAR;
        $pgSqlFilter = array('UID' => $pgProspectUID);
        wtkSqlExec($pgSQL, $pgSqlFilter);
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
        $pgSQL =<<<SQLVAR
SELECT s.`UID`, CRC32(s.`UID`) AS `Hash`, s.`ProspectUID`, s.`Email`,
    COALESCE(p.`CompanyName`,'') AS `ProspectName`,
    COALESCE(s.`FirstName`,'') AS `FirstName`,
    COALESCE(s.`LastName`,'') AS `LastName`
  FROM `wtkProspectStaff` s
    LEFT OUTER JOIN `wtkEmailsSent`e ON e.`OtherUID` = s.`UID` AND e.`EmailUID` = :EmailUID
    INNER JOIN `wtkProspects` p ON p.`UID` = s.`ProspectUID`
  WHERE s.`DoNotContact` = 'N' AND e.`UID` IS NULL
    AND s.`EmailsSent` = :EmailsSent
    AND p.`ProspectStatus` = 'new'
SQLVAR;
        if ($pgTimeZone != ''):
            $pgSQL .= " AND (p.`TimeZone` IS NULL OR p.`TimeZone` = '$pgTimeZone')";
        endif;
        $pgSQL .= ' GROUP BY s.`ProspectUID`' . "\n";
        $pgSQL .= ' ORDER BY s.`UID` ASC' . "\n";
        if ($gloDbConnection == 'Live'):
            $pgSQL .= ' LIMIT 50' . "\n";
        else:
            $pgSQL .= ' LIMIT 1' . "\n";
        endif;

    /* testing
        $pgSQL =<<<SQLVAR
SELECT s.`UID`, CRC32(s.`UID`) AS `Hash`, s.`ProspectUID`, s.`Email`,
    COALESCE(s.`FirstName`,'') AS `FirstName`,
    COALESCE(s.`LastName`,'') AS `LastName`
FROM `wtkProspectStaff` s
LEFT OUTER JOIN `wtkEmailsSent`e ON e.`OtherUID` = s.`UID` AND e.`EmailUID` = :EmailUID
WHERE s.`DoNotContact` = 'N'
--  AND e.`UID` IS NULL
  AND s.`EmailsSent` > :EmailsSent
  AND s.`ProspectUID` = 32768
GROUP BY s.`ProspectUID`
ORDER BY s.`UID` ASC LIMIT 1
SQLVAR;
*/
        $pgSqlFilter = array (
            'EmailUID' => $pgEmailUID,
            'EmailsSent' => 0
        );

        $pgSQL = wtkSqlPrep($pgSQL);
        $pgPDO = $gloWTKobjConn->prepare($pgSQL);
        $pgPDO->execute($pgSqlFilter);
        $pgUpdSQL =<<<SQLVAR
UPDATE `wtkProspectStaff`
  SET `EmailsSent` = (`EmailsSent` + 1)
WHERE `UID` = :UID
SQLVAR;
        $pgUpdProSQL =<<<SQLVAR
UPDATE `wtkProspects`
  SET `ProspectStatus` = 'email'
WHERE `UID` = :ProUID AND `ProspectStatus` = 'new'
SQLVAR;
        $pgCnt = 0;
        $pgUpdSqlFilter = array('UID' => 0);
        $pgProFilter = array('ProUID' => 0);
        $pgList = '';
        while ($pgRow = $pgPDO->fetch(PDO::FETCH_ASSOC)):
            $pgCnt ++;
            $pgToEmail = $pgRow['Email'];
            $pgUserUID = $pgRow['UID'];
            $pgUpdSqlFilter['UID'] = $pgUserUID;
            $pgSaveArray['OtherUID'] = $pgUserUID;
            $pgBody = wtkReplace($pgEmailBody, '@ProspectName@', $pgRow['ProspectName']);
            $pgBody = wtkReplace($pgBody, '@FirstName@', $pgRow['FirstName']);
            $pgBody = wtkReplace($pgBody, '@LastName@', $pgRow['LastName']);
            $pgBody = wtkReplace($pgBody, '@hash@', $pgRow['Hash']);
            $pgBody = wtkTokenToValue($pgBody);
            $pgTmp = wtkNotifyViaEmail($pgSubject, $pgBody, $pgToEmail, $pgSaveArray,'', $pgTemplate . '.htm');
            wtkSqlExec($pgUpdSQL, $pgUpdSqlFilter);
            $pgProspectUID = $pgRow['ProspectUID'];
            $pgProFilter['ProUID'] = $pgProspectUID;
            wtkSqlExec($pgUpdProSQL, $pgProFilter);
            if ($pgCnt < 20):
                if (strlen($pgToEmail) > 18):
                    $pgList .= '<div class="col m4 s6">';
                else:
                    $pgList .= '<div class="col m3 s4">';
                endif;
                $pgList .= $pgUserUID . ': ' . $pgToEmail . '</div>' . "\n";
            endif;
        endwhile;
        $pgAddS = 's';
        if ($pgCnt > 20):
            $pgList .= '<div class="col m3 s4">and more...</div>' . "\n";
        elseif ($pgCnt == 1):
            $pgAddS = '';
        endif;
        $pgForm  = wtkFormHidden('id', $gloRNG);
        $pgForm .= wtkFormHidden('EmailHTM', $pgTemplate);
//        $pgForm .= wtkFormHidden('Mode', 'SendOne');
        $pgPageTime = round(microtime(true) - $gloPageStart,4);
        $pgHtm =<<<htmVAR
<br>
<div class="container">
    <div class="card bg-second">
        <div class="card-content">
    	  <h3>"$pgSubject" email sent to:</h3><br>
          <form id="FemailResults" name="FemailResults">
          $pgForm
          </form>
          <div class="row">
              $pgList
          </div>
          <div class="center">
              <a class="btn" onclick="JavaScript:adminEmailing('Prospects','$pgEmailTemplate','SendAll')">Bulk Email</a>

          </div>
        </div>
    	<div class="card-action">Finished sending $pgCnt email$pgAddS in $pgPageTime seconds.</div>
    </div>
</div>
htmVAR;
// <a class="btn" onclick="JavaScript:ajaxGo('emailProspects','$pgEmailTemplate','SendAll')">Bulk Email</a>
        break;
    default: // View
        $pgSQL =<<<SQLVAR
SELECT CRC32(s.`UID`) AS `Hash`, s.`Email`,
    COALESCE(s.`FirstName`,'') AS `FirstName`,
    COALESCE(s.`LastName`,'') AS `LastName`
 FROM `wtkProspectStaff` s
WHERE s.`UID` = :UID
SQLVAR;
        $pgSqlFilter = array('UID' => $gloId );
        wtkSqlGetRow($pgSQL, $pgSqlFilter);
        $pgBody = wtkLoadInclude(_RootPATH . 'wtk/htm/email' . $gloDarkLight . '.htm');
        $pgBody = wtkReplace($pgBody, '@wtkContent@', $pgEmailBody);
        $pgBody = wtkReplace($pgBody, '@FirstName@', wtkSqlValue('FirstName'));
        $pgBody = wtkReplace($pgBody, '@LastName@', wtkSqlValue('LastName'));
        $pgBody = wtkReplace($pgBody, '@email@', wtkSqlValue('Email')); // urlencode(trim($fncEmail)));
        $pgBody = wtkReplace($pgBody, '@hash@', wtkSqlValue('Hash'));
        $pgBody = wtkTokenToValue($pgBody);
        $pgHtm  = $pgBody . "\n";
        break;
endswitch;
$pgHtm = wtkReplace($pgHtm, 'btn  btn', 'btn btn');

echo $pgHtm;
exit;
?>
