// used by /index.php
function openEarlyReg() {
	let modalId = document.getElementById('earlyReg');
    let fncModal = M.Modal.getInstance(modalId);
    fncModal.open();
}

function verifyEmail(){
    let fncEmail = $('#email').val();
    if (fncEmail == '') {
        wtkAlert('You must enter an email address.');
    } else {
        if (isValidEmail(fncEmail)) {
            disableBtn('regBtn');
            let fncFormData = $('#regForm').serialize();
            $.ajax({
              type: "POST",
              url: 'earlyRegister.php',
              data: (fncFormData),
                success: function(data) {
					let fncJSON = $.parseJSON(data);
					if (fncJSON.result == 'OK'){
						M.toast({html: "Your message has been sent.", classes: "green rounded"});
						let fncId = document.getElementById('submitWebsite');
						let fncModal = M.Modal.getInstance(fncId);
						fncModal.close();
					} else {
						M.toast({html: "Email failed because no email address", classes: "red rounded"});
					}
                }
            })
        }else{ // not valid email
            wtkAlert("Please enter a valid email address.");
        }
    } // email entered
}

function startPage(fncFrom) {
	$(document).ready(function($){
		$('.parallax').parallax();
		$('.materialboxed').materialbox();
		$('#owl-one').owlCarousel({
			loop:true,
			margin:10,
			nav:false,
			autoplay: 5000,
			smartSpeed: 2000,
			dots: true,
			responsive:{
		        0:{
		            items:1
		        },
		        600:{
		            items:2
		        },
		        1000:{
		            items:4
		        }
		    }
		});
		$('#owl-two').owlCarousel({
			loop:true,
			margin:10,
			nav:false,
			autoplay: 5000,
			smartSpeed: 2000,
			dots: true,
			responsive:{
				0:{
				  items:1
				}
			}
		});
		let elems = document.querySelectorAll('.modal');
		let options = {
			dismissible: true,
			opacity: '40%',
			startingTop: '4%',
			endingTop: '10%'
		};
		let instances = M.Modal.init(elems, options);
		$('.sidenav').sidenav();
	});
	$(document).scroll(function () {
	   var $nav = $(".navbar-fixed-top");
	   $nav.toggleClass('scrolled', $(this).scrollTop() > $nav.height());
	});
}
