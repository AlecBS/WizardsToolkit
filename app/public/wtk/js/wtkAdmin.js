// These functions are used by files in the /admin folder

// widgetEdit
function widgetTypeChanged(fncValue){
    switch (fncValue){
        case 'List':
            $('#chartTypeDIV').addClass('hide');
            $('#colorDIV').addClass('hide');
            $('#skipFooterDIV').removeClass('hide');
            $('#skipSQLDIV').removeClass('hide');
            break;
        case 'Link':
            $('#skipFooterDIV').addClass('hide');
            $('#colorDIV').addClass('hide');
            $('#chartTypeDIV').addClass('hide');
            $('#skipSQLDIV').addClass('hide');
            break;
        case 'Chart':
            $('#skipFooterDIV').addClass('hide');
            $('#colorDIV').addClass('hide');
            $('#chartTypeDIV').removeClass('hide');
            $('#skipSQLDIV').removeClass('hide');
            break;
        default: // Count
            $('#chartTypeDIV').addClass('hide');
            $('#skipFooterDIV').addClass('hide');
            $('#colorDIV').removeClass('hide');
            $('#skipSQLDIV').removeClass('hide');
            break;
    } // switch
} // widgetTypeChanged
// widgetEdit
function showHideLinkOptions(){
    let fncLink = $('#wtkwtkWidgetWidgetURL').val();
    if (fncLink != ''){
        $('#PassRNGDIV').removeClass('hide');
        $('#WindowModalDIV').removeClass('hide');
    } else {
        $('#PassRNGDIV').addClass('hide');
        $('#WindowModalDIV').addClass('hide');
    }
}

// moneyStats
function revenueRptFilter(){
    let fncFormData = $('#dateRngForm').serialize();
    fncFormData = fncFormData + '&apiKey=' + pgApiKey;
    $('#payChartSPAN').text('');
    $('#provChartSPAN').text('');

    $.ajax({
        type: "POST",
        url: '/admin/moneyStats.php?TableID=payChart',
        data: (fncFormData),
        success: function(data) {
            $('#payChartSPAN').html(data);
        }
    })
    $.ajax({
        type: "POST",
        url: '/admin/moneyStats.php?TableID=providerChart',
        data: (fncFormData),
        success: function(data) {
            $('#provChartSPAN').html(data);
        }
    })
    $('#payFilterMsg').html('&nbsp;');
}

// promoPlanEdit
function showCodeMaker(){
    $('#codeMaker').removeClass('hide');
    $('#showBtn').addClass('hide');
}
// promoPlanEdit
function generatePromoCodes(){
    var fncId = $('#ID1').val();
    var fncQty = $('#qty').val();
    var fncCodeLength = $('#codeLength').val();
    $.ajax({
        type: "POST",
        url:  'ajxGenPromoCodes.php',
        data: { apiKey: pgApiKey, id: fncId, qty: fncQty, codeLength: fncCodeLength },
            success: function(data) {
                $('#codeMaker').addClass('hide');
                $('#showBtn').removeClass('hide');
                let fncMsgDiv = document.getElementById('theCodes');
                fncMsgDiv.innerHTML = data;
            }
    })

}

// wtkBuilder
function ajaxWTKbuild() {
    let fncBrFile = $('#wtkRFBBrPHPfilename').val();
    let fncUpFile = $('#wtkRFBUpPHPfilename').val();
//    if ((fncBrFile == undefined) && (fncUpFile == undefined)){
    if ((fncBrFile == '') && (fncUpFile == '')){
        wtkAlert('Enter the name of the List and/or Form PHP page you wish to create.');
    } else {
        let fncFormData = $('#wtkBuild').serialize();
        fncFormData = fncFormData + '&apiKey=' + pgApiKey ;
        waitLoad('on');
        $.ajax({
            type: 'POST',
            url:  'wtkBuilder.php',
            data: (fncFormData),
            success: function(data) {
                M.toast({html: 'Your file has been created!', classes: 'rounded'});
                $('#buildMsg').html(data);
                waitLoad('off');
                wtkFixSideNav();
            }
        })
    }
} // ajaxWTKbuild

// reportEdit
function hideRptFields(fncValue){
    if (fncValue == ''){
        $('#RptSelectDIV').removeClass('hide');
        $('#filterOpsDIV').removeClass('hide');
        $('#browseAffectsDIV').removeClass('hide');
        $('#chartOpsDIV').removeClass('hide');
        $('#dateFilterDIV').removeClass('hide');
    } else {
        $('#RptSelectDIV').addClass('hide');
        $('#filterOpsDIV').addClass('hide');
        $('#browseAffectsDIV').addClass('hide');
        $('#chartOpsDIV').addClass('hide');
        $('#dateFilterDIV').addClass('hide');
    }
}
function showHideChartTypes(fncValue){
    if ($('#wtkwtkReportsGraphRpt').is(':checked')) {
        $('#chartSupressDIV').removeClass('hide');
        $('#chartTypeDIV').removeClass('hide');
    } else {
        $('#chartSupressDIV').addClass('hide');
        $('#chartTypeDIV').addClass('hide');
    }
}

// sendEmail
function showEmailOrUser(){
    if ($('#UserOrEmail').is(':checked')) {
        $('#UserUIDDIV').addClass('hide');
        $('#ToEmailDIV').removeClass('hide');
    } else {
        $('#UserUIDDIV').removeClass('hide');
        $('#ToEmailDIV').addClass('hide');
    }
}
// sendEmail
function adminValidateEmail(){
    var fncSendEmail = 'N';
    if ($('#UserOrEmail').is(':checked')) {
        let fncToEmail = $('#ToEmail').val();
        if (fncToEmail == ''){
            wtkAlert('Enter an email address to send to');
        } else {
            if (isValidEmail(fncToEmail)) {
                fncSendEmail = 'Y';
            } else {
                wtkAlert('Email address is not valid');
            }
        }
    } else {
        fncSendEmail = 'Y';
    }
    if (fncSendEmail == 'Y'){
        modalSave('/admin/sendEmail','emailResults');
        let fncId = document.getElementById('modalWTK');
        let fncModal = M.Modal.getInstance(fncId);
        fncModal.close();
        M.toast({html: 'The email has been sent.', classes: 'green rounded'});
    }
}
// pickEmailTemplate
function adminEmailing(fncEmailGroup, fncId, fncMode = 'SendOne'){
    waitLoad('on');
    let fncEmailCode = $('#EmailCode').val();
    let fncEmailHTM = $('#EmailHTM').val();
    $.ajax({
        type: 'POST',
        url: '/admin/email' + fncEmailGroup + '.php',
        data: { apiKey: pgApiKey, id: fncId, emailCode: fncEmailCode,
                Mode: fncMode, EmailHTM: fncEmailHTM },
        success: function(data) {
            waitLoad('off');
            M.toast({html: 'Email sent', classes: 'rounded green'});
        }
    })
}

// affiliateEdit
function resetAffiliate(fncID){
    $.ajax({
        type: 'POST',
        url: 'ajxResetAffiliate.php',
        data: { apiKey: pgApiKey, id: fncID },
        success: function(data) {
            M.toast({html: 'Affiliate has been reset', classes: 'green rounded'});
        }
    })
}
