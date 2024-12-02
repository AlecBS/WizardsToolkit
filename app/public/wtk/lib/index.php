<?php
function wtkRedirect($fncURL, $fncPermanent = 'Y') {
    if (!headers_sent()):
        if ($fncPermanent == 'Y'):
            header("HTTP/1.1 301 Moved Permanently");
        endif;
        header('Location: ' . $fncURL);
    else:
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $fncURL . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $fncURL . '" />';
        echo '</noscript>';
    endif;
    exit;
}  // end of wtkRedirect

wtkRedirect('/');
?>
