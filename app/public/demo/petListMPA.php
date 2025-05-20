<?PHP
$gloCSSLib = 'MaterializeCSS';
$pgSecurityLevel = 1;
$gloSiteDesign  = 'MPA'; // MPA or SPA for Multi-Page App or Single Page App; usually set in wtkServerInfo.php
// next IF statement only needed for SPA methodology
// if (!isset($gloConnected)):
    define('_RootPATH', '../');
    require('../wtk/wtkLogin.php');
//endif;
/*
run /SQL/demoMySQL.sql script to generate table and supporting data

must have $gloSiteDesign set to 'MPA' in wtk/wtkServerInfo.php for this to work
$gloSiteDesign = 'MPA';
*/

$pgSQL =<<<SQLVAR
SELECT COUNT(*) AS `Count`
  FROM information_schema.`tables`
   WHERE `TABLE_SCHEMA` = 'wiztools' AND `TABLE_NAME` = :Table
SQLVAR;
$pgTableCount = wtkSqlGetOneResult($pgSQL, ['pets']);

if (($gloId == 'InsertSQL') && ($pgTableCount == 0)):
    $pgSQL =<<<SQLVAR
CREATE TABLE `pets` (
  `UID` int UNSIGNED NOT NULL auto_increment,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DelDate` datetime,
  `UserUID` int UNSIGNED NOT NULL COMMENT 'Owner',
  `PetName` varchar(40),
  `Gender` enum('M','F','U') default NULL,
  `PetType` varchar(4) DEFAULT NULL,
  `City` varchar(40),
  `State` varchar(2),
  `Zipcode` varchar(10),
  `OwnerPhone` varchar(20),
  `OwnerEmail` varchar(60),
  `CanTreat` enum('N','Y') default 'N',
  `BirthDate` date,
  `NextTime` char(8),
  `FilePath` varchar(30) NULL,
  `NewFileName` varchar(12) NULL,
  `Latitude` DECIMAL(20,14) NULL,
  `Longitude` DECIMAL(20,14) NULL,
  `Note` text NULL,
  PRIMARY KEY (`UID`),
  KEY `ix_pets_UserUID` (`UserUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1
SQLVAR;
    wtkSqlExec($pgSQL, []);
    $pgSQL =<<<SQLVAR
CREATE TABLE `petNotes` (
  `UID` int UNSIGNED NOT NULL auto_increment,
  `AddDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `PetUID` int UNSIGNED NOT NULL,
  `UserUID` int UNSIGNED NOT NULL COMMENT 'who added note',
  `PetNote` varchar(120),
  PRIMARY KEY (`UID`),
  FOREIGN KEY (`PetUID`) REFERENCES pets(`UID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1
SQLVAR;
    wtkSqlExec($pgSQL, []);
    $pgSQL =<<<SQLVAR
INSERT INTO `pets` (`DelDate`, `UserUID`, `PetName`, `Gender`, `PetType`, `City`, `State`, `Zipcode`, `OwnerPhone`, `OwnerEmail`, `CanTreat`, `BirthDate`, `NextTime`, `FilePath`, `NewFileName`, `Latitude`, `Longitude`, `Note`)
   VALUES
   	(NULL, 1, 'Dogbert', 'M', 'D', 'Ceres', 'CA', NULL, '(209) 555-1212', 'dude@email.com', 'Y', '2019-04-09', '04:20 PM', NULL, NULL, NULL, NULL, NULL),
   	('2022-05-27 09:16:33', 1, 'Coati deleted', 'M', 'C', NULL, 'AK', NULL, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'female'),
   	(NULL, 1, 'Teva Bunner', 'M', 'R', NULL, 'AK', NULL, NULL, NULL, 'Y', NULL, '05:15 PM', NULL, NULL, NULL, NULL, 'edit'),
   	(NULL, 1, 'Carrot Muncher', 'U', 'R', 'San Jose', 'CA', NULL, '(408) 555-6400', 'coati@email.com', 'Y', NULL, NULL, NULL, NULL, 37.32096470000000, -121.86118270000000, NULL),
   	(NULL, 1, 'Puppers', 'M', 'D', NULL, 'MT', NULL, NULL, NULL, 'N', '2016-05-04', NULL, NULL, NULL, NULL, NULL, 'edit text'),
   	(NULL, 1, 'Cwoat the Coati', 'F', 'C', NULL, 'AK', NULL, NULL, NULL, 'Y', '2022-05-03', '03:25 AM', NULL, NULL, NULL, NULL, 'added photo')
SQLVAR;
    wtkSqlExec($pgSQL, []);
    $pgSQL =<<<SQLVAR
INSERT INTO `petNotes` (`PetUID`, `UserUID`, `PetNote`)
  VALUES
	(1, 1, 'likes meat mucho'),
	(1, 1, 'Loves peanut Butter!'),
	(3, 1, 'likes stuff a lot'),
	(1, 1, 'Cool!'),
	(4, 1, 'Very cute bunny'),
	(4, 1, 'And super fuzzy!'),
	(2, 1, 'name changed again')
SQLVAR;
    wtkSqlExec($pgSQL, []);
    $pgPetLookup = wtkSqlGetOneResult("SELECT COUNT(*) FROM `wtkLookups` WHERE `LookupType` = 'PetType'", []);
    if ($pgPetLookup == 0):
        $pgSQL =<<<SQLVAR
INSERT INTO `wtkLookups` (`LookupType`,`LookupValue`,`LookupDisplay`)
VALUES ('PetType','D','Dog'),
       ('PetType','C','Cat'),
       ('PetType','R','Rabbit')
SQLVAR;
        wtkSqlExec($pgSQL, []);
    endif;
    $pgTableCount = 1;
endif;

if ($pgTableCount == 0):
    $pgHtm =<<<htmVAR
<div class="row">
    <div class="col m6 offset-m3 s12">
        <div class="card">
            <div class="card-content">
                <h3>Missing Pet Demo Data</h3>
                <p>This set of demo pages require `pets` and `petNotes` data tables and data.</p>
                <p>Click <strong><a onclick="JavaScript:ajaxGo('petList','InsertSQL')">here</a></strong>
                 to generate the SQL tables and data.</p>
            </div>
        </div>
    </div>
</div>
htmVAR;
    echo $pgHtm;
    exit;
endif;

$pgSQL =<<<SQLVAR
SELECT `UID`, `PetName`, `City`, DATE_FORMAT(`BirthDate`,'$gloSqlDate') AS `DOB`,
    `fncContactIcons`(`OwnerEmail`,`OwnerPhone`,`Latitude`,`Longitude`,'Y',`UID`,'Y','Y','') AS `OwnerContact`
  FROM `pets`
WHERE `DelDate` IS NULL
SQLVAR;
$pgHideReset = ' class="hide"';
$pgFilterValue = wtkFilterRequest('wtkFilter');
if ($pgFilterValue != ''):
    $pgSQL .= " AND lower(`PetName`) LIKE lower('%" . $pgFilterValue . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilterValue != ''

$pgFilter2Value = wtkFilterRequest('wtkFilter2');
if ($pgFilter2Value != ''):
    $pgSQL .= " AND lower(`OwnerPhone`) LIKE lower('%" . $pgFilter2Value . "%')" . "\n";
    $pgHideReset = '';
endif;  // $pgFilter2Value != ''

$pgSQL .= ' ORDER BY `PetName` ASC';
$pgSQL = wtkSqlPrep($pgSQL);

$gloEditPage = '/demo/petEditMPA';
$gloAddPage  = $gloEditPage;
$gloDelPage  = 'petsDelDate'; // have DelDate at end if should DelDate instead of DELETE

// put in columns you want sortable here:
wtkSetHeaderSort('PetName');
wtkSetHeaderSort('DOB', 'Birth Day', 'BirthDate'); // third parameter is what sort uses
if ($gloDeviceType == 'phone'):
    wtkFillSuppressArray('DOB');
    $pgNameTip = 'pet name';
    $pgOwnerTip = 'owner phone';
else:
    $pgNameTip = 'enter partial Pet Name to search for';
    $pgOwnerTip = 'enter partial Owner Phone to search for';
endif;

$gloColumnAlignArray = array (
    'City' => 'center',
    'DOB'  => 'right'
);

// can have unlimited "more buttons"
$gloMoreButtons = array(
    'Pet Notes' => array(
        'act' => '/demo/petNote',
        'mode' => 'list',
        'img' => 'event_note'
    )
);

$pgHtm =<<<htmVAR
<div class="container">
    <br><br>
    <h4>Pet List
        <small id="filterReset"$pgHideReset>
        <button onclick="JavaScript:wtkBrowseReset('/demo/petList','pets','$gloRNG')" type="button" class="btn btn-small btn-save waves-effect waves-light right">Reset List</button>
        </small>
    </h4>
    <form method="post" name="wtkFilterForm" id="wtkFilterForm" role="search" class="wtk-search card b-shadow">
        <input type="hidden" id="Filter" name="Filter" value="Y">
        <div class="input-field">
           <div class="filter-width-50">
              <input value="$pgFilterValue" name="wtkFilter" id="wtkFilter" type="search" placeholder="$pgNameTip">
           </div>
           <div class="filter-width-50">
			  <input type="search" name="wtkFilter2" id="wtkFilter2" value="$pgFilter2Value" placeholder="$pgOwnerTip">
		   </div>
           <button onclick="Javascript:wtkBrowseFilter('/demo/petList','pets')" id="wtkFilterBtn" type="button" class="btn waves-effect waves-light"><i class="material-icons">search</i></button>
        </div>
    </form>
    <div class="wtk-list card b-shadow">
htmVAR;

$pgHtm .= wtkBuildDataBrowse($pgSQL, [], 'pets', '/demo/petListMPA.php', 'P');

$pgHtm  = wtkReplace($pgHtm, 'No data.','no pets yet');
$pgHtm .= '</div></div>' . "\n";

wtkMergePage($pgHtm, $gloCoName, '../wtk/htm/mpa.htm');
?>
