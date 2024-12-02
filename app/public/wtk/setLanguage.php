<?PHP
$gloLoginRequired = false;
define('_RootPATH', '../');
require('wtkLogin.php');

wtkSetCookie('wtkLang', wtkGetParam('Lang'));

echo '{"result":"ok"}';
exit; // no display needed, handled via JS and spa.htm
?>
