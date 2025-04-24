<?PHP
$pgSecurityLevel = 25;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

// BEGIN Generate UPDATE scripts for chosen language
$gloSkipFooter = true;
$gloRowsPerPage = 100;
$pgScriptSQL =<<<SQLVAR
SELECT CONCAT("UPD", "ATE `wtkLanguage` SET `NewText` = '",`PrimaryText`,
    "' WHERE `UID` = ", `UID`, ';') AS `Scripts`
FROM `wtkLanguage`
WHERE `Language` = :Language AND `NewText` IS NULL
ORDER BY `UID` ASC
SQLVAR;
//  END  Generate UPDATE scripts for chosen language

// BEGIN check which language needs the most conversions
$pgLang = wtkGetParam('p');
if ($pgLang == ''):
    $pgSqlFilter = array('Language' => 'eng');
    $pgSQL =<<<SQLVAR
SELECT `Language`
 FROM `wtkLanguage`
WHERE `Language` <> :Language AND `NewText` IS NULL
GROUP BY `Language`
ORDER BY COUNT(*) DESC LIMIT 1
SQLVAR;
    $pgNewLang = wtkSqlGetOneResult($pgSQL, $pgSqlFilter, 'none');
else: // called from this page picking a new language
    $pgNewLang = $pgLang;
endif; // pgLang = ''
//  END  check which language needs the most conversions

$pgSqlFilter = array('Language' => $pgNewLang);
$pgList = wtkBuildDataBrowse($pgScriptSQL, $pgSqlFilter);

$pgSQL =<<<SQLVAR
SELECT `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = 'LangPref' AND `LookupValue` = :Language
SQLVAR;
$pgLanguage = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);

$pgHowTo =<<<htmVAR
<h5><br>How to Use</h5><br>
<p>Run the following in <a target="_blank" href="https://chatgpt.com/">ChatGPT</a>
   or some other AI to receive the SQL scripts
   that will generate the language translations for <strong>$pgLanguage.</strong></p>
<p>This will do 100 translations at a time so may need to be run multiple times.
 </p>
<p>After AI generates the UPDATE scripts, run them in your database.</p>
<hr>
<pre><code>
The following SQL scripts show the `NewText` being set to `English` phrases.
Translate those into '$pgLanguage'.
</code></pre>
htmVAR;

$pgList = $pgHowTo . $pgList;

if ($pgLang != ''):
    echo $pgList;
    exit;
endif;

$gloWTKmode = 'ADD';

$pgSelSQL =<<<SQLVAR
SELECT `LookupValue`, `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = 'LangPref' AND `LookupValue` <> 'eng'
ORDER BY `LookupDisplay` ASC
SQLVAR;
$pgSelOptions = wtkGetSelectOptions($pgSelSQL, [], 'LookupDisplay', 'LookupValue', $pgNewLang);

$pgHtm =<<<htmVAR
<div class="container">
    <h4>Use AI to Generate Translations to $pgLanguage</h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <input type="hidden" id="HasSelect" name="HasSelect" value="Y">
        <div class="input-field">
            <div class="filter-width-50">
                <select id="wtkFilter2" name="wtkFilter2">
                    <option value="">Show All</option>
                    $pgSelOptions
                </select>
                <label for="wtkFilter2" class="active">Change Language to Translate</label>
            </div>
            <button onclick="Javascript:prepLang()" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow" id="LangDIV">
        $pgList
htmVAR;

if ($pgNewLang == 'none'):
    $pgHtm .=<<<htmVAR
<br><h4>No Translations Needed</h4>
<p>No language prompts need to be translated.  To generate language prompts,
 choose the language you want on the login page, then login and and surf the website.
 This will generate the language prompts data that needs to be translated.</p>
<p>Then come back to this page to generate the SQL scripts for AI to translate.</p>
htmVAR;
endif;
$pgHtm .=<<<htmVAR
    </div><br>
</div>
<script type="text/javascript">
function prepLang(){
    let fncLang = $('#wtkFilter2').val();
    ajaxFillDiv('languageTranslate', fncLang, 'LangDIV');
}
</script>
htmVAR;
wtkShowTimeTracks();
echo $pgHtm;
exit;
?>
