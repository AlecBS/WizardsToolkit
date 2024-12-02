<?PHP
function wtkGetIPaddress() {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $fncKey):
        if (array_key_exists($fncKey, $_SERVER) === true):
            foreach (explode(',', $_SERVER[$fncKey]) as $fncIP):
                $fncIP = trim($fncIP); // just to be safe
                if (filter_var($fncIP, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false):
                    return $fncIP;
                endif;
            endforeach;
        endif;  // array_key_exists($fncKey, $_SERVER) === true
    endforeach;
    return 'no-IP'; // ABS 05/21/18
}  // end of wtkGetIPaddress

$pgIPaddress = wtkGetIPaddress();
if ($pgIPaddress != 'no-IP'):
    $pgTmp .= "<p>Your IP address ($pgIPaddress) has been logged and our";
else:
    $pgTmp .= "<p>Our";
endif;
$pgTmp .= " technical staff has been notified so they can look into this immediately.</p>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Hacker Prevention</title>
</head>
<body>
    <div align="center"><br><br>
        <h2>Nefarious Action Detected</h2><br>
        <?php echo $pgTmp; ?>
    </div>
</body>
</html>
