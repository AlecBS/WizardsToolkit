<!--
/*
 This file has jQuery and pure Javascript without having any other requirements.
 wtkLibrary.js contains the following functions:
    ajaxGo, ajaxPost, mobAlert, and many more required by Wizards Toolkit
*/
// var wtkBrowseFilter;
const wtkParams = new URLSearchParams(window.location.search);
var pgDebug = 'N';

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
            afterPageLoad();
        }
    });
} // wtkStartMaterializeCSS

var fncLastIconColor = 'red';
function wtkAlert(fncText, fncHdr = 'Oops!', fncColor = 'red', fncIcon = 'warning') {
    if (fncLastIconColor != fncColor) {
        $('#modIcon').removeClass(fncLastIconColor + '-text');
        $('#modIcon').addClass(fncColor + '-text');
        fncLastIconColor = fncColor;
    }
    $('#modIcon').text(fncIcon);
    $('#modHdr').text(fncHdr);
    $('#modText').html(fncText);
    let fncId = document.getElementById('modalAlert');
    let fncModal = M.Modal.getInstance(fncId);
    fncModal.open();
    wtkDebugLog('mobAlert called: ' + fncText);
} // wtkAlert
function wtkChangeRequired(fncId,fncRequired) {
	let fncTmp = document.getElementById(fncId);
	if (fncRequired == true) {
		fncTmp.setAttribute('required', '');
		wtkDebugLog('changeRequired : set required for ' + fncId);
	} else {
		fncTmp.removeAttribute('required');
		wtkDebugLog('changeRequired : set NOT required for ' + fncId);
	}
}
function wtkDebugMobile(fncDebug) {
    if (elementExist('MobileDebugging')){
        let fncTmp = $('#MobileDebugging').html();
        $('#MobileDebugging').html(fncTmp + '<br>' + fncDebug);
    }
}
function wtkDebugLog(fncMsg) {
    if (pgDebug == 'Y') {
        let now = new Date();
        let sec = now.getSeconds();
        let msec = now.getMilliseconds();
        console.log(sec + ':' + msec + ' ' + fncMsg);
        let fncElement = document.getElementById('debugLogDIV');
        if (typeof(fncElement) != 'undefined' && fncElement != null){
            $('#debugLogDIV').append('<br>' + sec + ':' + msec + ' ' + fncMsg);
        }
    } // pgDebug == 'Y'
}
function wtkSaveDebugLog() {
    if ((pgDebug == 'Y') && elementExist('debugLogDIV')) {
        // wait half a second to get additional debug logs
        setTimeout(function() {
            let fncDebugLog = $('#debugLogDIV').html();
            $.ajax({
                type: 'POST',
                url:  '/wtk/ajxSaveJSDebug.php',
                data: { apiKey: pgApiKey, DebugLog: fncDebugLog },
            })
        }, 500);
    }
}
function wtkGetValue(fncIdName, fncFormId = '') {
    let fncResult = '';
    if (fncFormId == '') { // no form ID passed
        let fncTest = document.getElementById(fncIdName);
        if (fncTest) {
            fncResult = document.getElementById(fncIdName).value;
        }
    } else {
        fncResult = $('#' + fncFormId + ' input[type=hidden][id=' + fncIdName + ']').val();
    }
    return fncResult;
} // wtkGetValue
function wtkDisableBtn(fncBtnName) {
    if (elementExist(fncBtnName)) {
        $('#' + fncBtnName).attr("disabled", true);
        setTimeout(function() {
            $('#' + fncBtnName).attr("disabled", false);
        }, 3600);
    }
}
function wtkChangeLang(fncLanguage, fncGoTo) {
    pgLanguage = fncLanguage;
    let fncLang = 'English';
    switch (pgLanguage) {
        case 'esp':
            fncLang = 'Spanish';
            break;
    }
    $('#langBtn').text(fncLang);
    $.getJSON('/wtk/setLanguage.php?Lang=' + fncLanguage, function(data) {
        window.location.replace(fncGoTo);
    });
} // wtkChangeLang
function wtkLangUpdate(fncLanguage) {
    pgLanguage = fncLanguage;
	$.getJSON('/wtk/ajxUpdateLang.php?Lang=' + fncLanguage, function(data) {
        document.getElementById('selLangList').value = fncLanguage;
		$.each(data, function(key, value) {
			if (key in gloLangArray){
				for (let i=1; i <= gloLangArray[key]; i++){
					$('#lang'+key+i).html(value);
				}
			} else {
				$('#lang'+key).html(value);
			}
		});
	});
} // wtkLangUpdate

