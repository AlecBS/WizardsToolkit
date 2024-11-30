<?php
$gloLoginRequired = false;
define('_RootPATH', '../../');
require('../../wtk/wtkLogin.php');

$pgFileName = basename($_FILES['file']['name']);
$pgFileExt = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
$pgNewFileName = wtkGenerateFileName('wtkBlog', $pgFileExt);
$pgUploadFile = '../imgs/' . $pgNewFileName;
if (move_uploaded_file($_FILES['file']['tmp_name'], $pgUploadFile)):
    echo '../imgs/' . $pgNewFileName;
else:
    echo 'upload failure';
endif;
exit;
?>
