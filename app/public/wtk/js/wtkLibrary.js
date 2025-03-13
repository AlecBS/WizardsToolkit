"use strict";
/* site-specific functions */
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

function showBugReport() {
//    hidePriorPage();
//    $('#reportBug').removeClass('hide');
    if ($('#backBtn').hasClass('hide')) {
        $('#backBtn').removeClass('hide');
    }
    pageTransition('priorPage', 'reportBug');
    pgPageArray.push('0~0~reportBug');
} // showBugReport

function sendBug(){
    pgPageArray.push('0~0~mainPage');
    wtkDisableBtn('btnBugSave');
    let fncBugMsg = $("#bugMsg").val();
    $('#mainPage').html($('#bugSent').html());
    $('#reportBug').addClass('hide');
    $('#mainPage').removeClass('hide');
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

function showBugReportModal() {
    if (pgLastModal != 'reportBug') {
        $('#modalWTK').html($('#reportBug').html());
        $('#reportBug').html('purged to prevent dupe ID');
        pgLastModal = 'reportBug';
    }
    let fncModalId = document.getElementById('modalWTK');
    let fncModal = M.Modal.getInstance(fncModalId);
    fncModal.open();
    afterPageLoad('modal');
}

function sendBugModal(){
    let fncBugMsg = $("#bugMsg").val();
    $("#bugMsg").val('');
    $.ajax({
        type: 'POST',
        url:  '/wtk/saveBug.php',
        data: { apiKey: pgApiKey, bugMsg: fncBugMsg },
        success: function(data) {
            $('#reportBug').html($('#modalWTK').html());
            $('#modalWTK').html('');
            pgLastModal = '';
        }
    })
    let fncId = document.getElementById('modalWTK');
    let fncModal = M.Modal.getInstance(fncId);
    fncModal.close();
    M.toast({html: 'Your message has been sent.', classes: 'green rounded'});
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
                    $('#noChat').addClass('hide');
                }
                let fncMsgDiv = document.getElementById('chatDIV');
                fncMsgDiv.innerHTML += data + '<br>';
            }
        })
    } // empty message
} // saveChat

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
            $('#noForum').addClass('hide');
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
                            pgMPAvsSPA = fncMPAvsSPA;
                            wtkDebugLog('wtkLoginForm successful: pgMPAvsSPA: ' + pgMPAvsSPA);
                            if (fncMPAvsSPA == 'MPA') {
                                let fncGoToURL = $('#goToUrl').val();
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
                                                $('#hamburger').addClass('hide');
                                                $('#loginPage').addClass('hide');
                                                $('#backBtn').addClass('hide');
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
                                    $('#backBtn').addClass('hide');
                                    $('#myNavbar').removeClass('hide');
                                    $('#upgMsg').addClass('hide');
                                    $('#hamburger').removeClass('hide');
                            //          $('#slideOut').removeClass('hide');
                                    wtkFixSideNav();
                                    if (elementExist('FABbtn')){
                                        $('#FABbtn').removeClass('hide');
                                    }
                                    if (elementExist('sideBar')){
                                        $('#sideBar').removeClass('hide');
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

function showPage(fncPage, fncAddPageQ = 'Y') {
    let fncCurInfo = pgPageArray[pgPageArray.length - 1];
    let fncCurArray = fncCurInfo.split('~');
    let fncCurrent = fncCurArray[2];
    pageTransition(fncCurrent, fncPage);
    if (isCorePage(fncCurrent)) {
        $('#' + fncCurrent).addClass('hide');
    } else {
        $('#mainPage').addClass('hide');
    }
    if (fncAddPageQ == 'Y') {
        pgPageArray.push('0~0~' + fncPage);
        if ($('#backBtn').hasClass('hide')) {
            $('#backBtn').removeClass('hide')
        }
    }
    $('#' + fncPage).removeClass('hide')
} // showPage

function showRegister(fncFrom) {
    pgPageArray.push('0~0~registerPage');
    pageTransition(fncFrom, 'registerPage');
} //showRegister

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
}

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
                      $('#regForgot').removeClass('hide');
                  } else {
                      $('#registerPage').addClass('hide');
                      $('#mainPage').removeClass('hide');
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
                              $('#backBtn').addClass('hide');
                              $('#myNavbar').removeClass('hide');
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

function showSignIn(fncFrom) {
    pgPageArray.push('0~0~loginPage');
    wtkDebugLog('showSignIn called');
    pageTransition(fncFrom, 'loginPage');
    $('#backBtn').removeClass('hide');
    if (pgAccessMethod != 'website') {
        // ios, Android, pwa (phone)
        $('body').removeClass('bg-second');
        wtkDebugLog('showSignIn: pgAccessMethod = ' + pgAccessMethod);
    } else {
        wtkDebugLog('showSignIn 2: pgAccessMethod = ' + pgAccessMethod);
    }
} //showSignIn

function wtkLogout(){
    if (pgAlertUpdate != 0) {
        clearInterval(pgAlertUpdate);
    }
    $('#backBtn').addClass('hide');
    $('#hamburger').addClass('hide');
    hidePriorPage();
    if (elementExist('sideBar')){
        $('#sideBar').addClass('hide');
    }
    if (elementExist('myNavbar')){
        $('#myNavbar').addClass('hide'); // may or may not want to hide
    }
    $('#mainPage').addClass('hide');
    $('body').addClass('bg-second');
    $('#logoutPage').removeClass('hide');
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
                    $('#wrongApp').addClass('hide');
                }
                if (elementExist('FABbtn')){
                    $('#FABbtn').addClass('hide');
                }
                $('body').addClass('bg-second');
                if (elementExist('pageWrapper')) {
                    $('#pageWrapper').addClass('hide');
                }
                const fncElement = document.getElementById('fullPage');

                if (fncElement) {
                    fncElement.scrollIntoView({ behavior: 'smooth' });
                    wtkDebugLog('wtkLogout - scroll to fullPage');
                }
                pgPageArray.splice(0); // Clear go-back array
                pgPageArray.push('0~0~logoutPage');
            } else {
                wtkAlert('Failed to logout - please contact tech support.');
            }
        }
    })
} // wtkLogout

