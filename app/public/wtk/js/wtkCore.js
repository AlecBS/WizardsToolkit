"use strict";
// this file previously named wtkLibrary.js; all MaterializeCSS-specific functions moved to wtkMaterialize.js

var pgHide = 'hide'; // set to 'hide' for MaterializeCSS, or 'hidden' for TailwindCSS
var pgMPAvsSPA = 'SPA'; // set to SPA or MPA
var pgApiKey = '';
let pgSecLevel = 0;
let pgHasPhoto = 'Y'; // Can be used to force user to add photo before using app
// For calls to reports.php
if (wtkParams.has('NP')) {
    let pgReport = wtkParams.get('NP');
    if (pgReport == 'Y') {
        pgApiKey = wtkParams.get('apiKey');
    }
}

var pgPageArray = [];
pgPageArray.push('0~0~logoutPage');
function wtkLoginForm(fncMenu = '', fncMPAvsSPA = 'SPA', fncWhichApp = ''){
    $('#LoginErrMsg').html('');
    wtkDisableBtn('btnLogin');
    let fncEmail = $('#myEmail').val();
    let fncPW = $('#myPassword').val();
    if (fncEmail == '') {
        wtkAlert('You must enter an email address.');
    } else {
        if (isValidEmail(fncEmail)) {
            if (fncPW == '') {
                wtkAlert('You must enter a password.');
            } else {
                waitLoad('on');
                let fncRemember = 'N';
                if (elementExist('rememberMe')) {
                    if (document.getElementById('rememberMe').checked) {
                        fncRemember = 'Y';
                    }
                }
                $.ajax({
                    type: 'POST',
                    url:  '/wtk/ajxLogin.php',
                    data: { em: fncEmail, pw: fncPW, rem: fncRemember, menu: fncMenu, app: fncWhichApp, AccessMethod: pgAccessMethod, MpaOrSpa: fncMPAvsSPA},
                    success: function(data) {
                        waitLoad('off');
                        let fncJSON = $.parseJSON(data);
                        if (fncJSON.result == 'success'){
                            $('body').removeClass('bg-second');
                            pgApiKey = fncJSON.apiKey;
                            gloMaxFileSize = fncJSON.maxFileUploadSize;
                            pgMPAvsSPA = fncMPAvsSPA;
                            wtkDebugLog('wtkLoginForm successful: pgMPAvsSPA: ' + pgMPAvsSPA);
                            if (fncMPAvsSPA == 'MPA') {
                                let fncGoToURL = $('#goToUrl').val();
                                if (fncGoToURL.includes('?')) {
                                    fncGoToURL = fncGoToURL + '&apiKey=' + pgApiKey;
                                } else {
                                    fncGoToURL = fncGoToURL + '?apiKey=' + pgApiKey;
                                }
                                window.location.replace(fncGoToURL); // redirect
                            } else {
                                // need to do next lines at Dashboard
                                pgPageArray.splice(0);
                                pgPageArray.push('0~0~dashboard');
                                $('#myName').text(fncJSON.myName);
                                pgSecLevel = fncJSON.secLevel;
                                wtkDebugLog('wtkLoginForm successful: pgSite: ' + pgSite + '; pgSecLevel: ' + pgSecLevel);
                                let fncPhoto = fncJSON.myPhoto;
                                if (fncPhoto != 'noPhoto') {
                                    $("#myPhoto").attr("src", fncPhoto);
                                }
                                if (pgAccessMethod == 'ios') {
                                    wtkDebugMobile('logged in; pgSite = ' + pgSite);
                                }
                                let fncWrongApp = 'N';
                                // if multiple apps on same server, use this for notifying login to wrong app
                                switch (pgSite) {
                                    case 'testing':
                                        if (pgSecLevel > 5) {
                                            fncWrongApp = 'Y';
                                        }
                                        break;
                                    case 'admin':
                                        pgHide = 'hide';
                                        if (pgSecLevel < 71) {
                                            fncWrongApp = 'Y';
                                            if (pgSecLevel < 26) {
                                                $('#appName').text('public');
                                            }
                                            // pgAlertUpdate = setInterval(function () {
                                            //     wtkCheckNotifications();
                                            // }, (3*60*1000));
                                        }
                                        break;
                                    case 'publicApp':
                                        /* If you want to prevent staff from accessing client site
                                        if (pgSecLevel > 29) {
                                            fncWrongApp = 'Y';
                                        } else {
                                            // If you want to force users to upload a photo
                                            if ((fncPhoto == 'noPhoto') && (pgForceUserPhoto == 'Y')) {
                                                pgHasPhoto = 'N';
                                                $('#hamburger').addClass(pgHide);
                                                $('#loginPage').addClass(pgHide);
                                                $('#backBtn').addClass(pgHide);
                                                pgPageArray.splice(0);
                                                pgPageArray.push('0~0~user');
                                                ajaxGo('user',0,0,'Y');
                                            }
                                        }
                                        */
                                        break;
                                } // switch pgSite
                                if (fncWrongApp == 'Y') {
                                    pgPageArray.push('0~0~wrongApp');
                                    pageTransition('loginPage', 'wrongApp');
                                } else {
                                    if ((fncPhoto != 'noPhoto') || (pgForceUserPhoto == 'N')) {
                                        pageTransition('loginPage', 'dashboard');
                                        getDashboardCounts();
                                    }
                                    $('#backBtn').addClass(pgHide);
                                    $('#myNavbar').removeClass(pgHide);
                                    $('#upgMsg').addClass(pgHide);
                                    $('#hamburger').removeClass(pgHide);
                            //          $('#slideOut').removeClass(pgHide);
                                    wtkFixSideNav();
                                    if (elementExist('FABbtn')){
                                        $('#FABbtn').removeClass(pgHide);
                                    }
                                    if (elementExist('sideBar')){
                                        $('#sideBar').removeClass(pgHide);
                                        ajaxFillDiv('/ajxFillSideBar','N','slide-out');
                                    }
                                    if (fncMenu != ''){
                                        ajaxFillDiv('menuRefresh',fncJSON.menu,'myNavbar');
                                    }
                                }
                            } // MPA vs SPA
                        } else {
                            $('#LoginErrMsg').html(fncJSON.result);
                            $('#LoginErrMsg').fadeIn(540);
                        }
                    }
                 })
             } // PW entered
        } else { // not valid email
          if (typeof pgLanguage !== 'undefined' && pgLanguage == 'esp') {
              wtkAlert("Tiene que ser una direccion de correo electronico valida.");
          } else {
              wtkAlert('<p class="center">Please enter a valid email address.</p>');
          }
        }
    } // email entered
} // wtkLoginForm

function wtkLogout(){
    if (pgAlertUpdate != 0) {
        clearInterval(pgAlertUpdate);
    }
    $('#backBtn').addClass(pgHide);
    $('#hamburger').addClass(pgHide);
    hidePriorPage();
    if (elementExist('sideBar')){
        $('#sideBar').addClass(pgHide);
    }
    if (elementExist('myNavbar')){
        $('#myNavbar').addClass(pgHide); // may or may not want to hide
    }
    $('#mainPage').addClass(pgHide);
    $('body').addClass('bg-second');
    $('#logoutPage').removeClass(pgHide);
    $.ajax({
        type: 'POST',
        url:  '/wtk/ajxLogout.php',
        data: { apiKey: pgApiKey },
        success: function(data) {
            wtkDebugLog('wtkLogout - new after');
            let fncJSON = $.parseJSON(data);
            if (fncJSON.result == 'ok'){
                pgApiKey = '';
                if (pgAccessMethod == 'ios') {
                    //              window.ReactNativeWebView.postMessage('logout');
                }
                if (elementExist('wrongApp')){
                    $('#wrongApp').addClass(pgHide);
                }
                if (elementExist('FABbtn')){
                    $('#FABbtn').addClass(pgHide);
                }
                $('body').addClass('bg-second');
                if (elementExist('pageWrapper')) {
                    $('#pageWrapper').addClass(pgHide);
                }
                const fncElement = document.getElementById('fullPage');

                if (fncElement) {
                    fncElement.scrollIntoView({ behavior: 'smooth' });
                    wtkDebugLog('wtkLogout - scroll to fullPage');
                }
                pgPageArray.splice(0); // Clear go-back array
                pgPageArray.push('0~0~logoutPage');
                wtkToggleShowPassword();
            } else {
                wtkAlert('Failed to logout - please contact tech support.');
            }
        }
    })
} // wtkLogout