function wtkGoToURL(fncPage, fncId='', fncRNG='', fncTarget='') {
    // WTK Browse pages call this instead of ajaxGo when using MPA
    let fncGoToURL = fncPage + '.php?id=' + fncId + '&rng=' + fncRNG;
    if (fncTarget == 'targetBlank') {
        window.open(fncGoToURL, 'ViewFile');
    } else {
        window.location.href = fncGoToURL; // redirect
    }
} // wtkGoToURL BB version

function wtkOpenPage(fncPage, fncId, fncRNG){
    // used for SPA pages to be able to open other SPA pages in a new tab
    let fncParams = 'apiKey=' + pgApiKey + '&p=ok&id=' + fncId + '&rng=' + fncRNG;
    window.open(fncPage + '.php?' + fncParams );
}

// WTK Validation Logic  -- BEGIN
function wtkValidate(fncCaller, fncDataType) {
    let fncLabel = wtkGetLabelTextById(fncCaller.id);
    let fncMinVal = parseFloat(fncCaller.min);
    let fncMaxVal = parseFloat(fncCaller.max);
    let pgValueChanged = false;
    let res;
    if (fncCaller.type === "radio") {
        let fncParentName = fncCaller.name;
        if (document.forms.wtkForm[fncParentName] !== fncCaller.value ) {
            pgValueChanged = true;
        }
    }else{
        if (fncCaller.SavedValue != fncCaller.value) {
            pgValueChanged = true;
        }
		if (fncDataType == 'DATE'){
			if (pgVarMobile != 'N'){
	            pgValueChanged = true;
			}
		}
    }
    fncCaller.Valid = "true";
    let fncPointer;
    fncPointer = fncCaller;
    if (fncPointer.required == true && (!fncPointer.value || fncPointer.value == "")) {
        if (pgLanguage == 'esp') {
            wtkAlert(fncLabel + ": necesario");
        }else{
            wtkAlert(fncLabel + ": Required field");
        }
        fncCaller.Valid = "false";
    } else if (fncPointer.SavedValue != fncPointer.value) {
        switch(fncDataType) {
          case 'ZIPCODE':
                zVal = fncPointer.value + '';
                if (zVal == ''){
                    res = true;
                }else{
                    res = isValidZip(zVal);
                    if (res != -1 ) {
                        fncPointer.value = res;
                        res = true;
                    }else{
                        wtkAlert(fncLabel + " needs to be a valid zipcode.");
                        fncCaller.Valid = "false";
                        res = false;
                      }
                }
                return res;
            case 'PHONE':
                pVal = fncPointer.value + '';
                if(pVal == ''){
                    res = true;
                }else{
                    // switch to this for international phone numbers
                    // let fncTmpPhone = pVal.replace(/\D/g, '');
                    // let fncCount = fncTmpPhone.length;
                    // if (fncCount > 8) {
                    // or below for USA phone numbers
                    res = isPhoneNum(pVal);
                    if (res != -1) {
                        fncPointer.value = res;
                        res = true;
                    }else{
                        wtkAlert(fncLabel + " needs to be a valid phone number.");
                        fncCaller.Valid = "false";
                        res = false;
                    }
                }
                return res;
            case 'EMAIL':
                let elementName = fncCaller.name;
                if (isValidEmail(fncPointer.value)) {
                    fncCaller.Valid = "true";
                }else{
                    fncCaller.Valid = "false";
                    if (fncLabel == '') {
                        fncLabel = 'Email';
                    }
                    if (pgLanguage == 'esp') {
                        wtkAlert("Por favor, introduce una dirección de correo electrónico válida.");
                    }else{
                        wtkAlert("Please enter a valid email address.");
                    }
                }
                return(0);
            case "STRING" : // check the Caps statement
            	if (fncPointer.Caps == "WORD") {
                    //let pattern = /(\w)(\w*)/;
                    let pattern = /([A-Za-z0-9_���������������������])([A-Za-z0-9_���������������������]*)/;
                    let a = fncPointer.value.split(/\s+/g);
                    for (i = 0 ; i < a.length ; i ++ ) {
                        let parts = a[i].match(pattern);
                        let firstLetter = parts[1].toUpperCase();
                        let restOfWord = parts[2].toLowerCase();
                        a[i] = firstLetter + restOfWord;
                    }
                    fncPointer.value = a.join(' ');
                }
                if (fncPointer.Caps == "ALL") {
                    let fncCaps = fncPointer.value;
                    fncPointer.value = fncCaps.toUpperCase();
                }
                return(0);
            case "NUMERIC" :    // test for numeric entry
                res = true;
                // strip out commas then test for number
                let fncNumValue = fncCaller.value.replace(/[,|$]/g, ''); // Remove commas and dollar signs
                if (isNaN(fncNumValue)) {
					if (pgLanguage == 'esp') {
	                    wtkAlert(fncLabel + ': Numerico');
					}else{
	                    wtkAlert(fncLabel + ': Numeric field');
					}
                    // set to minimum if required
                    if (!isNaN(fncMinVal) && typeof fncMinVal !== 'undefined') {
			            fncPointer.value = fncMinVal;
                    } else { // otherwise zero
                        fncPointer.value = 0;
                    }
//                    fncPointer.select();
                    fncPointer.focus();
                    res = false;
                } else {
                    fncCaller.value = fncNumValue;
                    // minimum value
                    if (!isNaN(fncMinVal) && typeof fncMinVal !== 'undefined' && fncNumValue < fncMinVal) {
                        fncCaller.value = fncMinVal;
                        if (pgLanguage == 'esp') {
    	                    wtkAlert('El valor minimo ' + fncLabel + ': ' + fncMinVal);
    					}else{
        	                wtkAlert('Minimum value ' + fncLabel + ': ' + fncMinVal);
    					}
                        fncPointer.select();
                        res = false;
                    }
                    // maximum value
                    if (!isNaN(fncMaxVal) && typeof fncMaxVal !== 'undefined' && fncNumValue > fncMaxVal) {
                        fncCaller.value = fncMaxVal;
                        if (pgLanguage == 'esp') {
    	                    wtkAlert('El valor maximo ' + fncLabel + ': ' + fncMaxVal);
    					}else{
        	                wtkAlert('Maximum value ' + fncLabel + ': ' + fncMaxVal);
    					}
                        fncPointer.select();
                        res = false;
                    }
                }
                // set precision to decimal places
                // this old code on decimals needs to be replaced
                if (fncPointer.Places) {
                    let i;
                    let val;
                    val = fncNumValue + '';
                    let decPos = val.indexOf('.');
                    if (decPos == -1) {
                        val += '.';
                        for (i=0; i < fncPointer.Places; i++) {
                            val += '0';
                        }
                    } else {
                        let actualDec = (val.length - 1) - decPos;
                        let diff = fncPointer.Places - actualDec;
                        if (diff > 0) {
                            for (i=0; i < diff; i++) {
                                val += '0';
                            }
                        }
                        if (diff < 0) {
                            val = val.substr(0,(decPos + fncPointer.Places + 1));
                        }
                    }
                  fncPointer.value = val;
                }
                return res;
            case "DATE" :
                if (pgVarMobile == 'N'){ // change from != Y to == N
                    let dVal;
                    let iD,iM,iY;
                    dVal = fncPointer.value + '';
          // 06/25/07 make so blanked date does not return invalid message if not required
			        if (dVal== ''){
			            res = true;
			        }else{
			            res = isDate(fncPointer, dVal);
			        }
                    if (res==false){
                       fncCaller.Valid = "false";
                    }
				} else {
					res = true;
	        	}
				return res;
            case "TIME" :
              	if(fncPointer.value) {
	                let d = new Date("07/07/1962 "+fncPointer.value);
	                 //let t = Date.parse(fncPointer.value);
	                if(isNaN(d)) {
	                    wtkAlert(fncLabel + ": Invalid time");
	                    fncCaller.Valid = "false";
	                   	fncPointer.select();
	                    return false;
	                }
            	}
                return true;
            case "CURRENCY" :
                let curval;
                let val;
                val = fncPointer.value + '';
                curval = val;
                // test for numeric entry
                if (val.length==0) {
                    wtkAlert(fncLabel + ": CURRENCY field");
                 	fncCaller.Valid = "false";
                   // set to minimum if required
                    if (!isNaN(fncMinVal) && typeof fncMinVal !== 'undefined') {
                        fncPointer.value = "$" + fncMinVal;
                    }else{  // otherwise zero/zed
                        fncPointer.value = "$" + 0;
                    }
                    fncPointer.select();
                    return(0);
                }
                let i;
                let decPos = val.indexOf('$');
                if (decPos == 0) {
                    val = val.substr(1,(val.length-2));
                    curval= val;
                }

              	val = curval + '';
               	while(true) {
                	decPos = val.indexOf(',');
                  	if (decPos == -1){
                   		break;
					}else{
                        val = val.substr(0,decPos)+val.substr(decPos+1,val.length-decPos-3);
                        curval= val;
                	}
              	}
                let ival=0;
                ival=curval;
                  // test for numeric entry
                if (isNaN(ival)) {
                    wtkAlert(fncLabel + ": Must be CURRENCY($###,###.##)");
						fncCaller.Valid = "false";
                   // set to minimum if required
                    if (!isNaN(fncMinVal) && typeof fncMinVal !== 'undefined') {
                        fncPointer.value = "$" + fncMinVal;
                    }else{ // otherwise zero/zed
                        fncPointer.value = "$" + 0;
                    }
                    fncPointer.select();
                    return(0);
                }
                // minimum value
                if (!isNaN(fncMinVal) && typeof fncMinVal !== 'undefined' && ival < fncMinVal) {
                    wtkAlert("Minimum value " + fncLabel + ": " + fncMinVal);
					fncCaller.Valid = "false";
                   	fncPointer.value = "$" + fncMinVal ;
                    fncPointer.select();
                }
                // maximum value
                if (!isNaN(fncMaxVal) && typeof fncMaxVal !== 'undefined' && ival > fncMaxVal) {
                    wtkAlert("Maximum value " + fncLabel + ": " + fncMaxVal);
                    fncPointer.value = "$" + fncMaxVal;
                    fncPointer.select();
                }
                // set precision to decimal places
                if (fncPointer.Places) {
                    decPos = curval.indexOf('.');
                    if (decPos == -1) {
                        curval += '.';
                        for (i=0; i < fncPointer.Places; i++) {
                            curval += '0';
                        }
                    }else{
                        let actualDec = (curval.length - 1) - decPos;
                        let diff = fncPointer.Places - actualDec;
                        if (diff > 0) {
                            for (i=0; i < diff; i++) {
                                curval += '0';
                            }
                        }
                        if (diff < 0) {
                            curval = curval.substr(0,(decPos + fncPointer.Places + 1));
                        }
                    }
                    fncPointer.value = "$"+curval;
                }
                return(0);
        } // switch(fncDataType)
    } // value changed
} // wtkValidate

