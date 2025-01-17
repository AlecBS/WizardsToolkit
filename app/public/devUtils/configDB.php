<?PHP
$gloSkipConnect = 'Y';
require('../wtk/lib/Utils.php');
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

function wtkPageProtect($fncPagePasscode, $fncHTMLtemplate = '') {
    // this version does not encrypt the passcode
    global $gloMyPage, $gloWTKmode, $gloShowPrint, $gloSkipConnect, $gloConnected;
    $fncSkipConnect = $gloSkipConnect;
    $fncConnected = $gloConnected;
    $gloSkipConnect = 'Y';    // because this is a non-data-related lookup
    $gloConnected = false;
    $gloShowPrint = false;
    $fncHeader    = '';
    $fncPasscode  = wtkGetPost('PgPasscode');
    if ($fncPasscode != ''):
        if ($fncPasscode != $fncPagePasscode):
            $fncHeader = '<div align="center"><strong>Incorrect password - please try again.<br><br>Must enter a password to access this page.</strong></div>';
        else:
            wtkSetCookie('PgPasscode', $fncPasscode, '1year');
        endif;  // $fncPasscode !=  $fncPagePasscode
    else:   // Not $fncPasscode != ''
        $fncPasscode = wtkGetCookie('PgPasscode');
        if (($fncPasscode == '') || ($fncPasscode != $fncPagePasscode)):
            $fncHeader = '<div align="center"><strong>Must enter a password to access this page.</strong></div>';
        endif;  // $fncPasscode == ''
    endif;  // $fncPasscode != ''
    if ($fncHeader != ''):
        $fncHtm =<<<htmVAR
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Password Required</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="/wtk/css/materialize.min.css">
	<link rel="stylesheet" href="/wtk/css/wtkBlue.css">
	<link rel="stylesheet" href="/wtk/css/wtkLight.css">
	<link rel="stylesheet" href="/wtk/css/wtkGlobal.css">
	<link rel="shortcut icon" href="/imgs/favicon/favicon.ico" type="image/x-icon" />
	<script type="text/javascript" src="/wtk/js/wtkUtils.js" defer></script>
	<script type="text/javascript" src="/wtk/js/jquery.min.js" defer></script>
	<script type="text/javascript" src="/wtk/js/materialize.min.js" defer></script>
	<script type="text/javascript" src="/wtk/js/wtkPaths.js" defer></script>
	<script type="text/javascript" src="/wtk/js/Chart.bundle.min.js" defer></script>
	<script type="text/javascript" src="/wtk/js/wtkColors.js" defer></script>
</head>
<body onload="Javascript:wtkStartMaterializeCSS()" class="blue">
	<div id="mainPage">
		<br>
		<div class="row"><div class="col m4 offset-m4 s12">
				<div class="card b-shadow">
					<div class="card-content">
					<br>
				    <form id="wtkForm" name="wtkForm" action="?" method="POST">
        <div class="row">    <div class="input-field col m9 s12">
        <input type="password" id="PgPasscode" name="PgPasscode" value="">
        <label for="PgPasscode">Passcode</label>
    </div>
    <div class="col m3 s12">
<button type="submit" class="btn btn-save b-shadow waves-effect right" onclick="Javascript:ajaxPost('/devUtils/index.php', 'wtkForm', '')">Enter</button>
    </div>
</div>
</form>

					</div>
				</div>
		</div></div>
	</div>
	<!-- preloader -->
	<div id="plsWait" class="modal wrapper-load center-align">
		<div class="preloader-wrapper medium-size active">
			<div class="spinner-layer spinner-custom">
				<div class="circle-clipper left">
					<div class="circle"></div>
				</div>
				<div class="gap-patch">
					<div class="circle"></div>
				</div>
				<div class="circle-clipper right">
					<div class="circle"></div>
				</div>
			</div>
		</div>
	</div>
	<!-- end preloader -->
	<div id="modalAlert" class="modal">
		<div class="modal-content card center">
			<i id="modIcon" class="material-icons large red-text text-darken-1">warning</i>
			<h4 id="modHdr">Ooops!</h4>
			<p id="modText"></p>
			<a class="btn b-shadow center modal-close waves-effect" id="langClose">Close</a>
		</div>
	</div>
	<script type="text/javascript" src="/wtk/js/wtkLibrary.js" defer></script>
	<script type="text/javascript" src="/wtk/js/wtkClientVars.js" defer></script>
	<script type="text/javascript" src="/wtk/js/wtkChart.js" defer></script>
	<script type="text/javascript" src="/wtk/js/wtkFileUpload.js" defer></script>
</body>
</html>
htmVAR;
        echo $fncHtm;
        exit;
    endif;  // $fncHeader  != ''
    $gloSkipConnect = $fncSkipConnect;
    $gloConnected = $fncConnected;
}  // end of wtkPageProtect
wtkPageProtect('wtk4LowCodeDB');