function wtkRegister() {
    wtkDisableBtn('btnSignUp');
    let fncEmail = $('#wtkwtkUsersEmail').val();
    let fncPW = $('#wtkwtkUsersWebPassword').val();
    let fncRePW = $('#rePW').val();
    if (fncPW == '') {
        wtkAlert('Enter a password.');
    } else if (fncPW != fncRePW) {
        wtkAlert('The passwords you entered do not match.');
    } else if (fncEmail == '') {
        wtkAlert('You must enter an email address.');
    } else {
        if (isValidEmail(fncEmail)) {
            waitLoad('on');
            let fncFormData = $('#wtkRegisterForm').serialize();
            $.ajax({
                type: 'POST',
                url: '/wtk/ajxVerifyEmail.php',
                data: (fncFormData),
                success: function(data) {
                  let fncJSON = $.parseJSON(data);
                  if (fncJSON.result != 'ok'){
                      waitLoad('off');
                      wtkAlert(fncJSON.result);
                      $('#regForgot').removeClass(pgHide);
                  } else {
                      $('#registerPage').addClass(pgHide);
                      $('#mainPage').removeClass(pgHide);
                      let fncFName = $('#wtkwtkUsersFirstName').val();
                      $('#myName').text(fncFName);
                      wtkFixSideNav();  // fixScroll();
                      $.ajax({
                          type: 'POST',
                          url:  '/wtk/ajxRegister.php',
                          data: (fncFormData),
                          success: function(data) {
                              $('#mainPage').html(data);
                              $('body').removeClass('bg-second');
                              pgPageArray.push('0~0~newRegOK');
                              waitLoad('off');
                              $('#backBtn').addClass(pgHide);
                              $('#myNavbar').removeClass(pgHide);
                              $('#myPassword').val(fncPW);
                              pgApiKey = $('#regApiKey').val();
                          }
                      })
                  }
                }
            })
        } else {
            wtkAlert('<p class="center">Please enter a valid email address.</p>');
        }
    }
} // wtkRegister

// 2VERIFY next function now replaced by wtkRegister
function wtkCheckEmail(fncEmail, fncGoToURL) {
    $.getJSON('/wtk/ajxVerifyEmail.php?Email=' + fncEmail, function(data) {
        $.each(data, function(key, value) {
            if(value == 0){
                ajaxPost('wtkForm', fncGoToURL);
            } else {
                wtkAlert("Your email already exists in our database.  Did you forget your login information? ");
            }
        });
    });
} // wtkCheckEmail

function showSignIn(fncFrom) {
    pgPageArray.push('0~0~loginPage');
    wtkDebugLog('showSignIn called');
    pageTransition(fncFrom, 'loginPage');
    $('#backBtn').removeClass(pgHide);
    if (pgAccessMethod != 'website') {
        // ios, Android, pwa (phone)
        $('body').removeClass('bg-second');
        wtkDebugLog('showSignIn: pgAccessMethod = ' + pgAccessMethod);
    } else {
        wtkDebugLog('showSignIn 2: pgAccessMethod = ' + pgAccessMethod);
    }
} //showSignIn

function showBugReport() {
//    hidePriorPage();
//    $('#reportBug').removeClass(pgHide);
    if ($('#backBtn').hasClass(pgHide)) {
        $('#backBtn').removeClass(pgHide);
    }
    pageTransition('priorPage', 'reportBug');
    pgPageArray.push('0~0~reportBug');
} // showBugReport

function showForgotPW(fncFrom) {
    pgPageArray.push('0~0~forgotPW');
    pageTransition(fncFrom, 'forgotPW');
//  $('#' + fncFrom).addClass(pgHide);
//  $('#forgotPW').removeClass(pgHide);
}
function showRegister(fncFrom = 'loginPage') {
    pgPageArray.push('0~0~registerPage');
    pageTransition(fncFrom, 'registerPage');
} //showRegister

function showPage(fncPage, fncAddPageQ = 'Y') {
    let fncCurInfo = pgPageArray[pgPageArray.length - 1];
    let fncCurArray = fncCurInfo.split('~');
    let fncCurrent = fncCurArray[2];
    pageTransition(fncCurrent, fncPage);
    if (isCorePage(fncCurrent)) {
        $('#' + fncCurrent).addClass(pgHide);
    } else {
        $('#mainPage').addClass(pgHide);
    }
    if (fncAddPageQ == 'Y') {
        pgPageArray.push('0~0~' + fncPage);
        if ($('#backBtn').hasClass(pgHide)) {
            $('#backBtn').removeClass(pgHide)
        }
    }
    $('#' + fncPage).removeClass(pgHide)
} // showPage
function sendBug(){
    pgPageArray.push('0~0~mainPage');
    wtkDisableBtn('btnBugSave');
    let fncBugMsg = $("#bugMsg").val();
    $('#mainPage').html($('#bugSent').html());
    $('#reportBug').addClass(pgHide);
    $('#mainPage').removeClass(pgHide);
    $('#pageTitle').text('Message Sent');
    $("#bugMsg").val('');
    // 2FIX need to make navigation away from page work
    $.ajax({
        type: 'POST',
        url:  '/wtk/saveBug.php',
        data: { apiKey: pgApiKey, bugMsg: fncBugMsg },
        success: function(data) {
            // do nothing
        }
    })
} // sendBug

function sendNote(fncParentUID, fncVer) {
    wtkDebugLog('top of sendNote');
    // pass 2 for fncVer if have 2 sets of sendNote boxes on same page
    wtkDisableBtn('btnSendNote' + fncVer);
    let fncNote = $('#myNote' + fncVer).val();
//    $('#msgResult' + fncVer).html('<div class="chip green white-text">Message Sent</div>');
    $('#myNote' + fncVer).fadeOut(9);
    $('#btnSendNote' + fncVer).fadeOut(9);
    document.getElementById("btnSendNote" + fncVer).style.marginTop= "0px";
    setTimeout(function() {
        $('#msgResult' + fncVer).html('');
        $('#myNote' + fncVer).fadeIn(450);
        $('#btnSendNote' + fncVer).fadeIn(450);
        document.getElementById("btnSendNote" + fncVer).style.marginTop = "15px";
    }, 2700);
    $.ajax({
        type: 'POST',
        url:  '/wtk/sendNote.php',
        data: { apiKey: pgApiKey, id: fncParentUID, msg: fncNote },
        success: function(data) {
            wtkDebugLog('sendNote.php called');
            $('#myNote' + fncVer).val('');
            $('#noForum').addClass(pgHide);
            let fncMsgDiv = document.getElementById('forumDIV');
            fncMsgDiv.innerHTML += data;
        }
    })
} // sendNote

function sendInvite(fncId) {
    wtkDisableBtn('inviteUserBtn');
    $.ajax({
        type: 'POST',
        url: '/wtk/sendInvite.php',
        data: { apiKey: pgApiKey, techId: fncId},
        success: function(data) {
            ajaxGo('userList',1,0,'Y');
        }
    })
}

function saveChat(fncToUID) {
    let fncMessage = $('#wtkMsg').val();
    if (fncMessage != '') {
        $('#wtkMsg').val('');
        document.getElementById("btnSendNote").style.marginTop = "15px";
//        $('#wtkMsg').fadeOut(9);
//        document.getElementById("btnSendNote").style.marginTop= "0px";
//         setTimeout(function() {
//             $('#wtkMsg').fadeIn(450);
//         }, 3600);
        $.ajax({
            type: 'POST',
            url:  '/wtk/saveChat.php',
            data: { apiKey: pgApiKey, to: fncToUID, msg: fncMessage},
            success: function(data) {
                if (elementExist('noChat')){
                    $('#noChat').addClass(pgHide);
                }
                let fncMsgDiv = document.getElementById('chatDIV');
                fncMsgDiv.innerHTML += data + '<br>';
            }
        })
    } // empty message
} // saveChat

function saveSMSchoice() {
    let fncSMS = 'Y';
    if (document.getElementById('SMSEnabled').checked) {
        fncSMS = 'N';
    }
    $.ajax({
        type: 'POST',
        url:  '/wtk/saveSMSchoice.php',
        data: { apiKey: pgApiKey, sms: fncSMS },
        success: function(data) {
            // no response needed
        }
    })
}

function wtkForgotPW() {
    wtkDebugLog('wtkForgotPW: top');
    let fncEmail = $('#emailForgot').val();
    if (fncEmail == '') {
        wtkAlert('Enter a valid email address.');
    } else {
        if (isValidEmail(fncEmail)) {
            $('#forgotMsg').html('');
            wtkDisableBtn('btnResetPW');
            waitLoad('on');
            $.ajax({
                type: 'POST',
                url:  '/wtk/ajxLogin.php',
                data: { em: fncEmail, fpw: 'Y'},
                success: function(data) {
                    waitLoad('off');
                    let fncJSON = $.parseJSON(data);
                    if (fncJSON.result == 'success'){ // email/account exists
                        pageTransition('forgotPW','resetPWdiv');
    //                              $('#forgotPW').addClass(pgHide);
    //                              $('#resetPWdiv').removeClass(pgHide);
                    } else {
                        let fncTmp = fncJSON.result;
                        $('#forgotMsg').html(fncTmp);
                        $('#forgotMsg').fadeIn(540);
                    }
                }
              })
          } else {
              wtkDebugLog('wtkForgotPW: not valid email');
              wtkAlert('Please enter a valid email address.');
          }
    }
} // wtkForgotPW

