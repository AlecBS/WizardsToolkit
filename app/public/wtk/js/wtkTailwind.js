"use strict";
// add this file to your spa.htm if you are using TailwindCSS implementation of Wizard's Toolkit
var pgHide = 'hidden'; // set to 'hidden' for TailwindCSS or 'hide' for MaterializeCSS

function wtkPageSetup(fncFromPage = '') {
    wtkToggleShowPassword();
    waitLoad('off');
    if (elementExist('pgSiteVar')) {
        pgSite = $('#pgSiteVar').val();
    } else {
        pgSite = 'publicApp';
    }
}
var pgLastClicked = 0;

function wtkToggleShowPassword() {
    wtkDebugLog('wtkToggleShowPassword top');
    document.querySelectorAll('.toggle-password').forEach(function(toggleIcon) {
        toggleIcon.addEventListener('click', function() {
            let fncNow = Date.now();
            if ((fncNow - pgLastClicked) < 500) return; // Ignore if triggered again within 500ms
            pgLastClicked = fncNow;

            const input = document.querySelector(this.getAttribute('data-toggle'));
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-eye-off"/></svg>';
                wtkDebugLog('wtkToggleShowPassword make visible: ' + pgLastClicked);
            } else {
                input.type = 'password';
                this.innerHTML = '<svg class="wtk-icon"><use href="/imgs/icons.svg#icon-eye"/></svg>';
                wtkDebugLog('wtkToggleShowPassword make invisible: ' + pgLastClicked);
            }
        });
    });
} // wtkToggleShowPassword

function wtkModal(fncPage, fncMode, fncId=0, fncRNG=0, fncColor='', fncDismissable = 'Y') {
    // First check and close any existing open modals
    const modal = document.getElementById('modalWTK');
    const backdrop = document.getElementById('modalBackdrop');

    if (modal && backdrop) {
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
    }

    waitLoad('on'); // shows loading screen until ready

    $.ajax({
        type: 'POST',
        url: fncPage + '.php',
        data: {apiKey: pgApiKey, Mode: fncMode, id: fncId, rng: fncRNG},
        success: function (data) {
            $('#modalContent').html(data);
            waitLoad('off'); // stops showing loading screen

            // Show modal and backdrop
            modal.classList.remove('hidden');
            backdrop.classList.remove('hidden');

            // Handle dismissable option
            if (fncDismissable === 'Y') {
                backdrop.onclick = function () {
                    modal.classList.add('hidden');
                    backdrop.classList.add('hidden');
                };
            } else {
                backdrop.onclick = null;
            }

            modal.scrollTop = 0;
            afterPageLoad('modal');
        }
    });
} // wtkModal

function wtkCloseModal(fncModalId = 'modalWTK'){
    document.getElementById(fncModalId).classList.add('hidden');
    document.getElementById('modalBackdrop').classList.add('hidden');
}

function waitLoad(fncMode) {
    if (fncMode == 'on') {
        document.getElementById('wtkLoader').classList.remove('hidden');
    } else {
        document.getElementById('wtkLoader').classList.add('hidden');
    }
} // waitLoad

function wtkAlert(fncText, fncHdr = 'Oops!', fncColor = 'red', fncIcon = 'warning', fncReqId = '') {
    const modalEl = document.getElementById('modalAlert');
    const iconEl = document.getElementById('modIcon');
    const headerEl = document.getElementById('modHdr');
    const textEl = document.getElementById('modText');
    const closeBtn = document.getElementById('langClose');

    // Update icon color
    if (fncLastIconColor !== fncColor) {
        iconEl.classList.remove(`text-${fncLastIconColor}-600`);
        iconEl.classList.add(`text-${fncColor}-600`);
        fncLastIconColor = fncColor;
    }

    // Update icon SVG based on type
    let iconPath = '';
    switch (fncIcon) {
        case 'check':
            iconPath = 'M5 13l4 4L19 7';
            break;
        case 'error':
            iconPath = 'M6 18L18 6M6 6l12 12';
            break;
        case 'info':
            iconPath = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
            break;
        default: // warning
            iconPath = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z';
    }

    iconEl.innerHTML = `
    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"/>
    </svg>
  `;

    // Set content
    headerEl.textContent = fncHdr;
    textEl.innerHTML = fncText;

    // Show modal
    modalEl.classList.remove('hidden');

    // Handle close
    const closeModal = () => {
        modalEl.classList.add('hidden');
        if (fncReqId) {
            const inputEl = document.getElementById(fncReqId);
            if (inputEl) inputEl.focus();
        }
    };

    closeBtn.onclick = closeModal;

    // Also close when clicking outside or pressing ESC
    modalEl.onclick = (e) => {
        if (e.target === modalEl) closeModal();
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modalEl.classList.contains('hidden')) {
            closeModal();
        }
    });

    wtkDebugLog('wtkAlert called: ' + fncText);
} // wtkAlert