$gloDriver1 = wtkGetGet('Driver',getenv('DATABASE_DRIVER'));
if ($gloDriver1 == ''):
    $gloDriver1 = 'mysql';
endif;
$gloServer1 = wtkGetGet('Server', getenv('DATABASE_ENDPOINT')); // AWS standard
if ($gloServer1 == ''): // assume Google Cloud Platform (GCP) naming convention
    $gloServer1 = 'localhost';
    $gloDb1     = wtkGetGet('DB',getenv('DB_NAME'));
    $gloUser1   = wtkGetGet('User',getenv('DB_USER'));
    $gloPassword1 = wtkGetGet('PW',getenv('DB_PASS'));
    $gloPort    = wtkGetGet('Port',getenv('DB_PORT'));
    $gloUnixSocket = wtkGetGet('Unix', getenv('INSTANCE_UNIX_SOCKET'));
else: // AWS naming convention
    $gloDb1     = wtkGetGet('DB',getenv('DATABASE'));
    $gloUser1   = wtkGetGet('User',getenv('DATABASE_LOGIN'));
    $gloPassword1 = wtkGetGet('PW',getenv('DATABASE_PASSWORD'));
    $gloPort    = wtkGetGet('Port',getenv('DATABASE_PORT'));
    $gloUnixSocket = wtkGetGet('Unix', getenv('UNIX_SOCKET'));
endif;

$pgSkipPort = wtkGetGet('SkipPort','N');
$pgSkipOptions = wtkGetGet('SkipOptions','N');

$pgHtm =<<<htmVAR
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>SQL Configuration</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="/wtk/css/materialize.min.css">
	<link rel="stylesheet" href="/wtk/css/wtkBlue.css">
	<link rel="stylesheet" href="/wtk/css/wtkLight.css">
	<link rel="stylesheet" href="/wtk/css/wtkGlobal.css">
	<link rel="shortcut icon" href="/imgs/favicon/favicon.ico" type="image/x-icon" />
</head>
<body class="blue">
    <div class="container"><br><br>
        <div class="card">
            <div class="card-content">
                <h2>SQL Connection Configuration</h2>
                <ul class="browser-default">
                    <li>gloDriver1 = $gloDriver1
                    <li>gloServer1 = $gloServer1 </li>
                    <li>gloDb1 = $gloDb1 </li>
                    <li>gloUser1 = $gloUser1 </li>
                    <li>gloPassword1 = $gloPassword1 </li>
                    <li>gloPort = $gloPort </li>
                </ul>
htmVAR;
if ($pgSkipPort == 'Y'):
    $pgHtm .= '<b> Port not being used in configuration</b>' . "\n";
endif;

if ($pgSkipOptions == 'N'):
    $pgHtm .= '<p>PDO standard options used</p>';
    $pgPDOoptions = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ];
else:
    $pgHtm .= '<p>No PDO options will be used</p>';
endif;
$pgHtm .= '<hr>' . "\n";