function showForgotPW(fncFrom) {
    pgPageArray.push('0~0~forgotPW');
    pageTransition(fncFrom, 'forgotPW');
//  $('#' + fncFrom).addClass('hide');
//  $('#forgotPW').removeClass('hide');
}

function wtkForgotPW() {
    wtkDebugLog('wtkForgotPW: top');
    let fncEmail = $('#emailForgot').val();
    if (fncEmail == '') {
        $('#forgotMsg').html('<div class="chip blue white-text">Enter a valid email address.</div>');
        $('#forgotMsg').fadeIn(540);
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
    //                              $('#forgotPW').addClass('hide');
    //                              $('#resetPWdiv').removeClass('hide');
                    } else {
                        let fncTmp = fncJSON.result;
                        $('#forgotMsg').html(fncTmp);
                        $('#forgotMsg').fadeIn(540);
                    }
                }
              })
          } else {
              wtkDebugLog('wtkForgotPW: not valid email');
              $('#forgotMsg').html('<div class="chip red white-text">Please enter a valid email address.</div>');
              $('#forgotMsg').fadeIn(540);
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
                  $('#resetForm').addClass('hide');
                  $('#finishedDIV').removeClass('hide');
              } else {
                  wtkAlert(fncJSON.result);
                  $('#resultMsg').html(fncJSON.result);
              }
          }
        })
    }
}

function wtkDeleteAccount(){
    $('#deleteModalDIV').html($('#deleteConfirmedDIV').html());
    $('#deleteModalFooter').html('<a class="btn modal-close waves-effect" onclick="Javascript:wtkLogout();">Close</a>');
    $.ajax({
        type: 'POST',
        url:  '/wtk/ajxDeleteAcct.php',
        data: { apiKey: pgApiKey },
        success: function(data) {
            setTimeout(function() {
                if (pgApiKey != '') {
                    wtkLogout();
                }
            }, 9000);
        }
    })
}

function goHome() {
//    hidePriorPage();
    pageTransition('priorPage', 'dashboard');
    pgPageArray.splice(0);
    pgPageArray.push('0~0~dashboard');
    $('#backBtn').addClass('hide');
//    $('#dashboard').removeClass('hide');
    if ($('#hamburger').hasClass('hide')) {
        $('#hamburger').removeClass('hide');
    }
    if ($('#slideOut').hasClass('hide')) {
        $('#slideOut').removeClass('hide');
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
    $('#photoProgressDIV').removeClass('hide');
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
                $('#photoProgressDIV').addClass('hide');
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
            //     $('#hamburger').removeClass('hide');
            //     $('#noPhotoDIV').addClass('hide');
            // }
            break;
        case 'cameraError':
            let fncMsg = messageArray[1];
            wtkAlert('Camera Error: ' + fncMsg);
            fixScroll();
            // not doing break because should do what is in cameraCancel
        case 'cameraCancel':
            wtkDebugMobile('cameraCancel called');
            $('#photoProgressDIV').addClass('hide');
            /*
            if (pgLastPhoto == 'user') {
                $('#userCard').removeClass('hide');
                $('#addPhotoPage').addClass('hide');
                $('#photoUpload').addClass('hide');
            }
            $('#mainPhoto').removeClass('hide');
            */
            break;
    } // switch
}; // handleMessage
// END photo and ReactNative functions

