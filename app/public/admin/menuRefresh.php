<?PHP
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

echo wtkMenu(wtkGetPost('p'));
exit; // no display needed, handled via JS and spa.htm
?>