function reValidateForm() {
    for (let ii=0; ii<document.forms.wtkForm.elements.length; ii++){
        if(document.forms.wtkForm.elements[ii].Valid == "false" || document.forms.wtkForm.elements[ii].Value == ""){  //If the field is left blank or incorrect input
            wtkAlert("The following field was either left blank and is required or does not have a valid value! \n" + document.forms.wtkForm.elements[ii].placeholder);
            document.forms.wtkForm.elements[ii].focus();
            break;
            return;
        }else{
            document.forms.wtkForm.elements[ii].Valid = true;
        }
    }
    document.forms.wtkForm.submit();
}
//  Beginning of Date Validation Functions  --------------------------------------------------------------------------------------------------
/**
 * DHTML date validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
 */

function isInteger(s){
    for (let i = 0; i < s.length; i++){
        // Check that current character is number.
        let c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag){
    let returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (let i = 0; i < s.length; i++){
        let c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary (year){
  // February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}
function daysArray(n) {
  for (let i = 1; i <= n; i++) {
      this[i] = 31
      if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
      if (i==2) {this[i] = 29}
  }
  return this
}

function isDate(pointer, dtStr){
    // Declaring valid date character, minimum year and maximum year
    let minYear=1900;
    let maxYear=2100;
    let pgVarDateDelimiter = '/';
    let daysInMonth = daysArray(12)
    let pos1=dtStr.indexOf(pgVarDateDelimiter)
    let pos2=dtStr.indexOf(pgVarDateDelimiter,pos1+1)
    let strMonth=dtStr.substring(0,pos1)
    let strDay=dtStr.substring(pos1+1,pos2)
    let strYear=dtStr.substring(pos2+1)
    strYr=strYear
    if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
    if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
    for (let i = 1; i <= 3; i++) {
        if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
    }
    month=parseInt(strMonth)
    day=parseInt(strDay)
    year=parseInt(strYr)
    if (pos1==-1 || pos2==-1){
        wtkAlert(pointer.placeholder + ": Invalid date.  The date format should be : mm/dd/yyyy");
        return false;
    }
    if (strMonth.length<1 || month<1 || month>12){
        wtkAlert(pointer.placeholder + ": Invalid date.  Please enter a valid month");
        return false;
    }
    if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
        wtkAlert(pointer.placeholder + ": Invalid date.  Please enter a valid day");
        return false;
    }
    if (strYear.length == 4 ) {
        if (year==0 || year<minYear || year>maxYear){
            wtkAlert(pointer.placeholder + ": Invalid date.  Please enter a valid 4 digit year between "+minYear+" and "+maxYear);
            return false;
        }
    } else {
        if (strYear.length == 2 ) {
            // do nothing while testing
            // wtkAlert(pointer.placeholder + ": Two-Digit Year, but that's Ok")
            return true;
        } else {
            wtkAlert(pointer.placeholder + ": Invalid date.  Please enter a valid 4 digit year between "+minYear+" and "+maxYear);
            return false;
        }
    }
/*
  if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
    wtkAlert(pointer.placeholder + ": Invalid date.  Please enter a valid 4 digit year between "+minYear+" and "+maxYear)
    return false
  }
*/
    if (dtStr.indexOf(pgVarDateDelimiter,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, pgVarDateDelimiter))==false){
        wtkAlert(pointer.placeholder + ": Invalid date.  Please enter a valid date");
        return false;
    }
    return true
} // isDate

function wtkFormatDate(fncDate){
	var fncDay = fncDate.getDate();
	var fncMonth = fncDate.getMonth() + 1;
	var fncYear = fncDate.getFullYear();
	if (fncDay < 10) {
		fncDay = '0' + fncDay;
	}
	if (fncMonth < 10) {
		fncMonth = '0' + fncMonth;
	}
	let fncResult = fncYear + '-' + fncMonth + '-' + fncDay;
	return fncResult;
}

function isValidZip(fncZip){
  let str = fncZip.split(' ').join('');
  str = str.split('-').join('');
  let regexObj = /^([A-Z,a-z,0-9]{5})?$/;
  let fncResult = regexObj.test(str);
  if (fncResult ==false){
	  regexObj = /^([A-Z,a-z,0-9]{6})?$/;
	  fncResult = regexObj.test(str);
      if(fncResult ==false){
      	regexObj = /^([A-Z,a-z,0-9]{9})?$/;
      	fncResult = regexObj.test(str);
        if (fncResult){
            fncResult = str.substring(0, 5) + "-" + str.substring(5, str.length);
        } else {
            fncResult = -1;
        }
      } else {
     	fncResult = str.substring(0, 3) + " " + str.substring(3, str.length);
      }
   } else {
      fncResult = str ;
   }
   return fncResult;
} // isValidZip

function isPhoneNum(fncPhone){
    // formats as USA phone number
    let str = fncPhone.split('-').join(' ');
	let fncCount = str.length;
    let fncResult = -1;
    str = str.split('(').join('');
    if(str.length != fncCount && (fncCount - str.length)%2 == 0){
		return -1;
    }
    fncCount = str.length;
    str = str.split(')').join('');
    if (str.length != fncCount && (fncCount - str.length)%2 == 0){
		return -1;
    }
    str = str.split(' ').join('').trim();
    if (str.length != 10 || !isInteger(str) ){
		return -1;
	}

    let temp = str.substring(0,3) + " " +  str.substring(3,6)  + " " +  str.substring(6,10)  ;
    let regexObj = /^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/;
    let res = regexObj.test(temp);
    if (res) {
		fncResult = temp.replace(regexObj, "($1) $2-$3");
    }else{ // Invalid phone number
		fncResult = -1;
    }
	return fncResult;
 }// end isPhoneNum

//  End of Date Validation Functions  -----------------------------------------------------------------------------------------------------------
function isValidEmail(fncEmail) {
    let fncEmailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,10})?$/;
    if (!fncEmailReg.test( fncEmail ) ) {
        return false;
    } else {
        return true;
    }
}

