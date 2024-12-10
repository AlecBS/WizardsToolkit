// These functions are used to import CSV files by pages in the /admin folder
var gloImportObject = {};
var gloCsvArray = [];

// admin/ajxImportData.php
function makeProspectStaff(){
    waitLoad('on');
    $.ajax({
        type: 'POST',
        url: 'ajxImportData.php',
        data: { apiKey: pgApiKey, step: 'prospectStaff' },
        success: function(data) {
            waitLoad('off');
            $('#successMsg').html('<h4>Prospect Staff generated</h4>');
            $('#runScriptBtn').addClass('hide');
        }
    })
}

// admin/tableColumns.php
function csvFileUpload(fncFormId,fncTableName){
    $('#tableDef').addClass('hide');
    $('#csvDIV').removeClass('m6');
    $('#wtkForm').addClass('hide');
    wtkfFileUpload('wtkForm',fncTableName);
    $('#columnList').addClass('hide');
}

function showLinks() {
    let fncLinks = document.querySelectorAll('.hidden-link');
    fncLinks.forEach(function(fncLink) {
        fncLink.style.display = 'block';
    });
}

function mapCSVcolumns(fncFileName, fncHasMatches){
    $('#csvDIV').addClass('m6');
    $('#tableDef').removeClass('hide');
    $('#mappingDIV').removeClass('hide');
    $('#columnList').removeClass('hide');
    $('#displayFileDIV').addClass('hide');
    $('#csvColCard').html($('#csvColumns').html());
    if (fncHasMatches == 'Y'){
        $('#importBtn').removeClass('hide');
    }
    showLinks();
}

function importDropName(fncColName){
    gloImportObject[fncColName] = pgFromDragId;
    $('#' + fncColName + 'Link').addClass('hide');
    $('#importBtn').removeClass('hide');
    showMappings();
}

function wtkImport(fncStep) {
    if (gloImportObject) {
        let fncColMap = JSON.stringify(gloImportObject)
        let fncCSVfile = $('#csvFileLocation').val();
        let fncTableName = $('#TableName').val();
        $('#mappingDIV').addClass('hide');
        $('#dataHeader').addClass('hide');
        $('#csvHeader').addClass('hide');
        waitLoad('on');
        $.ajax({
            type: 'POST',
            url: 'ajxImportData.php',
            data: { apiKey: pgApiKey, step: fncStep, tableName: fncTableName,
                    csvFile: fncCSVfile, colMap: fncColMap},
            success: function(data) {
                waitLoad('off');
                $('#tableDef').addClass('hide');
                $('#csvDIV').addClass('hide');
                $('#verifyImport').html(data);
                $('#verifyImport').removeClass('hide');
            }
        })
    }
}

function unLinkCSV2Table(fncColName){
    // remove from object then wipe out and rebuild mappingCSVul
    delete gloImportObject[fncColName];
    $('#' + fncColName + 'Link').removeClass('hide');
    showMappings();
}

function showMappings(){
   let $fncUL = $('#mappingCSVul');
   $fncUL.empty(); // Clear existing list items
   let fncTmp = '';
   let fncLIstart = '<li class="collection-item"><table class="table-basic" width="100%"><tr><td width="35%" class="center">';
   let fncLImiddle = '</td><td width="20%" class="center"><i class="material-icons small btn light-blue white-text" style="padding-top:4px">compare_arrows</i></td>';
   fncLImiddle += '<td width="35%" class="center"">';
   let fncLImid2 = '</td><td width="10%"><a onclick="JavaScript:unLinkCSV2Table(\'';
   let fncLIend = '\')" class="secondary-content"><i class="material-icons red-text small">remove_circle</i></a></div></li></td></tr></li>';
   $.each(gloImportObject, function(key, value) {
       fncTmp = fncLIstart + key + fncLImiddle + gloCsvArray[value] + fncLImid2 + key + fncLIend;
       $fncUL.append(fncTmp);
   });
}
