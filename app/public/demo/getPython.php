<?PHP
$pgSecurityLevel = 1;
define('_RootPATH', '../');
require('../wtk/wtkLogin.php');

$pgStep = wtkGetPost('step');
// modify calling method to pass as many parameters as you want, then pass those to your Python page

$pgPW = 'wtkInternalCall';
// $pgPW is not really necessary since Python page can only be called within PHP pages using method below
// so just need to secure this page

// wtk_python is the Docker image name for Python
$pgPythonURL = "http://wtk_python:5000/?pw=$pgPW&step=$pgStep";
$pgResponse  = file_get_contents($pgPythonURL);

echo $pgResponse;
exit;
?>