function isNumber(n, fncBlankOK) {
// strip out $ and commas then verify is number and return
// fixed number with 2 decimal places. If .00 then strip that out before returning.
   wtkDebugLog('isNumber top: n = ' + n);
   let fncStr = n.toString();
   fncStr = fncStr.replace('$','');
   fncStr = fncStr.replace(',','');
   wtkDebugLog('isNumber top: fncStr = ' + fncStr);
   let fncResult = false;
   if ((fncBlankOK == 'Y') && (fncStr == '')) {
       fncResult = true;
   } else {
       fncResult = !isNaN(parseFloat(fncStr)) && !isNaN(fncStr - 0);
       if (fncResult == false) {
           wtkAlert('Value entered needs to be a number like 9, 14, 27.');
           wtkDebugLog('FALSE isNumber: n = ' + n + '; fncNum = ' + fncStr + '; fncBlankOK = ' + fncBlankOK);
       } else {
           let fncNum = roundToPrecision(fncStr);
           let fncStr2 = fncNum.toString();
           fncResult = fncStr2.replace(/\.00$/,'');
           wtkDebugLog('TRUE isNumber: n = ' + n + '; fncNum = ' + fncStr + '; fncBlankOK = ' + fncBlankOK);
       }
   } // not blank
   return fncResult;
} // isNumber
// WTK Validation Logic  -- END

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    let i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function formatBytesMinimized(a,b=2){if(0===a)return"0 Bytes";const c=0>b?0:b,d=Math.floor(Math.log(a)/Math.log(1024));return parseFloat((a/Math.pow(1024,d)).toFixed(c))+" "+["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"][d]}

