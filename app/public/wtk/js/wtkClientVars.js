var gloPayPalClientId = 'enter-your-PayPal-SandBox-or-Live-ClientId';
var gloStripePublicKey = 'pk_test_YourStripeKey';
var gloWtkApiKey = '~369+success+BIG!winning+WTK+hope+Today~432~';
// above put in your PayPal Sandbox or Live Client ID
var pgLanguage = 'en'; // this is default language of English
var pgWidgets  = 'Y';  // determines whether widgets are used
var pgAlertUpdate = 0;
var pgProtoType = 'N'; // This can be manually changed to Y to use .htm prototype pages instead of .php pages
var pgShowWarning = 'N';
var pgForceUserPhoto = 'N';

// here set in bytes the maximum file size allowed to upload to web server
var gloMaxFileSize = 4718592; // currently 4.5MB
// here you can change order of wtkPieSliceColors or use other colors from wtkColors.js
var wtkPieSliceColors = [wtkColor.blue,wtkColor.red,wtkColor.yellow,wtkColor.green,wtkColor.cyan,wtkColor.purple,wtkColor.orange,wtkColor.black,
wtkColor.burlywood,wtkColor.blueviolet,wtkColor.darkturquoise,wtkColor.goldenrod,wtkColor.aquamarine,wtkColor.indigo,wtkColor.pink,
wtkColor.springgreen,wtkColor.wheat,wtkColor.thistle,wtkColor.teal,wtkColor.lemonchiffon,wtkColor.lightsalmon,wtkColor.lavenderblush];

var gloLangArray = new Array();
// used by wtkLangUpdate JS function in wtkUtils.js when language preference is changed
// add more here for your specific text to change in your main.htm file
gloLangArray['Email'] = 3;
gloLangArray['PW'] = 2;
gloLangArray['CreateAcct'] = 2;
gloLangArray['ForgotPW'] = 2;
gloLangArray['Close'] = 2;

// Add names of DIV here that you have in main.htm for static page viewing
function isCorePage(fncPage) {
    switch (fncPage) {
        case 'logoutPage':
        case 'registerPage':
        case 'forgotPW':
        case 'resetPWdiv':
        case 'loginPage':
        case 'dashboard':
        case 'reportBug':
        case 'bugSent':
        case 'wrongApp':
        case 'staticPage1':
            return true;
            break;
        default:
            return false;
    }
} // isCorePage

var pgUseTransition = 'N';
// see https://animate.style/ for options
var pgTransitionIn  = 'fadeInLeft';  // rotateIn, fadeInLeft, lightSpeedInLeft, slideInLeft
var pgTransitionOut = 'fadeOutRight'; // rotateOut, fadeOutRight, lightSpeedOutRight, slideOutRight
// Remove below IF when go to production / done with debugging
if (wtkParams.has('Animate')) {
    pgUseTransition = wtkParams.get('Animate');
}
// Below variables should be set via hidden HTML fields instead of here
// pgDebug defined in wtkUtils.js
if (wtkParams.has('Debug')) {
    pgDebug = wtkParams.get('Debug');
}
var pgSite = 'browser';
var pgAccessMethod = 'website';

// pgTextBar used for SummerNote
var pgTextBar = [
  ['style', ['style']],
  ['font', ['bold', 'underline', 'clear']],
  ['color', ['color']],
  ['para', ['ul', 'ol', 'paragraph']],
  ['table', ['table']],
  ['insert', ['link', 'video']],
  ['view', ['codeview', 'help']]
];

function cliPayPal(fncPage) {
    switch (fncPage) {
        case 'license':
            let fncDomain = $('#domain').val();
            let fncPaid = $('#amount').val();
            /*
// this is just an example of custom code you could write; this is called by wtkLibrary.js
            $.ajax({
                type: "POST",
                url:  '/saveLicense.php',
                data: { apiKey: pgApiKey, domain: fncDomain, paid: fncPaid},
                    success: function(data) {
                        $('#licenseForm').addClass('hide');
                    }
            })
            */
            break;
        default:
            wtkDebugLog('cliPayPal - fncPage not recognized: ' + fncPage);
    }
} // cliPayPal
