<?PHP
function wtkGetPost($fncPostVariable, $fncDefault = '') {
    $fncResult = isset($_POST[$fncPostVariable]) ? $_POST[$fncPostVariable] : '';
    if ($fncResult == ''):
        $fncResult = $fncDefault;
    endif;  // $fncResult == ''
    return $fncResult ;
} // end of wtkGetPost

$pgFileName = wtkGetPost('fileName');
$pgGradientLeft = wtkGetPost('--gradient-left');
$pgGradientRight = wtkGetPost('--gradient-right');
$pgColor1 = wtkGetPost('--btn-border-color');
$pgColor2 = wtkGetPost('--btn-color');
$pgColor3 = wtkGetPost('--btn-hover');
$pgColor4 = wtkGetPost('--href-link');
$pgColor5 = wtkGetPost('--active-label');
$pgColor6 = wtkGetPost('--light-theme-focus');
$pgColor7 = wtkGetPost('--dark-theme-focus');
$pgColor8 = wtkGetPost('--bg-second-color');

$pgHtm =<<<htmVAR
/*
MIT License

Copyright 2021-2024 Wizard's Toolkit

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

----------------------------------------------------------------
This file was created using Wizard's Toolkit CSS Maker
https://WizardsToolkit.com
Wizard's Toolkit is a low-code development library for PHP, SQL and JavaScript

This file to be used with these files in this order:
materialize.min.css
{this file}
wtkLight.css or wtkDark.css
wtkGlobal.css
*/
:root {
    --gradient-left: $pgGradientLeft;
	--gradient-right: $pgGradientRight;
    --gradient-color: linear-gradient(to right, var(--gradient-left), var(--gradient-right));
    --btn-border-color: $pgColor1;
	--btn-color: $pgColor2;
    --btn-hover: $pgColor3;
    --href-link: $pgColor4;
	--active-label: $pgColor5;
    --light-theme-focus: $pgColor6;
	--dark-theme-focus: $pgColor7;
	--bg-second-color: $pgColor8;
}
htmVAR;

$pgCssName = 'wtk' . $pgFileName . '.css';
if (is_writable($pgCssName)):
    $pgJSON = '{"result":"fileExists"}';
else:
    $pgFile = fopen($pgCssName, 'w');
    if (fwrite($pgFile, $pgHtm) === false):
        $pgJSON = '{"result":"writeFailed"}';
    else:
        $pgJSON = '{"result":"ok"}';
    endif;
    fclose($pgFile);
endif;
echo $pgJSON;
exit; // no display needed, handled via JS and spa.htm
?>