function closeParentDetails(el) {
    const details = el.closest('details');
    if (details) details.removeAttribute('open');
}

function closeSideMenu(el) {
    const fncElem = el.closest('dropdown');
    if (fncElem) fncElem.removeAttribute('dropdown-open');
}
function afterPageLoad(fncPage){
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
        for (let i = 0; i < fncFileUpArray.length; i++) {
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
    if (elementExist('CharCntr')) {
        if ($('#CharCntr').val() == 'Y') {
            wtkCharWordCounters();
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
    wtkToggleShowPassword();
} // afterPageLoad

function wtkCharWordCounters() {
    // Select all input and textarea elements with the class "char-cntr"
    document.querySelectorAll('input.char-cntr, textarea.char-cntr').forEach(function (element) {
        // Prevent duplicate counters if already present
        if (element.nextSibling && element.nextSibling.classList
            && element.nextSibling.classList.contains('char-word-counter')) {
            return;
        }
        // Create the counter display element
        var counter = document.createElement('div');
        counter.className = 'char-word-counter';
        counter.style.fontSize = '0.9em';
        counter.style.margin = '4px 0 8px 0';
        counter.style.color = '#666';

        // Insert the counter right after the input/textarea
        element.parentNode.insertBefore(counter, element.nextSibling);
     // Read data-length (string) and optionally coerce to number
        var maxLength = element.dataset.length || '';

        // Update the counter display
        function updateCounter() {
            var val = element.value;
            var charCount = val.length;
            // Remove multiple spaces; split, filter empty, count
            var wordCount = val.trim() === '' ? 0 : val.trim().split(/\s+/).length;
            counter.innerHTML =
                'Words: ' + wordCount +
                ' &nbsp;|&nbsp; Characters: ' + charCount +
                (maxLength ? ' &nbsp;|&nbsp; Max: ' + maxLength : '');
        }

        updateCounter(); // Initial count
        // Update on any input event
        element.addEventListener('input', updateCounter);
    });
}
function wtkFixSideNav(){
    wtkDebugLog('wtkFixSideNav called -may need to define');
}
function wtkTableSetup(){
    // not needed
}
function wtkRemoveToolTips(){
    // not needed
}

function wtkToastMsg(fncMsg, fncColor = "success", fncDivId = '', fncMaxWidth = 450) {
    switch (fncColor) {
        case 'green':  fncColor = 'success'; break;
        case 'blue':   fncColor = 'info';    break;
        case 'orange': fncColor = 'warning'; break;
        case 'red':    fncColor = 'error';   break;
    }

    var toastRoot = '';
    if (fncDivId == ''){
        toastRoot = document.body;
    } else {
        if (elementExist('fncDivId')){
            toastRoot = document.getElementById(fncDivId);
        } else {
            toastRoot = document.body;
        }
    }
    // Create container structure if it doesn't exist
    let toastStack = document.getElementById('toast-stack');
    if (!toastStack) {
        const toastContainer = document.createElement("div");
        toastContainer.id = "toast-root";
        toastContainer.className = "fixed left-1/2 -translate-x-1/2 z-50 pointer-events-none w-full";
        toastContainer.style.top = '72px';
        toastStack = document.createElement("div");
        toastStack.id = "toast-stack";
        toastStack.className = "w-full flex flex-col items-center";
        toastContainer.appendChild(toastStack);
        if (toastRoot.firstChild) {
            toastRoot.insertBefore(toastContainer, toastRoot.firstChild);
        } else {
            toastRoot.appendChild(toastContainer);
        }
    }

    const fncAlert = document.createElement("div");
    fncAlert.className = 'mx-auto alert alert-' + fncColor +
                     ' flex justify-center items-center text-center toast-animate';
    fncAlert.innerHTML = '<span>' + fncMsg + '</span>';

    // Insert at top of stack
    if (toastStack.firstChild) {
        toastStack.insertBefore(fncAlert, toastStack.firstChild);
    } else {
        toastStack.appendChild(fncAlert);
    }
    setTimeout(() => toastStack.remove(), 3000);
} // wtkToastMsg