var pgFileSizeOK = 'Y';
var pgFileToUpload = 'N';
let pgFileName = '';
let pgFileSize = '';

function wtkFileChanged(){
    let fncFileTest = document.getElementById('wtkUpload').files[0];
    if (fncFileTest) {
       pgFileName = fncFileTest.name;
       pgFileSize = fncFileTest.size;
       let fncMsg = 'file size: ' + formatBytes(pgFileSize);
       if (pgFileSize < gloMaxFileSize){
           pgFileSizeOK = 'Y';
       } else {
           wtkAlert('File is too large to upload<br>Maximum size allowed is ' + formatBytes(gloMaxFileSize) + '<br>File will not be uploaded.');
           pgFileSizeOK = 'N';
           fncMsg = (fncMsg + '<br><a href="https://resizeyourimage.com" target="_blank">(resize your image)</a>');
       }
       if (elementExist('wtkfPhoto') == false) {
           $('#uploadFileSize').html(fncMsg);
       }
       wtkDebugLog('wtkFileChanged: pgFileName = ' + pgFileName + '; pgFileSize = ' + pgFileSize + '; pgFileSizeOK = ' + pgFileSizeOK);
       if (elementExist('wtkwtkFilesDescription')) {
           if ($('#wtkwtkFilesDescription').val() == '') {
               fncFileName = pgFileName.replace(/\.[^/.]+$/, '');
               fncFileName = fncFileName.replaceAll('-',' ');
               fncFileName = fncFileName.replaceAll('_',' ');
               $('#wtkwtkFilesDescription').val(fncFileName);
               $('label[for="wtkwtkFilesDescription"]').addClass('active');
           }
       }
       wtkProcessFile(fncFileTest);
   } else {
       wtkDebugLog('wtkFileChanged: fncFileTest failed');
   }
} // wtkFileChanged

function wtkProcessFile(fncFile) {
    wtkDebugLog('wtkProcessFile');
    // we define fr as a new instance of FileReader
    let fr = new FileReader();
    fr.readAsDataURL(fncFile);
    // Handle progress, success, and errors
    fr.onerror = wtkfErrorHandler;
    fr.onabort = () => wtkChangeStatus('File process aborted!');
    fr.onloadstart = () => wtkChangeStatus('Start Loading');
    fr.onload = wtkLoaded;
    // Here you can perform some operations on the data asynchronously
    fr.onprogress = wtkSetProgress;
} // wtkProcessFile

 // Updates the value of the progress bar
function wtkSetProgress(e) {
    wtkDebugLog('wtkSetProgress');
    if (elementExist('photoProgressDIV')) {
        if ($('#photoProgressDIV').hasClass('hide')) {
            $('#photoProgressDIV').removeClass('hide');
        }
        let loadingPercentage = ((100 * e.loaded) / e.total);
        document.getElementById('photoProgress').style.width = (loadingPercentage + '%');
    }
}

function wtkChangeStatus(fncStatus){
    if (elementExist('uploadStatus')) {
        $('#uploadStatus').text('Upload Progress: ' + fncStatus);
    }
}

