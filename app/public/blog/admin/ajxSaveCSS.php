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

Copyright 2025 Wizard's Toolkit

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
header a {
	color: var(--wtk-blog-footer);
}
nav, main {
    margin: 0px;
    border: 0px;
}
nav {
	background-color: initial;
	box-shadow: initial;
	-webkit-box-shadow: initial;
}
nav ul a {
	font-weight: bold;
	color: var(--wtk-blog-footer);
}
/*
nav {
    min-height: 110vh;
    width: 30%;
    padding: 0px 18px;
    background-color: var(--wtk-blog-nav) !important;
    color: var(--wtk-blog-footer) !important;
    vertical-align: top;
    display: inline-block;
    box-shadow: inherit;
}
nav a {
	color: var(--wtk-blog-footer) !important;
}
*/

footer {
    background: var(--wtk-blog-footer);
    color: #efedf0;
    position: fixed;
    height: 50px;
    top: calc( 100vh - 50px );
}
footer a {
	color: #efedf0;
}
h1 { font-size: 2.5rem; }
h2 { font-size: 2rem; }
h3 { font-size: 1.75rem; }
h4 { font-size: 1.5rem; }
h5 { font-size: 1.3rem; }
h6 { font-size: 1.1rem; }
.brand-logo {
    max-height: 54px;
	padding-left: 18px;
    margin-top: 9px;
    position: relative;
    z-index: 1;
}
.blog-link {
	color: var(--wtk-blog-footer) !important;
}
.blog-link:hover {
	text-decoration: underline;
}
.blog-main {
    min-height: 100% !important;
    padding: 0px 30px;
    background-color: var(--wtk-blog-main) !important;
    box-sizing: border-box !important;
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
