/**
 * Get the data from the given form data and request a meeting with it.
 *  
 * @param form_num: The number of the current form being viewed
 * @param expert: The name / id of the associated expert with the form
 */
function vcita_set_meeting(form_num, expert) {
	if (expert == '') {
		alert('To enable meeting scheduling, please make sure your Email is configured in the meeting scheduler section');
		return false;
	}

	var email = document.getElementById('si_contact_email' + form_num).value; 
	var title = document.getElementById('si_contact_subject' + form_num).value;
	var agenda = document.getElementById('si_contact_message' + form_num).value;
	var first_name = "";
	var m_name = "";
	var last_name = "";
	
	if (document.getElementById('si_contact_mi_name' + form_num) != null) {
		m_name = document.getElementById('si_contact_mi_name' + form_num).value;
	} else if (document.getElementById('si_contact_m_name' + form_num)) {
		m_name = document.getElementById('si_contact_m_name' + form_num).value;
	}
	
	first_name = ((m_name != "") ? m_name + " " : "");
	
	if (document.getElementById('si_contact_name' + form_num) != null) {
		first_name += document.getElementById('si_contact_name' + form_num).value;
	} else {
		first_name += document.getElementById('si_contact_f_name' + form_num).value;
	}
	
	if (document.getElementById('si_contact_l_name' + form_num) != null) {
		last_name = document.getElementById('si_contact_l_name' + form_num).value;
	}
	
	window.open('http://www.vcita.com/' + expert + '/set_meeting' + 
		    '?first_name=' + encodeURIComponent(first_name) + 
		    '&last_name=' + encodeURIComponent(last_name) + 
		    '&email=' + encodeURIComponent(email) + 
		    '&title=' + encodeURIComponent(title) + 
		    '&agenda='+encodeURIComponent(agenda) + 
		    '&invite=wp-fscf&o=int.1', 
		    '_blank',
		    "scrollbars=yes,menubar=no,resizable=yes,toolbar=no,location=no,status=no,height=660,width=970");
	
}