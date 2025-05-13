"use strict";

// below function called in HTML by <body onload >
function wtkStartMaterializeCSS() {
    $(document).ready(function() {
        M.AutoInit();
        var fncTmpVar = '';
        if (elementExist('pgDebugVar')) {
            fncTmpVar = $('#pgDebugVar').val();
            if (fncTmpVar == 'Y') {
                pgDebug = 'Y';
            }
        }
        if (elementExist('pgSiteVar')) {
            pgSite = $('#pgSiteVar').val();
        } else {
            pgSite = 'publicApp';
        }
        if (elementExist('AccessMethod')) {
            pgAccessMethod = $('#AccessMethod').val();
        } else {
            pgAccessMethod = 'website';
        }
        if (elementExist('CharCntr')) {
            let fncCharCntr = $('#CharCntr').val();
            if (fncCharCntr == 'Y') {
                M.CharacterCounter.init();
                M.CharacterCounter.init(document.querySelectorAll('.char-cntr'));
            }
        }
        if (elementExist('HasCollapse')) {
            fncTmpVar = $('#HasCollapse').val();
            if (fncTmpVar == 'Y') {
                let fncElem = document.querySelectorAll('.collapsible');
                let fncTmp = M.Collapsible.init(fncElem);
            }
        }
        if (elementExist('HasImage')) {
            fncTmpVar = $('#HasImage').val();
            if (fncTmpVar == 'Y') {
                //        $('.materialboxed').materialbox();
                let fncElemImg = document.querySelectorAll('.materialboxed');
                let fncTmp2 = M.Materialbox.init(fncElemImg);
                wtkDebugLog('wtkStartMaterializeCSS: HasImage');
            }
        }
        if (elementExist('HasTooltip')) {
            fncTmpVar = $('#HasTooltip').val();
            if (fncTmpVar == 'Y') {
                let fncOption = {};
                let fncTmp3 = document.querySelectorAll('.tooltipped');
                let fncTmp4 = M.Tooltip.init(fncTmp3, fncOption);
            }
        }
        if (elementExist('wtkUpload')) {
            // all File-Upload related functions are in wtkFileUpload.js
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
        if (elementExist('changeLanguage')) {
            fncTmpVar = $('#changeLanguage').val();
            if (fncTmpVar != undefined) {
                wtkLangUpdate(fncTmpVar);
            }
        }
        $(".wrapper-load").fadeOut();
        if (typeof wtkMPAstart === "function") {
            wtkMPAstart();
        }
        if (elementExist('SPArestart')) {
            fncTmpVar = $('#SPArestart').val();
            if (fncTmpVar == 'Y') {
                $('#mainPage').removeClass('hide');
                $('#myNavbar').removeClass('hide');
                $('#loginPage').addClass('hide');
                pgApiKey = $('#apiKeyRestart').val();
                pgSecLevel = $('#secLvlRestart').val();
                pgMPAvsSPA = 'SPA';
            }
        }
        if (elementExist('myEmail')) {
            document.getElementById('myEmail').onchange = function() {
                wtkValidate(this,'EMAIL');
            };
        }
        if (elementExist('wtkwtkUsersEmail')) {
            document.getElementById('wtkwtkUsersEmail').onchange = function() {
                wtkValidate(this,'EMAIL');
            };
        }
        wtkDebugLog('wtkStartMaterializeCSS successful: pgSite: ' + pgSite + '; pgAccessMethod: ' + pgAccessMethod);
        if (pgMPAvsSPA == 'MPA') {
            if ((pgApiKey == '') && (elementExist('apiKey'))) {
                pgApiKey = $('#apiKey').val();
            }
            afterPageLoad();
        } else {
            wtkToggleShowPassword();
        }
        pgHide = 'hide'; // 'hide' for MaterializeCSS, TailwindCSS uses 'hidden'
    });
} // wtkStartMaterializeCSS
function wtkToggleShowPassword() {
    document.querySelectorAll('.toggle-password').forEach(function(toggleIcon) {
        toggleIcon.addEventListener('click', function() {
            const input = document.querySelector(this.getAttribute('data-toggle'));

            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                this.textContent = 'visibility';
            }
        });
    });
} // wtkToggleShowPassword

