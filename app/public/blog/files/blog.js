'use strict';
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
https://WizardsToolkit.com
Wizard's Toolkit is a low-code development library for PHP, SQL and JavaScript
*/

var gloMaxFileSize = 4718592; // currently 4.5MB
var pgMPAvsSPA = 'MPA';
// below function called in HTML by <body onload >
function wtkStart(fncPage = 'blog'){
    $(document).ready(function() {
        M.AutoInit();
        let fncCharCntr = $('#CharCntr').val();
        if (fncCharCntr == 'Y') {
            M.CharacterCounter.init();
            M.CharacterCounter.init(document.querySelectorAll('.char-cntr'));
        }
        let fncTmpVar = $('#HasCollapse').val();
        if (fncTmpVar == 'Y') {
            let fncElem = document.querySelectorAll('.collapsible');
            let fncTmp = M.Collapsible.init(fncElem);
        }
//        $(".wrapper-load").fadeOut();
        let fncElems = document.querySelectorAll('.tooltipped');
        let fncTmp = M.Tooltip.init(fncElems);
        switch (fncPage) {
            case 'Designer':
            //        makeAPicker('--gradient-top');
            //        makeAPicker('--gradient-bottom');
                    makeAPicker('--wtk-blog-header');
                    makeAPicker('--wtk-blog-nav');
                    makeAPicker('--wtk-blog-main');
                    makeAPicker('--wtk-blog-footer');
                    jscolor.trigger('input change');
                    let fncBody = document.getElementById('blogBody');
                    let fncCssObj = window.getComputedStyle(fncBody, null);
                    let fncFont = fncCssObj.getPropertyValue('font-family');
                    let $option = $('#blogFont').children('option[value='+ fncFont +']');
                    $option.attr('selected', true);
                break;
            case 'Writer':
                $('.snote').summernote({
                    callbacks: {
                        onImageUpload: function(files) {
//                          url = $(this).data('upload'); //path is defined as data attribute for textarea
                            let url = 'ajxUploadImg.php';  // WTK method
                            sendFile(files[0], url, $(this));
                        }
                    },
                  toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['codeview', 'help']]]
                });
                if (elementExist('wtkUpload')) {
                    document.getElementById('wtkUpload').addEventListener('change', (e) => {
                        wtkFileChanged();
                    })
                }
                break;
        }
    });
}

function sendFile(file, url, editor) {
    var data = new FormData();
    data.append("file", file);
    var request = new XMLHttpRequest();
    request.open('POST', url, true);
    request.onload = function() {
        if (request.status >= 200 && request.status < 400) {
            // Success!
            var resp = request.responseText;
            editor.summernote('insertImage', resp);
            console.log(resp);
        } else {
            // We reached our target server, but it returned an error
            var resp = request.responseText;
            console.log(resp);
        }
    };
    request.onerror = function(jqXHR, textStatus, errorThrown) {
        // There was a connection error of some sort
        console.log(jqXHR);
    };
    request.send(data);
}

function makeAPicker(fncClass){
    let fncColor = getComputedStyle(document.documentElement).getPropertyValue(fncClass);
    $('#' + fncClass).val(fncColor);
    $('#' + fncClass).addClass('jscolor');
    var myPicker = new JSColor('#' + fncClass, {preset:'dark'});
}

function setCssRoot(fncId,fncColor){
    document.documentElement.style.setProperty(fncId, fncColor);
    /*
    if ((fncId == '--gradient-top') || (fncId == '--gradient-bottom')){
        var fncTopColor = document.getElementById('--gradient-top').value;
        var fncBottomColor = document.getElementById('--gradient-bottom').value;
        document.documentElement.style.setProperty('--gradient-color', 'linear-gradient(to bottom, ' + fncTopColor + ', ' + fncBottomColor + ')');
    } else {
        document.documentElement.style.setProperty(fncId, fncColor);
    }
    */
}

function saveCSS(){
    let fncFormData = $('#cssForm').serialize();
    $.ajax({
        type: "POST",
        url:  'ajxSaveCSS.php',
        data: (fncFormData),
        success: function(data) {
            let fncJSON = $.parseJSON(data);
            switch (fncJSON.result) {
                case 'fileExists':
                    M.toast({html: 'A blog.css file already exists.  Backup or delete then try again.', classes: 'red rounded'});
                    break;
                case 'writeFailed':
                    M.toast({html: 'Writing file failed - check folder permissions.', classes: 'red rounded'});
                    break;
                case 'ok':
                    M.toast({html: 'Your new CSS file has been created.', classes: 'green rounded'});
            }
        }
    })
}

function makeSlug(fncString){
    let fncResult = fncString.replace(/-/g, ' ');
    fncResult = fncResult.replace(/[^a-zA-Z0-9 ]/g, '');
    fncResult = fncResult.replace(/ /g, '-');
    fncResult = fncResult.replace(/--/g, '-');
    fncResult = fncResult.toLowerCase();
    $('#wtkwtkBlogSlug').val(fncResult);
}

function saveBlog(){
    if (elementExist('wtkUpload')) {
        wtkfFileUpload();
    }
    document.getElementById('wtkForm').submit();
}
