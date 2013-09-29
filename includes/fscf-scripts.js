/*
 * fscf-scripts.js
 * Script file for displaying the contact form
 * Created by Ken Carlson
 */

var formSubmitted = false;

jQuery(document).ready(function(){
//	$('input[type=submit]').one('click', function() {
//	jQuery("[id^=fsc-submit]").click(function() {

	// Has the form already been submitted?  If so, reset the form.
	var formValue = jQuery('input[name=fscf_submitted][value=1]');
	if ( formValue.length ) {
//		alert("Form has already been submitted");
		// reset the form
		var myForm = formValue[0].form;
		myForm.reset();
		// Reset all the forms
//		var formLen = document.forms.length;
//		for( var i = 0; i < formLen; i++ ){
//			document.forms[i].reset();
//		}
	}

//	jQuery(".fsc_submit_button").one('click', function() {
	jQuery(".fsc_submit_button").click(function() {
		if ( formSubmitted ) {
			// A form has already been submitted, so ignore this submit
			alert('Form already submitted');
			return(false);
		} else {
			formSubmitted = true;
			// This will mark all the forms on the page as submitted, but that's okay, 
			// it will just reset them all if the back button is pressed
			jQuery("input[name='fscf_submitted']").val('1');
			return(true);
		}
	});

});

function fscf_captcha_refresh(form_num,securimage_url,securimage_show_url) {
   var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
   var string_length = 16;
   var prefix = '';
   for (var i=0; i<string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		prefix += chars.substring(rnum,rnum+1);
   }
  document.getElementById('fscf_captcha_prefix' + form_num).value = prefix;

  var si_image_ctf = securimage_show_url + prefix;
  document.getElementById('fscf_captcha_image' + form_num).src = si_image_ctf;

}
