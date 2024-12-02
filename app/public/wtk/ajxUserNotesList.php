<?php
$pgSecurityLevel = 1;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('wtkLogin.php');
endif;

$pgFlagImportant = wtkFilterRequest('FlagImportant');

$pgSQL =<<<SQLVAR
SELECT n.`UID`, DATE_FORMAT(n.`AddDate`, '$gloSqlDateTime') AS `Date`,
    CONCAT(COALESCE(u.`FirstName`,''), ' ', COALESCE(u.`LastName`,'')) AS `WrittenBy`,
    IF (n.`FlagImportant` = 'N','','<br><i class="material-icons small blue-text">stars</i>') AS `FlagImportant`,
    n.`Notes`
  FROM `wtkUserNote` n
   INNER JOIN `wtkUsers` u ON u.`UID` = n.`AddedByUserUID`
WHERE n.`SecurityLevel` <= :SecurityLevel AND n.`UserUID` = :UID
SQLVAR;
if ($pgFlagImportant == 'Y'):
    $pgSQL .= " AND n.`FlagImportant` = 'Y'" . "\n";
endif;
$pgSQL .= ' ORDER BY n.`UID` DESC';
$pgSqlFilter = array (
    'UID' => $gloRNG,
    'SecurityLevel' => $gloUserSecLevel
);
$gloEditPage = '/wtk/userNoteEdit';
$gloAddPage  = $gloEditPage;

if ($pgFlagImportant == 'Y'):
    $gloColHdr =<<<htmVAR
    <th><form>
        <h4>Notes &nbsp; <small>
        <a onclick="JavaScript:wtkBrowseReset('/wtk/ajxUserNotesList','wtkUserNoteDIV',$gloRNG)" class="btn btn-small btn-save waves-effect waves-light">Show All</a>
        </small></h4></form>
    </th>
htmVAR;
else:
    $gloColHdr =<<<htmVAR
    <th><form id="userNoteForm"><input type="hidden" id="Filter" name="Filter" value="Y">
            <input type="hidden" id="rng" name="rng" value="$gloRNG">
            <input type="hidden" id="FlagImportant" name="FlagImportant" value="Y">
        <h4>Notes &nbsp; <small>
        <a onclick="JavaScript:wtkBrowseFilter('/wtk/ajxUserNotesList','wtkUserNoteDIV','userNoteForm')" class="btn btn-small btn-save waves-effect waves-light">Show Only Important</a>
        </small></h4>
        </form>
    </th>
htmVAR;
endif;
$gloRowHtm =<<<htmVAR
<td>
<div class="row">
    <div class="col s1 center">@FlagImportant@</div>
    <div class="col s4">
        <p><strong>@WrittenBy@</strong><br>
        <em>@Date@</em>
    </div>
    <div class="col s7">
        <p>@Notes@</p>
    </div>
</div>
</td>
htmVAR;
$gloBrowseNL2BR = true;
$pgNotesList = wtkBuildDataBrowse($pgSQL, $pgSqlFilter, 'wtkUserNoteDIV', '','Y');
$pgNotesList = wtkReplace($pgNotesList, 'No data.','no notes yet');
$gloBrowseNL2BR = false;
$gloColHdr = '';
$gloRowHtm = '';

echo $pgNotesList;
?>
