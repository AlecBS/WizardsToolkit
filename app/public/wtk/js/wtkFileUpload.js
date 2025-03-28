var pgFileSizeOK = 'Y';
var pgFileToUpload = 'N';
var pgFormId = '';
let pgFileName = '';
let pgFileSize = '';

function wtkFileChanged(fncFormId = ''){
    wtkDebugLog('wtkFileChanged top of function');
    pgFormId = fncFormId; // used for Progress updating
    let fncFileTest = document.getElementById('wtkUpload' + fncFormId).files[0];
    if (fncFileTest) {
       pgFileName = fncFileTest.name;
       $('#wtkfOrigName' + fncFormId).val(pgFileName);
       pgFileSize = fncFileTest.size;
       let fncMsg = 'file size: ' + formatBytes(pgFileSize);
       if (pgFileSize < gloMaxFileSize){
           pgFileSizeOK = 'Y';
       } else {
           wtkAlert('File is too large to upload<br>Maximum size allowed is ' + formatBytes(gloMaxFileSize) + '<br>File will not be uploaded.');
           pgFileSizeOK = 'N';
           fncMsg = (fncMsg + '<br><a href="https://resizeyourimage.com" target="_blank">(resize your image)</a>');
       }
       if (elementExist('wtkfPhoto' + fncFormId) == false) {
           $('#uploadFileSize' + fncFormId).html(fncMsg);
       }
       wtkDebugLog('wtkFileChanged: pgFileName = ' + pgFileName + '; pgFileSize = ' + pgFileSize + '; pgFileSizeOK = ' + pgFileSizeOK);
       if (elementExist('wtkwtkFilesDescription')) {
           if ($('#wtkwtkFilesDescription').val() == '') {
               fncFileName = pgFileName.replace(/\.[^/.]+$/, '');
               fncFileName = fncFileName.replaceAll('-',' ');
               fncFileName = fncFileName.replaceAll('_',' ');
               $('#wtkwtkFilesDescription').val(addSpacesBeforeCaps(fncFileName));
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
    wtkDebugLog('wtkSetProgress with pgFormId = ' + pgFormId);
    if (elementExist('photoProgressDIV' + pgFormId)) {
        if ($('#photoProgressDIV' + pgFormId).hasClass('hide')) {
            $('#photoProgressDIV' + pgFormId).removeClass('hide');
        }
        let fncLoadingPercentage = ((100 * e.loaded) / e.total);
        document.getElementById('photoProgress' + pgFormId).style.width = (fncLoadingPercentage + '%');
    }
}

function wtkChangeStatus(fncStatus, fncFormId = ''){
    if (fncFormId == '') {
        fncFormId = pgFormId;
    }
    if (elementExist('uploadStatus' + fncFormId)) {
        $('#uploadStatus' + fncFormId).text('Upload Progress: ' + fncStatus);
    }
}

function wtkLoaded(e){
    wtkDebugLog('wtkLoaded top: pgFormId = ' + pgFormId);
    wtkChangeStatus('File load complete');
    pgFileToUpload = 'Y';
//  console.log(e);
    let fr = e.target;
    var fncImg = fr.result;
    if (elementExist('imgPreview' + pgFormId)) {
        if (pgFileSize < gloMaxFileSize){
            pgFileSizeOK = 'Y';
            var fncShowImg = document.querySelector('#imgPreview' + pgFormId);
            fncShowImg.src = fncImg;
            wtkDebugLog('wtkLoaded: Size OK: pgFileSize = ' + pgFileSize + '; gloMaxFileSize = ' + gloMaxFileSize);
        } else {
            wtkDebugLog('wtkLoaded: too Big: pgFileSize = ' + pgFileSize + '; gloMaxFileSize = ' + gloMaxFileSize);
        }
        if (elementExist('wtkfRefresh' + pgFormId)) {
            let fncRefresh = $('#wtkfRefresh' + pgFormId).val();
            if (fncRefresh != ''){
                wtkDebugLog('wtkfLoaded: fncRefresh = ' + fncRefresh);
                if (elementExist(fncRefresh)) {
                    let fncShowImg2 = document.querySelector('#' + fncRefresh);
                    fncShowImg2.src = fncImg;
                }
            }
        }
    } else {
        if (elementExist('filePreview' + pgFormId)) {
            let fncShowFile = document.querySelector('#filePreview' + pgFormId);
            document.getElementById('filePreview' + pgFormId).removeAttribute('onclick');
            fncShowFile.href = fncImg;
            if ($('#filePreview' + pgFormId).hasClass('hide')) {
                $('#filePreview' + pgFormId).removeClass('hide');
            }
        }
    }
    if (elementExist('phoneSideBar')) {
        let elems = document.querySelectorAll('.sidenav');
        M.Sidenav.init(elems, {edge:'right'});
    }
    if (pgFileSize < gloMaxFileSize){
        if (elementExist('wtkfPhoto')) { // used by wtkFileUpload
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
        if (elementExist('wtkfUploadBtn' + pgFormId)) {
            $('#wtkfUploadBtn' + pgFormId).removeClass('hide');
        }
    }
    if (elementExist('photoProgressDIV' + pgFormId)) {
        document.getElementById('photoProgress' + pgFormId).style.width = '100%';
        $('#photoProgressDIV' + pgFormId).fadeOut(720);
    }
    $('#uploadStatus' + pgFormId).fadeOut(720);
    if (elementExist('wtkfUploadBtn' + pgFormId)) {
        $('#wtkfUploadBtn' + pgFormId).removeClass('hide');
    }
    setTimeout(function() {
        $('#uploadStatus' + pgFormId).text('');
        if (elementExist('photoProgressDIV' + pgFormId)) {
            document.getElementById('photoProgress' + pgFormId).style.width = '0%';
            $('#photoProgressDIV' + pgFormId).addClass('hide');
            $('#photoProgressDIV' + pgFormId).fadeIn(1);
        }
        $('#uploadStatus' + pgFormId).fadeIn(1);
        if (elementExist('wtkfPhoto' + pgFormId)) {
            $('#wtkfDelBtn' + pgFormId).addClass('hide');
            wtkDebugLog('wtkLoaded hiding wtkfDelBtn' + pgFormId);
        } else {
            let fncPastImg = $('#wtkfOrigPhoto' + pgFormId).val();
            if (fncPastImg != '/wtk/imgs/noPhotoSmall.gif') {
                if (pgFileSize < gloMaxFileSize){
                    if ($('#wtkfDelBtn' + pgFormId).hasClass('hide')) {
                        $('#wtkfDelBtn' + pgFormId).removeClass('hide');
                        wtkDebugLog('wtkLoaded removeClass(hide) for wtkfDelBtn' + pgFormId);
                    } else {
                        wtkDebugLog('wtkLoaded NOT removeClass(hide) because hasClass wtkfDelBtn' + pgFormId);
                    }
                }
            } else {
                wtkDebugLog('wtkLoaded NOT removeClass(hide) because noPhotoSmall.gif for wtkfDelBtn' + pgFormId);
            }
        }
    }, 720);
} // wtkLoaded

function wtkfErrorHandler(e){
    wtkDebugLog('wtkfErrorHandler top');
    wtkfChangeStatus("Error: " + e.target.error.name);
}

async function wtkfUploadFile(fncId) {
  wtkDebugLog('wtkfUploadFile top: ' + fncId);
  wtkDisableBtn('wtkfBtn' + fncId); // will re-enable 3600
  /*
  if value in wtkfRefresh+fncId is the id of an <img> on the page, it will be filled with image;
 otherwise it will be assumed to be a PHP page to refresh the displayFileDIV with file list after upload
  */
  if (elementExist('wtkfRefresh' + fncId)) { // update image on website
      let fncRefresh = $('#wtkfRefresh' + fncId).val();
      if (fncRefresh != '') {
          wtkDebugLog('wtkfLoaded: fncRefresh = ' + fncRefresh);
          if (elementExist(fncRefresh)) {
              let imageData = $('#imgPreview' + fncId).attr('src');
              let fncShowImg2 = document.querySelector('#' + fncRefresh);
              fncShowImg2.src = imageData;
          }
      }
  }
  let fncOrigName = $('#wtkfOrigName' + fncId).val();
  if (fncOrigName != '') { // not empty, so must have chosen a file to upload
      let fncFileData = document.getElementById('wtkUpload' + fncId).files[0];
      if (fncFileData) {
          let fncReader = new FileReader();
          fncReader.onload = function(event) {
              fncFileData = event.target.result;
              wtkfPostFile(fncId, fncFileData);
          };
          // Read the file readAsText (or use readAsDataURL for binary data)
          fncFileData = fncReader.readAsDataURL(fncFileData);
      }      // if (!imageData) {
  } // fncOrigName != ''
} // wtkfUploadFile

async function wtkfPostFile(fncId, fncFileData) {
  const fileName = wtkGetValue('wtkfOrigName' + fncId);
  wtkDebugLog('wtkfPostFile top: fncId = ' + fncId + '; fileName:' + fileName);
  wtkChangeStatus('Uploading...', fncId);
  // generate post data
  const id = wtkGetValue('wtkfID' + fncId);
  const uid = wtkGetValue('wtkfUID' + fncId);
  const tableName = wtkGetValue('wtkfTable' + fncId);
  const path = wtkGetValue('wtkfPath' + fncId);
  const mode = wtkGetValue('wtkfMode' + fncId);
  const colPath = wtkGetValue('wtkfColPath' + fncId);
  const colFile = wtkGetValue('wtkfColFile' + fncId);
  wtkDebugLog('wtkfPostFile: pgApiKey: ' + pgApiKey + '; fncFileData:' + fncFileData);

  try {
    const response = await fetch('/wtk/fileUpload.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        apiKey: pgApiKey,
        s: gloWtkApiKey,
        t: tableName,
        uid,
        id,
        path,
        mode,
        colPath,
        colFile,
        file: fncFileData,
        fileName: fileName,
        fileSize: pgFileSize,
        del: '',
        userUID: '',
        tabRel: '',
      }),
    });

    const result = await response.json();
    wtkChangeStatus('Upload Complete',fncId); //  + JSON.stringify(result)
  } catch (error) {
    wtkDebugLog('wtkfPostFile: Upload Failed: ' + error.message);
    wtkChangeStatus('Upload Failed: ' + error.message, fncId);
  }
} // wtkfPostFile

function wtkfFileUpload(fncFormId = '', fncId = '') {
   wtkDebugLog('wtkfFileUpload: fncFormId = ' + fncFormId + '; fncId = ' + fncId);
   if (elementInFormExist(fncFormId,'wtkfDelBtn' + fncId)) {
       $('#wtkfDelBtn' + fncId).addClass('hide');
   }
   let fr = new FileReader();
   let xhr = new XMLHttpRequest();

   if (elementInFormExist(fncFormId,'wtkfPhoto' + fncId) == false) {
       xhr.upload.addEventListener("progress", function(e) {
           if (e.lengthComputable) {
               let percentage = Math.round((e.loaded * 100) / e.total);
               $('#uploadProgress' + fncFormId).text(percentage);
           }
       }, false);

       xhr.upload.addEventListener("load", function(e) {
           $('#uploadProgress' + fncId).text(100);
       }, false);
   }

   xhr.open('POST', '/wtk/fileUpload.php');
   xhr.overrideMimeType('text/plain; charset=x-user-defined-binary');
   //  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

   let fncRefresh = $('#wtkfRefresh' + fncId).val();
   xhr.onreadystatechange = function() {
       if (elementInFormExist(fncFormId,'wtkfUploadBtn' + fncId)) {
          $('#uploadFileDIV' + fncId).addClass('hide');
          if (elementInFormExist(fncFormId,'uploadFileBtn' + fncId)) {
             $('#uploadFileBtn' + fncId).removeClass('hide');
          }
       }

       if (this.readyState == 4 && this.status == 200) {
           pgFileToUpload = 'N';
//         if (elementInFormExist(fncFormId,'wtkfRefresh' + fncFormId + 'DIV')) {
           if (elementInFormExist(fncFormId,'wtkfRefresh' + fncId)) {
               let fncRefreshDIV = $('#wtkfRefresh' + fncId).val();
               if (fncRefreshDIV != ''){
                   let fncParameter = 'photos';
                   if (elementInFormExist(fncFormId,'imgDescription')) {
                       fncParameter = $('#imgDescription').val();
                   }
                   ajaxFillDiv(fncRefreshDIV,fncParameter,'displayFileDIV' + fncId,$('#ID1').val());
                   $('#wtkUpload' + fncId).val();
               }
               if (elementInFormExist(fncFormId,'wtkfUploadBtn' + fncId)) {
                   $('#wtkfUploadBtn' + fncId).addClass('hide');
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
               if (elementInFormExist(fncFormId,'wtkfPhoto' + fncId)) {
                  $('#wtkfOrigPhoto' + fncId).val(fncImg);
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
       data.fileSize = pgFileSize;
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

   let upFile = document.getElementById('wtkUpload' + fncId).files[0];
   if (typeof upFile !== 'undefined') {
      fr.readAsDataURL(upFile);
   }
} // wtkfFileUpload

function wtkfDelFile(fncId, fncFormId = '') {
    $('#wtkfUploadBtn' + fncFormId).addClass('hide');
    let fncTbl = wtkGetValue('wtkfTable' + fncFormId);
    let fncColPath = wtkGetValue('wtkfColPath' + fncFormId);
    let fncColFile = wtkGetValue('wtkfColFile' + fncFormId);
    let fncUID = wtkGetValue('wtkfUID' + fncFormId);
    let fncPath = wtkGetValue('wtkfPath' + fncFormId);
    let fncDel = wtkGetValue('wtkfDelete' + fncFormId);
    if (typeof pgApiKey === 'undefined') {
        pgApiKey = '';
    }
    $.ajax({
        type: "POST",
        url:  '/wtk/ajxDelFile.php',
        data: { apiKey: pgApiKey, colPath: fncColPath, colFile: fncColFile, t: fncTbl,
                uid: fncUID, id: fncId, path: fncPath, del: fncDel, wtkDesign: pgMPAvsSPA},
        success: function(data) {
            if (elementExist('imgPreview' + fncFormId)) {
                var fncShowImg = document.querySelector('#imgPreview' + fncFormId);
                fncShowImg.src = '/wtk/imgs/noPhotoSmall.gif';
            } else {
                if (elementExist('filePreview' + fncFormId)) {
                    var fncShowFile = document.querySelector('#filePreview' + fncFormId);
                    fncShowFile.href = '';
                    $('#filePreview' + fncFormId).addClass('hide');
                }
            }
            $('#wtkfDelBtn' + fncFormId).addClass('hide');
            $('#wtkfAddBtn' + fncFormId).removeClass('hide');
            if (elementExist('wtkfPhoto')) {
                $('#wtkfPhotoDIV').addClass('hide');
                $('#wtkf2btns').removeClass('hide');
            }
            if (elementExist('wtkfOrigPhoto')) {
                $('#wtkfOrigPhoto').val('/wtk/imgs/noPhotoSmall.gif');
            }
            pgFileToUpload = 'N';
            $('#uploadFileSize' + fncFormId).text('');
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
            if (elementExist('imgPreview' + fncFormId)) {
                var fncShowImg = document.querySelector('#imgPreview' + fncFormId);
                fncShowImg.src = '/wtk/imgs/noPhotoSmall.gif';
            } else {
                if (elementExist('filePreview' + fncFormId)) {
                    var fncShowFile = document.querySelector('#filePreview' + fncFormId);
                    fncShowFile.href = '';
                    $('#filePreview' + fncFormId).addClass('hide');
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
    let fncPastImg = $('#wtkfOrigPhoto').val();
    if (elementExist('imgPreview' + pgFormId)) {
        let fncShowImg = document.querySelector('#imgPreview' + pgFormId);
        fncShowImg.src = fncPastImg;
    }
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

function wtkShowImageUpload(fncId = ''){
    $('#uploadFileBtn' + fncId).addClass('hide');
    $('#uploadFileDIV' + fncId).removeClass('hide');
    $('#uploadFileSize' + fncId).text('');
    $('#uploadProgress' + fncId).text('');
    if (elementExist('imgPreview' + fncId)) {
        var fncShowImg = document.querySelector('#imgPreview' + fncId);
        fncShowImg.src = '/wtk/imgs/noPhotoSmall.gif';
    }
}// used in conjunction with wtkFormFile PHP function