$pgTest = wtkGetGet('Test','basic');
$pgTestB = '';
$pgTestU = '';
$pgTestD = '';
switch ($pgTest):
    case 'basic':
        $pgHtm .= '<h3>Basic Test</h3>' . "\n";
        $pgTestB = 'CHECKED';
        $pgDSN = "$gloDriver1:host=$gloServer1;dbname=$gloDb1";
        switch ($gloDriver1):
            case 'mysql':
            case 'mysqli':
                $pgDSN .= ';charset=utf8mb4';
                break;
        endswitch;
        if ($pgSkipPort == 'N'):
            $pgDSN .= ";port=$gloPort";
        endif;
        break;
    case 'unix':
        $pgHtm .= '<h3>UNIX Socket Test</h3>' . "\n";
        $pgTestU = 'CHECKED';
        $pgDSN = "$gloDriver1:dbname=$gloDb1;unix_socket=$gloUnixSocket";
        break;
    case 'dsn':
        $pgHtm .= '<h3>DSN Direct Test</h3>' . "\n";
        $pgTestD = 'CHECKED';
        $pgDSN = wtkGetGet('DSN');
        if ($pgDSN == ''):
            echo '<br>No DSN passed so building a default one' . "\n";
            $pgDSN = "$gloDriver1:host=$gloServer1;dbname=$gloDb1";
            if ($pgSkipPort == 'N'):
                $pgDSN .= ";port=$gloPort";
            endif;
            if ($gloUnixSocket != ''):
                $pgDSN .= ";unix_socket=$gloUnixSocket";
            endif;
        endif;
        break;
endswitch;

$pgHtm .= 'Configured DSN: ' . $pgDSN . "\n";
echo $pgHtm;
$pgHtm = '';
$pgError = false;
try {
    if ($pgSkipOptions == 'N'):
        $gloWTKobjConn = new PDO($pgDSN, $gloUser1, $gloPassword1, $pgPDOoptions);
    else:
        $gloWTKobjConn = new PDO($pgDSN, $gloUser1, $gloPassword1);
    endif;
} catch (\PDOException $e) {
    // throw new \PDOException($e->getMessage(), (int)$e->getCode());
    $pgHtm  = '<hr><h3>PDO Connection Error</h3>' . "\n";
    $pgHtm .= '<hr>Error Code: ' . $e->getCode() . "\n";
    $pgHtm .= '<br>Error Message: ' . $e->getMessage() . "\n";
    $pgError = true;
}

if ($pgError == false):
    echo '<br><h5 class="green-text">Succeeded with new PDO() !</h5><br>' . "\n";
    $pgSQL =<<<SQLVAR
SELECT `FirstName`,`LastName`
 FROM `wtkUsers`
ORDER BY `UID` DESC LIMIT 1
SQLVAR;
    if ($gloDriver1 == 'pgsql'):
        $pgSQL = str_replace('`', '"', $pgSQL);
    endif;
    $pgPDO = $gloWTKobjConn->prepare($pgSQL);
    $pgPDO->execute([]);
    $gloPDOrow = $pgPDO->fetch(PDO::FETCH_ASSOC);
    $pgHtm  = '<hr><h3>SQL SELECT Results</h3>' . "\n";
    $pgHtm .= '<p>Last User in wtkUsers data table:</p>' . "\n";
    $pgHtm .= 'First Name: ' . $gloPDOrow['FirstName'] . "\n";
    $pgHtm .= '<br>Last Name: ' . $gloPDOrow['LastName'] . "\n";
else:
    $pgSkipPort = wtkGetGet('SkipPort','N');
    if ($pgSkipPort == 'Y'):
        $pgSkipPortY = 'CHECKED';
        $pgSkipPortN = '';
    else:
        $pgSkipPortY = '';
        $pgSkipPortN = 'CHECKED';
    endif;
    $pgSkipOptions = wtkGetGet('SkipOptions','N');
    if ($pgSkipOptions == 'Y'):
        $pgSkipOptionsY = 'CHECKED';
        $pgSkipOptionsN = '';
    else:
        $pgSkipOptionsY = '';
        $pgSkipOptionsN = 'CHECKED';
    endif;

    $pgHtm .=<<<htmVAR
