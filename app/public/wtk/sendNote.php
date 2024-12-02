<?PHP
define('_RootPATH', '../');
require('wtkLogin.php');

$pgParentId = wtkGetPost('id');
$pgNote = wtkGetPost('msg');

$pgSQL =<<<SQLVAR
INSERT INTO `wtkForumMsgs` (`ForumUID`, `UserUID`, `ForumMsg`)
   VALUES (:ForumUID, :UserUID, :ForumMsg)
SQLVAR;
$pgSqlFilter = array (
    'ForumUID' => $pgParentId,
    'UserUID' => $gloUserUID,
    'ForumMsg' => $pgNote
);
wtkSqlExec($pgSQL, $pgSqlFilter);

$pgSQL =<<<SQLVAR
SELECT `FilePath`, `NewFileName`,
    CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `UserName`
  FROM `wtkUsers`
WHERE `UID` = ?
SQLVAR;
wtkSqlGetRow($pgSQL,[$gloUserUID]);
$pgUserName = wtkSqlValue('UserName');
$pgNewFileName = wtkSqlValue('NewFileName');
if ($pgNewFileName != ''):
    $pgFilePath = wtkSqlValue('FilePath');
    $pgPhoto = $pgFilePath . $pgNewFileName;
else:
    $pgPhoto = '/wtk/imgs/bg-user.jpg';
endif;
$pgDateTime = date('n/j/Y \a\t g:i A');

$pgHtm =<<<htmVAR
<div class="forum-single b-shadow">
    <div class="content-user">
        <img src="$pgPhoto">
        <h5>$pgUserName</h5>
        $pgDateTime
    </div>
    <div class="content-text">
        <p>$pgNote</p>
    </div>
</div>
htmVAR;
echo $pgHtm;
exit; // no display needed, handled via JS
?>