function resetPW() {
    // called from passwordReset.php
    let fncPW = $('#wtkwtkUsersWebPassword').val();
    let fncRePW = $('#rePW').val();
    wtkDebugLog('resetPW ' + fncPW);
    if (fncPW == '') {
        wtkAlert('Enter a password.');
    } else if (fncPW != fncRePW) {
        wtkAlert('The passwords you entered do not match.');
    } else {
        let fncHash = $('#u').val();
        let fncId = $('#id').val();
        waitLoad('on');
        wtkDebugLog('resetPW just before AJAX call: ' + fncId);
        $.ajax({
          type: 'POST',
          url: '/wtk/ajxReset.php',
          data: { u: fncHash, id: fncId, pw: fncPW },
          success: function(data) {
              waitLoad('off');
              let fncJSON = $.parseJSON(data);
              if (fncJSON.result == 'ok'){
                  $('#resetForm').addClass(pgHide);
                  $('#finishedDIV').removeClass(pgHide);
              } else {
                  wtkAlert(fncJSON.result);
                  $('#resultMsg').html(fncJSON.result);
              }
          }
        })
    }
}

function goHome() {
//    hidePriorPage();
    pageTransition('priorPage', 'dashboard');
    pgPageArray.splice(0);
    pgPageArray.push('0~0~dashboard');
    $('#backBtn').addClass(pgHide);
//    $('#dashboard').removeClass(pgHide);
    if ($('#hamburger').hasClass(pgHide)) {
        $('#hamburger').removeClass(pgHide);
    }
    if ($('#slideOut').hasClass(pgHide)) {
        $('#slideOut').removeClass(pgHide);
    }
    wtkFixSideNav();
    getDashboardCounts();
} // goHome

function getDashboardCounts() {
    if ((pgSite == 'admin') || (pgWidgets == 'Y')) {
        if (pgSite == 'admin') {
            ajaxFillDiv('/wtk/widgets',0,'widgetDIV');
        } else {
            ajaxFillDiv('/wtk/widgets',1,'widgetDIV');
        }
    }
    if (pgAlertUpdate != 0) {
        wtkCheckNotifications();
        clearInterval(pgAlertUpdate);
        pgAlertUpdate = setInterval(function () {
            wtkCheckNotifications();
        }, (3*60*1000));
    }
} // getDashboardCounts

// START photo and ReactNative functions (obsolete from before WTKSDK)
var pgLastPhoto = '';
function takePhoto(fncTable, fncId) {
    pgLastPhoto = fncTable;
    $('#photoProgressDIV').removeClass(pgHide);
    if (pgDebug == 'Y') {
        setTimeout(function() {
            receiveMessageTST('photo-myphoto-8');
//          receiveMessageTST('cameraError-Camera permissions not granted');
        }, 2700);
    } else {
        if (pgAccessMethod == 'ios') {
            wtkDebugMobile('takePhoto: ' + fncTable + '; id = ' + fncId);
            window.ReactNativeWebView.postMessage('photo-' + fncTable + '-' + fncId);
        }
    }
} // takePhoto

function wtkDialPhone(fncPhone) {
    wtkDebugMobile('fncPhone = ' + fncPhone);
    if (pgAccessMethod == 'ios') {
        let fncCount = fncPhone.length;
        if (fncCount == 10) { // this fixes USA issue with some area codes
            fncPhone = '+1' + fncPhone.toString();
        }
        wtkSDK.makePhoneCall(fncPhone);
//      window.ReactNativeWebView.postMessage('dialPhone-' + fncPhone );
    } else {
        window.location.href = 'tel:' + fncPhone;
    }
    wtkDebugMobile('wtkDialPhone after dialPhone');
}

