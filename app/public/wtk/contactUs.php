<?php
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

$pgHtm =<<<htmVAR
    <h2>Contact Us</h2>
    <p>We always look forward to hearing from you.</p>
    <form method="post" id="regForm" name="regForm">
        <div class="row">
            <div class="input-field col m12 s12">
                <input id="name" name="name" type="text" class="validate">
                <label for="name">Name</label>
            </div>
            <div class="input-field col m12 s12">
                <input id="email" name="email" type="email" onChange="JavaScript:wtkValidate(this,'EMAIL');" class="validate">
                <label for="email">Email</label>
            </div>
            <div class="input-field col m12 s12">
                <textarea id="msg" name="msg" class="materialize-textarea"></textarea>
                <label for="msg">Message</label>
            </div>
            <div class="input-field col m12 s12">
                <a class="btn waves-effect waves-light right" onclick="Javascript:sendMail('N');">Send<i class="material-icons right">send</i></a>
            </div>
        </div>
     </form>
     <div id="thanksMsg" class="card bg-second hide">
         <div class="card-content">
            <div class="center">
                <h3>Thanks!</h3>
                <p>We will respond to your email soon.</p>
                <a class="btn waves-effect waves-light" href="/">Return Home</a>
            </div>
         </div>
     </div>
htmVAR;
wtkSearchReplace('col m4 offset-m4 s12">','col m6 offset-m3 s12"><h1 class="center">' . $gloCoName . '</h1><br>');

wtkMergePage($pgHtm, $gloCoName, 'htm/minibox.htm');
?>
