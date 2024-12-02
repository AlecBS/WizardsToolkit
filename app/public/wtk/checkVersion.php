<?php
/*
 * @author Eric
 */

//$input = $req->input();
$input = file_get_contents('php://input');
$input = json_decode($input);
//print_r($input); exit;

$currentVersionWithPeriods = "1.0.0";
$currentVersion = "100";
$button = "Update";

header('Content-Type: application/json');

if (isset($input->version)):
	$mobileApp = str_replace(".", "", $input->version);
	if ($currentVersion > $mobileApp):
		$message = "Adds support for new stuff. Your current version is " . $mobileApp;
		$data['message'] = $message;
		$data['button'] = $button;
		$data['version'] = $currentVersionWithPeriods;
		$data['link'] = "https://itunes.apple.com/us/app/itunesURL";
		$data['title'] = "New Version Available!";
	else:
		$data['error'] = "Nothing To return";
	endif;
else:
	$data['error'] = "Nothing To return";
endif;
echo json_encode($data);

exit;
?>
