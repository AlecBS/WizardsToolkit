<?PHP
$pgSecurityLevel = 25;
if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
endif;

$pgFillMsg = '';
if ($gloRNG != 0): // request to copy translations from other language
    $pgSqlFilter = array(
        'FromLanguage' => $gloRNG,
        'ToLanguage'  => $gloId,
        'ToLanguage2' => $gloId
    );
    $pgSQL =<<<SQLVAR
INSERT INTO `wtkLanguage` (`MassUpdateId`, `Language`, `PrimaryText`)
  SELECT L1.`MassUpdateId`, :ToLanguage, L1.`PrimaryText`
    FROM `wtkLanguage` L1
    LEFT OUTER JOIN `wtkLanguage` L2
      ON L2.`Language` = :ToLanguage2 AND L2.`PrimaryText` = L1.`PrimaryText`
WHERE L1.`Language` = :FromLanguage AND L2.`UID` IS NULL
ORDER BY L1.`UID` ASC
SQLVAR;
    wtkSqlExec($pgSQL, $pgSqlFilter);
    $pgFillMsg = "<p class='green-text'>Missing `$gloId` language translations filled by `$gloRNG`.</p>";
endif;

// BEGIN Generate UPDATE scripts for chosen language
$gloSkipFooter = true;
$gloRowsPerPage = 100;
$pgScriptSQL =<<<SQLVAR
SELECT CONCAT("UPD", "ATE `wtkLanguage` SET `NewText` = '",
    REPLACE(`PrimaryText`,'<','&lt;'),
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
$pgSqlFilter2 = array(
    'Language'  => $pgNewLang,
    'Language2' => $pgNewLang
);
// BEGIN auto-fill SPA MassUpdateId data when go to languageTranslate page (if none exists)
$pgSQL =<<<SQLVAR
INSERT INTO `wtkLanguage` (`MassUpdateId`, `Language`, `PrimaryText`)
  SELECT L1.`MassUpdateId`, :Language, L1.`NewText`
    FROM `wtkLanguage` L1
    LEFT OUTER JOIN `wtkLanguage` L2 ON L2.`Language` = :Language2 AND L2.`PrimaryText` = L1.`NewText`
WHERE L1.`Language` = 'eng' AND L1.`MassUpdateId` IS NOT NULL AND L2.`UID` IS NULL
ORDER BY L1.`UID` ASC
SQLVAR;
wtkSqlExec($pgSQL, $pgSqlFilter2);
//  END  auto-fill SPA MassUpdateId data when go to languageTranslate page (if none exists)

$pgList = wtkBuildDataBrowse($pgScriptSQL, $pgSqlFilter);

$pgSQL =<<<SQLVAR
SELECT `LookupDisplay`
 FROM `wtkLookups`
WHERE `LookupType` = 'LangPref' AND `LookupValue` = :Language
SQLVAR;
$pgLanguage = wtkSqlGetOneResult($pgSQL, $pgSqlFilter);

$pgFillOption = '';

// BEGIN Check to see what language has the most translations which this language is missing
$pgSQL =<<<SQLVAR
SELECT o.`LookupDisplay` AS `Language`, L1.`Language` AS `LangCode`,
    COUNT(L1.`UID`) AS `Count`
  FROM `wtkLanguage` L1
    LEFT OUTER JOIN `wtkLookups` o ON o.`LookupType` = 'LangPref' AND o.`LookupValue` = L1.`Language`
    LEFT OUTER JOIN `wtkLanguage` L2
        ON L2.`Language` = :Language AND L2.`PrimaryText` = L1.`PrimaryText`
WHERE L1.`Language` NOT IN ('eng',:Language2) AND L1.`NewText` IS NOT NULL
  AND L2.`UID` IS NULL
GROUP BY L1.`Language`
ORDER BY COUNT(L1.`UID`) DESC LIMIT 1
SQLVAR;
wtkSqlGetRow($pgSQL, $pgSqlFilter2);
$pgNewCount = wtkSqlValue('Count');
$pgLangCode = wtkSqlValue('LangCode');
$pgMaxLanguage = wtkSqlValue('Language');

if ($pgNewCount > 0):
    $pgFillOption .=<<<htmVAR
<p>There are $pgNewCount translations defined for $pgMaxLanguage which are not
 in $pgLanguage.  Click
 <a onclick="JavaScript:ajaxGo('/admin/languageTranslate','$pgNewLang','$pgLangCode')">copy data</a>
  to add those to $pgLanguage for translation.</p>
<hr>
htmVAR;
endif;
//  END  Check to see what language has the most translations which this language is missing

$pgHowTo =<<<htmVAR
<h5><br>How to Use</h5><br>$pgFillMsg
<p>Run the following in <a target="_blank" href="https://chatgpt.com/">ChatGPT</a>
   or some other AI to receive the SQL scripts
   that will generate the language translations for <strong>$pgLanguage.</strong></p>
<p>This will do 100 translations at a time so may need to be run multiple times.
 </p>
<p>After AI generates the UPDATE scripts, run them in your database.</p>
<hr>$pgFillOption
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