const isUIWebView = () => {
    return navigator.userAgent.toLowerCase().match(/\(.*applewebkit(?!.*(version|crios))/)
};

const receiver = isUIWebView() ? window : document;

receiver.addEventListener('message', data => {
    receiveMessage(data);
});

function receiveMessage(e) {
    let messageArray = e.data.split("-");
    let fncVar1 = messageArray[0];
    let fncVar2 = messageArray[1];
    let fncVar3 = messageArray[2];
    wtkDebugMobile('receiveMessage: ' + fncVar1 + '-' + fncVar2 + '-' + fncVar3);
    handleMessage(messageArray);
};

function receiveMessageTST(e) { // Debug Testing
//    wtkAlert('inside receiveMessageTST');
    wtkDebugLog('receiveMessageTST called');
    wtkDebugLog(e);
    let messageArray = e.split("-");
//    wtkAlert('inside receiveMessageTST for: ' + messageArray[0]);
    handleMessage(messageArray);
}; // receiveMessageTST

function handleMessage(messageArray) {
    switch(messageArray[0]){
        case 'photo':
            let fncPath = messageArray[1];
            let fncFileName = messageArray[2];
            wtkDebugMobile('handleMessage: fncPath = ' + fncPath + '; fncFileName = ' + fncFileName);
            if (elementExist('photoProgressDIV')){
                $('#photoProgressDIV').addClass(pgHide);
            }
            if (elementExist('imgPreview')){
                $("#imgPreview").attr("src", fncPath + fncFileName);
            }
            // below example code for other ways this could be used
            // if (pgLastPhoto == 'SomePhotoList') {
            //     let fncStrName = fncFileName.toString();
            //     let fncHtm = '<div class="col s4"><div class="contents"><a class="waves-effect waves-light" href="/imgs/my/' + fncStrName + '" data-lightbox="gallery1"><img src="/imgs/my/' + fncStrName + '"></a></div></div>';
            //     document.getElementById("photoRowDIV").innerHTML += fncHtm;
            //     wtkFixSideNav(); // fixScroll();
            // }
            // if (pgHasPhoto == 'N') {
            //     pgHasPhoto = 'Y';
            //     $('#hamburger').removeClass(pgHide);
            //     $('#noPhotoDIV').addClass(pgHide);
            // }
            break;
        case 'cameraError':
            let fncMsg = messageArray[1];
            wtkAlert('Camera Error: ' + fncMsg);
            fixScroll();
            // not doing break because should do what is in cameraCancel
        case 'cameraCancel':
            wtkDebugMobile('cameraCancel called');
            $('#photoProgressDIV').addClass(pgHide);
            /*
            if (pgLastPhoto == 'user') {
                $('#userCard').removeClass(pgHide);
                $('#addPhotoPage').addClass(pgHide);
                $('#photoUpload').addClass(pgHide);
            }
            $('#mainPhoto').removeClass(pgHide);
            */
            break;
    } // switch
}; // handleMessage
// END photo and ReactNative functions

var pgMainPage = '';
var pgLoadWhenReady = 'N'; // in case ajax result is slower than animation
function pageTransition(fncFrom, fncTo, fncPage = ''){
    wtkDebugLog('pageTransition Top: fncFrom = ' + fncFrom + '; fncTo = ' + fncTo + '; fncPage = ' + fncPage + '; pgUseTransition =' + pgUseTransition);
    if (fncFrom == 'priorPage') {
        let fncCurInfo = pgPageArray[pgPageArray.length - 1];
        let fncCurArray = fncCurInfo.split('~');
        let fncCurrent = fncCurArray[2];
        if (isCorePage(fncCurrent)) {
            fncFrom = fncCurrent;
        } else {
            fncFrom = 'mainPage';
        }
        wtkDebugLog('pageTransition: fncCurrent = ' + fncCurrent + '; fncFrom = ' + fncFrom);
    }
    if ((fncFrom == 'dashboard') && (fncTo == 'dashboard')){
        fncFrom = 'mainPage';
    }
    if (fncTo == 'dashboard'){
        const fncTopOfPage = document.getElementById('myNavbar');
        if (fncTopOfPage) {
            setTimeout(function () {
                fncTopOfPage.scrollIntoView({ behavior: 'smooth' });
                wtkDebugLog('pageTransition: scroll to myNavbar');
            }, 360);
        }
    }
    if ((fncFrom == 'mainPage') && (fncTo == 'mainPage')){
        pgMainPage = '... loading ...';
        wtkDebugLog('pageTransition: pgMainPage = ' + pgMainPage);
    } else {
        pgMainPage = '';
    }
    if (pgUseTransition == 'Y') {
//      $('#pageTitle').html('&nbsp;');
        animateCSS('#' + fncFrom, pgTransitionOut).then((message) => {
          // Do something after the animation
            $('#' + fncFrom).addClass(pgHide);
            $('#' + fncTo).removeClass(pgHide);
            wtkDebugLog('animateCSS Out finished: Hide ' + fncFrom + '; Show ' + fncTo + '; fncPage = ' + fncPage);
            if ((fncTo == 'mainPage') && (pgMainPage != '')){
                $('#mainPage').html(pgMainPage);
                if (pgMainPage == '... loading ...') {
                    pgLoadWhenReady = 'Y'; // response from server not ready yet
                } else {
                    afterPageLoad(fncPage);
                }
            }
            animateCSS('#' + fncTo, pgTransitionIn).then((message) => {
                wtkDebugLog('pageTransition: animateCSS In finished');
//              $('#navCol1').removeClass(pgHide);
//              $('#pageTitle').text('YourCompanyName');
//              $('#navCol3').removeClass(pgHide);
            });
        });
    } else {
        pgLoadWhenReady = 'Y'; // response from server not ready yet
        $('#' + fncFrom).addClass(pgHide);
        if (fncTo == 'mainPage') {
            $('#mainPage').html('');
        }
        $('#' + fncTo).removeClass(pgHide);
        wtkDebugLog('pageTransition: bottom removed ' + pgHide + ' for ' + fncTo);
    }
} // pageTransition

const animateCSS = (element, animation, prefix = 'animate__') =>
  // We create a Promise and return it
  new Promise((resolve, reject) => {
    const animationName = `${prefix}${animation}`;
    const node = document.querySelector(element);

    node.classList.add(`${prefix}animated`, 'animate__faster');
    node.classList.add(`${prefix}animated`, animationName);

    // When the animation ends, we clean the classes and resolve the Promise
    function handleAnimationEnd() {
      node.classList.remove(`${prefix}animated`, animationName);
      node.classList.remove(`${prefix}animated`, 'animate__faster');
      resolve('Animation ended');
    }

    node.addEventListener('animationend', handleAnimationEnd, {once: true});
});

function hidePriorPage() {
    let fncCurInfo = pgPageArray[pgPageArray.length - 1];
    let fncCurArray = fncCurInfo.split('~');
    let fncCurrent = fncCurArray[2];
    if (isCorePage(fncCurrent)) {
        $('#' + fncCurrent).addClass(pgHide);
    } else {
        $('#mainPage').addClass(pgHide);
    }
} // hidePriorPage

function searchPage(fncPage) {
    let fncSearch = $('#search').val();
    ajaxGo(fncPage, fncSearch, 0, 'N');
}
function ajaxGo(fncPage, fncId=0, fncRNG=0, fncAddPageQ='Y', fncFrom='') {
    // BEGIN remove any TinyMCE so can re-initialize on other pages or return to this page
    if (elementExist('HasTinyMCE')){
        let fncHasTinyMCE = $('#HasTinyMCE').val();
        wtkDebugLog('ajaxGo: HasTinyMCE going to tinymce.remove');
        if (fncHasTinyMCE != '') {
            tinymce.remove(fncHasTinyMCE);
            $('#HasTinyMCE').val('');
        }
    }
    //  END  remove any TinyMCE so can re-initialize on other pages or return to this page
    if (elementExist('HasTooltip')){
        wtkRemoveToolTips();
    }
    let fncCurInfo = pgPageArray[pgPageArray.length - 1];
    let fncCurArray = fncCurInfo.split('~');
    let fncCurrent = fncCurArray[2];
    if (fncAddPageQ == 'Y') {
        if (isCorePage(fncCurrent)) {
            pageTransition(fncCurrent, 'mainPage', fncPage);
        } else {
            pageTransition('mainPage', 'mainPage', fncPage);
        }
        pgPageArray.push(fncId + '~' + fncRNG + '~' + fncPage);
        wtkDebugLog('ajaxGo: pushed to pgPageArray fncPage = ' + fncPage + ' ; fncId = ' + fncId + '; fncRNG = ' + fncRNG);
        if ($('#backBtn').hasClass(pgHide)) {
            $('#backBtn').removeClass(pgHide)
        }
    } else {
        wtkDebugLog('ajaxGo: not pushing to pgPageArray for fncPage ' + fncPage );
    }
    let fncPath = gloFilePath[fncPage]; // gloFilePath defined in wtkPaths.js
    if (fncPath == undefined) {
        fncPath = '';
    }
    let fncExt  = '.php';
    if (pgProtoType == 'Y') {
        fncPath = 'htm/';
        fncExt  = '.htm';
    }
    switch(fncPage) {
        case 'addPage': // this trick can make passign 0 have page be in ADD mode
            break;
        case 'takePhoto':
            // can do special code here
            break;
    } //switch
    waitLoad('on');
    wtkDebugLog('ajaxGo: fncPath = ' + fncPath + '; fncPage = ' + fncPage + ' ; fncExt = ' + fncExt);
    $.ajax({
        type: 'POST',
        url:  fncPath + fncPage + fncExt,
        data: { apiKey: pgApiKey, id: fncId, rng: fncRNG, from: fncFrom },
        success: function(data) {
            wtkFixSideNav();
            if (pgLoadWhenReady == 'Y') {
                $('#mainPage').html(data);
                afterPageLoad(fncPage);
            } else {
                pgMainPage = data; // now managed in pageTransition
            }
            waitLoad('off');
            /*
            let fncPageTitle = $('#myTitle').val();
            if (fncPageTitle != '') {
                $('#pageTitle').text(fncPageTitle);
            }
            */
        }
    })
} // ajaxGo

function wtkRequiredFieldsFilled(fncFormName) {
    wtkDebugLog('wtkRequiredFieldsFilled: fncFormName = ' + fncFormName);
    var fncFieldArray = document.getElementById(fncFormName).elements;
    var fncOK = true;
    for (var i = 0; i < fncFieldArray.length; i++){
        if (fncFieldArray[i].required){
            wtkDebugLog('wtkRequiredFieldsFilled: required field: ' + fncFieldArray[i].id + '; type: ' + fncFieldArray[i].type);
            let fncValue = document.getElementById(fncFieldArray[i].id).value;
            if ((fncFieldArray[i].type == 'textarea') && (fncValue == '<br>')) {
                fncOK = false; // summernote adds this for blank textareas
            } else if (fncFieldArray[i].type == 'file') {  // Check if a file is selected
               if (fncFieldArray[i].files.length === 0) {
                   fncOK = false;
               }
            } else {
                if (!fncFieldArray[i].value || fncFieldArray[i].value == "") {
                    fncOK = false;
                }
            }
            if (fncOK == false) {
                let fncLabel = $('label[for="' + fncFieldArray[i].id + '"]').html();
                if (fncLabel == '') {
                    fncLabel = fncFieldArray[i].name;
                }
                wtkAlert(fncLabel + ' is a required field','Required Information','red','warning',fncFieldArray[i].id);
                break;
            }
        }
    }
    return fncOK;
} // wtkRequiredFieldsFilled

function ajaxPost(fncPage, fncPost, fncAddPageQ='Y') {
    wtkDebugLog('ajaxPost: fncPage = ' + fncPage + '; fncPost = ' + fncPost + '; fncAddPageQ = ' + fncAddPageQ);
    let fncResult = wtkPrepFormPost(fncPost);
    let fncReady = fncResult.ready;
    let fncContentType = fncResult.contentType;
    let fncEncType = fncResult.enctype;
    if (fncReady == 'Y') {
        wtkDisableBtn('btnSave');
        if (fncAddPageQ == 'Y') {
            if (fncPage == '/wtk/lib/Save') {
                if (elementExist('wtkGoToURL') && (pgMPAvsSPA == 'SPA')) {
                    let fncGoTo = $('#wtkGoToURL').val();
                    fncGoTo = fncGoTo.replace('.php','');
                    let fncRNG = 0;
                    if (elementExist('rng')) {
                        fncRNG = $('#rng').val();
                    }
                    pgPageArray.push('p~' + fncRNG + '~' + fncGoTo);
                    wtkDebugLog('ajaxPost: pushing to pgPageArray p~' + fncRNG + '~' + fncGoTo);
                }
            } else {
                pgPageArray.push('p~0~' + fncPage);
                wtkDebugLog('ajaxPost: pushing to pgPageArray ' + fncPage);
            }
        } else {
            wtkDebugLog('ajaxPost: not pushing to pgPageArray ' + fncPage);
        }
        let fncFormData = '';
        if (pgMPAvsSPA == 'MPA') {
            wtkDebugLog('ajaxPost: pgMPAvsSPA = MPA');
            fncFormData = document.getElementById(fncPost);
            fncFormData.setAttribute('method', 'post');
            fncFormData.setAttribute('action', fncPage + '.php');
            let fncFormMPA = new FormData(fncFormData);
            // Append the apiKey to the FormData
            fncFormMPA.append('apiKey', pgApiKey);
            fncFormData.submit();
        } else {
            if (fncContentType == false) {
                fncFormData = new FormData($('#' + fncPost)[0]);
                fncFormData.append('apiKey', pgApiKey);
            } else {
                fncFormData = $('#' + fncPost).serialize();
                fncFormData = fncFormData + '&apiKey=' + pgApiKey ;
            }
            waitLoad('on');
            $.ajax({
                method: 'POST',
                type: 'POST',
                url:  fncPage + '.php',
                cache: false,
                contentType: fncContentType,
                enctype: fncEncType,
                processData: false,
                data: (fncFormData),
                success: function(data) {
                    waitLoad('off');
                    if (elementInFormExist(fncPost,'HasTinyMCE')) { // because page may have more than one form
                        wtkDebugLog('ajaxPost: HasTinyMCE going to tinymce.remove');
                        let fncHasTinyMCE = $('#HasTinyMCE').val();
                        tinymce.remove(fncHasTinyMCE);
                        $('#HasTinyMCE').val('');
                    }
                    if (data == 'goHome') {
                        goHome();
                    } else {
                        if (pgUseTransition == 'Y') {
                            animateCSS('#mainPage', pgTransitionOut).then((message) => {
                              // Do something after the animation
                                $('#mainPage').html(data);
                                $('#mainPage').removeClass(pgHide);
                                pgFileToUpload = 'N';
                                afterPageLoad(fncPage);
                                animateCSS('#mainPage', pgTransitionIn).then((message) => {
                    //              $('#pageTitle').text('YourCompanyName');
                                });
                            });
                        } else {
                            $('#mainPage').html(data);
                            $('#mainPage').removeClass(pgHide);
                            pgFileToUpload = 'N';
                //          $('#hamburger').removeClass(pgHide);
                            wtkFixSideNav();
                //          wtkFixSideNav(); // if you add this, it comes from right side
                            afterPageLoad(fncPage);
                        }
                    }
                }
            })
        } // SPA
    } // wtkRequiredFieldsFilled(fncPost)
} // ajaxPost

function ajaxCopy(fncPage, fncPost) {
    // based on ajaxPost but copies data and remains on screen
    wtkDebugLog('ajaxCopy: fncPage = ' + fncPage + '; fncPost = ' + fncPost);
    let fncFormData = $('#' + fncPost).serialize();
    fncFormData = fncFormData + '&apiKey=' + pgApiKey ;
    waitLoad('on');
    $.ajax({
        type: 'POST',
        url:  fncPage + '.php',
        data: (fncFormData),
        success: function(data) {
            waitLoad('off');
            $('#wtkMode').val('ADD');
            $('#ID1').val(0);
            $('#copyBtn').text('Add & Repeat');
            M.toast({html: 'Your data has been saved. Ready to modify your copy?', classes: 'rounded'});
            wtkFixSideNav();
//          afterPageLoad(fncPage);
        }
    })
} // ajaxCopy

var pgLastDashboard = 'widgTD1';
function ajaxFillDiv(fncPage, fncParam, fncDiv, fncRNG = 0) {
    wtkDebugLog('ajaxFillDiv top for fncPage: ' + fncPage + ' to ' + fncDiv);
    if (elementExist('HasTinyMCE')){
        let fncHasTinyMCE = $('#HasTinyMCE').val();
        wtkDebugLog('ajaxFillDiv: HasTinyMCE going to tinymce.remove');
        tinymce.remove(fncHasTinyMCE);
        $('#HasTinyMCE').val('');
    }
    $.ajax({
        type: 'POST',
        url:  fncPage + '.php',
        data: { apiKey: pgApiKey, p: fncParam, rng: fncRNG },
        success: function(data) {
            if ((fncPage == '/wtk/widgets') && (fncDiv != 'nonDashWidgetsDIV')) {
                $('#mainPage').text('... loading ...'); // to prevent conflicts with widgets
            }
            $('#' + fncDiv).html(data);
            switch (fncPage) {
                case '/wtk/widgets':
                    if (elementExist('HasTooltip') && (pgHide == 'hide')){
                        $('.tooltipped').tooltip();
                    }
                    if (elementExist('myDashBtn')) {
                        if (fncParam == 1) {
                            $('#myDashBtn').removeClass(pgHide);
                        } else {
                            $('#myDashBtn').addClass(pgHide);
                        }
                    }
                    $(pgLastDashboard).removeClass('widget-header');
                    pgLastDashboard = '#widgTD' + fncParam;
                    $(pgLastDashboard).addClass('widget-header');
                    waitLoad('off');
                    break;
                case 'menuRefresh':
                    $(document).ready(function(){
                        $(".dropdown-trigger").dropdown(); // this will fail if jquery is after materialize JS
                        let fncElem = document.querySelectorAll('.collapsible');
                        let fncTmp = M.Collapsible.init(fncElem);
                        wtkFixSideNav();
                    });
                    break;
                case '/ajxFillSideBar':
                    let fncElem = document.querySelectorAll('.collapsible');
                    let fncTmp = M.Collapsible.init(fncElem);
                    wtkDebugLog('ajaxFillDiv collapsible called')
                    break;
                default:
                    afterPageLoad(fncPage);
            } // switch
        }
    })
} // ajaxFillDiv

// Below Functions are more core related

function wtkGoBack() {
    if (pgMPAvsSPA == 'MPA') {
        window.history.back();
    } else {
        if (elementExist('HasTinyMCE')){
            let fncHasTinyMCE = $('#HasTinyMCE').val();
            wtkDebugLog('wtkGoBack: HasTinyMCE going to tinymce.remove');
            tinymce.remove(fncHasTinyMCE);
            $('#HasTinyMCE').val('');
        }
        let fncPriorInfo = pgPageArray.pop();
        let fncPriorArray = fncPriorInfo.split('~');
        let fncPriorPage = fncPriorArray[2];
        if (isCorePage(fncPriorPage)) {
            $('#' + fncPriorPage).addClass(pgHide);
        } else {
    //        $('#mainPage').addClass(pgHide);
            fncPriorPage = 'mainPage';
        }
        let fncCurInfo = pgPageArray[pgPageArray.length - 1];
        let fncCurArray = fncCurInfo.split('~');
        let fncCurrent = fncCurArray[2];
        wtkDebugLog('wtkGoBack hiding ' + fncPriorPage + ' and showing ' + fncCurrent + '; fncCurInfo = ' + fncCurInfo + '; pgPageArray.length = ' + pgPageArray.length);
        if (pgPageArray.length == 1) {
            $('#backBtn').addClass(pgHide);
        }
        if (isCorePage(fncCurrent)) {
            pageTransition(fncPriorPage,fncCurrent);
            wtkDebugLog('wtkGoBack isCorePage');
            if (fncCurrent == 'dashboard') {
    //          goHome();
                pgPageArray.splice(0);
                pgPageArray.push('0~0~dashboard');
                $('#backBtn').addClass(pgHide);
                if ($('#hamburger').hasClass(pgHide)) {
                    $('#hamburger').removeClass(pgHide);
                }
                if ($('#slideOut').hasClass(pgHide)) {
                    $('#slideOut').removeClass(pgHide);
                }
                wtkFixSideNav();
                getDashboardCounts();
            }
        } else {
            wtkDebugLog('wtkGoBack NOT isCorePage, calling ajaxGo');
    //        $('#mainPage').html('... loading ...');
    //        $('#mainPage').removeClass(pgHide);
            pageTransition(fncPriorPage, 'mainPage');
            if (fncCurrent == '../reports') {
                fncCurrent = '/wtk/reports'
            } else {
                wtkDebugLog('wtkGoBack fncCurrent before set with gloFilePath: ' + fncCurrent);
                let fncPath = gloFilePath[fncCurrent]; // gloFilePath defined in wtkPaths.js
                if (fncPath == undefined) {
                    fncPath = '';
                }
                fncCurrent = fncPath + fncCurrent;
                wtkDebugLog('wtkGoBack adjusted for Path: ' + fncPath + ' for ' + fncCurrent);
            }
            ajaxGo(fncCurrent, fncCurArray[0], fncCurArray[1], 'N');
        }
    } // SPA
} //wtkGoBack

function showBackBtn() {
    // currently not used but may be needed; 2VERIFY
    if (pgPageArray.length > 1) {
        $('#backBtn').removeClass(pgHide);
    }
}

function showDiv(fncDiv) {
    pageTransition('priorPage', fncDiv);
    pgPageArray.push('0~0~' + fncDiv);
}
function ajxFilterEmailTemplate(){
    let fncEmailType = $('#wtkEmailType').val();
    let fncEmailDept = $('#wtkEmailDept').val();
    $.ajax({
        type: 'POST',
        url:  '/wtk/ajxFilterEmailTemplate.php',
        data: { apiKey: pgApiKey, Type: fncEmailType, Dept: fncEmailDept },
        success: function(data) {
            $('#wtkEmailTmps').html(data);
            let fncSelElem = document.getElementById('wtkEmailTmps');
            let fncTmp = M.FormSelect.init(fncSelElem);
        }
    })
}

function wtkSendEmail(fncModalId = '', fncURL = '/wtk/ajxSendEmail', fncFormName = 'emailForm', fncEmailBtn = 'emailBtn'){
    let fncEmail = '';
    if (elementInFormExist(fncFormName, 'email')) {
        fncEmail = $('#email').val();
    } else {
        fncEmail = $('#ToEmail').val();
    }
    if (fncEmail == '') {
        wtkAlert('You must enter an email address.');
    } else {
        if (isValidEmail(fncEmail)) {
            let fncAjaxURL = fncURL + '.php';
            wtkDisableBtn(fncEmailBtn);
            let fncFormData = $('#' + fncFormName).serialize();
            fncFormData = fncFormData + '&formName=' + fncFormName ;
            $.ajax({
                type: 'POST',
                url: fncAjaxURL,
                data: (fncFormData),
                success: function(data) {
                    let fncJSON = $.parseJSON(data);
                    if (fncJSON.result == 'OK'){
                        wtkToastMsg('Your message has been sent.', 'green');
                        if (elementExist('thanksMsg')) {
                            $('#thanksMsg').removeClass(pgHide);
                        }
                        if (fncModalId == '') {
                            $('#' + fncFormName).addClass(pgHide);
                        } else {
                            let fncId = document.getElementById(fncModalId);
                            let fncModal = M.Modal.getInstance(fncId);
                            fncModal.close();
                        }
                    } else {
                        wtkToastMsg('Email failed to send"', 'red');
                    }
                }
            })
        }else{ // not valid email
            wtkAlert("Please enter a valid email address.");
        }
    } // email entered
} // wtkSendEmail

function sendMail(fncCloseModal = 'Y') {
    let fncName = $('#name').val();
    let fncEmail = $('#email').val();
    let fncMsg = $('#msg').val();
    if (fncEmail == '') {
        wtkAlert('Please enter your email so we can reply to your question.');
    } else {
        if (fncMsg == '') {
            wtkAlert('Enter a message before Sending');
        } else {
            if (isValidEmail(fncEmail)) {
                $.ajax({
                    type: 'POST',
                    url:  '/wtk/sendEmail.php',
                    data: { name: fncName, email: fncEmail, msg: fncMsg },
                    success: function(data) {
                        wtkAlert('Thank you, we will respond to your email soon.','Message Sent', 'blue', 'email');
                        if (fncCloseModal == 'Y') {
                            wtkCloseModal('contactDiv');
                        } else {
                            $('#regForm').addClass(pgHide);
                            $('#thanksMsg').removeClass(pgHide);
                        }
                    }
                })
            } else {
                if (typeof pgLanguage !== 'undefined' && pgLanguage == 'esp') {
                    wtkAlert('Email tiene que ser una direccion de correo electronico valida.');
                } else {
                    wtkAlert('<p class="center">Please enter a valid email address.</p>');
                }
            }
        }
    }
} // sendMail

function wtkModalSendEmail(){
    wtkDisableBtn('sendEmailBtn');
    if (elementExist('HasModalTinyMCE')){
        var fncHasTinyMCE = $('#HasModalTinyMCE').val();
        let fncTextArea = fncHasTinyMCE.replace('textarea#','');
        let fncNewValue = tinymce.get(fncTextArea).getContent();
        $('#' + fncTextArea).val(fncNewValue);
    }
    let fncToEmail = $('#ToEmail').val();
    let fncEmailMsg = $('#EmailMsg').val();
    if (fncEmailMsg == ''){
        wtkAlert('No email message entered');
    } else {
        let fncFormData = $('#emailForm').serialize();
        fncFormData = fncFormData + '&apiKey=' + pgApiKey ;
        $.ajax({
            type: 'POST',
            url:  '/wtk/ajxModalEmail.php',
            data: (fncFormData),
            success: function(data) {
                wtkToastMsg('Email sent','green');
                wtkCloseModal('modalWTK');
                if (elementExist('wtkEmailsSentDIV') == true) {
                    if ($('#T').val() == '96xh5r45') {
                        ajaxFillDiv('/wtk/ajxEmailList', 'UserUID', 'wtkEmailsSentDIV', $('#ID1').val());
                    } else {
                        ajaxFillDiv('/wtk/ajxEmailList', 'OtherUID', 'wtkEmailsSentDIV', $('#ID1').val());
                    }
                }
            }
        })
    }
} // wtkModalSendEmail

function wtkSendSMS(fncId){
    wtkDisableBtn('cancelSmsBtn');
    let fncPhone = $('#smsPhone').val();
    let fncSmsMsg = $('#smsMsg').val();
    if (fncSmsMsg == ''){
        wtkAlert('No message entered');
    } else {
        $.ajax({
            type: 'POST',
            url:  '/wtk/ajxSendSMS.php',
            data: { apiKey: pgApiKey, id: fncId, SmsPhone: fncPhone, SmsMsg: fncSmsMsg},
            success: function(data) {
                wtkToastMsg('SMS message sent','green');
            }
        })
    }
} // wtkSendSMS

/* Begin: Notification related functions */
function wtkShowNotificationAdvanced(){
    if ($('#futureDateDIV').hasClass(pgHide)){
        $('#futureDateDIV').removeClass(pgHide)
        wtkChangeRequired('wtkwtkNotificationsStartDate',true);
    } else {
        $('#futureDateDIV').addClass(pgHide)
        $('#wtkwtkNotificationsStartDate').val('');
        $('#wtkwtkNotificationsRepeatFrequency1').prop('checked',true);
        wtkChangeRequired('wtkwtkNotificationsStartDate',false);
    }
}
function wtkNotificationAudience(fncValue){
    ajaxFillDiv('/wtk/ajxSelAudience', fncValue, 'pickToUID');
    if (fncValue == 'S') {
        $('#AltDelivery').removeClass(pgHide);
    } else {
        $('#AltDelivery').addClass(pgHide);
    }
}
function wtkGoToNotification(fncId,fncGoToUrl,fncGoToId,fncGoToRng){
    $('#alertId' + fncId).addClass(pgHide);
    let fncAlertCount = roundToPrecision($('#alertCounter').text());
    if (fncAlertCount == 1) {
        $('#alertCounter').addClass(pgHide);
        $('#alertCounter').text(0);
    } else {
        fncAlertCount = (fncAlertCount - 1);
        if (fncAlertCount >= 0) {
            $('#alertCounter').text(fncAlertCount);
            if ($('#alertCounter').hasClass(pgHide)) {
                $('#alertCounter').removeClass(pgHide);
            }
        }
    }
    if (fncGoToUrl == '@GoToUrl@') {
//        wtkModal('/wtk/ajxNotificationView', 'show', fncId);
        wtkModal('/wtk/notificationClose', 'show', fncId);
    } else {
        ajaxGo(fncGoToUrl,fncGoToId,fncGoToRng);
        $.ajax({
            type: 'POST',
            url:  '/wtk/ajxNotificationSave.php',
            data: { apiKey: pgApiKey, Mode: 'Seen', id: fncId },
            success: function(data) {
    //          M.toast({html: 'Alert updated', classes: 'rounded green'});
            }
        })
    }
} // wtkGoToNotification
/* End: Notification related functions */

function wtkEditHelp(){
    $('#editHelp').removeClass(pgHide);
    $('#editHelpBtn').addClass(pgHide);
    $('#saveHelpBtn').removeClass(pgHide);
}

function wtkShowHelp(fncId) {
    wtkModal('/wtk/ajxGetHelp','help',fncId,0,'bg-second');
}
function wtkSaveHelp(fncId) {
    let fncTitle = $('#wtkwtkHelpHelpTitle').val();
    let fncText = $('#wtkwtkHelpHelpText').val();
    let fncVideo = $('#wtkwtkHelpVideoLink').val();
    $.ajax({
        type: 'POST',
        url: '/wtk/ajxSaveHelp.php',
        data: { apiKey: pgApiKey, id: fncId, title: fncTitle, vid: fncVideo, text: fncText },
        success: function(data) {
            wtkToastMsg('The help data has been saved.', 'green');
        }
    })
} // wtkSaveHelp

var pgClearBottomModal = true;

function wtkModalUpdate(fncPage, fncId=0, fncRNG=0) {
    waitLoad('on');
    $.ajax({
        type: 'POST',
        url:  fncPage + '.php',
        data: { apiKey: pgApiKey, id: fncId, rng: fncRNG },
        success: function(data) {
            if (pgHide == 'hidden'){ // TailwindCSS
                $('#modalContent').html(data);
            } else {
                $('#modalWTK').html(data);
            }
            waitLoad('off');
            afterPageLoad('modalWTK');
        }
    })
}

function wtkPrepFormPost(fncFormName) {
    // BEGIN if TinyMCE is used, copy into original textarea form fields
    // 2ENHANCE currently will only work for 1 textarea per form on a page
    if (elementInFormExist(fncFormName,'HasModalTinyMCE')){
        let fncHasTinyMCE = $('#HasModalTinyMCE').val();
        let fncTextArea = fncHasTinyMCE.replace('textarea#','');
        let fncNewValue = tinymce.get(fncTextArea).getContent();
        $('#' + fncTextArea).val(fncNewValue);
    }
    if (elementInFormExist(fncFormName,'HasTinyMCE')){
        let fnc2HasTinyMCE = $('#HasTinyMCE').val();
        let fnc2TextArea = fnc2HasTinyMCE.replace('textarea#','');
        let fnc2NewValue = tinymce.get(fnc2TextArea).getContent();
        $('#' + fnc2TextArea).val(fnc2NewValue);
        wtkDebugLog('wtkPrepFormPost: HasTinyMCE  = ' + fnc2HasTinyMCE + '; fnc2NewValue = ' + fnc2NewValue);
    }
    //  END  if TinyMCE is used, copy into original textarea form fields
    let fncReady = 'N';
    let fncContentType = false; // should stay false if file upload
    let fncEncType = 'application/x-www-form-urlencoded';

    if ((pgFileToUpload == 'Y') && (pgFileSizeOK == 'N')) {
        wtkAlert('File is too large to upload<br>Maximum size allowed is ' + formatBytes(gloMaxFileSize) + '<br>File will not be uploaded.');
    } else { // not trying to upload a file that is too large
        if (wtkRequiredFieldsFilled(fncFormName)) {
            fncReady = 'Y';
        }
    }
    if (fncReady == 'Y') {
        let fncWtkMode = 'ADD';
        if (elementInFormExist(fncFormName, 'wtkMode')) { // because page may have more than one
            fncWtkMode = wtkGetValue('wtkMode',fncFormName);
        }
        if (pgAccessMethod == 'ios') {
            wtkUploadFile($('#ID1').val());
        } else {
            if (elementInFormExist(fncFormName,'wtkUploadFiles')) { // check form for upload files
                if (fncWtkMode == 'ADD') {
                    fncEncType = 'multipart/form-data';
                } else { // set fncContentType as below because file upload handled in separate post for EDIT
                    // because Edit, file(s) uploaded in other threads and other saves handled here
                    fncContentType = 'application/x-www-form-urlencoded; charset=UTF-8';
                    if ((pgFileToUpload == 'Y') && (pgFileSizeOK == 'Y')) {
                        // upload files in separate threads
                        wtkDebugLog('wtkPrepFormPost: going to upload files');
                        if (elementInFormExist(fncFormName,'FileUploaded')) {
                            $('#FileUploaded').val('Y');
                        }
                        let fncFileIDs = wtkGetValue('wtkUploadFiles',fncFormName);
                        let fncFileUpArray = fncFileIDs.split(',');
                        for (let i = 0; i < fncFileUpArray.length; i++){
                            wtkfUploadFile(fncFileUpArray[i]);
                        }
                    } else {
                        wtkDebugLog('wtkPrepFormPost: not going to upload files; pgFileToUpload: ' + pgFileToUpload + '; pgFileSizeOK: ' + pgFileSizeOK);
                    }
                }
            } else { // not uploading forms
                fncContentType = 'application/x-www-form-urlencoded; charset=UTF-8';
            } // not uploading forms
        } // pgAccessMethod != 'ios'
    }
    // Return an object with multiple values
    return { ready: fncReady, contentType: fncContentType, enctype: fncEncType };
} // wtkPrepFormPost

function modalSave(fncPage, fncDiv, fncClose = 'N', fncAppend = 'N') {
    wtkDebugLog('modalSave: fncDiv = ' + fncDiv + '; pgFileToUpload = ' + pgFileToUpload + '; pgFileSizeOK = ' + pgFileSizeOK);

    let fncResult = wtkPrepFormPost('F' + fncDiv);
    let fncReady = fncResult.ready;
    let fncContentType = fncResult.contentType;
    let fncEncType = fncResult.enctype;
    wtkDebugLog('modalSave: fncReady = ' + fncReady + '; fncContentType = ' + fncContentType + '; fncEncType = ' + fncEncType);

    if (fncReady == 'Y') {
        let fncFormData = '';
        if (fncContentType == false) {
            wtkDebugLog('modalSave fncContentType == false');
            fncFormData = new FormData($('#F' + fncDiv)[0]);
            fncFormData.append('apiKey', pgApiKey);
            fncFormData.append('append', fncAppend);
        } else {
            wtkDebugLog('modalSave fncContentType = ' + fncContentType);
            fncFormData = $('#F' + fncDiv).serialize();
            fncFormData = fncFormData + '&apiKey=' + pgApiKey ;
            fncFormData = fncFormData + '&append=' + fncAppend ;
        }
//      wtkDebugLog('modalSave fncFormData');
//      wtkDebugLog(fncFormData);
        waitLoad('on');
        $.ajax({
            method: 'POST',
            type: 'POST',
            url:  fncPage + '.php',
            cache: false,
            contentType: fncContentType,
            enctype: fncEncType,
            processData: false,
            data: (fncFormData),
            success: function(data) {
                waitLoad('off');
                pgFileToUpload = 'N';
                if (fncDiv != '') {
                    if (fncDiv == 'widgetRefresh') {
                        let fncWidgetUID = $('#WidgetUID').val();
                        $.ajax({
                            type: 'POST',
                            url: '/wtk/widgets.php',
                            data: { apiKey: pgApiKey, wuid: fncWidgetUID },
                            success: function(data) {
                                $('#widget' + fncWidgetUID + 'DIV').html(data);
                            }
                        })
                    } else {
                        if (fncAppend == 'Y') {
                            document.getElementById('wtkModalList').innerHTML += data;
                        } else {
                            if (fncPage == 'yourSpecialPage') {
                                alert('your function goes here'); // your custom function
                            } else {
                                if (fncDiv == 'mainPage') {
                                    pageTransition('priorPage', fncDiv);
                                }
                                if (elementExist(fncDiv) == false) { // div does not exist
                                    wtkDebugLog('modalSave fncDiv (' + fncDiv + ') does not exist');
                                } else {
                                    $('#' + fncDiv).html(data);
                                    wtkDebugLog('modalSave filled fncDiv');
                                }
                            }
                            afterPageLoad(fncDiv);
                        }
                    }
                }
                if (fncClose == 'Y') {
                    pgClearBottomModal = false;
                    wtkCloseModal('modalWTK');
                    wtkToastMsg('Your data has been saved.', 'green');
                }
                wtkFixSideNav();
            }
        })
    } // fncResult == 'Y'
} // modalSave

function modalSaveDoc(fncPage, fncDiv) {
    waitLoad('on');
//    let fncFormData = $('#F' + fncDiv).serialize();
    // Get form
    let fncFormData = $('#F' + fncDiv)[0];
    // Create an FormData object
    let fncData = new FormData(fncFormData);
    //  fncFormData = fncFormData + '&apiKey=' + pgApiKey ;
    $.ajax({
        type: 'POST',
        enctype: 'multipart/form-data',
        url:  fncPage + '.php',
        data: fncData,
        processData: false,
        contentType: false,
        cache: false,
        timeout: 800000,
        success: function(data) {
            waitLoad('off');
            $('#' + fncDiv).html(data);
            wtkCloseModal('modalWTK');
            wtkFixSideNav();
        }
    })
} // modalSaveDoc

function rpt(fncId, fncFilter = '') {
    waitLoad('on');
    let fncYN = 'Y';
    if (fncFilter == '') {
        fncYN = 'N';
    }
    $.ajax({
        type: 'POST',
        url:  '/wtk/reports.php',
        data: { apiKey: pgApiKey, rng: fncId, PgIx: 1, RptFilter: fncFilter, Filter: fncYN },
        success: function(data) {
            waitLoad('off');
            $('#rptSpan').html(data);
            wtkFixSideNav();
            afterPageLoad('reports');
        }
    })
} // rpt

function rptFilter() {
    let fncFormData = $('#rptForm').serialize();
    wtkDebugLog('rptFilter: 2 fncFormData = ' + fncFormData);
    waitLoad('on');
    $.ajax({
        type: 'POST',
        url:  '/wtk/reports.php',
        data: (fncFormData),
        success: function(data) {
            waitLoad('off');
            $('#rptSpanFltr').html(data);
            wtkFixSideNav();
            // fixScroll();
            afterPageLoad('reports');
        }
    })
} // rptFilter

// BEGIN Browse Box Functions
function wtkBrowseBox(fncURL, fncTableID, fncRNG, fncPgIx, fncOB, fncSRT) {
    $.ajax({
        type: 'POST',
        url:  fncURL + '.php',
        data: { apiKey: pgApiKey, TableID: fncTableID, rng: fncRNG, PgIx: fncPgIx, OB: fncOB, SRT: fncSRT, AJAX: 'Y'},
        success: function(data) {
            let updatedTable = $(data);
            let oldTable = $('#' + fncTableID);
            oldTable.replaceWith(updatedTable);
            wtkTableSetup();
            const fncElement = document.getElementById(fncTableID);
            if (fncElement) {
                fncElement.scrollIntoView({ behavior: 'smooth' });
                wtkDebugLog('wtkBrowseBox scrollIntoView ' + fncTableID);
            }
        }
    })
};

function wtkBrowseFilter(fncURL, fncTableID = '', fncFormID = 'wtkFilterForm') {
    if (fncTableID == '') {
        fncTableID = fncURL;
    }
    let fncFormData = $('#' + fncFormID).serialize();
    $.ajax({
        type: 'POST',
        url: fncURL + '.php',
        data: (fncFormData + '&AJAX=Y&apiKey=' + pgApiKey + '&TableID=' + fncTableID),
        success: function(data) {
            let updatedTable = $(data);
            let oldTable = $('#' + fncTableID);
            oldTable.replaceWith(updatedTable);
            $('#filterReset').removeClass(pgHide);
            wtkTableSetup();
        }
    })
}

function wtkBrowseReset(fncURL, fncTableID = '', fncRNG=0) {
    if (fncTableID == '') {
        fncTableID = fncURL;
    }
    if (elementExist('wtkFilter')){
        let fncType = document.getElementById('wtkFilter').type;
        if (fncType == 'checkbox') {
            document.getElementById('wtkFilter').checked = false;
        } else {
            $('#wtkFilter').val('');
        }
    }
    if (elementExist('wtkFilter2')){
        let fncType2 = document.getElementById('wtkFilter2').type;
        if (fncType2 == 'checkbox') {
            document.getElementById('wtkFilter2').checked = false;
        } else {
            $('#wtkFilter2').val('');
        }
    }
    if (elementExist('wtkFilter3')){
        let fncType3 = document.getElementById('wtkFilter3').type;
        if (fncType3 == 'checkbox') {
            document.getElementById('wtkFilter3').checked = false;
        } else {
            $('#wtkFilter3').val('');
        }
    }
    $('#filterReset').addClass(pgHide);
    $.ajax({
        type: 'POST',
        url:  fncURL + '.php',
        data: { apiKey: pgApiKey, Reset: 'Y', TableID: fncTableID, rng: fncRNG, AJAX: 'Y' },
        success: function(data) {
            let updatedTable = $(data);
            let oldTable = $('#' + fncTableID);
            oldTable.replaceWith(updatedTable);
            wtkTableSetup();
        }
    })
} // wtkBrowseReset

function wtkDel(fncTbl, fncId, fncDelDate, fncDesign = 'SPA', fncConfirm = 'N') {
    // use when browse does not have totals and therefore does not need refresh
    if (fncDesign == '') {
        fncDesign = pgMPAvsSPA;
    }
    let fncOK = false;
    if ((fncConfirm == 'Y') || (fncConfirm == true)) {
        fncOK = confirm('Are you certain you want to delete?');
    } else {
        fncOK = true;
    }
    if (fncOK == true) {
        $('#D' + fncTbl + fncId).addClass(pgHide);
        let fncTableRow = document.getElementById('D' + fncTbl + fncId);
        fncTableRow.style.display = 'none';
        $.ajax({
            type: 'POST',
            url:  '/wtk/ajxDelete.php',
            data: { apiKey: pgApiKey, tbl: fncTbl, id: fncId, date: fncDelDate, wtkDesign: fncDesign},
            success: function(data) { }
        })
    }
} // wtkDel
//  END  Browse Box Functions

function wtkDeleteRefresh(fncTbl,fncId,fncRNG) {
    // requires the browse's TableID is same as the SQL table name
    // assumes AJAX PHP file to refresh list will be named: 'ajx' + table name + 'List.php'
    let fncURL = 'ajx' + fncTbl + 'List.php';
    $.ajax({
        type: 'POST',
        url:  '/wtk/ajxDelete.php',
        data: { apiKey: pgApiKey, tbl: fncTbl, id: fncId, date: 'N', wtkDesign: 'SPA'},
        success: function(data) {
            $.ajax({
                type: 'POST',
                url:  fncURL,
                data: { apiKey: pgApiKey, rng: fncRNG},
                success: function(data) {
                    let updatedTable = $(data);
                    let oldTable = $('#' + fncTbl);
                    oldTable.replaceWith(updatedTable);
                    wtkTableSetup();
                }
            })
        }
    })
}

function wtkMakePageList() {
    // This creates JS file which stores paths to PHP files
    $.getJSON('/wtk/ajxMakePageList.php?apiKey=' + pgApiKey, function(data) {
        gloFilePath.splice(0); // empty array
        $.each(data, function(key, value) {
            gloFilePath[key] = value;
        });
        wtkToastMsg('Website paths and files updated','green')
    });
} // wtkMakePageList

function wtkPayPal(fncPayPalItem, fncAmt, fncPage, fncCurCode='USD', fncDivs = '', fncAffiliateId = 0) {
    window.paypalLoadScript({
         "client-id": gloPayPalClientId,
         "currency": fncCurCode
        }).then((paypal) => {
            paypal.Buttons({
                createOrder: function(data, actions) {
                  wtkDebugLog('wtkPayPal createOrder');
                  wtkDebugLog(fncPayPalItem);
                  return actions.order.create(fncPayPalItem);
                },
            onError: function (err) {
                wtkDebugLog('Error during purchase:');
                wtkDebugLog(err);
                wtkToastMsg('Error during purchase - please contact tech support', 'red');
            },
            onCancel: function (data) {
                // Show a cancel page, or return to cart
                wtkDebugLog('Canceled order!');
                wtkToastMsg('Your order has been canceled', 'orange');
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    cliPayPal(fncPage);
                    let fncOrderUID = details.id;
                    let fncEmail = details.payer.email_address;
                    let fncPayerId = details.payer.payer_id;
                    let fncFirstName = details.payer.name.given_name;
                    let fncLastName = details.payer.name.surname;
                    let fncStatus = details.status;
//  let fncAmt = details.purchase_units.amount.value; // this fails
                    $.ajax({
                        type: 'POST',
                        url:  '/wtk/payPalSave.php',
                        data: { apiKey: pgApiKey, OrderUID: fncOrderUID, PayeeEmail: fncEmail,
                            PayerId: fncPayerId, FirstName: fncFirstName, LastName: fncLastName,
                            Status: fncStatus, Amount: fncAmt, CurrencyCode: fncCurCode,
                            AffiliateUID: fncAffiliateId },
                        success: function(data) {
                            wtkDebugLog('success on PayPal Save');
                            if (elementExist('paypal-thanks' + fncDivs)){
                                $('#paypal-buttons' + fncDivs).addClass(pgHide);
                                $('#paypal-thanks' + fncDivs).removeClass(pgHide);
                            } else {
                                $('#paypal-buttons' + fncDivs).html('<h1>Thank You!</h1>');
                            }
                            if (elementExist('payBtn' + fncDivs)){
                                $('#payBtn' + fncDivs).text('Close');
                            }
                        }
                    })
                });
            }
        }).render('#paypal-buttons' + fncDivs);
    });
} // wtkPayPal

