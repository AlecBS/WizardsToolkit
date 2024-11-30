<?php
$gloLoginRequired = false;
define('_RootPATH', '');
require('wtk/wtkLogin.php');

$pgHtm =<<<htmVAR
	<h3 class="center">Terms of Service</h3>
	<br><br>
	<p>By using the Wizard&rsquo;s Toolkit you agree
		 we may email you occasionally about updates to our development library.</p>
	<br><br>
	<div class="center">
		<a href="/" class="wtkBtn btn waves-effect waves-purple">Return Home</a>
	</div>
htmVAR;
wtkSearchReplace('col m4 offset-m4 s12">','col m6 offset-m3 s12"><h1 class="center">' . $gloCoName . '</h1><br>');
wtkSearchReplace('wtkLight.css','wtkDark.css');
wtkSearchReplace('href="wtk/css/wtkGlobal.css">','href="wtk/css/wtkGlobal.css"><link rel="stylesheet" href="mktg/wtkMarketing.css">');

wtkMergePage($pgHtm, $gloCoName, 'wtk/htm/minibox.htm');
?>