function elementExist(fncElemId) {
    // test to see if HTML object like hidden field exists
    let fncElement = document.getElementById(fncElemId);
    if (typeof(fncElement) != 'undefined' && fncElement != null){
        wtkDebugLog('elementExist true for ' + fncElemId);
        return true;
    } else {
        wtkDebugLog('elementExist false for ' + fncElemId);
        return false;
    }
}
function elementInFormExist(fncFormId='', fncElemId='') {
    if (fncFormId == '') {
        return elementExist(fncElemId);
    } else {
        // test to see if hidden field exists in specific form
        let formElement = document.getElementById(fncFormId);
        if (formElement) {
            let fncElement = formElement.querySelector('#' + fncElemId);
            if (fncElement) {
                wtkDebugLog('elementInFormExist true for ' + fncFormId + '.' + fncElemId);
                return true;
            } else {
                wtkDebugLog('elementInFormExist false for ' + fncFormId + '.' + fncElemId);
                return false;
            }
        }
    }
}
function getCookie(cname) {
  let name = cname + '=';
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
          c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
          return c.substring(name.length, c.length);
      }
  }
  return '';
}

function roundToPrecision(inputNum){
    let fncBigNum = (inputNum * 100).toFixed(2); //.toFixed(2) required to prevent .499999997 math failures
    let fncRounded = Math.round(fncBigNum);
    let fncResult = (fncRounded / 100);
    return(fncResult);  // ABS 07/30/12
}
function nl2br(str, is_xhtml) {
	let breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

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

function scorePassword(fncPass) {
    let fncScore = 0;
    if (!fncPass){
        return fncScore;
    } else {
        // award every unique letter until 5 repetitions
        let letters = new Object();
        for (let i=0; i<fncPass.length; i++) {
            letters[fncPass[i]] = (letters[fncPass[i]] || 0) + 1;
            fncScore += 5.0 / letters[fncPass[i]];
        }

        // bonus points for mixing it up
        let variations = {
            digits: /\d/.test(fncPass),
            lower: /[a-z]/.test(fncPass),
            upper: /[A-Z]/.test(fncPass),
            nonWords: /\W/.test(fncPass),
        }

        let variationCount = 0;
        for (let check in variations) {
            variationCount += (variations[check] == true) ? 1 : 0;
        }
        fncScore += (variationCount - 1) * 10;
        return parseInt(fncScore);
    }
} // scorePassword

function checkPassStrength(fncPass) {
    let fncResult = 'too weak';
    let fncScore = scorePassword(fncPass);
    if (fncScore > 80) {
        M.toast({html: 'Password is strong', classes: 'rounded green'});
    } else if (fncScore > 60) {
        M.toast({html: 'Password is good', classes: 'rounded green accent-3 black-text'});
    } else if (fncScore > 30) {
        M.toast({html: 'Password is weak - use numbers and both UPPER and lower case letters', classes: 'rounded orange'});
    } else {
        M.toast({html: 'Password is too weak - use numbers and both UPPER and lower case letters', classes: 'rounded red'});
    }
    return fncScore;
} // checkPassStrength

function wtkGetLabelTextById(fncId){
	const fncLabel = document.querySelectorAll(`label[for="${fncId}"]`);
	return fncLabel.length>0?fncLabel[0].innerHTML:"";
}

function wtkLabelSetActive(fncID){
    // This works for MaterializeCSS HTML format of labels and input fields
    let fncValue = $('#' + fncID).val();
    if (fncValue != ''){
        let fncInput = document.getElementById(fncID);
        let fncLabel = fncInput.parentElement.querySelector("label");
        if (fncLabel) {
            fncLabel.classList.add("active");
        }
    }
}

// BEGIN Drag and Drop functionality
// used by /admin/widgetMgr.php and possibly other files for UI method of changing Priorities within data
var pgFromDragId = 0;
var pgFromDragPos = 0;

function wtkDragStart(fncId,fncPos) {
    pgFromDragId = fncId;
    pgFromDragPos = fncPos;
    wtkDebugLog('wtkDragStart called: pgFromDragId = ' + pgFromDragId );
}
function wtkDragOver(ev) {
    ev.preventDefault(); // necessary
}
function wtkDropId(fncToId, fncToPos, fncSet = '') {
    wtkDebugLog('wtkDropId called: Set = ' + fncSet + '; FromId = ' + pgFromDragId + '; ToId = ' + fncToId);
    let fncTable = $('#wtkDragTable' + fncSet).val();
    let fncColumn = $('#wtkDragColumn' + fncSet).val();
    let fncFilter = $('#wtkDragFilter' + fncSet).val();
    wtkDebugLog('wtkDropId called: Set = ' + fncSet + '; FromId = ' + pgFromDragId + '; ToId = ' + fncToId + '; fncTable = ' + fncTable);
    $.ajax({
        type: 'POST',
        url:  '/wtk/ajxPriorityAdj.php',
        data: { apiKey: pgApiKey, tbl: fncTable, col: fncColumn, filter: fncFilter,
            fromId: pgFromDragId, toId: fncToId, fromPos: pgFromDragPos, toPos: fncToPos},
            success: function(data) {
                let fncURL = $('#wtkDragRefresh' + fncSet).val();
                if ($('#wtkDragLocation' + fncSet).val() == 'table') {
                    wtkBrowseReset(fncURL, fncTable, fncFilter);
                } else {
                    wtkModalUpdate(fncURL, 0, fncFilter);
                }
            }
    })
}
//  END  Drag and Drop functionality
// -->
