<?PHP
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;
$gloSiteDesign  = 'SPA';

$pgSQL =<<<SQLVAR
SELECT CONCAT(u.`FirstName`, ' ', COALESCE(u.`LastName`,'')) AS `FromUser`,
 IF(u.`NewFileName` IS NULL, '/wtk/imgs/noPhotoAvail.png', CONCAT(u.`FilePath`, u.`NewFileName`)) AS `FromPhoto`,
 CONCAT(u2.`FirstName`, ' ', COALESCE(u2.`LastName`,'')) AS `ToUser`,
 u2.`FirstName` AS `ToFirstName`,
 IF(u2.`NewFileName` IS NULL, '/wtk/imgs/noPhotoAvail.png', CONCAT(u2.`FilePath`, u2.`NewFileName`)) AS `ToPhoto`
  FROM `wtkUsers` u
   LEFT OUTER JOIN `wtkUsers` u2 ON u2.`UID` = :ToUID
WHERE u.`UID` = :FromUID
SQLVAR;
$pgSqlFilter = array (
    'FromUID' => $gloUserUID,
    'ToUID' => $gloId
);
wtkSqlGetRow($pgSQL, $pgSqlFilter);
$pgFromUser = wtkSqlValue('FromUser');
$pgToFirstName = wtkSqlValue('ToFirstName');
$pgToUser = wtkSqlValue('ToUser');
$pgFromPhoto = wtkSqlValue('FromPhoto');
$pgToPhoto = wtkSqlValue('ToPhoto');

$pgSQL =<<<SQLVAR
SELECT c.`UID`, c.`Message`, c.`SendByUserUID`,
 DATE_FORMAT(c.`AddDate`, '$gloSqlDate') AS `AddDate`
  FROM `wtkChat` c
WHERE (c.`SendToUserUID` = :ToUserUID
    AND c.`SendByUserUID` = :UserUID)
    OR (c.`SendByUserUID` = :ToUser2UID
      AND c.`SendToUserUID` = :User2UID)
ORDER BY c.`UID` ASC
SQLVAR;
$pgSQL = wtkSqlPrep($pgSQL);
$pgSqlFilter = array (
    'ToUserUID' => $gloId,
    'UserUID' => $gloUserUID,
    'ToUser2UID' => $gloId,
    'User2UID' => $gloUserUID
);
$pgChat = wtkChatList($pgSQL, $pgSqlFilter);
$gloWTKmode = 'ADD';

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Chat with $pgToFirstName</h4><br>
    <div class="card content b-shadow">
        <form id="wtkForm" name="wtkForm" method="POST" onkeydown="return event.key != 'Enter';">
            <span id="formMsg" class="green-text">$gloFormMsg</span>
            <div class="row chat-list">
                <div class="col m6 s12">
                    <div class="valign-wrapper">
                        <img src="$pgFromPhoto" class="circle responsive-img" style="max-width: 45px;">
                        <h5>&nbsp;&nbsp; $pgFromUser</h5>
                    </div>
                </div>
                <div class="col m6 s12">
                    <div class="valign-wrapper right">
                        <img src="$pgToPhoto" class="circle responsive-img" style="max-width: 45px;">
                        <h5>&nbsp;&nbsp; $pgToUser</h5>
                    </div>
                </div>
            </div>
            $pgChat
            <div class="form-bottom wtk-chat">
htmVAR;

$pgHtm .= wtkSaveChat($gloId);

$pgHtm .=<<<htmVAR
        </form>
        </div>
    </div>
</div>
htmVAR;

wtkProtoType($pgHtm);
wtkAddUserHistory('Chat');

echo $pgHtm;
exit;
?>