var pgMainPage = '';
var pgLoadWhenReady = 'N'; // in case ajax result is slower than animation
function pageTransition(fncFrom, fncTo, fncPage = ''){
    wtkDebugLog('pageTransition Top: fncFrom = ' + fncFrom + '; fncTo = ' + fncTo + '; fncPage = ' + fncPage);
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
            $('#' + fncFrom).addClass('hide');
            $('#' + fncTo).removeClass('hide');
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
//              $('#navCol1').removeClass('hide');
//              $('#pageTitle').text('YourCompanyName');
//              $('#navCol3').removeClass('hide');
            });
        });
    } else {
        pgLoadWhenReady = 'Y'; // response from server not ready yet
        $('#' + fncFrom).addClass('hide');
        if (fncTo == 'mainPage') {
            $('#mainPage').html('');
        }
        $('#' + fncTo).removeClass('hide');
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

function afterPageLoad(fncPage) {
    if ($('#HasImage').val() == 'Y') {
//        $('.materialboxed').materialbox();
        let elemImg = document.querySelectorAll('.materialboxed');
        let fncTmp = M.Materialbox.init(elemImg);
        wtkDebugLog('afterPageLoad: HasImage');
    }
    if ($('#HasCollapse').val() == 'Y') {
        let elem1 = document.querySelectorAll('.collapsible');
        let fncTmp1 = M.Collapsible.init(elem1);
    }
    if ($('#HasSelect').val() == 'Y') {
        let elem2 = document.querySelectorAll('select');
        let fncTmp2 = M.FormSelect.init(elem2);
        wtkDebugLog('afterPageLoad: HasSelect');
    }
    if ($('#HasDatePicker').val() == 'Y') {
        let elem3 = document.querySelectorAll('.datepicker');
        let option3 = {
            onClose: wtkFixSideNav,
            setDefaultDate: true
        };
        let fncTmp3 = M.Datepicker.init(elem3, option3);
        wtkDebugLog('afterPageLoad: HasDatePicker');
    }
    if ($('#HasTimePicker').val() == 'Y') {
        let elem4 = document.querySelectorAll('.timepicker');
        let fncTmp4 = M.Timepicker.init(elem4);
        wtkDebugLog('afterPageLoad: HasTimePicker');
    }
    if ($('#HasTextArea').val() !== undefined) {
        let fncTextAreas = $('#HasTextArea').val();
        let fncAreaArray = fncTextAreas.split(',');
        for(let i = 0; i < fncAreaArray.length; i++){
            M.textareaAutoResize($('#' + fncAreaArray[i]));
        }
    }
    if ($('#HasSummernote').val() == 'Y') {
        $('.snote').summernote({
            toolbar: [
              ['style', ['style']],
              ['font', ['bold', 'italic', 'underline', 'clear']],
              ['fontname', ['fontname']],
              ['color', ['color']],
              ['para', ['ul', 'ol', 'paragraph']],
              ['table', ['table']],
              ['view', ['codeview', 'help']]],
            dialogsInBody: true
        });
    }
    if ($('#HasTabs').val() == 'Y') {
        let elem5 = document.querySelectorAll('.tabs');
        let fncTmp5 = M.Tabs.init(elem5);
    }
    let optionNone = {};
    if ($('#HasFAB').val() == 'Y') {
        let elems6 = document.querySelectorAll('.fixed-action-btn');
        let fncTmp6 = M.FloatingActionButton.init(elems6, optionNone);
    }
    if ($('#HasTooltip').val() == 'Y') {
        let elems7 = document.querySelectorAll('.tooltipped');
        let fncTmp7 = M.Tooltip.init(elems7, optionNone);
    }
    if ($('#HasCarousel').val() == 'Y') {
        wtkDebugLog('afterPageLoad: HasCarousel 5');
        $('.carousel').carousel({
            indicators: true
        });
    }
    if ($('#refreshMenu').val() == 'Y') {
        $('#refreshMenu').val('N');
        ajaxFillDiv('menuRefresh','WTK-Admin','myNavbar');
    }
    if ($('#CharCntr').val() == 'Y') {
        M.CharacterCounter.init();
        M.CharacterCounter.init(document.querySelectorAll('.char-cntr'));
    }
    if ($('#wtkUpload').val() !== undefined) {
        // all File-Upload related functions are in wtkFileUpload.js
        wtkDebugLog('afterPageLoad: about to set EventListener for wtkUpload to do wtkFileChanged');
        document.getElementById('wtkUpload').addEventListener('change', (e) => {
            wtkFileChanged();
        })
    }
    if ($('#wtkUploadFiles').val() !== undefined) {
        let fncFileIDs = $('#wtkUploadFiles').val();
        let fncFileUpArray = fncFileIDs.split(',');
        for (let i = 0; i < fncFileUpArray.length; i++){
            wtkDebugLog('afterPageLoad: set wtkFileChanged for wtkUpload' + fncFileUpArray[i]);
            if (elementExist('wtkUpload' + fncFileUpArray[i])) {
                document.getElementById('wtkUpload' + fncFileUpArray[i]).addEventListener('change', (e) => {
                    wtkFileChanged(fncFileUpArray[i]);
                })
            } else {
                wtkDebugLog('afterPageLoad: wtkUpload' + fncFileUpArray[i] + ' does not exist');
            }
            wtkDebugLog('after set EventListener for wtkUpload to do wtkFileChanged');
        }
    }
    // BEGIN For Quick Filters make Enter Key act to Submit filter
    if (elementExist('wtkFilter') && elementExist('wtkFilterBtn')){
        let fncFilter = document.getElementById("wtkFilter");
        // Execute a function when the user presses a key on the keyboard
        fncFilter.addEventListener("keypress", function(event) {
          // Number 13 is the "Enter" key on the keyboard
          if (event.keyCode === 13) {
            // Cancel the default action, if needed
            event.preventDefault();
            // Trigger the button element with a click
            document.getElementById("wtkFilterBtn").click();
          }
        });
    }
    if (elementExist('wtkFilter2') && elementExist('wtkFilterBtn')){
        let fncFilter2 = document.getElementById("wtkFilter2");
        // Execute a function when the user releases a key on the keyboard
        fncFilter2.addEventListener("keypress", function(event) {
          // Number 13 is the "Enter" key on the keyboard
          if (event.keyCode === 13) {
            // Cancel the default action, if needed
            event.preventDefault();
            // Trigger the button element with a click
            document.getElementById("wtkFilterBtn").click();
          }
        });
    }
    //  END  For Quick Filters make Enter Key act to Submit filter
    if (elementExist('HasTinyMCE') || elementExist('HasModalTinyMCE')){
        let fncHasTinyMCE = '';
        if (elementExist('HasModalTinyMCE')) {
            fncHasTinyMCE = $('#HasModalTinyMCE').val();
            wtkDebugLog('afterPageLoad: HasModalTinyMCE = ' + fncHasTinyMCE);
        }
        if (fncHasTinyMCE == '') {
            if (elementExist('HasTinyMCE')) {
                fncHasTinyMCE = $('#HasTinyMCE').val();
                wtkDebugLog('afterPageLoad: HasTinyMCE = ' + fncHasTinyMCE);
            }
        }
        if (fncHasTinyMCE != '') {
            tinymce.init({
                selector: fncHasTinyMCE,
                theme: "modern",
                height: 250,
                plugins: [
                    "advlist autolink link lists charmap print preview hr anchor pagebreak spellchecker",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons template paste textcolor"
                ],
                toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link"
            });
        }
    }
    switch(fncPage) {
      case 'somePage':
        $('#backBtn').addClass('hide');
        break;
      case 'datePages': // change to pages that need date pickers
        // $('.datepicker').datepicker();
        let elem5 = document.querySelectorAll('.datepicker');
        let option5 = {
            onClose: fixScroll,
            setDefaultDate: true
        };
        let fncTmp5 = M.Datepicker.init(elem5, option5);
        // var instancesDate = M.Datepicker.init(elems, {setDefaultDate:true});
        /*
        // Range feature did not work in FireFox; did not test other browsers
//        M.Range.init($(document.querySelector('input[type=range]')));
        setTimeout(function() {
            var array_of_dom_elements = document.querySelectorAll("input[type=range]");
            M.Range.init(array_of_dom_elements);
            wtkDebugLog('afterPageLoad: calling Range.init');
        }, 900);
        */
        break;
      case 'dropListPages': // change to pages that require droplist/selects
        let elem6 = document.querySelectorAll('select');
        let fncTmp6 = M.FormSelect.init(elem6);
        wtkDebugLog('afterPageLoad: FormSelect');
        break;
      case 'reportEdit': // change to pages that require textarea fields
        wtkDebugLog('afterPageLoad: textareaAutoResize');
        M.textareaAutoResize($('#wtkwtkReportsRptNotes'));
        M.textareaAutoResize($('#wtkwtkReportsRptSelect'));
        M.textareaAutoResize($('#wtkwtkReportsSortableCols'));
        break;
    }
} // afterPageLoad

function hidePriorPage() {
    let fncCurInfo = pgPageArray[pgPageArray.length - 1];
    let fncCurArray = fncCurInfo.split('~');
    let fncCurrent = fncCurArray[2];
    if (isCorePage(fncCurrent)) {
        $('#' + fncCurrent).addClass('hide');
    } else {
        $('#mainPage').addClass('hide');
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
        let fncTooltipInstance;
        let fncTooltipElems = document.querySelectorAll('.tooltipped');
        fncTooltipElems.forEach(function(elem) {
            if (elem.id) { // Check if element has an ID
                fncTooltipInstance = M.Tooltip.getInstance(document.getElementById(elem.id));
                $('#' + elem.id).removeClass('tooltipped');
                wtkDebugLog('removed id ' + elem.id + ' tooltipped style');
            } else {
                fncTooltipInstance = M.Tooltip.getInstance(elem);
                elem.classList.remove('tooltipped'); // Remove tooltipped class
                wtkDebugLog('removed elem tooltipped style');
            }
            if (fncTooltipInstance) {
                fncTooltipInstance.close();
                fncTooltipInstance.destroy();
                wtkDebugLog('fncTooltipInstance.closed/destroyed');
            }
        });
        let fncMatTipElems = document.querySelectorAll('.material-tooltip');
        fncMatTipElems.forEach(function(elem) { // hide ones not properly destroyed
//            elem.classList.add('hide');
            elem.remove(); // Completely remove the tooltip element from the DOM
            wtkDebugLog('Removed material-tooltip element');
        });
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
        if ($('#backBtn').hasClass('hide')) {
            $('#backBtn').removeClass('hide')
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
    // BEGIN if TinyMCE is used, copy into original textarea form fields
    if (elementExist('HasTinyMCE')){ // 2ENHANCE currently will only work for 1 textarea on a page
        var fncHasTinyMCE = $('#HasTinyMCE').val();
        let fncTextArea = fncHasTinyMCE.replace('textarea#','');
        let fncNewValue = tinymce.get(fncTextArea).getContent();
        $('#' + fncTextArea).val(fncNewValue);
    }
    //  END  if TinyMCE is used, copy into original textarea form fields

    if (wtkRequiredFieldsFilled(fncPost)) { // check to see if any fields are required
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
        wtkDisableBtn('btnSave');
        var fncEncType = 'application/x-www-form-urlencoded';
        var fncContentType = false;
        if (pgAccessMethod == 'ios') {
            wtkUploadFile($('#ID1').val());
        } else {
            if (elementExist('wtkUpload') == false) { // upload does not exist
                fncContentType = 'application/x-www-form-urlencoded; charset=UTF-8';
            } else {
                let fncWtkMode = 'ADD';
                if (elementInFormExist(fncPost,'wtkMode') == true) { // because page may have more than one
                    fncWtkMode = $('#' + fncPost + ' input[type=hidden][id=wtkMode]').val();
                }
                if (fncWtkMode == 'ADD') {
                    fncEncType = 'multipart/form-data';
                } else {
                    fncContentType = 'application/x-www-form-urlencoded; charset=UTF-8';
                    // if ((pgFileToUpload == 'Y') && (pgFileSizeOK == 'Y')) {
                    //     if (elementExist('FileUploaded')) {
                    //         $('#FileUploaded').val('Y');
                    //     }
                    //     wtkfFileUpload(fncPost,$('#ID1').val());
                    // }
                }
            }
        } // pgAccessMethod != 'ios'
        let fncFormData = '';
        // upload images
        if ($('#wtkUploadFiles').val() !== undefined) {
            if ((pgFileToUpload == 'Y') && (pgFileSizeOK == 'Y')) {
                wtkDebugLog('ajaxPost: wtkUploadFiles going to upload');
                if (elementExist('FileUploaded')) {
                    $('#FileUploaded').val('Y');
                }
                let fncFileIDs = $('#wtkUploadFiles').val();
                let fncFileUpArray = fncFileIDs.split(',');
                for (let i = 0; i < fncFileUpArray.length; i++){
                    wtkfUploadFile(fncFileUpArray[i]);
                }
            } else {
                wtkDebugLog('ajaxPost: wtkUploadFiles NOT pgFileToUpload = ' + pgFileToUpload + '; pgFileSizeOK = ' + pgFileSizeOK);
            }
        }
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
            wtkDebugLog('ajaxPost: pgMPAvsSPA = SPA');
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
                    if (elementExist('HasTinyMCE')){
                        wtkDebugLog('ajaxPost: HasTinyMCE going to tinymce.remove');
                        tinymce.remove(fncHasTinyMCE);
                        $('#HasTinyMCE').val('');
                    }
                    waitLoad('off');
                    if (data == 'goHome') {
                        goHome();
                    } else {
                        if (pgUseTransition == 'Y') {
                            animateCSS('#mainPage', pgTransitionOut).then((message) => {
                              // Do something after the animation
                                $('#mainPage').html(data);
                                $('#mainPage').removeClass('hide');
                                pgFileToUpload = 'N';
                                afterPageLoad(fncPage);
                                animateCSS('#mainPage', pgTransitionIn).then((message) => {
                    //              $('#pageTitle').text('YourCompanyName');
                                });
                            });
                        } else {
                            $('#mainPage').html(data);
                            $('#mainPage').removeClass('hide');
                            pgFileToUpload = 'N';
                //          $('#hamburger').removeClass('hide');
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
//              afterPageLoad(fncPage);
        }
    })
} // ajaxCopy

var pgLastDashboard = 'widgTD1';
function ajaxFillDiv(fncPage, fncParam, fncDiv, fncRNG = 0) {
    wtkDebugLog('ajaxFillDiv top for fncPage: ' + fncPage);
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
            if (fncPage == '/wtk/widgets') {
                $('#mainPage').text('... loading ...'); // to prevent conflicts with widgets
            }
            $('#' + fncDiv).html(data);
            switch (fncPage) {
                case '/wtk/widgets':
                    if (elementExist('HasTooltip')){
                        $('.tooltipped').tooltip();
                    }
                    if (elementExist('myDashBtn')) {
                        if (fncParam == 1) {
                            $('#myDashBtn').removeClass('hide');
                        } else {
                            $('#myDashBtn').addClass('hide');
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
function fixScroll() {
    $('.sidenav').sidenav();
    wtkDebugLog('called fixScroll');
}
function wtkFixSideNav(){
    if (elementExist('phoneSideBar')) {
        let fncElem = document.querySelectorAll('.sidenav');
        M.Sidenav.init(fncElem, {edge:'right'});
    }
}

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
            $('#' + fncPriorPage).addClass('hide');
        } else {
    //        $('#mainPage').addClass('hide');
            fncPriorPage = 'mainPage';
        }
        let fncCurInfo = pgPageArray[pgPageArray.length - 1];
        let fncCurArray = fncCurInfo.split('~');
        let fncCurrent = fncCurArray[2];
        wtkDebugLog('wtkGoBack hiding ' + fncPriorPage + ' and showing ' + fncCurrent + '; fncCurInfo = ' + fncCurInfo + '; pgPageArray.length = ' + pgPageArray.length);
        if (pgPageArray.length == 1) {
            $('#backBtn').addClass('hide');
        }
        if (isCorePage(fncCurrent)) {
            pageTransition(fncPriorPage,fncCurrent);
            wtkDebugLog('wtkGoBack isCorePage');
            if (fncCurrent == 'dashboard') {
    //          goHome();
                pgPageArray.splice(0);
                pgPageArray.push('0~0~dashboard');
                $('#backBtn').addClass('hide');
                if ($('#hamburger').hasClass('hide')) {
                    $('#hamburger').removeClass('hide');
                }
                if ($('#slideOut').hasClass('hide')) {
                    $('#slideOut').removeClass('hide');
                }
                wtkFixSideNav();
                getDashboardCounts();
            }
        } else {
            wtkDebugLog('wtkGoBack NOT isCorePage, calling ajaxGo');
    //        $('#mainPage').html('... loading ...');
    //        $('#mainPage').removeClass('hide');
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
        $('#backBtn').removeClass('hide');
    }
}

function contactUs() {
    $('.modal').modal({
        dismissible: false,
        startingTop: '4%',
        endingTop: '10%'
        }
    );
    let fncId = document.getElementById('contactDiv');
    let fncModal = M.Modal.getInstance(fncId);
    fncModal.open();
} // contactUs

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

function ajxEmailTemplate(fncId,fncOtherUID=0){
    // called by emailModal.php
    if (fncId == '0'){
        $('#EmailUID').val('');
        $('#Subject').val('');
        $('#EmailMsg').val('');
        $('#labelEmailMsg').removeClass('active');
        $('#labelSubject').removeClass('active');
        M.textareaAutoResize($('#EmailMsg'));
    } else {
        $.ajax({
          type: 'POST',
          url: '/wtk/ajxEmailTemplate.php',
          data: { apiKey: pgApiKey, id: fncId, oid: fncOtherUID },
          success: function(data) {
              let fncJSON = $.parseJSON(data);
              if (fncJSON.result == 'ok'){
                  $('#EmailUID').val(fncId);
                  $('#Subject').val(fncJSON.Subject);
                  $('#labelSubject').addClass('active');
                  let fncBody = fncJSON.Body;
                  fncBody = fncBody.replaceAll('~!~','"');
                  fncBody = fncBody.replaceAll('^n^', "\r\n");
                  $('#EmailMsg').val(fncBody);
                  $('#labelEmailMsg').addClass('active');
                  M.textareaAutoResize($('#EmailMsg'));
              }
          }
        })
    }
} // ajxEmailTemplate

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
						M.toast({html: "Your message has been sent.", classes: "green rounded"});
                        if (elementExist('thanksMsg')) {
                            $('#thanksMsg').removeClass('hide');
                        }
                        if (fncModalId == '') {
                            $('#' + fncFormName).addClass('hide');
                        } else {
                            let fncId = document.getElementById(fncModalId);
    						let fncModal = M.Modal.getInstance(fncId);
    						fncModal.close();
                        }
					} else {
						M.toast({html: "Email failed to send", classes: "red rounded"});
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
                            let fncId = document.getElementById('contactDiv');
                            let fncModal = M.Modal.getInstance(fncId);
                            fncModal.close();
                        } else {
                            $('#regForm').addClass('hide');
                            $('#thanksMsg').removeClass('hide');
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
                M.toast({html: 'Email sent', classes: 'rounded green'});
                let fncId = document.getElementById('modalWTK');
                let fncModal = M.Modal.getInstance(fncId);
                fncModal.close();
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
                M.toast({html: 'SMS message sent', classes: 'rounded green'});
            }
        })
    }
} // wtkSendSMS

