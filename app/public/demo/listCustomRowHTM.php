<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');
/*
Define the Header you want in $gloColHdr
Define the HTML template to be used for every row of data in $gloRowHtm
  By doing so the HTML template will be used and all @ColumnNames@ will be replaced by the respective data.
*/
$pgSQL =<<<SQLVAR
SELECT `UID`, DATE_FORMAT(`AddDate`, '$gloSqlDateTime') AS `Date`,
    CONCAT(`FilePath`, `NewFileName`) AS `WTKIMAGE`,
    CONCAT(`FirstName`, ' ', COALESCE(`LastName`,'')) AS `Name`,
    `Address`, `City`, `State`, `Zipcode`
  FROM `wtkUsers`
 WHERE `NewFileName` IS NOT NULL
ORDER BY `FirstName` ASC
SQLVAR;

$gloColHdr = '<th class="center">The WTK Demo List</th>';

$gloRowHtm =<<<htmVAR
<td><div class="row">
        <div class="col s5 center">
            @WTKIMAGE@
            <a class="btn" onclick="JavaScript:alert('My UID is @UID@ and my name is @Name@')">Click Me</a>
        </div>
        <div class="col s7">
            <h4>@Name@</h4><br>
            <p><em>added @Date@</em><br>
            @Address@<br>
            @City@, @State@ @Zipcode@</p>
        </div>
    </div>
</td>
htmVAR;

$pgHtm  = '<h4>Listing with Custom Row Template</h4>' . "\n";
$pgHtm .= wtkBuildDataBrowse($pgSQL);

wtkSearchReplace('m4 offset-m4 s12','m10 offset-m1 s12'); // for minibox adjustment
wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/minibox.htm');
?>