function wtkLoaded(e){
    wtkDebugLog('wtkLoaded top');
    wtkChangeStatus('File load complete');
    pgFileToUpload = 'Y';
//  console.log(e);
    let fr = e.target;
    var fncImg = fr.result;
    if (elementExist('imgPreview')) {
        if (pgFileSize < gloMaxFileSize){
            pgFileSizeOK = 'Y';
            let fncShowImg = document.querySelector('#imgPreview');
            fncShowImg.src = fncImg;
        }
        if (elementExist('wtkfRefresh')) {
            let fncRefresh = $('#wtkfRefresh').val();
            if (fncRefresh != ''){
                wtkDebugLog('wtkfLoaded: fncRefresh = ' + fncRefresh);
                if (elementExist(fncRefresh)) {
                    let fncShowImg2 = document.querySelector('#' + fncRefresh);
                    fncShowImg2.src = fncImg;
                }
            }
        }
    } else {
        if (elementExist('filePreview')) {
            let fncShowFile = document.querySelector('#filePreview');
            document.getElementById('filePreview').removeAttribute('onclick');
            fncShowFile.href = fncImg;
            if ($('#filePreview').hasClass('hide')) {
                $('#filePreview').removeClass('hide');
            }
        }
    }
    if (elementExist('phoneSideBar')) {
        let elems = document.querySelectorAll('.sidenav');
        M.Sidenav.init(elems, {edge:'right'});
    }
    if (pgFileSize < gloMaxFileSize){
        if (elementExist('wtkfPhoto')) {
            if ($('#wtkfPhotoDIV').hasClass('hide')) {
                $('#wtkfPhotoDIV').removeClass('hide');
            }
            if ($('#wtkfSaveBtn').hasClass('hide')) {
                $('#wtkfSaveBtn').removeClass('hide');
            }
            if ($('#wtkf2btns').hasClass('hide')) {
                $('#wtkf2btns').removeClass('hide');
            }
        }
    }
    if (elementExist('photoProgressDIV')) {
        document.getElementById('photoProgress').style.width = '100%';
        $('#photoProgressDIV').fadeOut(720);
    }
    $('#uploadStatus').fadeOut(720);
    if (elementExist('wtkfUploadBtn')) {
        $('#wtkfUploadBtn').removeClass('hide');
    }
    setTimeout(function() {
        $('#uploadStatus').text('');
        if (elementExist('photoProgressDIV')) {
            document.getElementById('photoProgress').style.width = '0%';
            $('#photoProgressDIV').addClass('hide');
            $('#photoProgressDIV').fadeIn(1);
        }
        $('#uploadStatus').fadeIn(1);
        if (elementExist('wtkfPhoto')) {
            $('#wtkfDelBtn').addClass('hide');
        } else {
            let fncPastImg = $('#wtkfOrigPhoto').val();
            if (fncPastImg != '/wtk/imgs/noPhotoSmall.gif') {
                if ($('#wtkfDelBtn').hasClass('hide')) {
                    $('#wtkfDelBtn').removeClass('hide');
                }
            }
        }
    }, 720);
} // wtkLoaded

function wtkfErrorHandler(e){
    wtkDebugLog('wtkfErrorHandler top');
    wtkfChangeStatus("Error: " + e.target.error.name);
}

