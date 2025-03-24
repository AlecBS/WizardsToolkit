<?php
/**
* This page is part of the Wizard's Toolkit
*
* This displays a waiting page while a page is converted into PDF.
* Once the PDF is generated this page displays the PDF.
*
* All rights reserved.
*
* This file is only usable by subscribers of the Wizard's Toolkit.  It may also
* be used while testing on localhost but not deployed to a production server until
* subscription is active.  You may not, except with our express written permission,
* distribute or commercially exploit the content.  Nor may you transmit it or store
* it in any other website or other form of electronic retrieval system.
*
* The above copyright notice and this permission notice shall be included
* in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
* OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
* IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
* CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
* TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
* SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
* @author      Programming Labs <support@programminglabs.com>
* @license     Copyright 2021-2025, All rights reserved.
* @link        Official page: https://wizardstoolkit.com
* @version     2.0
*/

$gloDbConnection = '';
$pgServerName = $_SERVER['SERVER_NAME'];
require('Utils.php');

$pgPDF = wtkGetPost('myPdf');
if ($pgPDF != ''):
    //Display PDF
    $pgPDF = urldecode($pgPDF);
    header('Content-Type: application/pdf');
    header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
    header('Pragma: public');
    header('Expires: Mon, 18 Oct 2021 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s').' GMT');
    header('Content-Length: ' . strlen($pgPDF));
    echo $pgPDF;
    exit;
endif;  // pgPDF != ''