/* Begin: Notification related functions */
function wtkShowNotificationAdvanced(){
    if ($('#futureDateDIV').hasClass('hide')){
        $('#futureDateDIV').removeClass('hide')
        wtkChangeRequired('wtkwtkNotificationsStartDate',true);
    } else {
        $('#futureDateDIV').addClass('hide')
        $('#wtkwtkNotificationsStartDate').val('');
        $('#wtkwtkNotificationsRepeatFrequency1').prop('checked',true);
        wtkChangeRequired('wtkwtkNotificationsStartDate',false);
    }
}
function wtkNotificationAudience(fncValue){
    ajaxFillDiv('/wtk/ajxSelAudience', fncValue, 'pickToUID');
    if (fncValue == 'S') {
        $('#AltDelivery').removeClass('hide');
    } else {
        $('#AltDelivery').addClass('hide');
    }
}
function wtkProofNotification(){
    let fncIconColor = $('#wtkwtkNotificationsIconColor').val();
    let fncIcon = $('#wtkwtkNotificationsIcon').val();

    $("#proofIconColor").attr("class", ""); // remove all
    $("#proofIconColor").addClass('btn-floating');
    $("#proofIconColor").addClass('btn-large');
    $("#proofIconColor").addClass(fncIconColor);
    $("#proofIcon").text(fncIcon);
}
function wtkGoToNotification(fncId,fncGoToUrl,fncGoToId,fncGoToRng){
    $('#alertId' + fncId).addClass('hide');
    let fncAlertCount = roundToPrecision($('#alertCounter').text());
    if (fncAlertCount == 1) {
        $('#alertCounter').addClass('hide');
        $('#alertCounter').text(0);
    } else {
        fncAlertCount = (fncAlertCount - 1);
        if (fncAlertCount >= 0) {
            $('#alertCounter').text(fncAlertCount);
            if ($('#alertCounter').hasClass('hide')) {
                $('#alertCounter').removeClass('hide');
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
    $('#editHelp').removeClass('hide');
    $('#editHelpBtn').addClass('hide');
    $('#saveHelpBtn').removeClass('hide');
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
            M.toast({html: 'The help data has been saved.', classes: 'green rounded'});
        }
    })
} // wtkSaveHelp

