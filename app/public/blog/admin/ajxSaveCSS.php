<?PHP
function wtkGetPost($fncPostVariable, $fncDefault = '') {
    $fncResult = isset($_POST[$fncPostVariable]) ? $_POST[$fncPostVariable] : '';
    if ($fncResult == ''):
        $fncResult = $fncDefault;
    endif;  // $fncResult == ''
    return $fncResult ;
} // end of wtkGetPost

$pgFileName = 'blog.css';
$pgFont = wtkGetPost('blogFont');
$pgGradientTop = wtkGetPost('--gradient-top');
$pgGradientBottom = wtkGetPost('--gradient-bottom');
$pgHeader = wtkGetPost('--wtk-blog-header');
$pgNav = wtkGetPost('--wtk-blog-nav');
$pgMain = wtkGetPost('--wtk-blog-main');
$pgFooter = wtkGetPost('--wtk-blog-footer');

$pgHtm =<<<htmVAR
/*
MIT License

Copyright 2023 Wizard's Toolkit

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
This file was created using Wizard's Toolkit Blog Designer
https://WizardsToolkit.com
Wizard's Toolkit is a low-code development library for PHP, SQL and JavaScript
*/
:root {
    --gradient-top: $pgGradientTop;
	--gradient-bottom: $pgGradientBottom;
    --gradient-color: linear-gradient(to bottom, var(--gradient-top), var(--gradient-bottom));
	--wtk-blog-header: $pgHeader;
	--wtk-blog-nav: $pgNav;
    --wtk-blog-main: $pgMain;
    --wtk-blog-footer: $pgFooter;
}

html, body {
  height: 100%;
  min-height: 100vh;
}
body {
    font-family: '$pgFont';
    background-color: var(--wtk-blog-nav); /* because nav height does not consistently work in all browsers */
}

header, footer {
    padding: 9px 18px;
    display: block;
    width: 100%;
}
header {
    background-color: var(--wtk-blog-header);
}
nav, main {
    margin: 0px;
    border: 0px;
}
nav {
    min-height: 110vh;
    width: 30%;
    padding: 0px 18px;
    background-color: var(--wtk-blog-nav) !important;
    vertical-align: top;
    display: inline-block;
    box-shadow: inherit;
}
main {
    min-height: 100% !important;
    width: 70%;
    padding: 0px 30px;
    background-color: var(--wtk-blog-main) !important;
    box-sizing: border-box !important;
    float: right;
}
footer {
    background: var(--wtk-blog-footer);
    color: #efedf0;
    position: fixed;
    height: 50px;
    top: calc( 100vh - 50px );
}
.w72 {
    width: 72px !important;
}
.nav-date {
    margin-top: -22px;
    margin-left: 18px;
    font-size: smaller;
}
/* Summernote styles for images */
img.note-float-left{margin-right:10px}
img.note-float-right{margin-left:10px}
htmVAR;

$pgCssName = '../files/blog.css';
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