$pgHtm =<<<htmVAR
<!doctype html>
<html><head><title>Printing</title>
<style>
.svg-loader{
  height: 20vmin;
  padding: 3vmin 20vmin;
  vertical-align: top;
}
h2 {
    color: #f4f4f4;
    font-family: 'Open Sans', sans-serif;
    font-weight: 600;
    margin: 2px 0px;
    padding: 0px;
    font-size: 25px;
    line-height: 40px;
}
</style>
</head>
<body bgcolor="#000000">
<div align="center">
<br><br><h2>Please wait... generating report</h2>
<svg version="1.1"
    class="svg-loader"
    xmlns="http://www.w3.org/2000/svg"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    x="0px"
    y="0px"
    viewBox="0 0 80 80"
    xml:space="preserve">
    <path
        fill="#51D466"
        d="M10,40c0,0,0-0.4,0-1.1c0-0.3,0-0.8,0-1.3c0-0.3,0-0.5,0-0.8c0-0.3,0.1-0.6,0.1-0.9c0.1-0.6,0.1-1.4,0.2-2.1
        c0.2-0.8,0.3-1.6,0.5-2.5c0.2-0.9,0.6-1.8,0.8-2.8c0.3-1,0.8-1.9,1.2-3c0.5-1,1.1-2,1.7-3.1c0.7-1,1.4-2.1,2.2-3.1
        c1.6-2.1,3.7-3.9,6-5.6c2.3-1.7,5-3,7.9-4.1c0.7-0.2,1.5-0.4,2.2-0.7c0.7-0.3,1.5-0.3,2.3-0.5c0.8-0.2,1.5-0.3,2.3-0.4l1.2-0.1
        l0.6-0.1l0.3,0l0.1,0l0.1,0l0,0c0.1,0-0.1,0,0.1,0c1.5,0,2.9-0.1,4.5,0.2c0.8,0.1,1.6,0.1,2.4,0.3c0.8,0.2,1.5,0.3,2.3,0.5
        c3,0.8,5.9,2,8.5,3.6c2.6,1.6,4.9,3.4,6.8,5.4c1,1,1.8,2.1,2.7,3.1c0.8,1.1,1.5,2.1,2.1,3.2c0.6,1.1,1.2,2.1,1.6,3.1
        c0.4,1,0.9,2,1.2,3c0.3,1,0.6,1.9,0.8,2.7c0.2,0.9,0.3,1.6,0.5,2.4c0.1,0.4,0.1,0.7,0.2,1c0,0.3,0.1,0.6,0.1,0.9
        c0.1,0.6,0.1,1,0.1,1.4C74,39.6,74,40,74,40c0.2,2.2-1.5,4.1-3.7,4.3s-4.1-1.5-4.3-3.7c0-0.1,0-0.2,0-0.3l0-0.4c0,0,0-0.3,0-0.9
        c0-0.3,0-0.7,0-1.1c0-0.2,0-0.5,0-0.7c0-0.2-0.1-0.5-0.1-0.8c-0.1-0.6-0.1-1.2-0.2-1.9c-0.1-0.7-0.3-1.4-0.4-2.2
        c-0.2-0.8-0.5-1.6-0.7-2.4c-0.3-0.8-0.7-1.7-1.1-2.6c-0.5-0.9-0.9-1.8-1.5-2.7c-0.6-0.9-1.2-1.8-1.9-2.7c-1.4-1.8-3.2-3.4-5.2-4.9
        c-2-1.5-4.4-2.7-6.9-3.6c-0.6-0.2-1.3-0.4-1.9-0.6c-0.7-0.2-1.3-0.3-1.9-0.4c-1.2-0.3-2.8-0.4-4.2-0.5l-2,0c-0.7,0-1.4,0.1-2.1,0.1
        c-0.7,0.1-1.4,0.1-2,0.3c-0.7,0.1-1.3,0.3-2,0.4c-2.6,0.7-5.2,1.7-7.5,3.1c-2.2,1.4-4.3,2.9-6,4.7c-0.9,0.8-1.6,1.8-2.4,2.7
        c-0.7,0.9-1.3,1.9-1.9,2.8c-0.5,1-1,1.9-1.4,2.8c-0.4,0.9-0.8,1.8-1,2.6c-0.3,0.9-0.5,1.6-0.7,2.4c-0.2,0.7-0.3,1.4-0.4,2.1
        c-0.1,0.3-0.1,0.6-0.2,0.9c0,0.3-0.1,0.6-0.1,0.8c0,0.5-0.1,0.9-0.1,1.3C10,39.6,10,40,10,40z"
    >
        <animateTransform
            attributeType="xml"
            attributeName="transform"
            type="rotate"
            from="0 40 40"
            to="360 40 40"
            dur="0.8s"
            repeatCount="indefinite"
        />
    </path>
    <path
        fill="#41AA52"
        d="M62,40.1c0,0,0,0.2-0.1,0.7c0,0.2,0,0.5-0.1,0.8c0,0.2,0,0.3,0,0.5c0,0.2-0.1,0.4-0.1,0.7
        c-0.1,0.5-0.2,1-0.3,1.6c-0.2,0.5-0.3,1.1-0.5,1.8c-0.2,0.6-0.5,1.3-0.7,1.9c-0.3,0.7-0.7,1.3-1,2.1c-0.4,0.7-0.9,1.4-1.4,2.1
        c-0.5,0.7-1.1,1.4-1.7,2c-1.2,1.3-2.7,2.5-4.4,3.6c-1.7,1-3.6,1.8-5.5,2.4c-2,0.5-4,0.7-6.2,0.7c-1.9-0.1-4.1-0.4-6-1.1
        c-1.9-0.7-3.7-1.5-5.2-2.6c-1.5-1.1-2.9-2.3-4-3.7c-0.6-0.6-1-1.4-1.5-2c-0.4-0.7-0.8-1.4-1.2-2c-0.3-0.7-0.6-1.3-0.8-2
        c-0.2-0.6-0.4-1.2-0.6-1.8c-0.1-0.6-0.3-1.1-0.4-1.6c-0.1-0.5-0.1-1-0.2-1.4c-0.1-0.9-0.1-1.5-0.1-2c0-0.5,0-0.7,0-0.7
        s0,0.2,0.1,0.7c0.1,0.5,0,1.1,0.2,2c0.1,0.4,0.2,0.9,0.3,1.4c0.1,0.5,0.3,1,0.5,1.6c0.2,0.6,0.4,1.1,0.7,1.8
        c0.3,0.6,0.6,1.2,0.9,1.9c0.4,0.6,0.8,1.3,1.2,1.9c0.5,0.6,1,1.3,1.6,1.8c1.1,1.2,2.5,2.3,4,3.2c1.5,0.9,3.2,1.6,5,2.1
        c1.8,0.5,3.6,0.6,5.6,0.6c1.8-0.1,3.7-0.4,5.4-1c1.7-0.6,3.3-1.4,4.7-2.4c1.4-1,2.6-2.1,3.6-3.3c0.5-0.6,0.9-1.2,1.3-1.8
        c0.4-0.6,0.7-1.2,1-1.8c0.3-0.6,0.6-1.2,0.8-1.8c0.2-0.6,0.4-1.1,0.5-1.7c0.1-0.5,0.2-1,0.3-1.5c0.1-0.4,0.1-0.8,0.1-1.2
        c0-0.2,0-0.4,0.1-0.5c0-0.2,0-0.4,0-0.5c0-0.3,0-0.6,0-0.8c0-0.5,0-0.7,0-0.7c0-1.1,0.9-2,2-2s2,0.9,2,2C62,40,62,40.1,62,40.1z"
    >
        <animateTransform
            attributeType="xml"
            attributeName="transform"
            type="rotate"
            from="0 40 40"
            to="-360 40 40"
            dur="0.6s"
            repeatCount="indefinite"
        />
    </path>