// Print functions
function wtkPrint(fncUID) {
//  document.PrintForm.setAttribute('target', 'printWindow');
    let outWidth  = (screen.width * .75);
    let outHeight = (screen.height * .75);
    let outLeft = (screen.width * .14);
    let fncOutWin = 'height='+outHeight+',width='+outWidth+',left='+outLeft+'top=5,fullscreen=0,hotkeys=1,location=1,menubar=1,resizable=1,scrollbars=1,status=1,titlebar=1,toolbar=1,z-lock=0';
    $('#PrintForm').append('<input type="hidden" name="u" value="' + fncUID + '">');
    window.open('', 'printWindow', fncOutWin);
    document.PrintForm.target = 'printWindow';
    document.PrintForm.submit();
}
function wtkSubmitToPrint(fncURL) {    // obsolete: old method used for Exports
    let outWidth  = (screen.width * .75);
    let outHeight = (screen.height * .75);
    let outLeft = (screen.width * .14);
    let fncOutWin = 'height='+outHeight+',width='+outWidth+',left='+outLeft+'top=5,fullscreen=0,hotkeys=1,location=1,menubar=1,resizable=1,scrollbars=1,status=1,titlebar=1,toolbar=1,z-lock=0';
    window.open(fncURL, "printWindow", fncOutWin);
}