function wtkfFileUpload(fncFormId = '', fncId = '') {
   wtkDebugLog('wtkfFileUpload: fncFormId = ' + fncFormId + '; fncId = ' + fncId);
   let fr = new FileReader();
   let xhr = new XMLHttpRequest();

   if (elementInFormExist(fncFormId,'wtkfPhoto') == false) {
       xhr.upload.addEventListener("progress", function(e) {
           if (e.lengthComputable) {
               let percentage = Math.round((e.loaded * 100) / e.total);
               $('#uploadProgress').text(percentage);
           }
       }, false);

       xhr.upload.addEventListener("load", function(e) {
           $('#uploadProgress').text(100);
       }, false);
   }

   xhr.open('POST', '/wtk/fileUpload.php');
   xhr.overrideMimeType('text/plain; charset=x-user-defined-binary');
   //  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

   let fncRefresh = $('#wtkfRefresh').val();
   xhr.onreadystatechange = function() {
       if (elementInFormExist(fncFormId,'wtkfUploadBtn')) {
          $('#uploadFileDIV').addClass('hide');
          if (elementInFormExist(fncFormId,'uploadFileBtn')) {
             $('#uploadFileBtn').removeClass('hide');
          }
       }

       if (this.readyState == 4 && this.status == 200) {
           pgFileToUpload = 'N';
           if (elementInFormExist(fncFormId,'wtkfRefreshDIV')) {
               let fncRefreshDIV = $('#wtkfRefreshDIV').val();
               if (fncRefreshDIV != ''){
                   let fncParameter = 'photos';
                   if (elementInFormExist(fncFormId,'imgDescription')) {
                       fncParameter = $('#imgDescription').val();
                   }
                   ajaxFillDiv(fncRefreshDIV,fncParameter,'displayFileDIV',$('#ID1').val());
                   $('#wtkUpload').val();
               }
               if (elementInFormExist(fncFormId,'wtkfUploadBtn')) {
                   $('#wtkfUploadBtn').addClass('hide');
               }
           }
           if (fncRefresh != ''){
               wtkDebugLog('fncRefresh = ' + fncRefresh);
               let fncJSON = JSON.parse(this.responseText);
               let fncImg = fncJSON.path + fncJSON.fileName;
               if (elementInFormExist(fncFormId,fncRefresh)) {
                   let fncShowImg = document.querySelector('#' + fncRefresh);
                   fncShowImg.src = fncImg;
               }
               if (elementInFormExist(fncFormId,'wtkfPhoto')) {
                  $('#wtkfOrigPhoto').val(fncImg);
               }
           }
       }
   };

   fr.onload = function(evt) {
       wtkDebugLog('wtkfFileUpload fr.onload fncId = ' + fncId);
       let fncPath = $('#wtkfColPath' + fncId).val();
       if (typeof pgMPAvsSPA === 'undefined') {
           var pgMPAvsSPA = 'MPA';
       }

       let data={};
       if (elementInFormExist(fncFormId,'imgMode')) {
           data.mode = wtkGetValue('imgMode', fncFormId);
       } else {
           data.mode = wtkGetValue('wtkMode', fncFormId);
       }
       if (elementInFormExist(fncFormId,'imgTable')) {
           data.t = wtkGetValue('imgTable', fncFormId);
       } else {
           data.t = wtkGetValue('T', fncFormId);
       }
       if (elementInFormExist(fncFormId,'tabRel')) {
           data.tabRel = wtkGetValue('tabRel', fncFormId);
       } else {
           data.tabRel = '';
       }
       data.colPath = fncPath;
       data.wtkDesign = pgMPAvsSPA;
       data.colFile = wtkGetValue('wtkfColFile' + fncId, fncFormId);
       data.uid = wtkGetValue('UID', fncFormId);
       if (elementInFormExist(fncFormId,'UserUID')) {
           data.userUID = wtkGetValue('UserUID', fncFormId);
       } else {
           data.userUID = '';
       }
       if (elementInFormExist(fncFormId,'ParentUID')) {
           data.parentUID = wtkGetValue('ParentUID', fncFormId);
       }
       if (elementInFormExist(fncFormId,'imgDescription')) {
           data.imgDescription = wtkGetValue('imgDescription', fncFormId);
       }
       data.id = wtkGetValue('ID1', fncFormId);
       data.path = wtkGetValue('wtkfPath' + fncId, fncFormId);
       data.fileName = pgFileName;
       data.del = wtkGetValue('wtkfDelete' + fncId, fncFormId);
       data.file = evt.target.result;
       data.s = gloWtkApiKey;
       if ((typeof pgApiKey === 'undefined') || (pgApiKey == '')){
           const wtkParams = new URLSearchParams(window.location.search);
           if (wtkParams.has('apiKey')) {
               pgApiKey = wtkParams.get('apiKey');
               wtkDebugLog('wtkfFileUpload: pgApiKey = ' + pgApiKey);
           } else {
               pgApiKey = '';
           }
       }
       data.apiKey = pgApiKey;
       let string = JSON.stringify(data);
       xhr.send(string);
   };

   let upFile = document.getElementById('wtkUpload').files[0];
   if (typeof upFile !== 'undefined') {
      fr.readAsDataURL(upFile);
   }
} // wtkfFileUpload

function wtkfDelFile(fncId) {
    let fncTbl = wtkGetValue('wtkfTable' + fncId);
    let fncColPath = wtkGetValue('wtkfColPath' + fncId);
    let fncColFile = wtkGetValue('wtkfColFile' + fncId);
    let fncUID = wtkGetValue('wtkfUID' + fncId);
    let fncPath = wtkGetValue('wtkfPath' + fncId);
    let fncDel = wtkGetValue('wtkfDelete' + fncId);
    if (typeof pgApiKey === 'undefined') {
        pgApiKey = '';
    }
    $.ajax({
        type: "POST",
        url:  '/wtk/ajxDelFile.php',
        data: { apiKey: pgApiKey, colPath: fncColPath, colFile: fncColFile, t: fncTbl,
                uid: fncUID, id: fncId, path: fncPath, del: fncDel, wtkDesign: pgMPAvsSPA},
        success: function(data) {
            if (elementExist('imgPreview')) {
                var fncShowImg = document.querySelector('#imgPreview');
                fncShowImg.src = '/wtk/imgs/noPhotoSmall.gif';
            } else {
                if (elementExist('filePreview')) {
                    var fncShowFile = document.querySelector('#filePreview');
                    fncShowFile.href = '';
                    $('#filePreview').addClass('hide');
                }
            }
            $('#wtkfDelBtn').addClass('hide');
            $('#wtkfAddBtn').removeClass('hide');
            if (elementExist('wtkfPhoto')) {
                $('#wtkfPhotoDIV').addClass('hide');
                $('#wtkf2btns').removeClass('hide');
            }
            if (elementExist('wtkfOrigPhoto')) {
                $('#wtkfOrigPhoto').val('/wtk/imgs/noPhotoSmall.gif');
            }
            pgFileToUpload = 'N';
            $('#uploadFileSize').text('');
            if (elementExist('FileUploaded')) {
                $('#FileUploaded').val('N');
            }
        }
    })
} // wtkfDelFile