var pgModalColor = '';
var pgLastModal = '';
var pgClearBottomModal = true;
function wtkModal(fncUrl, fncMode, fncId=0, fncRNG=0, fncColor='', fncDismissable = 'Y') {
    // First check and close any existing open modals
    let fncExistingModal = M.Modal.getInstance(document.getElementById('modalWTK'));
    if (fncExistingModal) {
        fncExistingModal.close();
        fncExistingModal.destroy();
    }
    waitLoad('on');
    $.ajax({
        type: 'POST',
        url:  fncUrl + '.php',
        data: { apiKey: pgApiKey, Mode: fncMode, id: fncId, rng: fncRNG },
        success: function(data) {
            if (pgLastModal == 'reportBug') {
                $('#reportBug').html($('#modalWTK').html());
                pgLastModal = '';
            }
            $('#modalWTK').html(data);
            waitLoad('off');

            let fncOptions = {};
            let fncModalId = document.getElementById('modalWTK');
            if ($(data).find('input#HasModalTinyMCE').length > 0) {
                wtkDebugLog("wtkModal: The input field with ID 'HasTinyMCE' exists!");
                fncOptions.onCloseStart = wtkRemoveModalTinyMCE;
            }
            if (fncDismissable == 'N') {
                wtkDebugLog('wtkModal: added fncDismissable to modal window');
                fncOptions.dismissible = false;
            }
            let fncModal = M.Modal.init(fncModalId, fncOptions);
            if (pgModalColor != '') {
                $('#modalWTK').removeClass(pgModalColor);
            }
            if (fncColor != '') {
                pgModalColor = fncColor;
                $('#modalWTK').addClass(pgModalColor);
            }
            fncModal.open();
            document.getElementById('modalWTK').scrollTop = 0;
            afterPageLoad('modal');
        }
    })
} // wtkModal