function wtkAlert(fncText, fncHdr = 'Oops!', fncColor = 'red', fncIcon = 'warning', fncReqId = '') {
    if (fncLastIconColor != fncColor) {
        $('#modIcon').removeClass(fncLastIconColor + '-text');
        $('#modIcon').addClass(fncColor + '-text');
        fncLastIconColor = fncColor;
    }
    $('#modIcon').text(fncIcon);
    $('#modHdr').text(fncHdr);
    $('#modText').html(fncText);
    let fncOptions = {};
    let fncModalId = document.getElementById('modalAlert');
    if (fncReqId != '') {
        fncRequiredFieldId = fncReqId;
        fncOptions.onCloseEnd = wtkFocusOnInput;
    }
    let fncModal = M.Modal.init(fncModalId, fncOptions);
    fncModal.open();
    wtkDebugLog('mobAlert called: ' + fncText);
} // wtkAlert

function waitLoad(fncMode) {
    if (fncMode == 'on') {
        $('#fullPage').addClass('shade-background');
        $('#loaderDiv1').removeClass('hide');
        $('#loaderDiv1').removeClass('active');
        $('#loaderDiv2').addClass('active');
        let fncDiv = document.getElementById('loaderDiv1');
        fncDiv.style.removeProperty('display');
    } else {
        $('#loaderDiv2').removeClass('active');
        $('#loaderDiv1').addClass('hide');
        $('#fullPage').removeClass('shade-background');
    }
} // waitLoad
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
        $('#backBtn').addClass(pgHide);
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
    wtkToggleShowPassword();
} // afterPageLoad

function wtkRemoveToolTips(){
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
//            elem.classList.add(pgHide);
        elem.remove(); // Completely remove the tooltip element from the DOM
        wtkDebugLog('Removed material-tooltip element');
    });
} // wtkRemoveToolTips

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

function wtkToastMsg(fncMsg, fncColor = 'green'){
    let fncClass = fncColor + ' rounded';
    M.toast({html: fncMsg, classes: fncClass});
} // wtkToastMsg

function wtkCloseModal(fncModalId){
    let fncId = document.getElementById(fncModalId);
    let fncModal = M.Modal.getInstance(fncId);
    fncModal.close();
} // wtkCloseModal

function wtkProofNotification(){
    let fncIconColor = $('#wtkwtkNotificationsIconColor').val();
    let fncIcon = $('#wtkwtkNotificationsIcon').val();

    $("#proofIconColor").attr("class", ""); // remove all
    $("#proofIconColor").addClass('btn-floating');
    $("#proofIconColor").addClass('btn-large');
    $("#proofIconColor").addClass(fncIconColor);
    $("#proofIcon").text(fncIcon);
}

var pgModalColor = '';
var pgLastModal = '';
function wtkModal(fncPage, fncMode, fncId=0, fncRNG=0, fncColor='', fncDismissable = 'Y') {
    // First check and close any existing open modals
    let fncExistingModal = M.Modal.getInstance(document.getElementById('modalWTK'));
    if (fncExistingModal) {
        fncExistingModal.close();
        fncExistingModal.destroy();
    }
    waitLoad('on');
    $.ajax({
        type: 'POST',
        url:  fncPage + '.php',
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

function wtkTableSetup(){
    $('.materialboxed').materialbox();
}
// BEGIN Browse Box Functions

// Print functions
function wtkClearBroadcast(fncUID){
    $('#wtkBC' + fncUID).removeClass('active');
    $('#wtkBC' + fncUID).removeClass('carousel-item');
    $('#wtkBC' + fncUID).addClass(pgHide);
    $.ajax({ url: '/wtk/ajxClearBroadcast.php?id=' + fncUID + '&apiKey=' + pgApiKey });
    let fncCount = $('#broadcastCount').val();
    fncCount = (fncCount - 1);
    $('#broadcastCount').val(fncCount);
    if (fncCount == 0) {
        $('#broadcastDIV').addClass(pgHide);
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
