function savePage() {
	pgShowWarning = 'N';
	let fncId = document.getElementById('saveConfirm');
	let fncModal = M.Modal.getInstance(fncId);
	document.getElementById("showEmail").innerHTML = wtkGetValue('wtkwtkUsersEmail');
 	fncModal.open();
} // savePage

function onBoard(fncFormId) {
    // if a value is passed in fncFormId then we will NOT hide the form; if no value passed we WILL hide the form
    var fncEmail = wtkGetValue('email' + fncFormId);
    if (fncEmail == '') {
        wtkAlert('<p class="center">You must enter an email address.</p>');
    } else {
        if (isValidEmail(fncEmail)) {
            waitLoad('on');
            if (fncFormId == '') {
                document.getElementById("PLform" + fncFormId).classList.add("hide");
            }

            var xmlhttp = new XMLHttpRequest();
            var fncName = wtkGetValue('name' + fncFormId);
            let fncVID = wtkGetValue('vid' + fncFormId);
            let fncLID = wtkGetValue('lid' + fncFormId);
            let fncMsg = wtkGetValue('msg' + fncFormId);

            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == XMLHttpRequest.DONE) {   // XMLHttpRequest.DONE == 4
                    waitLoad('off');
                    if (xmlhttp.status == 200) {
                        let fncId = document.getElementById('modalAlert');
                        let fncModal = M.Modal.getInstance(fncId);
                        document.getElementById("modIcon").innerHTML = 'email';
                        document.getElementById("modIcon").classList.remove("red-text");
                        document.getElementById("modIcon").classList.add("blue-text");
                        document.getElementById("modHdr").innerHTML = 'Message Sent!';
                        fncMessage = '<h3>Thanks';
                        if (fncName != '') {
                            fncMessage = fncMessage + ' ' + fncName;
                        }
                        fncMessage = fncMessage + '</h3>';
                        let fncThanksMsg = wtkGetValue('thanksMsg');
                        fncThanksMsg.replace('~!~', '"');
                        fncMessage = fncMessage + fncThanksMsg;
                        document.getElementById("modText").innerHTML = fncMessage;
                        fncModal.open();
                        pgShowWarning = 'N';
                    } else if (xmlhttp.status == 400) {
                        alert('There was a 400 error!');
                    } else {
                        wtkAlert('There was an error and the email may not have gone through.  Contact support@programminglabs.com.  Our apologies!');
                    }
                }
            };
            xmlhttp.open("POST", "/wtk/landingPageEmail.php", true);
            xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xmlhttp.send('name=' + fncName + '&email=' + fncEmail + '&vid=' + fncVID + '&lid=' + fncLID + '&msg=' + fncMsg);
        }else{ // not valid email
            wtkAlert("Please enter a valid email address.");
        }
    } // email entered
} // onBoard

// Next functions handle registering with PIN verification
var pgUserUID = 0;
function landingRegister() {
	$('#btnSignUp').attr("disabled", true);
	setTimeout(function() {
		$('#btnSignUp').attr("disabled", false);
	}, 3600);
    let fncEmail = wtkGetValue('wtkwtkUsersEmail');
    let fncPW = wtkGetValue('wtkwtkUsersWebPassword');
    let fncRePW = wtkGetValue('rePW');
    if (fncPW == '') {
        wtkAlert('Enter a password.');
    } else if (fncPW != fncRePW) {
        wtkAlert('The passwords you entered do not match.');
    } else if (fncEmail == '') {
        wtkAlert('You must enter an email address.');
    } else {
        if (isValidEmail(fncEmail)) {
            waitLoad('on');
/* Verify account does not already exist;
 x  if it does ask for password to log them in without losing their landing page.
 x  Otherwise create account, email them a PIN and have this page ask for the PIN.
 x  After they enter the PIN confirm PIN is correct
 x  and save this Landing Page.
 x  Then take them to admin site
 x  and page to add remaining fields to page like
 x  email template and twitter card info.
*/
            let fncFormData = $('#wtkForm').serialize(); //2FIX
            $.ajax({
              type: "POST",
              url: '/wtk/ajxRegPIN.php',
              data: (fncFormData),
                success: function(data) {
	 			  waitLoad('off');
                  let fncJSON = $.parseJSON(data);
				  switch (fncJSON.result) {
					case 'accountExist':
						$('#tryLogin').removeClass('hide');
						$('#signUpMsg').addClass('hide');
						$('#wtkForm').addClass('hide');
						$('#showEmail2').text(fncEmail);
						break;
					case 'PIN':
						pgUserUID = fncJSON.userId;
						$('#signUpMsg').addClass('hide');
						$('#proveEmail').removeClass('hide');
						$('#wtkForm').addClass('hide');
                        $('#showEmail').text(fncEmail);
						break;
				  	default:
                    	$('#regForgot').removeClass('hide');
						wtkAlert(fncJSON.error);
				  }
                }
            })
        } else {
            wtkAlert('Please enter a valid email address.');
        }
    }
} // landingRegister

function verifyPIN() {
	waitLoad('on');
	fncPIN = wtkGetValue('myPIN');
	fncEmail = wtkGetValue('wtkwtkUsersEmail');
	var fncFormData = 'email=' + fncEmail + '&userId=' + pgUserUID + '&pin=' + fncPIN;
	fncFormData += '&title=' + wtkGetValue('tab1F');
	fncFormData += '&text1=' + wtkGetValue('tab2F');
	fncFormData += '&text2=' + wtkGetValue('tab3F');
	fncFormData += '&image=' + wtkGetValue('tab4F');
	fncFormData += '&video=' + wtkGetValue('tab5F');
	fncFormData += '&thanks=' + wtkGetValue('tab6F');
	fncFormData += '&titleColor=' + wtkGetValue('titleColor');
	fncFormData += '&textColor=' + wtkGetValue('textColor');
	fncFormData += '&text2Color=' + wtkGetValue('text2Color');
	fncFormData += '&btnColor=' + wtkGetValue('btnColor');
	fncFormData += '&topColor=' + wtkGetValue('topColor');
	fncFormData += '&bottomColor=' + wtkGetValue('bottomColor');
	if (elementExist('AccessMethod')) {
		fncFormData += '&AccessMethod=' + wtkGetValue('AccessMethod');
	}
	$.ajax({
	  type: "POST",
	  url: '/wtk/ajxVerifyPIN.php',
	  data: (fncFormData),
	  success: function(data) {
		  waitLoad('off');
		  let fncJSON = $.parseJSON(data);
		  switch (fncJSON.result) {
			case 'ok':
				$('#preLoginApiKey').val(fncJSON.regApiKey);
			    document.forms.loginAdmin.submit();
				break;
			default: // 'wrongPIN'
				M.toast({html: "That PIN number was not correct.  Please re-try.", classes: "red rounded"});
		  }
		}
	})
} // verifyPIN
