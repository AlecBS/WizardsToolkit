<?php
// 2FIX
// Send an SMS using Twilio
$gloSkipConnect = true;
require('twilio/config.php');

function wtkReplace($fncSubject, $fncSearch, $fncReplace) {
    $fncResult = $fncSubject;
    if (is_array($fncResult)):
        foreach ($fncResult as &$fncInnerArray):
            $fncInnerArray = wtkReplace($fncInnerArray, $fncSearch, $fncReplace);
        endforeach;
        unset($fncInnerArray);
        return $fncResult;
    else:   // Not is_array($fncResult)
        return str_replace($fncSearch, $fncReplace, $fncSubject);
    endif;  // is_array($fncResult)
} // end of Replace

function wtkGetPost($fncPostVariable) {
    return isset($_POST[$fncPostVariable]) ? $_POST[$fncPostVariable] : '';
} // end of getPost

$pgTwilio = new Services_Twilio($gloTwilioSID, $gloTwilioToken);

$pgMsg   = wtkGetPost('mySMS');
if ($pgMsg != ''):
    if ($gloDbConnection != 'Live'):
        $pgPhone = $gloTechPhone;
    else:
        $pgPhone = wtkGetPost('phone');
    endif;

    if ($pgPhone != ''):
        $pgPhone = wtkReplace($pgPhone, '(','');
        $pgPhone = wtkReplace($pgPhone, ')','');
        $pgPhone = wtkReplace($pgPhone, '-','');
        $pgPhone = wtkReplace($pgPhone, '.','');
        $pgPhone = wtkReplace($pgPhone, ' ','');
        $pgLength = strlen($pgPhone);
        if ($pgLength = 10):
            $pgPhone = '+1' . $pgPhone;

            $pgSMS = $pgTwilio->account->messages->sendMessage(
                    $gloTwilioPhone,  // our From phone number
                    $pgPhone,        // the number we are sending to - Any phone number
                    $pgMsg
                );
        endif;  // strlen($pgPhone) = 10
    endif;  // $pgPhone != ''
endif;  // $pgMsg != ''
?>
<!doctype html>
<html>
<head>
    <title>Close Window</title>
<script language="JavaScript" type="text/javascript">
<!--
parent.$.fancybox.close();
//-->
</script>
</head>
<body>&nbsp;</body>
</html>