</svg>
</div>
htmVAR;
echo $pgHtm ;
ob_flush();
flush();
ob_clean();
$pgHeader = wtkGetPost('hdr');
$pgFooter = wtkGetPost('ftr');
$pgGray   = wtkGetParam('grayscale', 'TRUE');
if ($pgFooter == '@ftr@'):
    $pgFooter = '';
endif;  // $pgFooter == '@ftr@'
if ($pgFooter == 'none'):
    $pgFooter = '';
    $pgFooterLeft = '';
    $pgFooterRight = '';
    $pgFooterLine = 'FALSE';
else:
    $pgFooterLeft = 'printing by WizardsToolkit.com';
    $pgFooterRight = 'Page [page] of [toPage]';
    $pgFooterLine = 'TRUE';
endif;  // $pgFooter == 'none'
$pgCookieURL = wtkGetCookie('wtkPrint');
if ($pgCookieURL != ''):
    $pgURLtoPrint = $pgCookieURL;
    wtkDeleteCookie('wtkPrint');
else:
    $pgURLtoPrint = $_SERVER['HTTP_REFERER'];
endif;
$pgURLtoPrint = wtkReplace($pgURLtoPrint, '#','');
$pgPos = strpos($pgURLtoPrint, '?');
if ($pgPos === false):
    $pgURLtoPrint .= '?';
else:   // Not $pgPos === false
    $pgURLtoPrint .= '&';
endif;  // $pgPos === false
//$pgURLtoPrint .= 'Print=ON&Debug=Printing&UserUID=' . wtkGetGet('u');
$pgURLtoPrint .= 'Print=ON&UserUID=' . wtkGetParam('u');
/* ------------------------------------------------
  'HeaderCenter' => $pgHeader,
 --------------------------------------------------*/
//Configuration Settings
$pgPDFArray = array (
  'HeaderLeft' => '',
  'HeaderRight' => '',
  'HeaderCenter' => '',
  'HeaderLine' => 'FALSE', //TRUE or FALSE

  'FooterCenter' => $pgFooter,
  'FooterLeft' => $pgFooterLeft,
  'FooterRight' => $pgFooterRight,
  'FooterLine' => $pgFooterLine, //TRUE or FALSE

  //Margin unit mm (default value if not set 10 mm)
  //recommended Top/Bottom value for pages with header/Footer 2 cm (20 mm)
  'MarginTop' => '10',
  'MarginBottom' => '20',
  'MarginLeft' => '10',
  'MarginRight' => '10',

  'Grayscale' => $pgGray, //TRUE or FALSE
  'Orientation' => 'Portrait', //Landscape or Portrait
  'Copies' => '1', //number of copies of the report within the PDF
  'PageSize' => 'Letter', //Letter or A4
  'URL' => urlencode($pgURLtoPrint)
);
//End Configuration Settings.
//Convert Array to XML
$pgXmlToPost = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<PDF user=\"Test\" Password=\"Test\">\n";
foreach($pgPDFArray as $key => $value){
        $pgXmlToPost .= "\t<$key>$value</$key>\n";
}
$pgXmlToPost .= "</PDF>";
//End Convert Array to XML
$pgPDFserver = "http://pdf.programminglabs.com/pdfxml";
/* ------------------------------------------------
echo "<br><br>pgURLtoPrint = " . $pgURLtoPrint . "\n";
echo "<br><br>pgXmlToPost = " . $pgXmlToPost . "\n";
echo "<br><br>pgPDFserver = " . $pgPDFserver . "\n";
exit;
 --------------------------------------------------*/
//Curl Portion - Post XML to Server
$pgStringToPost = "XMLData=" . urlencode($pgXmlToPost);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $pgPDFserver);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $pgStringToPost);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$pgPDF = curl_exec ($ch);
curl_close ($ch);
//End Curl Portion

$pgPDF = urlencode($pgPDF);
$pgTmp =<<<htmVAR
<form action="?Mode=PDF" method="post" name="PrintForm" id="PrintForm">
<input type="hidden" name="myPdf" id="myPdf" value="$pgPDF"></form>
<script language="JavaScript" type="text/javascript">
  document.PrintForm.submit();
</script>
</body></html>
htmVAR;
echo $pgTmp ;
exit;
?>