function wtkfDelFileOld(fncFormId) {
    let fncTbl = wtkGetValue('T', fncFormId);
    let fncColPath = wtkGetValue('wtkfColPath', fncFormId);
    let fncColFile = wtkGetValue('wtkfColFile', fncFormId);
    let fncUID = wtkGetValue('UID', fncFormId);
    let fncId = wtkGetValue('ID1', fncFormId);
    let fncPath = wtkGetValue('wtkfPath', fncFormId);
    let fncDel = wtkGetValue('wtkfDelete', fncFormId);
    if (typeof pgApiKey === 'undefined') {
        pgApiKey = '';
    }
    $.ajax({
        type: "POST",
        url:  '/wtk/ajxDelFile.php',
        data: { apiKey: pgApiKey, colPath: fncColPath, colFile: fncColFile, t: fncTbl,
                uid: fncUID, id: fncId, path: fncPath, del: fncDel, wtkDesign: pgMPAvsSPA},
        success: function(data) {
            if (elementExist('imgPreview')) {
                var fncShowImg = document.querySelector('#imgPreview');
                fncShowImg.src = '/wtk/imgs/noPhotoSmall.gif';
            } else {
                if (elementExist('filePreview')) {
                    var fncShowFile = document.querySelector('#filePreview');
                    fncShowFile.href = '';
                    $('#filePreview').addClass('hide');
                }
            }
            $('#wtkfDelBtn').addClass('hide');
            $('#wtkfAddBtn').removeClass('hide');
            if (elementExist('wtkfPhoto')) {
                $('#wtkfPhotoDIV').addClass('hide');
                $('#wtkf2btns').removeClass('hide');
            }
            if (elementExist('wtkfOrigPhoto')) {
                $('#wtkfOrigPhoto').val('/wtk/imgs/noPhotoSmall.gif');
            }
            pgFileToUpload = 'N';
            $('#uploadFileSize').text('');
            if (elementExist('FileUploaded')) {
                $('#FileUploaded').val('N');
            }
        }
    })
} // wtkfDelFileOld

function wtkfRevertImg() {
    let fncShowImg = document.querySelector('#imgPreview');
    let fncPastImg = $('#wtkfOrigPhoto').val();
    fncShowImg.src = fncPastImg;
    if (fncPastImg == '/wtk/imgs/noPhotoSmall.gif') {
        $('#wtkfPhotoDIV').addClass('hide');
    } else {
        $('#wtkf2btns').addClass('hide');
        $('#wtkfAddBtn').addClass('hide');
        $('#wtkfDelBtn').removeClass('hide');
    }
    pgFileToUpload = 'N';
} // wtkfRevertImg

function wtkfPhoto(fncFormId) {
    // used for single-click upload without needing full form submission
    // called from PHP function: wtkFileUpload
    wtkfFileUpload(fncFormId);
    $('#wtkfPhotoDIV').addClass('hide');
    pgFileToUpload = 'N';
}

function wtkShowImageUpload(){
    $('#uploadFileBtn').addClass('hide');
    $('#uploadFileDIV').removeClass('hide');
    $('#uploadFileSize').text('');
    $('#uploadProgress').text('');
    if (elementExist('imgPreview')) {
        var fncShowImg = document.querySelector('#imgPreview');
        fncShowImg.src = '/wtk/imgs/noPhotoSmall.gif';
    }
}// used in conjunction with wtkFormFile PHP function