<hr>
<h2>Connection Failed</h2>
<p>Try changing some of the parameters.</p>
<form action="configDB.php">
    <table>
        <tr>
            <td>Test Type:</td>
            <td>
              <div class="input-field">
                <p>
                    <label for="TestTypeB">
                        <input class="with-gap" type="radio" id="TestTypeB" name="Test" value="basic" $pgTestB>
                        <span>Basic</span>
                    </label>
                </p>
                <p>
                    <label for="TestTypeUNIX">
                        <input class="with-gap" type="radio" id="TestTypeUNIX" name="Test" value="unix" $pgTestU>
                        <span>UNIX</span>
                    </label>
                </p>
                <p>
                    <label for="TestTypeDSN">
                        <input class="with-gap" type="radio" id="TestTypeDSN" name="Test" value="dsn" $pgTestD>
                        <span>DSN</span>
                    </label>
                </p>
              </div>
            </td>
        </tr>
        <tr>
            <td>Driver:</td>
            <td><input type="text" id="Driver" name="Driver" value="$gloDriver1"></td>
        </tr>
        <tr>
            <td>Server:</td>
            <td><input type="text" id="Server" name="Server" value="$gloServer1"></td>
        </tr>
        <tr>
            <td>Database:</td>
            <td><input type="text" id="DB" name="DB" value="$gloDb1"></td>
        </tr>
        <tr>
            <td>DB User:</td>
            <td><input type="text" id="User" name="User" value="$gloUser1"></td>
        </tr>
        <tr>
            <td>DB Password:</td>
            <td><input type="text" id="PW" name="PW" value="$gloPassword1"></td>
        </tr>
        <tr>
            <td>Port:</td>
            <td><input type="text" id="Port" name="Port" value="$gloPort"></td>
        </tr>
        <tr>
            <td>UNIX Socket:</td>
            <td><input type="text" id="Unix" name="Unix" value="$gloUnixSocket">
                <br>Only used for "UNIX" Test Type</td>
        </tr>
        <tr>
            <td>Full DSN:</td>
            <td><input type="text" id="DSN" name="DSN" value="$pgDSN">
                <br>Only used for "DSN" Test Type.  Hardcode entire DSN you want.</td>
        </tr>
        <tr>
            <td>Skip Port:</td>
            <td>
                <div class="input-field">
                  <p>
                      <label for="SkipPortN">
                          <input class="with-gap" type="radio" id="SkipPortN" name="SkipPort" value="N" $pgSkipPortN>
                          <span>No</span>
                      </label>
                  </p>
                  <p>
                      <label for="SkipPortY">
                          <input class="with-gap" type="radio" id="SkipPortY" name="SkipPort" value="Y" $pgSkipPortY>
                          <span>Yes</span>
                      </label>
                  </p>
                </div>
            </td>
        </tr>
        <tr>
            <td>Skip PDO Options:</td>
            <td>
                <div class="input-field">
                  <p>
                      <label for="SkipOptionsN">
                          <input class="with-gap" type="radio" id="SkipOptionsN" name="SkipOptions" value="N" $pgSkipOptionsN>
                          <span>No</span>
                      </label>
                  </p>
                  <p>
                      <label for="SkipOptionsY">
                          <input class="with-gap" type="radio" id="SkipOptionsY" name="SkipOptions" value="Y" $pgSkipOptionsY>
                          <span>Yes</span>
                      </label>
                  </p>
              </div>
            </td>
        </tr>
    </table>
    <div class="center"><br><button class="btn" type="submit">Submit</button></div>
</form>
htmVAR;
endif;
$pgHtm .=<<<htmVAR
        </div></div></div>
    </body>
</html>
htmVAR;
echo $pgHtm;
?>