function wtkCheckNotifications() {
    $.ajax({
        type: 'POST',
        url:  '/wtk/ajxNotificationList.php',
        data: { apiKey: pgApiKey, p: 1 },
        success: function(data) {
            $('#wtkNotificationList').html(data);
        }
    })
}

function wtkRemoveTinyMCE() {
    if (elementExist('HasTinyMCE')) {
        let fncHasTinyMCE = $('#HasTinyMCE').val();
        tinymce.remove(fncHasTinyMCE);
        $('#HasTinyMCE').val('');
        wtkDebugLog('wtkRemoveTinyMCE called for ' + fncHasTinyMCE);
    }
}

function wtkRemoveModalTinyMCE() {
    if (elementExist('HasModalTinyMCE')) {
        let fncHasTinyMCE = $('#HasModalTinyMCE').val();
        tinymce.remove(fncHasTinyMCE);
        $('#HasModalTinyMCE').val('');
        if (elementExist('modalBottom')) {
            if (pgClearBottomModal == true) {
                $('#modalBottom').text('');
            }
        }
        wtkDebugLog('wtkRemoveModalTinyMCE called for ' + fncHasTinyMCE);
    }
} // wtkRemoveModalTinyMCE

function toDo(fncFrom) {
    wtkAlert(fncFrom + ' - Alec needs to code this...');
}

function test(fncShowPage) {
    if (fncShowPage == 'wait') {
        waitLoad('on');
    } else {
//        $('#myNavbar').removeClass(pgHide);
        pgPageArray.push('0~0~' + fncShowPage);
        $('#logoutPage').addClass(pgHide);
        if (isCorePage(fncShowPage)) {
            $('#' + fncShowPage).removeClass(pgHide);
        } else {
            $('#mainPage').html('... loading ...');
            $('#mainPage').removeClass(pgHide);
            ajaxGo(fncShowPage,0,0,'Y');
        }
        $('#hamburger').removeClass(pgHide);
        $('#slideOut').removeClass(pgHide);
        wtkFixSideNav();
    }
}