function wtkModalUpdate(fncPage, fncId=0, fncRNG=0) {
    waitLoad('on');
    $.ajax({
        type: 'POST',
        url:  fncPage + '.php',
        data: { apiKey: pgApiKey, id: fncId, rng: fncRNG },
        success: function(data) {
            $('#modalWTK').html(data);
            waitLoad('off');
            afterPageLoad('modalWTK');
        }
    })
}

function modalSave(fncUrl, fncDiv, fncClose = 'N', fncAppend = 'N') {
    wtkDebugLog('modalSave ' + fncDiv);
    // BEGIN if TinyMCE is used, copy into original textarea form fields
    if (elementExist('HasModalTinyMCE')){ // 2ENHANCE currently will only work for 1 textarea on a page
        var fncHasTinyMCE = $('#HasModalTinyMCE').val();
        let fncTextArea = fncHasTinyMCE.replace('textarea#','');
        let fncNewValue = tinymce.get(fncTextArea).getContent();
        $('#' + fncTextArea).val(fncNewValue);
    }
    //  END  if TinyMCE is used, copy into original textarea form fields
    if (wtkRequiredFieldsFilled('F' + fncDiv)) {
        waitLoad('on');
        var fncContentType = false;
        if (elementExist('wtkUpload') == false) { // upload does not exist
            fncContentType = 'application/x-www-form-urlencoded; charset=UTF-8';
        } else {
            let fncWtkMode = document.getElementById('F' + fncDiv).wtkMode.value; // because may have one from page calling modal
            if (fncWtkMode != 'ADD') {
                fncContentType = 'application/x-www-form-urlencoded; charset=UTF-8';
                if ((pgFileToUpload == 'Y') && (pgFileSizeOK == 'Y')) {
                    wtkfFileUpload('F' + fncDiv);
                }
            }
        }
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
        $.ajax({
            method: 'POST',
            type: 'POST',
            url:  fncUrl + '.php',
            cache: false,
            contentType: fncContentType,
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
                            if (fncUrl == 'yourSpecialPage') {
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
                    let fncId = document.getElementById('modalWTK');
                    let fncModal = M.Modal.getInstance(fncId);
                    fncModal.close();
                    M.toast({html: 'Your data has been saved.', classes: 'green rounded'});
                }
                wtkFixSideNav();
            }
        })
    } // wtkRequiredFieldsFilled
} // modalSave

function modalSaveDoc(fncUrl, fncDiv) {
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
        url:  fncUrl + '.php',
        data: fncData,
        processData: false,
        contentType: false,
        cache: false,
        timeout: 800000,
        success: function(data) {
            waitLoad('off');
            $('#' + fncDiv).html(data);
            let fncId = document.getElementById('modalWTK');
            let fncModal = M.Modal.getInstance(fncId);
            fncModal.close();
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
            $('.materialboxed').materialbox();
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
            $('#filterReset').removeClass('hide');
            $('.materialboxed').materialbox();
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
    $('#filterReset').addClass('hide');
    $.ajax({
        type: 'POST',
        url:  fncURL + '.php',
        data: { apiKey: pgApiKey, Reset: 'Y', TableID: fncTableID, rng: fncRNG, AJAX: 'Y' },
        success: function(data) {
            let updatedTable = $(data);
            let oldTable = $('#' + fncTableID);
            oldTable.replaceWith(updatedTable);
            $('.materialboxed').materialbox();
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
        $('#D' + fncTbl + fncId).addClass('hide');
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
                    $('.materialboxed').materialbox();
                }
            })
        }
    })
}

function wtkMakePageList() {
    // This creates JS file which stores paths to PHP files
    $.getJSON('/wtk/ajxMakePageList.php?apiKey=' + pgApiKey, function(data) {
        gloFilePath.splice(0); // empty array
        $('#pageMsg').html('<span class="chip green white-text">Website paths and files updated</span>');
        $.each(data, function(key, value) {
            gloFilePath[key] = value;
        });
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
                M.toast({html: 'Error during purchase - please contact tech support', classes: 'rounded red'});
            },
            onCancel: function (data) {
                // Show a cancel page, or return to cart
                wtkDebugLog('Canceled order!');
                M.toast({html: 'Your order has been canceled', classes: 'rounded orange'});
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
                                $('#paypal-buttons' + fncDivs).addClass('hide');
                                $('#paypal-thanks' + fncDivs).removeClass('hide');
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

function wtkClearBroadcast(fncUID){
    $('#wtkBC' + fncUID).removeClass('active');
    $('#wtkBC' + fncUID).removeClass('carousel-item');
    $('#wtkBC' + fncUID).addClass('hide');
    $.ajax({ url: '/wtk/ajxClearBroadcast.php?id=' + fncUID + '&apiKey=' + pgApiKey });
    let fncCount = $('#broadcastCount').val();
    fncCount = (fncCount - 1);
    $('#broadcastCount').val(fncCount);
    if (fncCount == 0) {
        $('#broadcastDIV').addClass('hide');
    } else {
        let fncElem = document.getElementById('broadcastDIV');
        let fncInstance = M.Carousel.getInstance(fncElem);
        fncInstance.destroy();
        wtkDebugLog('8 after fncInstance.destroy');
        setTimeout(function() {
            $('.carousel').carousel({
                indicators: false
            });
        }, 180);
    }
};

function wtkSetBreadCrumb(fncName,fncGoTo='',fncId=0,fncRNG=0,fncSpecial=''){
    if (fncSpecial == 'clearFirst'){
        document.getElementById('myBreadCrumbs').innerHTML = '';
    }
    var fncCrumbs = '<a class="breadcrumb"';
    if (fncGoTo == ''){
        fncCrumbs += '>';
    } else {
        fncCrumbs += ' onclick="JavaScript:ajaxGo(\'';
        fncCrumbs += fncGoTo + "'," + fncId + ',' + fncRNG + ')">';
    }
    fncCrumbs += fncName + '</a>';
    document.getElementById('myBreadCrumbs').innerHTML += fncCrumbs;
} // wtkSetBreadCrumb

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
//        $('#myNavbar').removeClass('hide');
        pgPageArray.push('0~0~' + fncShowPage);
        $('#logoutPage').addClass('hide');
        if (isCorePage(fncShowPage)) {
            $('#' + fncShowPage).removeClass('hide');
        } else {
            $('#mainPage').html('... loading ...');
            $('#mainPage').removeClass('hide');
            ajaxGo(fncShowPage,0,0,'Y');
        }
        $('#hamburger').removeClass('hide');
        $('#slideOut').removeClass('hide');
        wtkFixSideNav();
    }
}
