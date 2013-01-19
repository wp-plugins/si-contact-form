<?php
/*
Plugin Name: Fast Secure Contact Form
Plugin URI: http://www.FastSecureContactForm.com/
Description: Fast Secure Contact Form for WordPress. The contact form lets your visitors send you a quick E-mail message. Super customizable with a multi-form feature, optional extra fields, and an option to redirect visitors to any URL after the message is sent. Includes CAPTCHA and Akismet support to block all common spammer tactics. Spam is no longer a problem. <a href="plugins.php?page=si-contact-form/si-contact-form.php">Settings</a> | <a href="http://www.FastSecureContactForm.com/donate">Donate</a>
Version: 3.1.7.2
Author: Mike Challis
Author URI: http://www.642weather.com/weather/scripts.php
*/

$ctf_version = '3.1.7.2';

/*  Copyright (C) 2008-2013 Mike Challis  (http://www.fastsecurecontactform.com/contact)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// settings get deleted when plugin is deleted from admin plugins page
// this must be outside the class or it does not work
function si_contact_unset_options() {

  delete_option('si_contact_form');
  delete_option('si_contact_form_gb');

  // multi-forms (a unique configuration for each contact form)
  for ($i = 2; $i <= 100; $i++) {
    delete_option("si_contact_form$i");
  }
} // end function si_contact_unset_options

if (!class_exists('siContactForm')) {

 class siContactForm {
     var $si_contact_error;
     var $uploaded_files;
     var $ctf_notes_style;
     var $ctf_version;
     var $vcita_add_script;

function si_contact_add_tabs() {
    add_submenu_page('plugins.php', __('FS Contact Form Options', 'si-contact-form'), __('FS Contact Form Options', 'si-contact-form'), 'manage_options', __FILE__,array(&$this,'si_contact_options_page'));
	
}

function si_contact_update_lang() {
  global $si_contact_opt, $si_contact_option_defaults;

   // a few language options need to be re-translated now.
   // had to do this becuse the options were actually needed to be set before the language translator was initialized

  // update translation for these options (for when switched from English to another lang)
  if ($si_contact_opt['welcome'] == '<p>Comments or questions are welcome.</p>' ) {
     $si_contact_opt['welcome'] = __('<p>Comments or questions are welcome.</p>', 'si-contact-form');
     $si_contact_option_defaults['welcome'] = $si_contact_opt['welcome'];
  }

  if ($si_contact_opt['email_to'] == 'Webmaster,'.get_option('admin_email')) {
       $si_contact_opt['email_to'] = __('Webmaster', 'si-contact-form').','.get_option('admin_email');
       $si_contact_option_defaults['email_to'] = $si_contact_opt['email_to'];
  }

  if ($si_contact_opt['email_subject'] == get_option('blogname') . ' ' .'Contact:') {
      $si_contact_opt['email_subject'] =  get_option('blogname') . ' ' .__('Contact:', 'si-contact-form');
      $si_contact_option_defaults['email_subject'] = $si_contact_opt['email_subject'];
  }

} // end function si_contact_update_lang

function si_contact_options_page() {
  global $captcha_url_cf, $si_contact_opt, $si_contact_gb, $si_contact_gb_defaults, $si_contact_option_defaults, $ctf_version;

  require_once(WP_PLUGIN_DIR . '/si-contact-form/admin/si-contact-form-admin.php');

} // end function si_contact_options_page

/* --- vCita Admin Functions - Start ---  */

/**
 * Add the vcita Javascript to the admin section
 */
function vcita_add_admin_js() {

	if(isset($_GET['page']) && is_string($_GET['page']) && preg_match('/si-contact-form.php$/',$_GET['page']) ) {

          $form_num = $this->si_contact_form_num();
          $si_contact_opt = get_option("si_contact_form$form_num");
          if ($si_contact_opt['vcita_enabled'] == 'false') {  // Mike challis added  01/15/2013
            return;   // prevent setting vcita cookies in admin if vcita is disabled on the form
          }
		  wp_enqueue_script('jquery');
		  wp_register_script('vcita_fscf', plugins_url('vcita/vcita_fscf.js', __FILE__), array('jquery'), '1.1', true);
		  wp_register_script('vcita_fscf_admin', plugins_url('vcita/vcita_fscf_admin.js', __FILE__), array('jquery'), '1.1', true);
		  
		  wp_print_scripts('vcita_fscf');
          wp_print_scripts('vcita_fscf_admin');

	}
}

/**
 * Validate the user is initialized currenctly be performing the following. 
 * 1. Migration from old versions.
 * 2. New User - enable vCita if the auto install flag is set to true  
 * 3. Upgrade - enable vCita if wasn't previously disabled - Currently nothing is done
 */
function vcita_validate_initialized_user($form_num, $form_params, $general_params, $previous_version) {
    $auto_install = $general_params['vcita_auto_install'];
    $curr_version = $general_params['ctf_version'];
    $vcita_dismiss = $general_params['vcita_dismiss'];
    
    // Check if a initializtion is required
    if (!isset($form_params['vcita_initialized']) || $form_params['vcita_initialized'] == 'false') {
        // New Install - Only enable vCita 
        // This will cause the notification about misconfigured installation be shown.
         
        if ($auto_install == 'true' && $vcita_dismiss == "false") {
            $form_params['vcita_enabled'] = 'true';
        }
        
        // Currently nothing during upgrade.
    
        $form_params['vcita_initialized'] = 'true'; // Mark as initialized
        update_option("si_contact_form$form_num", $form_params);
    }
    
    $confirm_token = '';
    if (isset($form_params['vcita_confirm_token']))
        $confirm_token = $form_params['vcita_confirm_token'];
    
    // Migrate token to the new field
    if (!empty($confirm_token) && !empty($form_params['vcita_uid'])) {
        $form_params['vcita_confirm_tokens'] = '';
        $form_params = $this->vcita_set_confirmation_token($form_params, $confirm_token);

        $form_params['vcita_confirm_token'] = null;
        update_option("si_contact_form$form_num", $form_params);
    }
    
    // check if the approved flag should be turned on, happens when: 
    // When user available, enabled and approve is false (this can only happen if form is an old version)
    if (isset($form_params['vcita_enabled']) && $form_params['vcita_enabled'] == 'true' && 
        isset($form_params['vcita_uid']) && !empty($form_params['vcita_uid']) && 
        (!isset($form_params['vcita_approved']) || $form_params['vcita_approved'] == 'false')) {
        
        $form_params['vcita_approved'] = 'true';
        update_option("si_contact_form$form_num", $form_params);
    }
    
    return $form_params;
}

/**
 * Use the vCita API to get a user, either create a new one or get the id of an available user
 * In case the "default" email is used, no action takes place.
 * 
 * @return array of the user name, id and if he finished the registration or not
 */
function vcita_generate_or_validate_user($params) {
    $used_email = $params['vcita_email'];
    
	// Don't create / validate if this isn't the expert
	if (empty($_SESSION) || empty($_SESSION["vcita_expert"]) || !$_SESSION["vcita_expert"]) {
		return $params;
	}
	
	// Only generate a user if the mail isn't the default one.
	if ($used_email == 'mail@example.com') {
		$params['vcita_uid'] = '';
		
		return $params;
	} 
	
	extract($this->vcita_post_contents("http://www.vcita.com/api/experts?id=".$params['vcita_uid'].
	                                   "&email=".urlencode($used_email).
	                                   "&first_name=".urlencode($params['vcita_first_name'])."&last_name=".
	                                   urlencode($params['vcita_last_name'])."&ref=wp-fscf&o=int.1"));

	return $this->vcita_parse_user_info($params, $success, $raw_data);
}

/* 
 * Parse the result from the vCita API.
 * Update all the parameters with the given values / error.
 */
function vcita_parse_user_info($params, $success, $raw_data) {
    $previous_id = isset($params['vcita_uid']) ? $params['vcita_uid'] : '';
    $params['vcita_initialized'] = 'false';
	$params['vcita_uid'] = '';
	
	if (!$success) {
		$params['vcita_last_error'] = "Temporary problem, please try again later";
	} else {
		$data = json_decode($raw_data);
		
		if ($data->{'success'} == 1) {
			$params['vcita_confirmed'] = $data->{'confirmed'};
			$params['vcita_last_error'] = "";
			$params['vcita_uid'] = $data->{'id'};
			$params['vcita_initialized'] = 'true';
			$params['vcita_first_name'] = $data->{'first_name'};
			$params['vcita_last_name'] = $data->{'last_name'};
			
			if ($previous_id != $data->{'id'}) {
				$params = $this->vcita_set_confirmation_token($params, $data->{'confirmation_token'});
			}
			
			if (isset($data->{'email'}) && !empty($data->{'email'})) {
			    $params['vcita_email'] = $data->{'email'};
			}
			
		} else {
			$params['vcita_last_error'] = $data-> {'error'};
		}
	}
	
	return $params;
}

/**
 * Disconnect the user from vCita by removing his details.
 */
function vcita_disconnect_form($form_params) {
    global $si_contact_option_defaults;
    
     $form_params['vcita_approved']    = $si_contact_option_defaults['vcita_approved'];
     $form_params['vcita_uid']         = $si_contact_option_defaults['vcita_uid'];
     $form_params['vcita_email']       = $si_contact_option_defaults['vcita_email'];
     $form_params['vcita_first_name']  = $si_contact_option_defaults['vcita_first_name'];
     $form_params['vcita_last_name']   = $si_contact_option_defaults['vcita_last_name'];
     $form_params['vcita_initialized'] = 'true'; // Don't re-enable next time
     
     // On Purpose keeping the confirmation_tokens

     return $form_params;
}

/**
 * Perform an HTTP POST Call to retrieve the data for the required content.
 *
 * @param $url
 * @return array - raw_data and a success flag
 */
function vcita_post_contents($url) {
    $response  = wp_remote_post($url, array('header' => array('Accept' => 'application/json; charset=utf-8'),
                                          'timeout' => 10));

    return $this->vcita_parse_response($response);
}

/**
 * Perform an HTTP GET Call to retrieve the data for the required content.
 * 
 * @param $url
 * @return array - raw_data and a success flag
 */
function vcita_get_contents($url) {
    $response = wp_remote_get($url, array('header' => array('Accept' => 'application/json; charset=utf-8'),
                                          'timeout' => 10));

    return $this->vcita_parse_response($response);
}

/**
 * Parse the HTTP response and return the data and if was successful or not.
 */
function vcita_parse_response($response) {
    $success = false;
    $raw_data = "Unknown error";
    
    if (is_wp_error($response)) {
        $raw_data = $response->get_error_message();
    
    } elseif (!empty($response['response'])) {
        if ($response['response']['code'] != 200) {
            $raw_data = $response['response']['message'];
        } else {
            $success = true;
            $raw_data = $response['body'];
        }
    }
    
    return compact('raw_data', 'success');
}

/**
 * Add the dynamic notification area based on the current user status
 * 
 * This notification is for the Meeting scheduler section (Not for page header notifications)
 */
function vcita_add_notification($params) {
	$confirmation_token = $this->vcita_get_confirmation_token($params);
	
	if ($params['vcita_enabled'] == 'false') {
		$message = '<b>Meeting Scheduler is disabled</b>, please check the box below to allow users to request meetings via your contact form';
		$message_type = "fsc-notice";
		
	} elseif (!empty($params['vcita_last_error'])) {
        $message = $params['vcita_last_error'];
        $message_type = "fsc-error";
		
    } elseif (!empty($params['vcita_uid'])) {
	    $message_type = "fsc-notice";
		$message = "vCita Meeting Scheduler is <font style='color:green;font-weight:bold;'>active</font><br/>";
		
        if (!$params['vcita_confirmed'] && !empty($confirmation_token)) {
		    $message .= "<br/>Click below to set your meeting options and availability".
						"<div style='margin-top:10px;'><a href='http://www.vcita.com/users/confirmation?force=true&amp;non_avail=continue&amp;confirmation_token=".$this->vcita_get_confirmation_token($params)."&amp;o=int.2' target='_blank'><img src=".plugins_url( 'vcita/vcita_configure.png' , __FILE__ )." height='41px' width='242px' /></a></div>";
			$message_type = "fsc-error";
	    } elseif (!empty($params['vcita_last_name'])) {
	        $message .= "<b>Active account: </b>".$params['vcita_first_name']." ".$params['vcita_last_name'];
	    }
    } elseif ($this->vcita_get_email($params) == 'mail@example.com') {
		$message = "You are currently using the default mail: <b>mail@example.com</b>, To activate - please enter you email below.";
		$message_type = "fsc-notice";
		
	} elseif ($params['vcita_enabled'] == 'true') {
	    $message = "Please configure your vCita Meeting Scheduler below.";
		$message_type = "fsc-notice";
	} 
	
    echo "<br/><div class=".$message_type.">".$message."</div>";
	
	echo "<div style='clear:both;display:block'></div>";
}

/**
 * Location for the vcita banner
 */
function vcita_banner_location() {
	return plugins_url( 'vcita/vcita_banner.jpg' , __FILE__ );
}

/**
 * Add the vCita advanced configuraion links to user admin.
 * Show the settings only if the user is available
 */
function vcita_add_config($params) {
	// Only show the Edit link in case the user is available
	if (!empty($params["vcita_uid"]) && $params['vcita_enabled'] == 'true') {
		$confirmation_token = $this->vcita_get_confirmation_token($params);
		
		$vcita_curr_notifcation = "<div style='clear:both;float:left;text-align:left;display:block;padding:5px 0 10px 0;width:100%;'>";
		
		
		if ($params['vcita_confirmed']) {
		    $vcita_curr_notifcation .= "
                 <div style='margin-right:10px;float:left;'><a href='http://www.vcita.com/settings?section=profile' target='_blank'>Edit Profile</a></div>
                 <div style='margin-right:10px;float:left;'><a href='http://www.vcita.com/settings?section=configuration' target='_blank'>Edit Meeting Preferences</a></div>
                 <div style='margin-right:10px;float:left;'>
                     <input style='display:none;' id='vcita_disconnect_button' type='submit' name='vcita_disconnect'/>
                    <a id='vcita_fscf_disconnected_button' href='#' onclick='document.formoptions.vcita_disconnect_button.click();return false;' target='_blank'>Change Account</a>
                 </div>";
				 
		} elseif (empty($confirmation_token)) {
		    $vcita_curr_notifcation .= "
			    <div style='margin-right:10px;float:left;'>
			     <a href='http://www.vcita.com/users/send_password_instructions?activation=true&email=".$this->vcita_get_email($params)."' target='_blank'>Configure your account</a></div>
			     <div style='margin-right:5px;float:left;'>
                    <input style='display:none;' id='vcita_disconnect_button' type='submit' name='vcita_disconnect'/>
                   <a id='vcita_fscf_disconnected_button' href='#' onclick='document.formoptions.vcita_disconnect_button.click();return false;' target='_blank'>Change Account</a>
                </div>";
		} else {
		    $vcita_curr_notifcation .= "
		        <div style='margin-right:5px;float:left;'>
        		    <input style='display:none;' id='vcita_disconnect_button' type='submit' name='vcita_disconnect''/>
		            <a id='vcita_fscf_disconnected_button' href='#' onclick='document.formoptions.vcita_disconnect_button.click();return false;' target='_blank'>Change Account</a>
		        </div>";
		}
		
		$vcita_curr_notifcation .= "</div>";
		
		echo $vcita_curr_notifcation;
	}
}

/**
 * Print the notification for the admin page for the main plugins page or the fast secure page
 * 
 */
function vcita_print_admin_page_notification($si_contact_global_tmp = null, $form_params = null, $form_num = "", $internal_page = false) {
    $form_used = isset($form_params["vcita_enabled"]) && $form_params["vcita_enabled"] == "true";
     
    // Don't do anything if dismissed
    if (isset($si_contact_global_tmp["vcita_dismiss"]) && $si_contact_global_tmp["vcita_dismiss"] == "true" && !$form_used) {
        return false;
    }
    
    $notification_created = false;
    $prefix = ($internal_page) ? "" : "<p><b>Fast Secure Contact Form - </b>";
    $suffix = ($internal_page) ? "" : "</p>"; 
    $class = ($internal_page) ? "fsc-error" : "error"; 
    $origin = ($internal_page) ? "&amp;o=int.3" : "&amp;o=int.5";
    $notification_created = true;
    
    $vcita_section_url = admin_url( "plugins.php?ctf_form_num=$form_num&amp;page=si-contact-form/si-contact-form.php#vCitaSettings");
    $vcita_dismiss_url = admin_url( "plugins.php?vcita_dismiss=true&amp;ctf_form_num=$form_num&amp;page=si-contact-form/si-contact-form.php");
    
    // Show if empty, missing details, or internal page, vcita not used and upgrade 
    if (empty($form_params) || 
        $this->vcita_should_notify_missing_details($form_params) || 
        ($internal_page && !$this->vcita_is_being_used() && $this->vcita_should_show_when_not_used($si_contact_global_tmp))) {
        
        echo "<div id='si-fscf-vcita-warning' class='".$class."'>".$prefix."You still haven't completed your Meeting Scheduler settings. <a href='".esc_url($vcita_section_url)."'>Click here to learn more</a>, or <a href='".esc_url($vcita_dismiss_url)."'>Dismiss.</a>".$suffix."</div>";
        
    } elseif ($internal_page && $this->vcita_should_complete_registration($form_params)) {
        $vcita_complete_url = "http://www.vcita.com/users/confirmation?force=true&non_avail=continue&confirmation_token=".$this->vcita_get_confirmation_token($form_params).$origin."' target='_blank";
        
        if (!$internal_page) { // direct outside pages to vCita section (This currently won't happen but keeping for future use.) 
            $vcita_complete_url = $vcita_section_url;
        }
        
        echo "<div id='si-fscf-vcita-warning' class='".$class."'>".$prefix."Your Meeting Scheduler is active but some settings are still missing. <a href='".esc_url($vcita_complete_url)."'>Click here to configure</a>, or <a href='".esc_url($vcita_section_url)."'>here to disable</a>".$suffix."</div>";
        
    } elseif (!empty($params["vcita_last_error"])) {
        echo "<div class='".$class."'>".$prefix."<strong>"._e('Meeting Scheduler - '.$si_contact_opt["vcita_last_error"], 'si-contact-form')."</strong>".$suffix."</div>";
        
    } else {
        $notification_created = false;
    }
    
    return $notification_created;
}

/**
 * Check if registration for the given form wasn't completed yet.
 */
function vcita_should_complete_registration($form_params) {
	$vcita_confirmation_token = $this->vcita_get_confirmation_token($form_params);
	return isset($form_params['vcita_uid']) && !empty($form_params['vcita_uid']) && $form_params['vcita_enabled'] == 'true' && !$form_params['vcita_confirmed'] && !empty($vcita_confirmation_token);
}

/**
 * Check if a notification for the current form should be displayed to the user 
 */
function vcita_should_notify_missing_details($form_params) {
    return isset($form_params['vcita_uid']) && empty($form_params['vcita_uid']) && $form_params['vcita_enabled'] == 'true';
}


/** 
 * Check if should display a warning in the admin section 
 * Warning will be shown in all admin pages (as being done by many other plugins)
 * Won't shown for the actual fast contact page - it is being called directly from the page 
 */
function si_contact_vcita_admin_warning() {
    
   if (!isset($_GET['page']) || !preg_match('/si-contact-form.php$/',$_GET['page'])) {
       $si_contact_global_tmp = get_option("si_contact_form_gb");
       
       if (class_exists("siContactForm") && !isset($si_contact_form) ) {
         $si_contact_form = new siContactForm();
    
         if (empty($si_contact_global_tmp)) {
             $this->vcita_print_admin_page_notification();
              
         } else {
             $vcita_never_used = true;
             
             for ($i = 1; $i <= $si_contact_global_tmp['max_forms']; $i++) {
                 $form_num = ($i == 1) ? "" : $i;
                 $si_form_params = get_option("si_contact_form$form_num");
                  
                 if ($this->vcita_print_admin_page_notification($si_contact_global_tmp, $si_form_params, $form_num)) {
                     $vcita_never_used = false;
                     return;
                     
                 } else if ($this->vcita_is_form_used($si_form_params)) {
                     $vcita_never_used = false;
                 } 
             }
             
             if ($vcita_never_used && $this->vcita_should_show_when_not_used($si_contact_global_tmp)) {
                 $this->vcita_print_admin_page_notification($si_contact_global_tmp, null); // Put the general 
             }
         }
      }
   }
}

/**
 * Get the email which should be used for vcita meeting scheduling
 */
function vcita_get_email($params) {
	if (!empty($params["vcita_email"])) {
		return $params["vcita_email"];
	} else {
		return $this->si_contact_extract_email($params["email_to"]);
	}
}

/* 
 * Check if the user is already available in vCita
 */
function vcita_check_user($params) {
	extract($this->vcita_get_contents("http://www.vcita.com/api/experts/".$params['vcita_uid']));
	
	return $this->vcita_parse_user_info($params, $success, $raw_data);
}

/**
 * Get the confirmation token matches the current user
 */
function vcita_get_confirmation_token($params) {
	$token = "";
	
	if (!empty($params["vcita_confirm_tokens"])) {
		$token = "";
		$tokens = explode("|", $params["vcita_confirm_tokens"]);
		if (count($tokens) > 0) {
			foreach ($tokens as $raw_token) {
				$token_values = explode("-", $raw_token);
				
				if (!empty($raw_token) && $token_values[0] == $params["vcita_uid"]) {
					$token = $token_values[1];
					
					if (!empty($_SESSION) && $_SESSION['vcita_expert']) {
						$_SESSION['vcita_owner-of-'.$params['vcita_uid']] = true;
					}
					
					break;
				}
			}
		}
	}
	return $token;
}

/** 
 * Set the confirmation for the current user
 */ 
function vcita_set_confirmation_token($params, $confirmation_token) {
	if (!empty($confirmation_token)) {
		$tokens = explode("|", $params["vcita_confirm_tokens"]);
		array_push($tokens, $params["vcita_uid"]."-".$confirmation_token);
	
		$params["vcita_confirm_tokens"] = implode("|", $tokens);
	}
	
	return $params;
}

/**
 * Check if the vcita confirmation token should be saved.
 * Currently this means it will be also saved in the client side in a dedicated cookie.
 */
function vcita_should_store_expert_confirmation_token($params) {
	$confirmation_token = $this->vcita_get_confirmation_token($params);
	
	if (!empty($confirmation_token) && !empty($_SESSION) && $_SESSION['vcita_owner-of-'.$params['vcita_uid']]) {
		return $confirmation_token;
	} else {
		return "";
	}
}

/**
 * Flip the dismiss flag to true and make all the neccessary adjustments.
 */
function vcita_dismiss_pending_notification($global_params, $current_form_num) {
    global $si_contact_opt;
    
    // Go over all the forms and disable the pending ones
    for ($i = 1; $i <= $global_params['max_forms']; $i++) {
        $form_num = ($i == 1) ? "" : $i;
        
        if ($current_form_num == $form_num) {
            $si_form_params = $si_contact_opt;
        } else {
            $si_form_params = get_option("si_contact_form$form_num");
        }

        if ($this->vcita_should_complete_registration($si_form_params) || 
            $this->vcita_should_notify_missing_details($si_form_params)) {
            
            $si_form_params['vcita_enabled'] = 'false';
            $si_form_params['vcita_last_error'] = '';
            $si_form_params['vcita_uid'] = '';
            $si_form_params['vcita_first_name'] = '';
            $si_form_params['vcita_last_name'] = '';
            $si_form_params['vcita_email'] = '';
            update_option("si_contact_form$form_num", $si_form_params);

            // Also update the global variable
            if ($current_form_num == $form_num) {
                $si_contact_opt = $si_form_params;
            }
        }
    }

    // Put the dismiss flag
    $global_params["vcita_dismiss"] = "true";
    update_option("si_contact_form_gb", $global_params);
    
    return $global_params;
}


/**
 * True / False if notification should be displayed if user didn't use vCita
 * 
 * True only if upgrade user (never had auto install vCita) 
 */
function vcita_should_show_when_not_used($global_params) {
    return isset($global_params['vcita_auto_install']) && $global_params['vcita_auto_install'] == "false";
}

/**
 * vCita form is used if one of the following:
 *  
 * - form enabled
 * - has a vcita_uid 
 * - has a confirmation_token -> in the past had a user
 */
function vcita_is_form_used($form_param) {
    return ((isset($form_param["vcita_enabled"]) && $form_param["vcita_enabled"] == "true") ||
            (isset($form_param["vcita_uid"]) && !empty($form_param["vcita_uid"])) || 
            (isset($form_param["vcita_confirm_tokens"]) && !empty($form_param["vcita_confirm_tokens"])));
}

/**
 * Check if vcita is used in any form
 */
function vcita_is_being_used() {
    $si_contact_global_tmp = get_option("si_contact_form_gb");

    for ($i = 1; $i <= $si_contact_global_tmp['max_forms']; $i++) {
        $form_num = ($i == 1) ? "" : $i;
        $si_form_params = get_option("si_contact_form$form_num");
    
        if ($this->vcita_is_form_used($si_form_params)) {
            return true;
        }
    }
    
    return false;
}

/* --- vCita Admin Functions - End --- */

/* --- vCita Contact Functions - Start --- */

/** 
 * Add the vcita script to the pages of the fast secure
 */
function vcita_si_contact_add_script(){
    global $si_contact_opt, $vcita_add_script;

    if (!$vcita_add_script)
      return;
    wp_enqueue_script('jquery');
    wp_register_script('vcita_fscf', plugins_url('vcita/vcita_fscf.js', __FILE__), array('jquery'), '1.1', true);
    wp_print_scripts('vcita_fscf');
      ?>
    <script type="text/javascript">
//<![CDATA[
var vicita_fscf_style = "<!-- begin Fast Secure Contact Form - vCita scheduler page header -->" +
"<style type='text/css'>" + 
".vcita-widget-right { float: left !important; } " +
".vcita-widget-bottom { float: none !important; clear:both;}" + 
"</style>" + 
"<!-- end Fast Secure Contact Form - vCita scheduler page header -->";
jQuery(document).ready(function($) {
$('head').append(vicita_fscf_style);
});
//]]>
</script>
	<?php

}
/* --- vCita Contact Functions - End --- */

/**
 * Extract the mail contained and the received argument.
 * Handles the following usecases:
 * 1. Name and email concatenation - Webmaster,mail@example.com
 * 2. Only email
 *
 * Returns the email address
 */
function si_contact_extract_email($ctf_extracted_email) {
	$ctf_trimmed_email = trim($ctf_extracted_email);
	  
	if(!preg_match("/,/", $ctf_trimmed_email) ) { // single email without,name
		$name = '';        // name,email
		$email = $ctf_trimmed_email;
	} else{
		list($name, $email) = preg_split('#(?<!\\\)\,#',array_shift(preg_split('/[;]/',$ctf_trimmed_email)));
	}

	return $email;
}


function si_contact_captcha_perm_dropdown($select_name, $checked_value='') {
        // choices: Display text => permission_level
        $choices = array (
                 __('All registered users', 'si-contact-form') => 'read',
                 __('Edit posts', 'si-contact-form') => 'edit_posts',
                 __('Publish Posts', 'si-contact-form') => 'publish_posts',
                 __('Moderate Comments', 'si-contact-form') => 'moderate_comments',
                 __('Administer site', 'si-contact-form') => 'level_10'
                 );
        // print the <select> and loop through <options>
        echo '<select name="' . esc_attr($select_name) . '" id="' . esc_attr($select_name) . '">
';
        foreach ($choices as $text => $capability) :
                if ($capability == $checked_value) $checked = ' selected="selected" ';
                echo '    <option value="' . esc_attr($capability) . '"' . $checked . '>'.esc_html($text).'</option>
';
                $checked = '';
        endforeach;
        echo '    </select>
';
} // end function si_contact_captcha_perm_dropdown


function get_contacts() {
      global $si_contact_opt;
// Returns a list of contacts for display
// E-mail Contacts
// the drop down list array will be made automatically by this code
// checks for properly configured E-mail To: addresses in options.
$contacts = array ();
$contacts[] = '';	// dummy entry to take up key 0
$contacts_test = trim($si_contact_opt['email_to']);
if(!preg_match("/,/", $contacts_test) ) {
    if($this->ctf_validate_email($contacts_test)) {
        // user1@example.com
       $contacts[] = array('CONTACT' => __('Webmaster', 'si-contact-form'),  'EMAIL' => $contacts_test );
    }
} else {
  $ctf_ct_arr = explode("\n",$contacts_test);
  if (is_array($ctf_ct_arr) ) {
    foreach($ctf_ct_arr as $line) {
       // echo '|'.$line.'|' ;
       list($key, $value) = preg_split('#(?<!\\\)\,#',$line); //string will be split by "," but "\," will be ignored
       $key   = trim(str_replace('\,',',',$key)); // "\," changes to ","
       $value = trim($value);
       if ($key != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
               // just one email here
               // Webmaster,user1@example.com
               $value = str_replace('[cc]','',$value);
               $value = str_replace('[bcc]','',$value);
               if ($this->ctf_validate_email($value)) {
                  $contacts[] = array('CONTACT' => $key,  'EMAIL' => $value);
               }
          } else {
               // multiple emails here
               // Webmaster,user1@example.com;user2@example.com;user3@example.com;[cc]user4@example.com;[bcc]user5@example.com
               $multi_cc_arr = explode(";",$value);
               $multi_cc_string = '';
               foreach($multi_cc_arr as $multi_cc) {
                  $multi_cc_t = str_replace('[cc]','',$multi_cc);
                  $multi_cc_t = str_replace('[bcc]','',$multi_cc_t);
                  if ($this->ctf_validate_email($multi_cc_t)) {
                     $multi_cc_string .= "$multi_cc,";
                   }
               }
               if ($multi_cc_string != '') { // multi cc emails
                  $contacts[] = array('CONTACT' => $key,  'EMAIL' => rtrim($multi_cc_string, ','));
               }
         }
      }

   } // end foreach
  } // end if (is_array($ctf_ct_arr) ) {
} // end else
unset($contacts[0]);	// remove dummy entry.. the array keys now start with 1
//print_r($ctf_contacts);
return $contacts;

} // end function get_contacts

// this function builds the contact form content
// [si_contact_form form='2']
function si_contact_form_short_code($atts) {
  global $captcha_path_cf, $ctf_captcha_dir, $si_contact_opt, $si_contact_gb, $ctf_version, $vcita_add_script, $attach_names, $fsc_error_message;

  $this->ctf_version = $ctf_version;
  // get options
  $si_contact_gb_mf = get_option("si_contact_form_gb");

   extract(shortcode_atts(array(
   'form' => '',
   'redirect' => '',
   'hidden' => '',
   'email_to' => '',
   ), $atts));

    $form_num = '';
    $form_id_num = 1;
    if ( isset($form) && is_numeric($form) && $form <= $si_contact_gb_mf['max_forms'] ) {
       $form_num = (int)$form;
       $form_id_num = (int)$form;
       if ($form_num == 1)
         $form_num = '';
    }
  $_SESSION["fsc_shortcode_form_id_$form_id_num"] = $form_id_num;

  // http://www.fastsecurecontactform.com/shortcode-options
  $_SESSION["fsc_shortcode_redirect_$form_id_num"] = $redirect;
  $_SESSION["fsc_shortcode_hidden_$form_id_num"] = $hidden;
  $_SESSION["fsc_shortcode_email_to_$form_id_num"] = $email_to;

  // get options
  $si_contact_gb = $this->si_contact_get_options($form_num);

  // did we already get a valid and action completed form result?
  if(   ( isset($_POST['si_contact_action']) && $_POST['si_contact_action'] == 'send')
      && (isset($_POST['si_contact_form_id']) && is_numeric($_POST['si_contact_form_id'])) ){
     $form_id_num_this = (int)$_POST['si_contact_form_id'];
     if ( isset($_SESSION["fsc_shortcode_form_id_$form_id_num_this"]) ) {
            // return the form HTML now
      		if( isset( $_SESSION['fsc_form_display_html'] ) ) {
                   //already processed, this variable is used to print the form results HTML to shortcode now, even more than once if other plugins cause
                   return $_SESSION['fsc_form_display_html'];
            }
     } else {
            wp_die(__('No form selected', 'si-contact-form'));
     }
  }

  // have to continue on and build the form results HTML now.

  // a couple language options need to be translated now.
  $this->si_contact_update_lang();

// Email address(s) to receive Bcc (Blind Carbon Copy) messages
$ctf_email_address_bcc = $si_contact_opt['email_bcc']; // optional

// optional subject list
$subjects = array ();
$subjects_test = explode("\n",trim($si_contact_opt['email_subject_list']));
if(!empty($subjects_test) ) {
  $ct = 1;
  foreach($subjects_test as $v) {
       $v = trim($v);
       if ($v != '') {
          $subjects["$ct"] = $v;
          $ct++;
       }
  }
}

// get the list of contacts for display
$contacts = $this->get_contacts();

// Site Name / Title
$ctf_sitename = get_option('blogname');

// Site Domain without the http://www like this: $domain = '642weather.com';
// Can be a single domain:      $ctf_domain = '642weather.com';
// Can be an array of domains:  $ctf_domain = array('642weather.com','someothersite.com');
        // get blog domain
        $uri = parse_url(get_option('home'));
        $blogdomain = preg_replace("/^www\./i",'',$uri['host']);

$this->ctf_domain = $blogdomain;

$form_action_url = $this->form_action_url();

// Double E-mail entry is optional
// enabling this requires user to enter their email two times on the contact form.
$ctf_enable_double_email = $si_contact_opt['double_email'];


// initialize vars
$string = '';
$mail_to    = '';
$to_contact = '';
$name       = $this->si_contact_get_var($form_id_num,'name');
$f_name     = $this->si_contact_get_var($form_id_num,'f_name');
$m_name     = $this->si_contact_get_var($form_id_num,'m_name');
$mi_name    = $this->si_contact_get_var($form_id_num,'mi_name');
$l_name     = $this->si_contact_get_var($form_id_num,'l_name');
$email      = $this->si_contact_get_var($form_id_num,'email');
$email2     = $this->si_contact_get_var($form_id_num,'email');
$subject    = $this->si_contact_get_var($form_id_num,'subject');
$message    = $this->si_contact_get_var($form_id_num,'message');
$captcha_code  = '';
$vcita_add_script = false;
if ($si_contact_opt['vcita_enabled'] == 'true')
  $vcita_add_script = true;

// optional extra fields
// capture query string vars
$have_attach = '';
for ($i = 1; $i <= $si_contact_opt['max_fields']; $i++) {
   if ($si_contact_opt['ex_field'.$i.'_label'] != '') {
      ${'ex_field'.$i} = '';
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'time') {
         ${'ex_field'.$i.'h'} = $this->si_contact_get_var($form_id_num,'ex_field'.$i.'h');
         ${'ex_field'.$i.'m'} = $this->si_contact_get_var($form_id_num,'ex_field'.$i.'m');
         ${'ex_field'.$i.'ap'} = $this->si_contact_get_var($form_id_num,'ex_field'.$i.'ap');
      }
      if( in_array($si_contact_opt['ex_field'.$i.'_type'],array('hidden','text','email','url','textarea','date','password')) ) {
         ${'ex_field'.$i} = $this->si_contact_get_var($form_id_num,'ex_field'.$i);
      }
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'radio' || $si_contact_opt['ex_field'.$i.'_type'] == 'select') {
         $exf_opts_array = $this->si_contact_get_exf_opts_array($si_contact_opt['ex_field'.$i.'_label']);
         $check_ex_field = $this->si_contact_get_var($form_id_num,'ex_field'.$i);
         if($check_ex_field != '' && is_numeric($check_ex_field) && $check_ex_field > 0 ) {
           if( isset($exf_opts_array[$check_ex_field-1]) )
               ${'ex_field'.$i} = $exf_opts_array[$check_ex_field-1];
         }
      }
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'select-multiple') {
         $exf_opts_array = $this->si_contact_get_exf_opts_array($si_contact_opt['ex_field'.$i.'_label']);
         $ex_cnt = 1;
         foreach ($exf_opts_array as $k) {
             if( $this->si_contact_get_var($form_id_num,'ex_field'.$i.'_'.$ex_cnt) == 1 ){
                 ${'ex_field'.$i.'_'.$ex_cnt} = 'selected';
             }
             $ex_cnt++;
         }
      }
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'checkbox' || $si_contact_opt['ex_field'.$i.'_type'] == 'checkbox-multiple') {
         $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
         if(preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
            $exf_opts_array = $this->si_contact_get_exf_opts_array($si_contact_opt['ex_field'.$i.'_label']);
            $ex_cnt = 1;
            foreach ($exf_opts_array as $k) {
                if( $this->si_contact_get_var($form_id_num,'ex_field'.$i.'_'.$ex_cnt) == 1 ){
                     ${'ex_field'.$i.'_'.$ex_cnt} = 'selected';
                }
                $ex_cnt++;
            }
         }else{
              if($this->si_contact_get_var($form_id_num,'ex_field'.$i) == 1)
              ${'ex_field'.$i} = 'selected';
         }
      }
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'attachment')
         $have_attach = 1; // for <form post

   }
}
$req_field_ind = ( $si_contact_opt['req_field_indicator_enable'] == 'true' ) ? '<span '.$this->si_contact_convert_css($si_contact_opt['required_style']).'>'.$si_contact_opt['req_field_indicator'].'</span>' : '';

// see if WP user
global $current_user, $user_ID;
get_currentuserinfo();

      // gather all input variables, and they have already been validated in si_contact_check_form
    $cid = $this->si_contact_post_var('si_contact_CID');

    $mail_to    = ( isset($contacts[$cid]['EMAIL']) )   ? $this->ctf_clean_input($contacts[$cid]['EMAIL'])  : '';
    $to_contact = ( isset($contacts[$cid]['CONTACT']) ) ? $this->ctf_clean_input($contacts[$cid]['CONTACT']): '';

    // allow shortcode email_to
    // Webmaster,user1@example.com (must have name,email)
    // multiple emails allowed
    // Webmaster,user1@example.com;user2@example.com
   if ( $_SESSION["fsc_shortcode_email_to_$form_id_num"] != '') {
     if(preg_match("/,/", $_SESSION["fsc_shortcode_email_to_$form_id_num"]) ) {
       list($key, $value) = preg_split('#(?<!\\\)\,#',$_SESSION["fsc_shortcode_email_to_$form_id_num"]); //string will be split by "," but "\," will be ignored
       $key   = trim(str_replace('\,',',',$key)); // "\," changes to ","
       $value = trim(str_replace(';',',',$value)); // ";" changes to ","
       if ($key != '' && $value != '') {
             $mail_to    = $this->ctf_clean_input($value);
             $to_contact = $this->ctf_clean_input($key);
       }
     }
   }

    if ($si_contact_opt['name_type'] != 'not_available') {
        switch ($si_contact_opt['name_format']) {
          case 'name':
               $name = $this->ctf_name_case($this->si_contact_post_var('si_contact_name'));
          break;
          case 'first_last':
               $f_name = $this->ctf_name_case($this->si_contact_post_var('si_contact_f_name'));
               $l_name = $this->ctf_name_case($this->si_contact_post_var('si_contact_l_name'));
          break;
          case 'first_middle_i_last':
               $f_name =  $this->ctf_name_case($this->si_contact_post_var('si_contact_f_name'));
               $mi_name = $this->ctf_name_case($this->si_contact_post_var('si_contact_mi_name'));
               $l_name =  $this->ctf_name_case($this->si_contact_post_var('si_contact_l_name'));
          break;
          case 'first_middle_last':
               $f_name = $this->ctf_name_case($this->si_contact_post_var('si_contact_f_name'));
               $m_name = $this->ctf_name_case($this->si_contact_post_var('si_contact_m_name'));
               $l_name = $this->ctf_name_case($this->si_contact_post_var('si_contact_l_name'));
         break;
      }
    }
    if ($si_contact_opt['email_type'] != 'not_available') {
         $email = strtolower($this->si_contact_post_var('si_contact_email'));
       if ($ctf_enable_double_email == 'true') {
          $email2 = strtolower($this->si_contact_post_var('si_contact_email2'));
       }
    }

      if ($si_contact_opt['subject_type'] != 'not_available') {
      $subject = '';
        if(isset($_POST['si_contact_subject'])) {
            // posted subject text input
            $subject = $this->ctf_name_case($this->si_contact_post_var('si_contact_subject'));
        }else{
           if (!empty($subjects)) {
            // posted subject select input
            $sid = $this->si_contact_post_var('si_contact_subject_ID');
            if ($sid == '') $sid = 1;
            $subject = $this->ctf_clean_input($subjects[$sid]);
           }
       }
    }

    if ($si_contact_opt['message_type'] != 'not_available') {
       if ($si_contact_opt['preserve_space_enable'] == 'true') {
           $message = (isset($_POST['si_contact_message'])) ? $this->ctf_clean_input($_POST['si_contact_message'],1) : '';
       } else {
           $message = $this->si_contact_post_var('si_contact_message');
       }
    }
    if ( $this->isCaptchaEnabled() === true)
         $captcha_code = $this->si_contact_post_var('si_contact_captcha_code');

  // CAPS Decapitator
   if ($si_contact_opt['name_case_enable'] == 'true' && !preg_match("/[a-z]/", $message))
      $message = $this->ctf_name_case($message);

   if(!empty($f_name)) $name .= $f_name;
   if(!empty($mi_name))$name .= ' '.$mi_name;
   if(!empty($m_name)) $name .= ' '.$m_name;
   if(!empty($l_name)) $name .= ' '.$l_name;

   // optional extra fields form post validation
      for ($i = 1; $i <= $si_contact_opt['max_fields']; $i++) {
        if ($si_contact_opt['ex_field'.$i.'_label'] != '' && $si_contact_opt['ex_field'.$i.'_type'] != 'fieldset-close') {
          if ($si_contact_opt['ex_field'.$i.'_type'] == 'fieldset') {

          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'date') {

                      $cal_date_array = array(
'mm/dd/yyyy' => __('mm/dd/yyyy', 'si-contact-form'),
'dd/mm/yyyy' => __('dd/mm/yyyy', 'si-contact-form'),
'mm-dd-yyyy' => __('mm-dd-yyyy', 'si-contact-form'),
'dd-mm-yyyy' => __('dd-mm-yyyy', 'si-contact-form'),
'mm.dd.yyyy' => __('mm.dd.yyyy', 'si-contact-form'),
'dd.mm.yyyy' => __('dd.mm.yyyy', 'si-contact-form'),
'yyyy/mm/dd' => __('yyyy/mm/dd', 'si-contact-form'),
'yyyy-mm-dd' => __('yyyy-mm-dd', 'si-contact-form'),
'yyyy.mm.dd' => __('yyyy.mm.dd', 'si-contact-form'),
);
               // required validate
               ${'ex_field'.$i} = $this->si_contact_post_var("si_contact_ex_field$i");

          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'hidden') {
               ${'ex_field'.$i} = $this->si_contact_post_var("si_contact_ex_field$i");
          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'time') {
               ${'ex_field'.$i.'h'}  = $this->si_contact_post_var("si_contact_ex_field".$i."h");
               ${'ex_field'.$i.'m'}  = $this->si_contact_post_var("si_contact_ex_field".$i."m");
               if ($si_contact_opt['time_format'] == '12')
                  ${'ex_field'.$i.'ap'} = $this->si_contact_post_var("si_contact_ex_field".$i."ap");
          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'attachment') {
                   // file name that was uploaded.
                   ${'ex_field'.$i} = ( isset($attach_names[$i]) ) ? $attach_names[$i] : '';
          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'checkbox' || $si_contact_opt['ex_field'.$i.'_type'] == 'checkbox-multiple') {
             // see if checkbox children
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
                  list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                  $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                  $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                  if ($exf_opts_label != '' && $value != '') {
                     if(preg_match("/;/", $value)) {
                        // multiple options
                         $exf_opts_array = explode(";",$value);
                     }
                     // required check (only 1 has to be checked to meet required)
                    $ex_cnt = 1;
                    $ex_reqd = 0;
                    foreach ($exf_opts_array as $k) {
                      if( ! empty($_POST["si_contact_ex_field$i".'_'.$ex_cnt]) ){
                        ${'ex_field'.$i.'_'.$ex_cnt} = $this->si_contact_post_var("si_contact_ex_field$i".'_'.$ex_cnt);
                        $ex_reqd++;
                      }
                      $ex_cnt++;
                    }
                }
             }else{
                ${'ex_field'.$i} = $this->si_contact_post_var("si_contact_ex_field$i");
             }
           }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'select-multiple') {
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
                  list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                  $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                  $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                  if ($exf_opts_label != '' && $value != '') {
                     if(preg_match("/;/", $value)) {
                        // multiple options
                         $exf_opts_array = explode(";",$value);
                     }
                     // required check (only 1 has to be checked to meet required)
                     $ex_reqd = 0;
                     ${'ex_field'.$i} = $this->si_contact_post_var("si_contact_ex_field$i");
                     if (is_array(${'ex_field'.$i}) && !empty(${'ex_field'.$i}) ) {
                       // loop
                       foreach ($exf_opts_array as $k) {  // checkbox multi
                          if (in_array($k, ${'ex_field'.$i} ) ) {
                             $ex_reqd++;
                          }
                       }
                     }
                }
             }
           }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'email') {
                  ${'ex_field'.$i} = strtolower($this->si_contact_post_var("si_contact_ex_field$i"));

           }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'url') {
                  ${'ex_field'.$i} = $this->si_contact_post_var("si_contact_ex_field$i");
           }else{
                // text, textarea, radio, select, password
                if ($si_contact_opt['ex_field'.$i.'_type'] == 'textarea' && $si_contact_opt['textarea_html_allow'] == 'true') {
                      ${'ex_field'.$i} = wp_kses_data(stripslashes($this->si_contact_post_var("si_contact_ex_field$i"))); // allow only some safe html
                }else{
                      ${'ex_field'.$i} = $this->si_contact_post_var("si_contact_ex_field$i");
                }
           }
        }  // end if label != ''
      } // end foreach


     // The welcome is what gets printed just before the form.
     // It is not printed when there is an input error or after the form is completed
     $string .= '
'.$si_contact_opt['welcome'];

     // include the code to display the form
     include(WP_PLUGIN_DIR . '/si-contact-form/si-contact-form-display.php');


 return $string;
} // end function si_contact_form_short_code


// returns the URL for the WP page the form was on
function form_action_url( ) {

  if(function_exists('qtrans_convertURL'))
      // compatible with qtranslate plugin
      // In case of multi-lingual pages, the /de/ /en/ language url is used.
      $form_action_url = qtrans_convertURL(strip_tags($_SERVER['REQUEST_URI']));
  else
      $form_action_url = 'http://'.strip_tags($_SERVER['HTTP_HOST']).strip_tags($_SERVER['REQUEST_URI']);

  // set the type of request (SSL or not)
  if ( is_ssl() )
      $form_action_url = preg_replace('|http://|', 'https://', $form_action_url);

  return $form_action_url;

} // end function form_action_url

// validation Check all form input data, called during init
function si_contact_check_and_send(  ) {
    global $si_contact_opt;

   $not_mailed_before = 0;

  // do we process form now?
  if(   ( isset($_POST['si_contact_action']) && $_POST['si_contact_action'] == 'send')
      && (isset($_POST['si_contact_form_id']) && is_numeric($_POST['si_contact_form_id'])) ){
     $form_id_num = (int)$_POST['si_contact_form_id'];
     if ( isset($_SESSION["fsc_shortcode_form_id_$form_id_num"]) ) { // a form that was really page viewed
         // begin check server-side no back button mail again post once only token.
         if (!isset($_POST["si_postonce_$form_id_num"]) || empty($_POST["si_postonce_$form_id_num"]) || strpos($_POST["si_postonce_$form_id_num"] , ',') === false ) {
                // redirect, get out
                wp_redirect( $this->form_action_url() ); // token was no good
		        exit;
         }

         $vars = explode(',', $_POST["si_postonce_$form_id_num"]);
         if ( empty($vars[0]) || empty($vars[1]) || ! preg_match("/^[0-9]+$/",$vars[1]) ) {
                // redirect, get out
                wp_redirect( $this->form_action_url() ); // token was no good
		        exit;
         }
         if ( wp_hash( $vars[1] ) == $vars[0] ) {
             if ( isset($_SESSION["fsc_form_lastpost_$form_id_num"]) && ($_SESSION["fsc_form_lastpost_$form_id_num"] == $vars[0])){
               wp_redirect( $this->form_action_url() ); // no back button mail again, show blank form
		       exit;
             }
             $_SESSION["fsc_form_lastpost_$form_id_num"] = $vars[0];

         } else {
                // redirect, get out
                wp_redirect( $this->form_action_url() ); // token was no good
		        exit;
         }
         // end check server-side no back button mail again post once token.
            // prevent double action
      		if( !isset( $_SESSION["fsc_sent_mail"] )) { // form not already emailed out
					// Check all input data
				$this->si_contact_check_form($form_id_num);
            }
     }
  }


} // function si_contact_check_and_send

// validation Check all form input data, called by si_contact_check_and_send during init
// replaces  si-contact-form-process.php
// if form validates, will send mail, and process silent posts
function si_contact_check_form($form_id_num) {
     global $captcha_path_cf, $ctf_captcha_dir, $si_contact_opt, $si_contact_gb, $ctf_version, $vcita_add_script, $fsc_attach_names, $fsc_error_message;

    // get options for this form
    $form_to_fetch = ($form_id_num == 1) ? '' : $form_id_num;
    $si_contact_gb = $this->si_contact_get_options($form_to_fetch);

     // a couple language options need to be translated now.
  $this->si_contact_update_lang();

// Email address(s) to receive Bcc (Blind Carbon Copy) messages
$ctf_email_address_bcc = $si_contact_opt['email_bcc']; // optional

// optional subject list
$subjects = array ();
$subjects_test = explode("\n",trim($si_contact_opt['email_subject_list']));
if(!empty($subjects_test) ) {
  $ct = 1;
  foreach($subjects_test as $v) {
       $v = trim($v);
       if ($v != '') {
          $subjects["$ct"] = $v;
          $ct++;
       }
  }
}

// get the list of contacts for display
$contacts = $this->get_contacts();

// Site Name / Title
$ctf_sitename = get_option('blogname');

// Site Domain without the http://www like this: $domain = '642weather.com';
// Can be a single domain:      $ctf_domain = '642weather.com';
// Can be an array of domains:  $ctf_domain = array('642weather.com','someothersite.com');
        // get blog domain
        $uri = parse_url(get_option('home'));
        $blogdomain = preg_replace("/^www\./i",'',$uri['host']);

$this->ctf_domain = $blogdomain;

$form_action_url = $this->form_action_url();

// Make sure the form was posted from your host name only.
// This is a security feature to prevent spammers from posting from files hosted on other domain names
// "Input Forbidden" message will result if host does not match
$this->ctf_domain_protect = $si_contact_opt['domain_protect'];

// Double E-mail entry is optional
// enabling this requires user to enter their email two times on the contact form.
$ctf_enable_double_email = $si_contact_opt['double_email'];


// initialize vars
$this->si_contact_error = 0;
$fsc_error_message = array();
$fsc_attach_names = array();
$mail_to    = '';
$to_contact = '';
$name       = $this->si_contact_get_var($form_id_num,'name');
$fsc_error_message['name'] = '';
$f_name     = $this->si_contact_get_var($form_id_num,'f_name');
$fsc_error_message['f_name'] = '';
$m_name     = $this->si_contact_get_var($form_id_num,'m_name');
$fsc_error_message['m_name'] = '';
$mi_name    = $this->si_contact_get_var($form_id_num,'mi_name');
$fsc_error_message['mi_name'] = '';
$l_name     = $this->si_contact_get_var($form_id_num,'l_name');
$fsc_error_message['l_name'] = '';
$email      = $this->si_contact_get_var($form_id_num,'email');
$fsc_error_message['email'] = '';
$email2     = $this->si_contact_get_var($form_id_num,'email');
$fsc_error_message['email2'] = '';
$subject    = $this->si_contact_get_var($form_id_num,'subject');
$fsc_error_message['subject']  = '';
$message    = $this->si_contact_get_var($form_id_num,'message');
$fsc_error_message['message'] = '';
$fsc_error_message['captcha'] = '';
$fsc_error_message['attach_dir_error'] = '';
$captcha_code  = '';
$vcita_add_script = false;
if ($si_contact_opt['vcita_enabled'] == 'true')
  $vcita_add_script = true;

// optional extra fields
// capture query string vars
$have_attach = '';
for ($i = 1; $i <= $si_contact_opt['max_fields']; $i++) {
   if ($si_contact_opt['ex_field'.$i.'_label'] != '') {
      ${'ex_field'.$i} = '';
      $fsc_error_message["ex_field$i"]  = ''; // initialize the error array for extra fields
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'time') {
         ${'ex_field'.$i.'h'} = $this->si_contact_get_var($form_id_num,'ex_field'.$i.'h');
         ${'ex_field'.$i.'m'} = $this->si_contact_get_var($form_id_num,'ex_field'.$i.'m');
         ${'ex_field'.$i.'ap'} = $this->si_contact_get_var($form_id_num,'ex_field'.$i.'ap');
      }
      if( in_array($si_contact_opt['ex_field'.$i.'_type'],array('hidden','text','email','url','textarea','date','password')) ) {
         ${'ex_field'.$i} = $this->si_contact_get_var($form_id_num,'ex_field'.$i);
      }
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'radio' || $si_contact_opt['ex_field'.$i.'_type'] == 'select') {
         $exf_opts_array = $this->si_contact_get_exf_opts_array($si_contact_opt['ex_field'.$i.'_label']);
         $check_ex_field = $this->si_contact_get_var($form_id_num,'ex_field'.$i);
         if($check_ex_field != '' && is_numeric($check_ex_field) && $check_ex_field > 0 ) {
           if( isset($exf_opts_array[$check_ex_field-1]) )
               ${'ex_field'.$i} = $exf_opts_array[$check_ex_field-1];
         }
      }
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'select-multiple') {
         $exf_opts_array = $this->si_contact_get_exf_opts_array($si_contact_opt['ex_field'.$i.'_label']);
         $ex_cnt = 1;
         foreach ($exf_opts_array as $k) {
             if( $this->si_contact_get_var($form_id_num,'ex_field'.$i.'_'.$ex_cnt) == 1 ){
                 ${'ex_field'.$i.'_'.$ex_cnt} = 'selected';
             }
             $ex_cnt++;
         }
      }
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'checkbox' || $si_contact_opt['ex_field'.$i.'_type'] == 'checkbox-multiple') {
         $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
         if(preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
            $exf_opts_array = $this->si_contact_get_exf_opts_array($si_contact_opt['ex_field'.$i.'_label']);
            $ex_cnt = 1;
            foreach ($exf_opts_array as $k) {
                if( $this->si_contact_get_var($form_id_num,'ex_field'.$i.'_'.$ex_cnt) == 1 ){
                     ${'ex_field'.$i.'_'.$ex_cnt} = 'selected';
                }
                $ex_cnt++;
            }
         }else{
              if($this->si_contact_get_var($form_id_num,'ex_field'.$i) == 1)
              ${'ex_field'.$i} = 'selected';
         }
      }
      if ($si_contact_opt['ex_field'.$i.'_type'] == 'attachment')
         $have_attach = 1; // for <form post

   }
}
$req_field_ind = ( $si_contact_opt['req_field_indicator_enable'] == 'true' ) ? '<span '.$this->si_contact_convert_css($si_contact_opt['required_style']).'>'.$si_contact_opt['req_field_indicator'].'</span>' : '';

// see if WP user
global $current_user, $user_ID;
get_currentuserinfo();

    $fsc_message_sent = '';
    $fsc_error_message['contact'] = '';
     // check all input variables
    $cid = $this->ctf_clean_input($_POST['si_contact_CID']);
    if(empty($cid)) {
       $this->si_contact_error = 1;
       $fsc_error_message['contact'] = ($si_contact_opt['error_contact_select'] != '') ? $si_contact_opt['error_contact_select'] : __('Selecting a contact is required.', 'si-contact-form');
    }
    else if (!isset($contacts[$cid]['CONTACT'])) {
        $this->si_contact_error = 1;
        $fsc_error_message['contact'] = __('Requested Contact not found.', 'si-contact-form');
    }
    if (empty($contacts)) {
       $this->si_contact_error = 1;
    }
    $mail_to    = ( isset($contacts[$cid]['EMAIL']) )   ? $this->ctf_clean_input($contacts[$cid]['EMAIL'])  : '';
    $to_contact = ( isset($contacts[$cid]['CONTACT']) ) ? $this->ctf_clean_input($contacts[$cid]['CONTACT']): '';

    // allow shortcode email_to
    // Webmaster,user1@example.com (must have name,email)
    // multiple emails allowed
    // Webmaster,user1@example.com;user2@example.com
   if ( $_SESSION["fsc_shortcode_email_to_$form_id_num"] != '') {
     if(preg_match("/,/", $_SESSION["fsc_shortcode_email_to_$form_id_num"]) ) {
       list($key, $value) = preg_split('#(?<!\\\)\,#',$_SESSION["fsc_shortcode_email_to_$form_id_num"]); //string will be split by "," but "\," will be ignored
       $key   = trim(str_replace('\,',',',$key)); // "\," changes to ","
       $value = trim(str_replace(';',',',$value)); // ";" changes to ","
       if ($key != '' && $value != '') {
             $mail_to    = $this->ctf_clean_input($value);
             $to_contact = $this->ctf_clean_input($key);
       }
     }
   }

    if ($si_contact_opt['name_type'] != 'not_available') {
        switch ($si_contact_opt['name_format']) {
          case 'name':
             if (isset($_POST['si_contact_name']))
               $name = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_name']));
          break;
          case 'first_last':
             if (isset($_POST['si_contact_f_name']))
               $f_name = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_f_name']));
             if (isset($_POST['si_contact_l_name']))
               $l_name = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_l_name']));
          break;
          case 'first_middle_i_last':
             if (isset($_POST['si_contact_f_name']))
               $f_name = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_f_name']));
             if (isset($_POST['si_contact_mi_name']))
               $mi_name = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_mi_name']));
             if (isset($_POST['si_contact_l_name']))
               $l_name = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_l_name']));
          break;
          case 'first_middle_last':
             if (isset($_POST['si_contact_f_name']))
               $f_name = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_f_name']));
             if (isset($_POST['si_contact_m_name']))
               $m_name = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_m_name']));
             if (isset($_POST['si_contact_l_name']))
               $l_name = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_l_name']));
         break;
      }
    }
    if ($si_contact_opt['email_type'] != 'not_available') {
       if (isset($_POST['si_contact_email']))
         $email = strtolower($this->ctf_clean_input($_POST['si_contact_email']));
       if ($ctf_enable_double_email == 'true') {
         if (isset($_POST['si_contact_email2']))
          $email2 = strtolower($this->ctf_clean_input($_POST['si_contact_email2']));
       }
    }

    if ($si_contact_opt['subject_type'] != 'not_available') {
        if(isset($_POST['si_contact_subject'])) {
            // posted subject text input
            $subject = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_subject']));
        }else{
            // posted subject select input
            $sid = $this->ctf_clean_input($_POST['si_contact_subject_ID']);
            if(empty($sid) && $si_contact_opt['subject_type'] == 'required') {
               $this->si_contact_error = 1;
               $fsc_error_message['subject'] = ($si_contact_opt['error_subject'] != '') ? $si_contact_opt['error_subject'] : __('Selecting a subject is required.', 'si-contact-form');
            }
            else if (empty($subjects) || !isset($subjects[$sid])) {
               $this->si_contact_error = 1;
               $fsc_error_message['subject'] = __('Requested subject not found.', 'si-contact-form');
            } else {
               $subject = $this->ctf_clean_input($subjects[$sid]);
            }
       }
    }

    if ($si_contact_opt['message_type'] != 'not_available') {
       if (isset($_POST['si_contact_message'])) {
         if ($si_contact_opt['preserve_space_enable'] == 'true')
           $message = $this->ctf_clean_input($_POST['si_contact_message'],1);
         else
           $message = $this->ctf_clean_input($_POST['si_contact_message']);
       }
    }

    // check posted input for email injection attempts
    // fights common spammer tactics
    // look for newline injections
    $this->ctf_forbidifnewlines($name);
    $this->ctf_forbidifnewlines($email);
    if ($ctf_enable_double_email == 'true')
         $this->ctf_forbidifnewlines($email2);

    $this->ctf_forbidifnewlines($subject);

    // look for lots of other injections
    $forbidden = 0;
    $forbidden = $this->ctf_spamcheckpost();
    if ($forbidden)
       wp_die("$forbidden");

   // check for banned ip
//   if( $ctf_enable_ip_bans && in_array($_SERVER['REMOTE_ADDR'], $ctf_banned_ips) )
//      wp_die(__('Your IP is Banned', 'si-contact-form'));

   // CAPS Decapitator
   if ($si_contact_opt['name_case_enable'] == 'true' && !preg_match("/[a-z]/", $message))
      $message = $this->ctf_name_case($message);

    switch ($si_contact_opt['name_format']) {
       case 'name':
        if($name == '' && $si_contact_opt['name_type'] == 'required') {
          $this->si_contact_error = 1;
          $fsc_error_message['name'] =  ($si_contact_opt['error_name'] != '') ? $si_contact_opt['error_name'] : __('Your name is required.', 'si-contact-form');
        }
      break;
      default:
        if(empty($f_name) && $si_contact_opt['name_type'] == 'required') {
          $this->si_contact_error = 1;
          $fsc_error_message['f_name']=  ($si_contact_opt['error_name'] != '') ? $si_contact_opt['error_name'] : __('Your name is required.', 'si-contact-form');
        }
        if(empty($l_name) && $si_contact_opt['name_type'] == 'required') {
          $this->si_contact_error = 1;
          $fsc_error_message['l_name'] =  ($si_contact_opt['error_name'] != '') ? $si_contact_opt['error_name'] : __('Your name is required.', 'si-contact-form');
        }
    }

   if(!empty($f_name)) $name .= $f_name;
   if(!empty($mi_name))$name .= ' '.$mi_name;
   if(!empty($m_name)) $name .= ' '.$m_name;
   if(!empty($l_name)) $name .= ' '.$l_name;

   $fsc_error_message['double_email'] = '';
   if($si_contact_opt['email_type'] == 'required') {
     if (!$this->ctf_validate_email($email)) {
         $this->si_contact_error = 1;
         $fsc_error_message['email'] = ($si_contact_opt['error_email'] != '') ? $si_contact_opt['error_email'] : __('A proper e-mail address is required.', 'si-contact-form');
     }
     if ($ctf_enable_double_email == 'true' && !$this->ctf_validate_email($email2)) {
         $this->si_contact_error = 1;
         $fsc_error_message['email2'] = ($si_contact_opt['error_email'] != '') ? $si_contact_opt['error_email'] : __('A proper e-mail address is required.', 'si-contact-form');
     }
     if ($ctf_enable_double_email == 'true' && ($email != $email2)) {
         $this->si_contact_error = 1;
         $fsc_error_message['double_email'] = ($si_contact_opt['error_email2'] != '') ? $si_contact_opt['error_email2'] : __('The two e-mail addresses did not match, please enter again.', 'si-contact-form');
     }
   }

/*// check attachment directory
if ($have_attach){
	$attach_dir = WP_PLUGIN_DIR . '/si-contact-form/attachments/';
	if ( !is_dir($attach_dir) ) {
        $this->si_contact_error = 1;
		$fsc_error_message['attach_dir_error'] = __('The temporary folder for the attachment field does not exist.', 'si-contact-form');
    } else if(!is_writable($attach_dir)) {
          $this->si_contact_error = 1;
		 $fsc_error_message['attach_dir_error'] = __('The temporary folder for the attachment field is not writable.', 'si-contact-form');
    } else {
       // delete files over 3 minutes old in the attachment directory
       $this->si_contact_clean_temp_dir($attach_dir, 3);
	}
}*/

   // optional extra fields form post validation
      for ($i = 1; $i <= $si_contact_opt['max_fields']; $i++) {
        if ($si_contact_opt['ex_field'.$i.'_label'] != '' && $si_contact_opt['ex_field'.$i.'_type'] != 'fieldset-close') {
          if ($si_contact_opt['ex_field'.$i.'_type'] == 'fieldset') {

          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'date') {

                      $cal_date_array = array(
'mm/dd/yyyy' => __('mm/dd/yyyy', 'si-contact-form'),
'dd/mm/yyyy' => __('dd/mm/yyyy', 'si-contact-form'),
'mm-dd-yyyy' => __('mm-dd-yyyy', 'si-contact-form'),
'dd-mm-yyyy' => __('dd-mm-yyyy', 'si-contact-form'),
'mm.dd.yyyy' => __('mm.dd.yyyy', 'si-contact-form'),
'dd.mm.yyyy' => __('dd.mm.yyyy', 'si-contact-form'),
'yyyy/mm/dd' => __('yyyy/mm/dd', 'si-contact-form'),
'yyyy-mm-dd' => __('yyyy-mm-dd', 'si-contact-form'),
'yyyy.mm.dd' => __('yyyy.mm.dd', 'si-contact-form'),
);
               // required validate
               ${'ex_field'.$i} = ( !isset($_POST["si_contact_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["si_contact_ex_field$i"]);
               if( (${'ex_field'.$i} == '' || ${'ex_field'.$i} == $cal_date_array[$si_contact_opt['date_format']]) && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                  $this->si_contact_error = 1;
                  $fsc_error_message["ex_field$i"]  = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('This field is required.', 'si-contact-form');
               }
               // max_len validate
               if( ${'ex_field'.$i} != '' && $si_contact_opt['ex_field'.$i.'_max_len'] != '' && strlen(${'ex_field'.$i}) > $si_contact_opt['ex_field'.$i.'_max_len']) {
                  $this->si_contact_error = 1;
                  $fsc_error_message["ex_field$i"]  = sprintf( __('Maximum of %d characters exceeded.', 'si-contact-form'), $si_contact_opt['ex_field'.$i.'_max_len'] );
               }
               // regex validate
               if( ${'ex_field'.$i} != '' && $si_contact_opt['ex_field'.$i.'_regex'] != '' && !preg_match($si_contact_opt['ex_field'.$i.'_regex'],${'ex_field'.$i}) ) {
                  $this->si_contact_error = 1;
                  $fsc_error_message["ex_field$i"]  = ($si_contact_opt['ex_field'.$i.'_regex_error'] != '') ? $si_contact_opt['ex_field'.$i.'_regex_error'] : __('Invalid input.', 'si-contact-form');
               }

          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'hidden') {
               ${'ex_field'.$i} = ( !isset($_POST["si_contact_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["si_contact_ex_field$i"]);
          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'time') {
               ${'ex_field'.$i.'h'}  = $this->ctf_clean_input($_POST["si_contact_ex_field".$i."h"]);
               ${'ex_field'.$i.'m'}  = $this->ctf_clean_input($_POST["si_contact_ex_field".$i."m"]);
               if ($si_contact_opt['time_format'] == '12')
                  ${'ex_field'.$i.'ap'} = $this->ctf_clean_input($_POST["si_contact_ex_field".$i."ap"]);
          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'attachment') {
              // need to test if a file was selected for attach.
              $ex_field_file['name'] = '';
              if(isset($_FILES["si_contact_ex_field$i"]))
                  $ex_field_file = $_FILES["si_contact_ex_field$i"];
              if ($ex_field_file['name'] == '' && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                   $this->si_contact_error = 1;
                   $fsc_error_message["ex_field$i"]  = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('This field is required.', 'si-contact-form');
              }
              if($ex_field_file['name'] != ''){  // may not be required
                 // validate the attachment now
                 $ex_field_file_check = $this->si_contact_validate_attach( $ex_field_file, "ex_field$i" );
                 if (!$ex_field_file_check['valid']) {
                     $this->si_contact_error = 1;
                     $fsc_error_message["ex_field$i"]  = $ex_field_file_check['error'];
                 } else {
                    ${'ex_field'.$i} = $ex_field_file_check['file_name'];  // needed for email message
                    // because the file should only be uploaded once, this var is set for the si_contact_form_short_code function
                    $fsc_attach_names[$i] = $ex_field_file_check['file_name'];
                 }
              }
              unset($ex_field_file);
          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'checkbox' || $si_contact_opt['ex_field'.$i.'_type'] == 'checkbox-multiple') {
             // see if checkbox children
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
                  list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                  $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                  $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                  if ($exf_opts_label != '' && $value != '') {
                     if(!preg_match("/;/", $value)) {
                        $this->si_contact_error = 1;
                        $fsc_error_message["ex_field$i"]  = __('Error: A checkbox field is not configured properly in settings. If you are trying to use multiple checkbox options, make sure this field type is set to checkbox-multiple instead of just checkbox', 'si-contact-form');
                     } else {
                        // multiple options
                         $exf_opts_array = explode(";",$value);
                     }
                     // required check (only 1 has to be checked to meet required)
                    $ex_cnt = 1;
                    $ex_reqd = 0;
                    foreach ($exf_opts_array as $k) {
                      if( ! empty($_POST["si_contact_ex_field$i".'_'.$ex_cnt]) ){
                        ${'ex_field'.$i.'_'.$ex_cnt} = $this->ctf_clean_input($_POST["si_contact_ex_field$i".'_'.$ex_cnt]);
                        $ex_reqd++;
                      }
                      $ex_cnt++;
                    }
                    if(!$ex_reqd && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                        $this->si_contact_error = 1;
                        $fsc_error_message["ex_field$i"]  = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('At least one item in this field is required.', 'si-contact-form');
                     }
                }
             }else{
                ${'ex_field'.$i} = ( !isset($_POST["si_contact_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["si_contact_ex_field$i"]);
                if(${'ex_field'.$i} == '' && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                    $this->si_contact_error = 1;
                    $fsc_error_message["ex_field$i"]  = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('This field is required.', 'si-contact-form');
                }
             }
           }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'select-multiple') {
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
                  list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                  $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                  $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                  if ($exf_opts_label != '' && $value != '') {
                     if(!preg_match("/;/", $value)) {
                        $this->si_contact_error = 1;
                        $fsc_error_message["ex_field$i"]  = __('Error: A select-multiple field is not configured properly in settings.', 'si-contact-form');
                     } else {
                        // multiple options
                         $exf_opts_array = explode(";",$value);
                     }
                     // required check (only 1 has to be checked to meet required)
                     $ex_reqd = 0;
                     ${'ex_field'.$i} = ( !isset($_POST["si_contact_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["si_contact_ex_field$i"]);
                     if (is_array(${'ex_field'.$i}) && !empty(${'ex_field'.$i}) ) {
                       // loop
                       foreach ($exf_opts_array as $k) {  // checkbox multi
                          if (in_array($k, ${'ex_field'.$i} ) ) {
                             $ex_reqd++;
                          }
                       }
                     }
                     if((!$ex_reqd || empty(${'ex_field'.$i})) && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                        $this->si_contact_error = 1;
                        $fsc_error_message["ex_field$i"]  = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('At least one item in this field is required.', 'si-contact-form');
                     }
                }
             } else {
                  $this->si_contact_error = 1;
                  $fsc_error_message["ex_field$i"]  = __('Error: A checkbox-multiple field is not configured properly in settings.', 'si-contact-form');
             }
           }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'email') {
                  ${'ex_field'.$i} = ( !isset($_POST["si_contact_ex_field$i"]) ) ? '' : strtolower($this->ctf_clean_input($_POST["si_contact_ex_field$i"]));
                  // required validate
                  if(${'ex_field'.$i} == '' && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                    $this->si_contact_error = 1;
                    $fsc_error_message["ex_field$i"]  = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('This field is required.', 'si-contact-form');
                  }
                  // max_len validate
                  if( ${'ex_field'.$i} != '' && $si_contact_opt['ex_field'.$i.'_max_len'] != '' && strlen(${'ex_field'.$i}) > $si_contact_opt['ex_field'.$i.'_max_len']) {
                     $this->si_contact_error = 1;
                     $fsc_error_message["ex_field$i"]  = sprintf( __('Maximum of %d characters exceeded.', 'si-contact-form'), $si_contact_opt['ex_field'.$i.'_max_len'] );
                  }
                  // regex validate
                  if (${'ex_field'.$i} != '' && !$this->ctf_validate_email(${'ex_field'.$i})) {
                    $this->si_contact_error = 1;
                    $fsc_error_message["ex_field$i"]  = __('Invalid e-mail address.', 'si-contact-form');
                  }
           }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'url') {
                  ${'ex_field'.$i} = ( !isset($_POST["si_contact_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["si_contact_ex_field$i"]);
                  // required validate
                  if(${'ex_field'.$i} == '' && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                    $this->si_contact_error = 1;
                    $fsc_error_message["ex_field$i"]  = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('This field is required.', 'si-contact-form');
                  }
                  // max_len validate
                  if( ${'ex_field'.$i} != '' && $si_contact_opt['ex_field'.$i.'_max_len'] != '' && strlen(${'ex_field'.$i}) > $si_contact_opt['ex_field'.$i.'_max_len']) {
                     $this->si_contact_error = 1;
                     $fsc_error_message["ex_field$i"]  = sprintf( __('Maximum of %d characters exceeded.', 'si-contact-form'), $si_contact_opt['ex_field'.$i.'_max_len'] );
                  }
                  // regex validate
                  if (${'ex_field'.$i} != '' && !$this->ctf_validate_url(${'ex_field'.$i})) {
                    $this->si_contact_error = 1;
                    $fsc_error_message["ex_field$i"]  = __('Invalid URL.', 'si-contact-form');
                  }
           }else{
                // text, textarea, radio, select, password
                if ($si_contact_opt['ex_field'.$i.'_type'] == 'textarea' && $si_contact_opt['textarea_html_allow'] == 'true') {
                      ${'ex_field'.$i} = ( !isset($_POST["si_contact_ex_field$i"]) ) ? '' : wp_kses_data(stripslashes($_POST["si_contact_ex_field$i"])); // allow only some safe html
                }else{
                      ${'ex_field'.$i} = ( !isset($_POST["si_contact_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["si_contact_ex_field$i"]);
                }
                // required validate
                if(${'ex_field'.$i} == '' && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                  $this->si_contact_error = 1;
                  $fsc_error_message["ex_field$i"]  = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('This field is required.', 'si-contact-form');
                }
                // max_len validate
                if( ${'ex_field'.$i} != '' && $si_contact_opt['ex_field'.$i.'_max_len'] != '' && strlen(${'ex_field'.$i}) > $si_contact_opt['ex_field'.$i.'_max_len']) {
                  $this->si_contact_error = 1;
                  $fsc_error_message["ex_field$i"]  = sprintf( __('Maximum of %d characters exceeded.', 'si-contact-form'), $si_contact_opt['ex_field'.$i.'_max_len'] );
                }
                // regex validate
                if( ${'ex_field'.$i} != '' && $si_contact_opt['ex_field'.$i.'_regex'] != '' && !preg_match($si_contact_opt['ex_field'.$i.'_regex'],${'ex_field'.$i}) ) {
                  $this->si_contact_error = 1;
                  $fsc_error_message["ex_field$i"]  = ($si_contact_opt['ex_field'.$i.'_regex_error'] != '') ? $si_contact_opt['ex_field'.$i.'_regex_error'] : __('Invalid regex.', 'si-contact-form');
                }
           }
        }  // end if label != ''
      } // end foreach

   if ($si_contact_opt['subject_type'] == 'required' && $subject == '') {
       $this->si_contact_error = 1;
       if (count($subjects) == 0) {
         $fsc_error_message['subject'] = ($si_contact_opt['error_subject'] != '') ? $si_contact_opt['error_subject'] : __('Subject text is required.', 'si-contact-form');
       }
   }
   if($si_contact_opt['message_type'] == 'required' &&  $message == '') {
       $this->si_contact_error = 1;
       $fsc_error_message['message'] = ($si_contact_opt['error_message'] != '') ? $si_contact_opt['error_message'] : __('Message text is required.', 'si-contact-form');
   }

  // begin captcha check if enabled
  // captcha is optional but recommended to prevent spam bots from spamming your contact form
  if ( $this->isCaptchaEnabled() ) {

     //captcha with PHP sessions

      // uncomment for temporary advanced debugging only
      /*echo "<pre>";
      echo "COOKIE ";
      var_dump($_COOKIE);
      echo "\n\n";
      echo "SESSION ";
      var_dump($_SESSION);
      echo "</pre>\n";*/

      $captcha_code = $this->ctf_clean_input($_POST['si_contact_captcha_code']);

      if (!isset($_SESSION['securimage_code_ctf_'.$form_id_num]) || empty($_SESSION['securimage_code_ctf_'.$form_id_num])) {
          $this->si_contact_error = 1;
          $fsc_error_message['captcha'] = __('Could not read CAPTCHA cookie. Try again.', 'si-contact-form');
      }else{
         if (empty($captcha_code) || $captcha_code == '') {
           $this->si_contact_error = 1;
           $fsc_error_message['captcha'] = ($si_contact_opt['error_captcha_blank'] != '') ? $si_contact_opt['error_captcha_blank'] : __('Please complete the CAPTCHA.', 'si-contact-form');
         } else {
           require_once "$captcha_path_cf/securimage.php";
           $img = new Securimage();
           $img->form_num = $form_id_num; // makes compatible with multi-forms on same page
           $valid = $img->check("$captcha_code");
           // Check, that the right CAPTCHA password has been entered, display an error message otherwise.
           if($valid == true) {
              // some empty field and time based honyepot traps for spam bots
              $hp_check = $this->si_contact_check_honeypot("$form_id_num");
              if($hp_check != 'ok') {
                  $this->si_contact_error = 1;
                  $fsc_error_message['captcha'] =  ($si_contact_opt['error_spambot'] != '') ? $si_contact_opt['error_spambot'] : __('Possible spam bot.', 'si-contact-form');
              }
             // ok can continue
           } else {
              $this->si_contact_error = 1;
              $fsc_error_message['captcha'] = ($si_contact_opt['error_captcha_wrong'] != '') ? $si_contact_opt['error_captcha_wrong'] : __('That CAPTCHA was incorrect.', 'si-contact-form');
           }
        }
     }
 } // end if enable captcha

 if (!$this->si_contact_error && !$this->isCaptchaEnabled() ) { // skip if there are already form erros, if captcha is enabled this check is done in the captcha test
    // some empty field and time based honyepot traps for spam bots
    $hp_check = $this->si_contact_check_honeypot("$form_id_num");
    if($hp_check != 'ok') {
        $this->si_contact_error = 1;
        $fsc_error_message['captcha'] =  ($si_contact_opt['error_spambot'] != '') ? $si_contact_opt['error_spambot'] : __('Possible spam bot.', 'si-contact-form');
    }
 }

  if (!$this->si_contact_error) {

     // ok to send the email, so prepare the email message
     $posted_data = array();
     // new lines should be (\n for UNIX, \r\n for Windows and \r for Mac)
     //$php_eol = ( strtoupper(substr(PHP_OS,0,3) == 'WIN') ) ? "\r\n" : "\n";
	 $php_eol = (!defined('PHP_EOL')) ? (($eol = strtolower(substr(PHP_OS, 0, 3))) == 'win') ? "\r\n" : (($eol == 'mac') ? "\r" : "\n") : PHP_EOL;
	 $php_eol = (!$php_eol) ? "\n" : $php_eol;

     if($subject != '') {
          $subj = $si_contact_opt['email_subject'] ." $subject";
     }else{
          $subj = $si_contact_opt['email_subject'];
     }
     $msg = $this->make_bold(__('To', 'si-contact-form')).": $to_contact$php_eol$php_eol";
     if ($name != '' || $email != '')  {
        $msg .= $this->make_bold(__('From', 'si-contact-form')).":$php_eol";
        switch ($si_contact_opt['name_format']) {
          case 'name':
             if($name != '') {
              $msg .= "$name$php_eol";
              $posted_data['from_name'] = $name;
             }
          break;
          case 'first_last':
              $msg .= ($si_contact_opt['title_fname'] != '') ? $si_contact_opt['title_fname'] : __('First Name:', 'si-contact-form');
              $msg .= " $f_name$php_eol";
              $msg .= ($si_contact_opt['title_lname'] != '') ? $si_contact_opt['title_lname'] : __('Last Name:', 'si-contact-form');
              $msg .= " $l_name$php_eol";
              $posted_data['first_name'] = $f_name;
              $posted_data['last_name'] = $l_name;
          break;
          case 'first_middle_i_last':
              $msg .= ($si_contact_opt['title_fname'] != '') ? $si_contact_opt['title_fname'] : __('First Name:', 'si-contact-form');
              $msg .= " $f_name$php_eol";
              $posted_data['first_name'] = $f_name;
              if($mi_name != '') {
                 $msg .= ($si_contact_opt['title_miname'] != '') ? $si_contact_opt['title_miname'] : __('Middle Initial:', 'si-contact-form');
                 $msg .= " $mi_name$php_eol";
                 $posted_data['middle_initial'] = $mi_name;
              }
              $msg .= ($si_contact_opt['title_lname'] != '') ? $si_contact_opt['title_lname'] : __('Last Name:', 'si-contact-form');
              $msg .= " $l_name$php_eol";
              $posted_data['last_name'] = $l_name;
          break;
          case 'first_middle_last':
              $msg .= ($si_contact_opt['title_fname'] != '') ? $si_contact_opt['title_fname'] : __('First Name:', 'si-contact-form');
              $msg .= " $f_name$php_eol";
              $posted_data['first_name'] = $f_name;
              if($m_name != '') {
                 $msg .= ($si_contact_opt['title_mname'] != '') ? $si_contact_opt['title_mname'] : __('Middle Name:', 'si-contact-form');
                 $msg .= " $m_name$php_eol";
                 $posted_data['middle_name'] = $m_name;
              }
              $msg .= ($si_contact_opt['title_lname'] != '') ? $si_contact_opt['title_lname'] : __('Last Name:', 'si-contact-form');
              $msg .= " $l_name$php_eol";
              $posted_data['last_name'] = $l_name;
         break;
      }
      $msg .= "$email$php_eol$php_eol";
      $posted_data['from_email'] = $email;
   }

   if ($si_contact_opt['ex_fields_after_msg'] == 'true' && $message != '') {
        $msg .= $this->make_bold(__('Message:', 'si-contact-form'))."$php_eol$message$php_eol$php_eol";
        $posted_data['message'] = $message;
   }

   // optional extra fields
   for ($i = 1; $i <= $si_contact_opt['max_fields']; $i++) {
      if ( $si_contact_opt['ex_field'.$i.'_label'] != '' && $si_contact_opt['ex_field'.$i.'_type'] != 'fieldset-close') {
          if(preg_match('/^{inline}/',$si_contact_opt['ex_field'.$i.'_label'])) {
            // remove the {inline} modifier tag from the label
              $si_contact_opt['ex_field'.$i.'_label'] = str_replace('{inline}','',$si_contact_opt['ex_field'.$i.'_label']);
          }

         if ($si_contact_opt['ex_field'.$i.'_type'] == 'fieldset') {
             $msg .= $this->make_bold($si_contact_opt['ex_field'.$i.'_label']).$php_eol;
         } else if ($si_contact_opt['ex_field'.$i.'_type'] == 'hidden') {
             list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$si_contact_opt['ex_field'.$i.'_label']); //string will be split by "," but "\," will be ignored
             $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
             $msg .= $this->make_bold($exf_opts_label)."$php_eol${'ex_field'.$i}".$php_eol.$php_eol;
             $posted_data["ex_field$i"] = ${'ex_field'.$i};
         } else if ($si_contact_opt['ex_field'.$i.'_type'] == 'time') {
             if ($si_contact_opt['time_format'] == '12')
               $concat_time = ${'ex_field'.$i.'h'}.':'.${'ex_field'.$i.'m'}.' '.${'ex_field'.$i.'ap'};
             else
               $concat_time = ${'ex_field'.$i.'h'}.':'.${'ex_field'.$i.'m'};
             $msg .= $this->make_bold($si_contact_opt['ex_field'.$i.'_label']).$php_eol.$concat_time.$php_eol.$php_eol;
             $posted_data["ex_field$i"] = $concat_time;
         } else if ($si_contact_opt['ex_field'.$i.'_type'] == 'attachment' && $si_contact_opt['php_mailer_enable'] != 'php' && ${'ex_field'.$i} != '') {
             $msg .= $this->make_bold($si_contact_opt['ex_field'.$i.'_label'])."$php_eol * ".__('File is attached:', 'si-contact-form')." ${'ex_field'.$i}".$php_eol.$php_eol;
             $posted_data["ex_field$i"] = __('File is attached:', 'si-contact-form')." ${'ex_field'.$i}";
         } else if ($si_contact_opt['ex_field'.$i.'_type'] == 'select' || $si_contact_opt['ex_field'.$i.'_type'] == 'radio') {
             list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$si_contact_opt['ex_field'.$i.'_label']); //string will be split by "," but "\," will be ignored
             $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
             $msg .= $this->make_bold($exf_opts_label)."$php_eol${'ex_field'.$i}".$php_eol.$php_eol;
             $posted_data["ex_field$i"] = ${'ex_field'.$i};
         } else if ($si_contact_opt['ex_field'.$i.'_type'] == 'select-multiple') {
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test) && preg_match("/;/", $exf_array_test) ) {
                list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                if ($exf_opts_label != '' && $value != '') {
                    if(!preg_match("/;/", $value)) {
                       // error - a select-multiple field is not configured properly in settings.
                    } else {
                         // multiple options
                         $exf_opts_array = explode(";",$value);
                    }
                    $msg .= $this->make_bold($exf_opts_label).$php_eol;
                    $posted_data["ex_field$i"] = '';
                    if (is_array(${'ex_field'.$i}) && ${'ex_field'.$i} != '') {
                       // loop
                       $ex_cnt = 1;
                       foreach ($exf_opts_array as $k) {  // select-multiple
                          if (in_array($k, ${'ex_field'.$i} ) ) {
                             $msg .= ' * '.$k.$php_eol;
                             $posted_data["ex_field$i"] .= ' * '.$k;
                             $ex_cnt++;
                          }
                       }
                    }
                    $msg .= $php_eol;
                }
             }
         } else if ($si_contact_opt['ex_field'.$i.'_type'] == 'checkbox' || $si_contact_opt['ex_field'.$i.'_type'] == 'checkbox-multiple') {
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test)  && preg_match("/;/", $exf_array_test) ) {
                list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                if ($exf_opts_label != '' && $value != '') {
                    if(!preg_match("/;/", $value)) {
                       // error
                       //A checkbox field is not configured properly in settings.
                    } else {
                         // multiple options
                         $exf_opts_array = explode(";",$value);
                    }
                    $msg .= $this->make_bold($exf_opts_label).$php_eol;
                    $posted_data["ex_field$i"] = '';
                    // loop
                    $ex_cnt = 1;
                    foreach ($exf_opts_array as $k) {  // checkbox multi
                     if( isset(${'ex_field'.$i.'_'.$ex_cnt}) && ${'ex_field'.$i.'_'.$ex_cnt} == 'selected') {
                       $msg .= ' * '.$k.$php_eol;
                       $posted_data["ex_field$i"] .= ' * '.$k;
                     }
                     $ex_cnt++;
                    }
                    $msg .= $php_eol;
                }
             } else {  // checkbox single
                 if(${'ex_field'.$i} == 'selected') {
                   $si_contact_opt['ex_field'.$i.'_label'] = trim(str_replace('\,',',',$si_contact_opt['ex_field'.$i.'_label'])); // "\," changes to ","
                   $msg .= $this->make_bold($si_contact_opt['ex_field'.$i.'_label'])."$php_eol * ".__('selected', 'si-contact-form').$php_eol.$php_eol;
                   $posted_data["ex_field$i"] = '* '.__('selected', 'si-contact-form');
                 }
             }
         } else {  // text, textarea, date, password, email, url
               if(${'ex_field'.$i} != ''){
                   if ($si_contact_opt['ex_field'.$i.'_type'] == 'textarea' && $si_contact_opt['textarea_html_allow'] == 'true') {
                        $msg .= $this->make_bold($si_contact_opt['ex_field'.$i.'_label']).$php_eol.$this->ctf_stripslashes(${'ex_field'.$i}).$php_eol.$php_eol;
                        $posted_data["ex_field$i"] = ${'ex_field'.$i};
                   }else{
                        $msg .= $this->make_bold($si_contact_opt['ex_field'.$i.'_label']).$php_eol.${'ex_field'.$i}.$php_eol.$php_eol;
                        $posted_data["ex_field$i"] = ${'ex_field'.$i};
                        if ($si_contact_opt['ex_field'.$i.'_type'] == 'email' && $email == '' && $si_contact_opt['email_type'] == 'not_available') {
                          // admin set the standard email field 'not_available' then added an email extra field type.
                          // lets capture that as the 'from_email'.
                           $email = ${'ex_field'.$i};
                           $this->ctf_forbidifnewlines($email);
                           $posted_data['from_email'] = $email;
                       }
                   }
               }
         }
       }
    } // end for

   // allow shortcode hidden fields   http://www.fastsecurecontactform.com/shortcode-options
   if ( $_SESSION["fsc_shortcode_hidden_$form_id_num"] != '') {
      $hidden_fields_test = explode(",",$_SESSION["fsc_shortcode_hidden_$form_id_num"]);
      if ( !empty($hidden_fields_test) ) {
         foreach($hidden_fields_test as $line) {
           if(preg_match("/=/", $line) ) {
               list($key, $value) = explode("=",$line);
               $key   = trim($key);
               $value = trim($value);
               if ($key != '' && $value != '') {
                 if($key == 'form_page') {  // page url
                   $msg .= $this->make_bold(__('Form Page', 'si-contact-form')).$php_eol.esc_url($form_action_url).$php_eol.$php_eol;
                   $posted_data['form_page'] = esc_url($form_action_url);
                 }else{
                   $msg .= $this->make_bold($key).$php_eol.$this->ctf_stripslashes($value).$php_eol.$php_eol;
                   $posted_data[$key] = $value;
                 }
              }
          }
        }
      }
   }
    if ($si_contact_opt['ex_fields_after_msg'] != 'true' && $message != '') {
        $msg .= $this->make_bold(__('Message:', 'si-contact-form'))."$php_eol$message$php_eol$php_eol";
        $posted_data['message'] = $message;
    }

   // subject can include posted data names feature:
   foreach ($posted_data as $key => $data) {
         if( in_array($key,array('message','full_message','akismet')) )  // disallow these
            continue;
	     if( is_string($data) )
              $subj = str_replace('['.$key.']',$data,$subj);
   }
   $subj = preg_replace('/(\[ex_field)(\d+)(\])/','',$subj); // remove empty ex_field tags
   $posted_form_name = ( $si_contact_opt['form_name'] != '' ) ? $si_contact_opt['form_name'] : sprintf(__('Form: %d', 'si-contact-form'),$form_id_num);
   $subj = str_replace('[form_label]',$posted_form_name,$subj);
   $posted_data['subject'] = $subj;

  // lookup country info for this ip
  // geoip lookup using Visitor Maps and Who's Online plugin
  $geo_loc = '';
  if( $si_contact_opt['sender_info_enable'] == 'true' &&
    file_exists( WP_PLUGIN_DIR . '/visitor-maps/include-whos-online-geoip.php') &&
    file_exists( WP_PLUGIN_DIR . '/visitor-maps/GeoLiteCity.dat') ) {
   require_once(WP_PLUGIN_DIR . '/visitor-maps/include-whos-online-geoip.php');
   $gi = geoip_open_VMWO( WP_PLUGIN_DIR . '/visitor-maps/GeoLiteCity.dat', VMWO_GEOIP_STANDARD);
    $record = geoip_record_by_addr_VMWO($gi, $_SERVER['REMOTE_ADDR']);
   geoip_close_VMWO($gi);
   $li = array();
   $li['city_name']    = (isset($record->city)) ? $record->city : '';
   $li['state_name']   = (isset($record->country_code) && isset($record->region)) ? $GEOIP_REGION_NAME[$record->country_code][$record->region] : '';
   $li['state_code']   = (isset($record->region)) ? strtoupper($record->region) : '';
   $li['country_name'] = (isset($record->country_name)) ? $record->country_name : '--';
   $li['country_code'] = (isset($record->country_code)) ? strtoupper($record->country_code) : '--';
   $li['latitude']     = (isset($record->latitude)) ? $record->latitude : '0';
   $li['longitude']    = (isset($record->longitude)) ? $record->longitude : '0';
   if ($li['city_name'] != '') {
     if ($li['country_code'] == 'US') {
         $geo_loc = $li['city_name'];
         if ($li['state_code'] != '')
            $geo_loc = $li['city_name'] . ', ' . strtoupper($li['state_code']);
     } else {      // all non us countries
             $geo_loc = $li['city_name'] . ', ' . strtoupper($li['country_code']);
     }
   } else {
     $geo_loc = '~ ' . $li['country_name'];
   }
 }
    // add some info about sender to the email message
    $userdomain = '';
    $userdomain = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $user_info_string = '';
    if ($user_ID != '') {
        //user logged in
       if ($current_user->user_login != '') $user_info_string .= __('From a WordPress user', 'si-contact-form').': '.$current_user->user_login . $php_eol;
       if ($current_user->user_email != '') $user_info_string .= __('User email', 'si-contact-form').': '.$current_user->user_email . $php_eol;
       if ($current_user->user_firstname != '') $user_info_string .= __('User first name', 'si-contact-form').': '.$current_user->user_firstname . $php_eol;
       if ($current_user->user_lastname != '') $user_info_string .= __('User last name', 'si-contact-form').': '.$current_user->user_lastname . $php_eol;
       if ($current_user->display_name != '') $user_info_string .= __('User display name', 'si-contact-form').': '.$current_user->display_name . $php_eol;
       //if ($current_user->ID != '') $user_info_string .= __('User ID', 'si-contact-form').': '.$current_user->ID . $php_eol;
    }
    $user_info_string .= __('Sent from (ip address)', 'si-contact-form').': '.esc_attr($_SERVER['REMOTE_ADDR'])." ($userdomain)".$php_eol;
    if ( $geo_loc != '' ) {
      $user_info_string .= __('Location', 'si-contact-form').': '.$geo_loc. $php_eol;
      $posted_data['sender_location'] = __('Location', 'si-contact-form').': '.$geo_loc;
    }
    $user_info_string .= __('Date/Time', 'si-contact-form').': '.date_i18n(get_option('date_format').' '.get_option('time_format'), time() ) . $php_eol;
    $user_info_string .= __('Coming from (referer)', 'si-contact-form').': '.esc_url($form_action_url). $php_eol;
    $user_info_string .= __('Using (user agent)', 'si-contact-form').': '.$this->ctf_clean_input($_SERVER['HTTP_USER_AGENT']) . $php_eol.$php_eol;
    if ($si_contact_opt['sender_info_enable'] == 'true')
       $msg .= $user_info_string;

    $posted_data['date_time'] = date_i18n(get_option('date_format').' '.get_option('time_format'), time() );

   // Check with Akismet, but only if Akismet is installed, activated, and has a KEY. (Recommended for spam control).
   if( $si_contact_opt['akismet_disable'] == 'false' ) { // per form disable feature
     //if($si_contact_opt['message_type'] != 'not_available' && $message != '' && function_exists('akismet_http_post') && get_option('wordpress_api_key') ){
     if(function_exists('akismet_http_post') && get_option('wordpress_api_key') ){
      global $akismet_api_host, $akismet_api_port;
	  $c['user_ip']    		= preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
	  $c['user_agent'] 		= (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
	  $c['referrer']   		= (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
	  $c['blog']       		= get_option('home');
      $c['blog_lang']       = get_locale(); // default 'en_US'
      $c['blog_charset']    = get_option('blog_charset');
	  $c['permalink']       = $form_action_url;
	  $c['comment_type']    = 'contact-form';
	  $c['comment_author']  = $name;
      //$c['is_test']  = "1";  // uncomment this when testing spam detection
      //$c['comment_author']  = "viagra-test-123";  // uncomment this to test spam detection
      // or  You can just put viagra-test-123 as the name when testing the form (no need to edit this php file to test it)
      if($email != '') $c['comment_author_email'] = $email;
	  //$c['comment_content'] = $message;
      $c['comment_content'] = $msg;
	  $ignore = array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' );
      foreach ( $_SERVER as $key => $value ) {
           if ( !in_array( $key, $ignore ) && is_string($value) )
               $c["$key"] = $value;
            else
               $c["$key"] = '';
      }
      $query_string = '';
	  foreach ( $c as $key => $data ) {
	     if( is_string($data) )
		    $query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';
      }
      //echo "test $akismet_api_host, $akismet_api_port, $query_string"; exit;
	  $response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
	  if ( 'true' == $response[1] ) {
	    if( $si_contact_opt['akismet_send_anyway'] == 'false' ) {
            $this->si_contact_error = 1; // Akismet says it is spam.
            $fsc_error_message['message'] = ($si_contact_opt['error_input'] != '') ? $si_contact_opt['error_input'] : __('Invalid Input - Spam?', 'si-contact-form');
            if ($user_ID != '' && current_user_can('level_10') ) {
              // show administrator a helpful message
              $fsc_error_message['message'] .= '<br />'. __('Akismet determined your message is spam. This can be caused by the message content, your email address, or your IP address being on the Akismet spam system. The administrator can turn off Akismet for the form on the form edit menu.', 'si-contact-form');
            }
        } else {
              // Akismet says it is spam. flag the subject as spam and send anyway.
              $subj = __('Akismet: Spam', 'si-contact-form'). ' - ' . $subj;
              $msg = str_replace(__('Sent from (ip address)', 'si-contact-form'),__('Akismet Spam Check: probably spam', 'si-contact-form').$php_eol.__('Sent from (ip address)', 'si-contact-form'),$msg);
              $posted_data['akismet'] = __('probably spam', 'si-contact-form');
        }
	  }else {
            $msg = str_replace(__('Sent from (ip address)', 'si-contact-form'),__('Akismet Spam Check: passed', 'si-contact-form').$php_eol.__('Sent from (ip address)', 'si-contact-form'),$msg);
            $posted_data['akismet'] = __('passed', 'si-contact-form');
      }
    } // end if(function_exists('akismet_http_post')){
   }
   $posted_data['full_message'] = $msg;

   if ($si_contact_opt['email_html'] == 'true') {
     $msg = str_replace(array("\r\n", "\r", "\n"), "<br>", $msg);
     $msg = '<html><body>' . $php_eol . $msg . '</body></html>'.$php_eol;
   }

     // wordwrap email message
     $msg = wordwrap($msg, 70,$php_eol);

  $email_off = 0;
  if ($si_contact_opt['redirect_enable'] == 'true' && $si_contact_opt['redirect_query'] == 'true' && $si_contact_opt['redirect_email_off'] == 'true')
    $email_off = 1;

  if ($si_contact_opt['export_enable'] == 'true' && $si_contact_opt['export_email_off'] == 'true')
    $email_off = 1;

  if ($si_contact_opt['silent_send'] != 'off' &&  $si_contact_opt['silent_email_off'] == 'true')
    $email_off = 1;

  if (!$this->si_contact_error) {

   if (!$email_off) {
    $ctf_email_on_this_domain = $si_contact_opt['email_from']; // optional
    // prepare the email header
    $this->si_contact_from_name  = ($name == '') ? 'WordPress' : $name;
    $this->si_contact_from_email = ($email == '') ? get_option('admin_email') : $email;

    if ($ctf_email_on_this_domain != '' ) {
         if(!preg_match("/,/", $ctf_email_on_this_domain)) {
           // just an email: user1@example.com
           $this->si_contact_mail_sender = $ctf_email_on_this_domain;
           if($email == '' || $si_contact_opt['email_from_enforced'] == 'true')
              $this->si_contact_from_email = $ctf_email_on_this_domain;
         } else {
           // name and email: webmaster,user1@example.com
           list($key, $value) = explode(",",$ctf_email_on_this_domain);
           $key   = trim($key);
           $value = trim($value);
           $this->si_contact_mail_sender = $value;
           if($name == '')
             $this->si_contact_from_name = $key;
           if($email == '' || $si_contact_opt['email_from_enforced'] == 'true')
             $this->si_contact_from_email = $value;
         }
    }
    $header_php =  "From: $this->si_contact_from_name <$this->si_contact_from_email>\n"; // header for php mail only
    $header = '';  // for php mail and wp_mail
    // process $mail_to user1@example.com,[cc]user2@example.com,[cc]user3@example.com,[bcc]user4@example.com,[bcc]user5@example.com
    // some are cc, some are bcc
    $mail_to_arr = explode( ',', $mail_to );
    $mail_to = trim($mail_to_arr[0]);
    unset($mail_to_arr[0]);
    $ctf_email_address_cc = '';
    if ($ctf_email_address_bcc != '')
            $ctf_email_address_bcc = $ctf_email_address_bcc. ',';
	foreach ( $mail_to_arr as $key => $this_mail_to ) {
	       if (preg_match("/\[bcc\]/i",$this_mail_to) )  {
                 $this_mail_to = str_replace('[bcc]','',$this_mail_to);
                 $ctf_email_address_bcc .= "$this_mail_to,";
           }else{
                 $this_mail_to = str_replace('[cc]','',$this_mail_to);
                 $ctf_email_address_cc .= "$this_mail_to,";
           }
    }
    if ($ctf_email_address_cc != '') {
            $ctf_email_address_cc = rtrim($ctf_email_address_cc, ',');
            $header .= "Cc: $ctf_email_address_cc\n"; // for php mail and wp_mail
    }
    if ($ctf_email_address_bcc != '') {
            $ctf_email_address_bcc = rtrim($ctf_email_address_bcc, ',');
            $header .= "Bcc: $ctf_email_address_bcc\n"; // for php mail and wp_mail
    }

    if ($si_contact_opt['email_reply_to'] != '') { // custom reply_to
         $header .= "Reply-To: ".$si_contact_opt['email_reply_to']."\n"; // for php mail and wp_mail
    }else if($email != '') {   // trying this: keep users reply to even when email_from_enforced
         $header .= "Reply-To: $email\n"; // for php mail and wp_mail
    }else {
         $header .= "Reply-To: $this->si_contact_from_email\n"; // for php mail and wp_mail
    }
    if ($ctf_email_on_this_domain != '') {
      $header .= "X-Sender: $this->si_contact_mail_sender\n";  // for php mail
      $header .= "Return-Path: $this->si_contact_mail_sender\n";   // for php mail
    }

    if ($si_contact_opt['email_html'] == 'true') {
            $header .= 'Content-type: text/html; charset='. get_option('blog_charset') . $php_eol;
    } else {
            $header .= 'Content-type: text/plain; charset='. get_option('blog_charset') . $php_eol;
    }

    @ini_set('sendmail_from', $this->si_contact_from_email);

    // Check for safe mode
    $this->safe_mode = ((boolean)@ini_get('safe_mode') === false) ? 0 : 1;

    if ($si_contact_opt['php_mailer_enable'] == 'php') {
      // sending with php mail
       $header_php .= $header;

      if ($ctf_email_on_this_domain != '' && !$this->safe_mode) {
          // Pass the Return-Path via sendmail's -f command.
          @mail($mail_to,$subj,$msg,$header_php, '-f '.$this->si_contact_mail_sender);
      }else{
          // the fifth parameter is not allowed in safe mode
          @mail($mail_to,$subj,$msg,$header_php);
      }

    } else {
      // sending with wp_mail
      add_filter( 'wp_mail_from', array(&$this,'si_contact_form_from_email'),1);
      add_filter( 'wp_mail_from_name', array(&$this,'si_contact_form_from_name'),1);
      if ($ctf_email_on_this_domain != '') {
         // Add an action on phpmailer_init to add Sender $this->si_contact_mail_sender for Return-path in wp_mail
         // this helps spf checking when the Sender email address matches the site domain name
         add_action('phpmailer_init', array(&$this,'si_contact_form_mail_sender'),1);
      }
        if ( $this->uploaded_files ) {
			    $attach_this_mail = array();
			    foreach ( $this->uploaded_files as $path ) {
				    $attach_this_mail[] = $path;
			    }
			    @wp_mail($mail_to,$subj,$msg,$header,$attach_this_mail);
		} else {
		        @wp_mail($mail_to,$subj,$msg,$header);
		}
    }
   } // end if (!$email_off) {

   // autoresponder feature
   if ($si_contact_opt['auto_respond_enable'] == 'true' && $email != '' && $si_contact_opt['auto_respond_subject'] != '' && $si_contact_opt['auto_respond_message'] != ''){
       $subj = $si_contact_opt['auto_respond_subject'];
       $msg =  $si_contact_opt['auto_respond_message'];

       // $posted_data is an array of the form name value pairs
       // autoresponder can include posted data, tags are set on form settings page
       foreach ($posted_data as $key => $data) {
          if( in_array($key,array('message','full_message','akismet')) )  // disallow these
            continue;
	       if( is_string($data) ) {
	         $subj = str_replace('['.$key.']',$data,$subj);
             $msg = str_replace('['.$key.']',$data,$msg);
           }
       }
       $subj = preg_replace('/(\[ex_field)(\d+)(\])/','',$subj); // remove empty ex_field tags
       $msg = preg_replace('/(\[ex_field)(\d+)(\])/','',$msg);   // remove empty ex_field tags
       $subj = str_replace('[form_label]',$posted_form_name,$subj);

       // wordwrap email message
       $msg = wordwrap($msg, 70,$php_eol);

       $header = '';
       $header_php = '';
       $auto_respond_from_name  = $si_contact_opt['auto_respond_from_name'];
       $auto_respond_from_email = $si_contact_opt['auto_respond_from_email'];
       $auto_respond_reply_to   = $si_contact_opt['auto_respond_reply_to'];
       // prepare the email header

       $header_php =  "From: $auto_respond_from_name <". $auto_respond_from_email . ">\n";
       $this->si_contact_from_name = $auto_respond_from_name;
       $this->si_contact_from_email = $auto_respond_from_email;

       $header .= "Reply-To: $auto_respond_reply_to\n";   // for php mail and wp_mail
       $header .= "X-Sender: $this->si_contact_from_email\n";  // for php mail
       $header .= "Return-Path: $this->si_contact_from_email\n";  // for php mail
       if ($si_contact_opt['auto_respond_html'] == 'true') {
               $header .= 'Content-type: text/html; charset='. get_option('blog_charset') . $php_eol;
       } else {
               $header .= 'Content-type: text/plain; charset='. get_option('blog_charset') . $php_eol;
       }

       @ini_set('sendmail_from' , $this->si_contact_from_email);
       if ($si_contact_opt['php_mailer_enable'] == 'php') {
            // autoresponder sending with php
            $header_php .= $header;
            if (!$this->safe_mode) {
                // Pass the Return-Path via sendmail's -f command.
                @mail($email,$subj,$msg,$header_php, '-f '.$this->si_contact_from_email);
            } else {
                // the fifth parameter is not allowed in safe mode
                @mail($email,$subj,$msg,$header_php);
            }
       } else {
            // autoresponder sending with wp_mail
            add_filter( 'wp_mail_from_name', array(&$this,'si_contact_form_from_name'),1);
            add_filter( 'wp_mail_from', array(&$this,'si_contact_form_from_email'),1);
	        @wp_mail($email,$subj,$msg,$header);
       }
  }

  if ($si_contact_opt['silent_send'] == 'get' && $si_contact_opt['silent_url'] != '') {
     // build query string
     $query_string = $this->si_contact_export_convert($posted_data,$si_contact_opt['silent_rename'],$si_contact_opt['silent_ignore'],$si_contact_opt['silent_add'],'query');
     if(!preg_match("/\?/", $si_contact_opt['silent_url']) )
        $silent_result = wp_remote_get( $si_contact_opt['silent_url'].'?'.$query_string, array( 'timeout' => 20, 'sslverify'=>false ) );
      else
        $silent_result = wp_remote_get( $si_contact_opt['silent_url'].'&'.$query_string, array( 'timeout' => 20, 'sslverify'=>false ) );
	 if ( !is_wp_error( $silent_result ) ) {
       $silent_result = wp_remote_retrieve_body( $silent_result );
	 }
     //echo $silent_result;
  }

  if ($si_contact_opt['silent_send'] == 'post' && $si_contact_opt['silent_url'] != '') {
     // build post_array
     $post_array = $this->si_contact_export_convert($posted_data,$si_contact_opt['silent_rename'],$si_contact_opt['silent_ignore'],$si_contact_opt['silent_add'],'array');
	 $silent_result = wp_remote_post( $si_contact_opt['silent_url'], array( 'body' => $post_array, 'timeout' => 20, 'sslverify'=>false ) );
	 if ( !is_wp_error( $silent_result ) ) {
       $silent_result = wp_remote_retrieve_body( $silent_result );
	 }
     //echo $silent_result;
  }

  if ($si_contact_opt['export_enable'] == 'true') {
      // filter posted data based on admin settings
      $posted_data_export = $this->si_contact_export_convert($posted_data,$si_contact_opt['export_rename'],$si_contact_opt['export_ignore'],$si_contact_opt['export_add'],'array');
      // Use form name from form edit page if one is set.
      $posted_form_name = ( $si_contact_opt['form_name'] != '' ) ? $si_contact_opt['form_name'] : sprintf(__('Form: %d', 'si-contact-form'),$form_id_num);
      // hook for other plugins to use (just after message posted)
      $fsctf_posted_data = (object) array('title' => $posted_form_name, 'posted_data' => $posted_data_export, 'uploaded_files' => (array) $this->uploaded_files );
      do_action_ref_array( 'fsctf_mail_sent', array( &$fsctf_posted_data ) );
   }  // end if export_enable

        $_SESSION["fsc_sent_mail"] = true; // toggle this on so check_and_send won't send back to this function a 2nd time
       if( $si_contact_opt['redirect_enable'] == 'true' ){
          $ctf_redirect_enable = 'true';
          $ctf_redirect_url = $si_contact_opt['redirect_url'];
       }
       // allow shortcode redirect to override options redirect settings
       if ( $_SESSION["fsc_shortcode_redirect_$form_id_num"] != '') {
           $ctf_redirect_enable = 'true';
           $ctf_redirect_url = strip_tags($_SESSION["fsc_shortcode_redirect_$form_id_num"]);
       }
       if ($ctf_redirect_enable == 'true') {
           if ($ctf_redirect_url == '#') {  // if you put # for the redirect URL it will redirect to the same page the form is on regardless of the page.
                $form_action_url = $this->form_action_url();
                $ctf_redirect_url = $form_action_url;
           }
           $ctf_redirect_url_before = $ctf_redirect_url; // before adding query
           // redirect query string code
           if ($si_contact_opt['redirect_query'] == 'true') {
               // build query string
               $query_string = $this->si_contact_export_convert($posted_data,$si_contact_opt['redirect_rename'],$si_contact_opt['redirect_ignore'],$si_contact_opt['redirect_add'],'query');
               if(!preg_match("/\?/", $ctf_redirect_url) )
                   $ctf_redirect_url .= '?'.$query_string;
               else
                  $ctf_redirect_url .= '&'.$query_string;
           }
           // using meta refresh instead
/*           if ($have_attach){
             // unlink attachment temp files
              foreach ( (array) $this->uploaded_files as $path ) {
                @unlink( $path );
              }
           }
           if ($ctf_redirect_url_before == $this->form_action_url()){ // redirecting to same page so will have to show a message senrt
               $ctf_redirect_url = str_replace("?fsc_form_message_sent$form_id_num=1",'',$ctf_redirect_url); // prevent this from doubling up on the URL
               $ctf_redirect_url = str_replace("&fsc_form_message_sent$form_id_num=1",'',$ctf_redirect_url); //
              if(!preg_match("/\?/", $ctf_redirect_url) )
                   $ctf_redirect_url .= '?'."fsc_form_message_sent$form_id_num=1";
               else
                  $ctf_redirect_url .= '&'."fsc_form_message_sent$form_id_num=1";
           }
		   wp_redirect( $ctf_redirect_url );
		   exit;*/
		}
    $fsc_message_sent = 1;
  } // end if ! error
 } // end if ! error

if ($have_attach){
  // unlink attachment temp files
  foreach ( (array) $this->uploaded_files as $path ) {
   @unlink( $path );
  }
}


 // if message sent stuff for form display
if ($fsc_message_sent){
// what gets printed after the form is sent, unless redirect is on.
$this->ctf_form_style = $this->si_contact_convert_css($si_contact_opt['form_style']);

$ctf_thank_you = '
<!-- Fast Secure Contact Form plugin '.esc_html($this->ctf_version).' - begin - FastSecureContactForm.com -->
<div id="FSContact'.$form_id_num.'" '.$this->ctf_form_style.'>
';

if ($si_contact_opt['border_enable'] == 'true') {
  $ctf_thank_you .= '
    <fieldset '. $this->si_contact_convert_css($si_contact_opt['border_style']).'>
';
  if ($si_contact_opt['title_border'] != '')
        $ctf_thank_you .= '      <legend>'.esc_html($si_contact_opt['title_border']).'</legend>';
}

if ($ctf_redirect_enable == 'true') {

       $ctf_redirect_timeout = absint($si_contact_opt['redirect_seconds']); // time in seconds to wait before loading another Web page

// disabled javascript refresh in favor of header meta refresh

// meta refresh page timer feature
$this->meta_string = "<meta http-equiv=\"refresh\" content=\"$ctf_redirect_timeout;URL=$ctf_redirect_url\">\n";
if (is_admin())
   add_action('admin_head', array(&$this,'si_contact_form_meta_refresh'),1);
else
   add_action('wp_head', array(&$this,'si_contact_form_meta_refresh'),1);


} // end if ($ctf_redirect_enable == 'true')



$ctf_thank_you .= '
<div '.$this->si_contact_convert_css($si_contact_opt['redirect_style']).'>
';
$ctf_thank_you .= ($si_contact_opt['text_message_sent'] != '') ? $si_contact_opt['text_message_sent'] : __('Your message has been sent, thank you.', 'si-contact-form'); // can have HTML

if ($ctf_redirect_enable == 'true') {
$ctf_thank_you .= '
  <br />
  <img src="'.plugins_url( 'ctf-loading.gif' , __FILE__ ).'" alt="'.esc_attr(__('Redirecting', 'si-contact-form')).'" />
  <a href="'.$ctf_redirect_url.'">'.__('Redirecting', 'si-contact-form').'</a>';
}
$ctf_thank_you .= '
</div>';

if ($si_contact_opt['border_enable'] == 'true') {
  $ctf_thank_you .= '
    </fieldset>';
}
$ctf_thank_you .= '
</div>
<!-- Fast Secure Contact Form plugin '.esc_html($this->ctf_version).' - end - FastSecureContactForm.com -->
';

      // thank you message html that can now be used in si_contact_form_short_code function
      // saved into a session var because the si_contact_form_short_code function can be run multiple times by other plugins applying "the_content" filter
      $_SESSION['fsc_form_display_html'] = $ctf_thank_you;

} // end if message sent

        //  print_r($fsc_error_message); exit;

} // function si_contact_check_form

function si_contact_export_convert($posted_data,$rename,$ignore,$add,$return = 'array') {
    $query_string = '';
    $posted_data_export = array();
    //rename field names array
    $rename_fields = array();
    $rename_fields_test = explode("\n",$rename);
    if ( !empty($rename_fields_test) ) {
      foreach($rename_fields_test as $line) {
         if(preg_match("/=/", $line) ) {
            list($key, $value) = explode("=",$line);
            $key   = trim($key);
            $value = trim($value);
            if ($key != '' && $value != '')
              $rename_fields[$key] = $value;
         }
      }
    }
    // add fields
    $add_fields_test = explode("\n",$add);
    if ( !empty($add_fields_test) ) {
      foreach($add_fields_test as $line) {
         if(preg_match("/=/", $line) ) {
            list($key, $value) = explode("=",$line);
            $key   = trim($key);
            $value = trim($value);
            if ($key != '' && $value != '') {
              if($return == 'array')
		        $posted_data_export[$key] = $value;
              else
                $query_string .= $key . '=' . urlencode( stripslashes($value) ) . '&';
            }
         }
      }
    }
    //ignore field names array
    $ignore_fields = array();
    $ignore_fields = array_map('trim', explode("\n", $ignore));
    // $posted_data is an array of the form name value pairs
    foreach ($posted_data as $key => $value) {
	  if( is_string($value) ) {
         if(in_array($key, $ignore_fields))
            continue;
         $key = ( isset($rename_fields[$key]) ) ? $rename_fields[$key] : $key;
         if($return == 'array')
		    $posted_data_export[$key] = $value;
         else
            $query_string .= $key . '=' . urlencode( stripslashes($value) ) . '&';
      }
    }
    if($return == 'array')
      return $posted_data_export;
    else
      return $query_string;
} // end function si_contact_export_convert


function si_contact_get_var($form_id_num,$name) {
   $value = (isset( $_GET["$form_id_num$name"])) ? $this->ctf_clean_input($_GET["$form_id_num$name"]) : '';
   return $value;
}

function si_contact_post_var($index) {
   $value = (isset( $_POST["$index"])) ? $this->ctf_clean_input($_POST["$index"]) : '';
   return $value;
}


function si_contact_get_exf_opts_array($label) {
  $exf_opts_array = array();
  $exf_opts_label = '';
  $exf_array_test = trim($label);
  if(!preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
                // Error: A radio field is not configured properly in settings
  } else {
      list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
      $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
      $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
      if ($exf_opts_label != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
             //Error: A radio field is not configured properly in settings.
          } else {
             // multiple options
             $exf_opts_array = explode(";",$value);
          }
      }
  } // end else
  return $exf_opts_array;
} //end function

// needed for making temp directories for attachments
function si_contact_init_temp_dir($dir) {
    $dir = trailingslashit( $dir );
    // make the temp directory
	wp_mkdir_p( $dir );
	//@chmod( $dir, 0733 );
	$htaccess_file = $dir . '.htaccess';
	if ( !file_exists( $htaccess_file ) ) {
	   if ( $handle = @fopen( $htaccess_file, 'w' ) ) {
		   fwrite( $handle, "Deny from all\n" );
		   fclose( $handle );
	   }
    }
    $php_file = $dir . 'index.php';
	if ( !file_exists( $php_file ) ) {
       	if ( $handle = @fopen( $php_file, 'w' ) ) {
		   fwrite( $handle, '<?php //do not delete ?>' );
		   fclose( $handle );
     	}
	}
} // end function si_contact_init_temp_dir

// needed for emptying temp directories for attachments
function si_contact_clean_temp_dir($dir, $minutes = 30) {
    // deletes all files over xx minutes old in a temp directory
  	if ( ! is_dir( $dir ) || ! is_readable( $dir ) || ! is_writable( $dir ) )
		return false;

	$count = 0;
    $list = array();
	if ( $handle = @opendir( $dir ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( $file == '.' || $file == '..' || $file == '.htaccess' || $file == 'index.php')
				continue;

			$stat = @stat( $dir . $file );
			if ( ( $stat['mtime'] + $minutes * 60 ) < time() ) {
			    @unlink( $dir . $file );
				$count += 1;
			} else {
               $list[$stat['mtime']] = $file;
            }
		}
		closedir( $handle );
        // purge xx amount of files based on age to limit a DOS flood attempt. Oldest ones first, limit 500
        if( isset($list) && count($list) > 499) {
          ksort($list);
          $ct = 1;
          foreach ($list as $k => $v) {
            if ($ct > 499) @unlink( $dir . $v );
            $ct += 1;
          }
       }
	}
	return $count;
}

// used for file attachment feature
function si_contact_validate_attach( $file, $ex_field  ) {
    global $si_contact_opt;

    $result['valid'] = true;

    if ($si_contact_opt['php_mailer_enable'] == 'php') {
        $result['valid'] = false;
		$result['error'] = __('Attachments not supported.', 'si-contact-form');
		return $result;
    }

	if ( ($file['error'] && UPLOAD_ERR_NO_FILE != $file['error']) || !is_uploaded_file( $file['tmp_name'] ) ) {
		$result['valid'] = false;
		$result['error'] = __('Attachment upload failed.', 'si-contact-form');
		return $result;
	}

	if ( empty( $file['tmp_name'] ) ) {
		$result['valid'] = false;
		$result['error'] = __('This field is required.', 'si-contact-form');
		return $result;
	}

    // check file types
    $file_type_pattern = $si_contact_opt['attach_types'];
	if ( $file_type_pattern == '' )
		$file_type_pattern = 'doc,pdf,txt,gif,jpg,jpeg,png';
    $file_type_pattern = str_replace(',','|',$si_contact_opt['attach_types']);
    $file_type_pattern = str_replace(' ','',$file_type_pattern);
	$file_type_pattern = trim( $file_type_pattern, '|' );
	$file_type_pattern = '(' . $file_type_pattern . ')';
	$file_type_pattern = '/\.' . $file_type_pattern . '$/i';

	if ( ! preg_match( $file_type_pattern, $file['name'] ) ) {
		$result['valid'] = false;
		$result['error'] = __('Attachment file type not allowed.', 'si-contact-form');
		return $result;
	}

    // check size
    $allowed_size = 1048576; // 1mb default
	if ( preg_match( '/^([[0-9.]+)([kKmM]?[bB])?$/', $si_contact_opt['attach_size'], $matches ) ) {
	     $allowed_size = (int) $matches[1];
		 $kbmb = strtolower( $matches[2] );
		 if ( 'kb' == $kbmb ) {
		     $allowed_size *= 1024;
		 } elseif ( 'mb' == $kbmb ) {
		     $allowed_size *= 1024 * 1024;
		 }
	}
	if ( $file['size'] > $allowed_size ) {
		$result['valid'] = false;
		$result['error'] = __('Attachment file size is too large.', 'si-contact-form');
		return $result;
	}

	$filename = $file['name'];

	// safer file names for scripts.
	if ( preg_match( '/\.(php|pl|py|rb|js|cgi)\d?$/', $filename ) )
		$filename .= '.txt';

 	$attach_dir = WP_PLUGIN_DIR . '/si-contact-form/attachments/';

	$filename = wp_unique_filename( $attach_dir, $filename );

	$new_file = trailingslashit( $attach_dir ) . $filename;

	if ( false === @move_uploaded_file( $file['tmp_name'], $new_file ) ) {
		$result['valid'] = false;
		$result['error'] = __('Attachment upload failed while moving file.', 'si-contact-form');
		return $result;
	}

	// uploaded only readable for the owner process
	@chmod( $new_file, 0400 );

	$this->uploaded_files[$ex_field] = $new_file;

    $result['file_name'] = $filename; // needed for email message

	return $result;
}

// makes bold html email labels
function make_bold($label) {
   global $si_contact_opt;

   if ($si_contact_opt['email_html'] == 'true')
        return '<b>'.$label.'</b>';
   else
        return $label;

}

// checks if captcha is enabled based on the current captcha permission settings set in the plugin options
function isCaptchaEnabled() {
   global $si_contact_opt;

   if ($si_contact_opt['captcha_enable'] !== 'true') {
        return false; // captcha setting is disabled for si contact
   }
   // skip the captcha if user is loggged in and the settings allow
   if (is_user_logged_in() && $si_contact_opt['captcha_perm'] == 'true') {
       // skip the CAPTCHA display if the minimum capability is met
       if ( current_user_can( $si_contact_opt['captcha_perm_level'] ) ) {
               // skip capthca
               return false;
        }
   }
   return true;
} // end function isCaptchaEnabled

function captchaCheckRequires() {
  global $captcha_path_cf;

  $ok = 'ok';
  // Test for some required things, print error message if not OK.
  if ( !extension_loaded('gd') || !function_exists('gd_info') ) {
      $this->captchaRequiresError .= '<p '.$this->ctf_error_style.'>'.__('ERROR: si-contact-form.php plugin says GD image support not detected in PHP!', 'si-contact-form').'</p>';
      $this->captchaRequiresError .= '<p>'.__('Contact your web host and ask them why GD image support is not enabled for PHP.', 'si-contact-form').'</p>';
      $ok = 'no';
  }
  if ( !function_exists('imagepng') ) {
      $this->captchaRequiresError .= '<p '.$this->ctf_error_style.'>'.__('ERROR: si-contact-form.php plugin says imagepng function not detected in PHP!', 'si-contact-form').'</p>';
      $this->captchaRequiresError .= '<p>'.__('Contact your web host and ask them why imagepng function is not enabled for PHP.', 'si-contact-form').'</p>';
      $ok = 'no';
  }
  if ( !@strtolower(ini_get('safe_mode')) == 'on' && !file_exists("$captcha_path_cf/securimage.php") ) {
       $this->captchaRequiresError .= '<p '.$this->ctf_error_style.'>'.__('ERROR: si-contact-form.php plugin says captcha_library not found.', 'si-contact-form').'</p>';
       $ok = 'no';
  }
  if ($ok == 'no')  return false;
  return true;
}

// check the honeypot traps for spam bots
// this is a very basic implementation, more agressive approaches might have to be added later
function si_contact_check_honeypot($form_id) {
    global $si_contact_opt;

    if ($si_contact_opt['honeypot_enable'] == 'false')
         return 'ok';

    // hidden honeypot field
    if( isset($_POST["email_$form_id"]) && trim($_POST["email_$form_id"]) != '')
         return 'failed honeypot';

    // server-side timestamp forgery token.
    if (!isset($_POST["si_tok_$form_id"]) || empty($_POST["si_tok_$form_id"]) || strpos($_POST["si_tok_$form_id"] , ',') === false )
         return 'no timestamp';

    $vars = explode(',', $_POST["si_tok_$form_id"]);
    if ( empty($vars[0]) || empty($vars[1]) || ! preg_match("/^[0-9]+$/",$vars[1]) )
         return 'bad timestamp';

    if ( wp_hash( $vars[1] ) != $vars[0] )
       return 'bad timestamp';

      $form_timestamp = $vars[1];
      $now_timestamp  = time();
      $human_typing_time = 5; // page load (1s) + submit (1s) + typing time (3s)
      if ( $now_timestamp - $form_timestamp < $human_typing_time )
	     return 'too fast less than 5 sec';
      if ( $now_timestamp - $form_timestamp > 1800 )
	     return 'over 30 min';

      return 'ok';

}  //  end function si_contact_validate_honeypot

// this function adds the captcha to the contact form
function si_contact_get_captcha_html($form_id_num) {
   global $ctf_captcha_url, $ctf_captcha_dir, $captcha_path_cf, $captcha_url_cf, $si_contact_gb, $si_contact_opt, $fsc_error_message;
   $req_field_ind = ( $si_contact_opt['req_field_indicator_enable'] == 'true' ) ? '<span '.$this->si_contact_convert_css($si_contact_opt['required_style']).'>'.$si_contact_opt['req_field_indicator'].'</span>' : '';

  $string = '';

// Test for some required things, print error message right here if not OK.
if ($this->captchaCheckRequires()) {

  $si_contact_opt['captcha_image_style'] = 'border-style:none; margin:0; padding:0px; padding-right:5px; float:left;';
  $si_contact_opt['reload_image_style'] = 'border-style:none; margin:0; padding:0px; vertical-align:bottom;';

// the captch html

 $string = '
<div '.$this->ctf_title_style.'> </div>
 <div ';
$this->ctf_captcha_div_style_sm = $this->si_contact_convert_css($si_contact_opt['captcha_div_style_sm']);
$this->ctf_captcha_div_style_m = $this->si_contact_convert_css($si_contact_opt['captcha_div_style_m']);

// url for no session captcha image
$securimage_show_url = $captcha_url_cf .'/securimage_show.php?';
$securimage_size = 'width="175" height="60"';
if($si_contact_opt['captcha_small'] == 'true') {
  $securimage_show_url .= 'ctf_sm_captcha=1&';
  $securimage_size = 'width="132" height="45"';
}

$parseUrl = parse_url($captcha_url_cf);
$securimage_url = $parseUrl['path'];

if($si_contact_opt['captcha_difficulty'] == 'low') $securimage_show_url .= 'difficulty=1&';
if($si_contact_opt['captcha_difficulty'] == 'high') $securimage_show_url .= 'difficulty=2&';
if($si_contact_opt['captcha_no_trans'] == 'true') $securimage_show_url .= 'no_trans=1&';

$securimage_show_rf_url = $securimage_show_url . 'ctf_form_num=' .$form_id_num;
$securimage_show_url .= 'ctf_form_num=' .$form_id_num;


$string .= ($si_contact_opt['captcha_small'] == 'true') ? $this->ctf_captcha_div_style_sm : $this->ctf_captcha_div_style_m;
$string .= '>
    <img class="ctf-captcha" id="si_image_ctf'.$form_id_num.'" ';
    $string .= ($si_contact_opt['captcha_image_style'] != '') ? 'style="' . esc_attr( $si_contact_opt['captcha_image_style'] ).'"' : '';
    $string .= ' src="'.esc_url($securimage_show_url).'" '.$securimage_size.' alt="';
    $string .= esc_attr(($si_contact_opt['tooltip_captcha'] != '') ? $si_contact_opt['tooltip_captcha'] : __('CAPTCHA Image', 'si-contact-form'));
    $string .='" title="';
    $string .= esc_attr(($si_contact_opt['tooltip_captcha'] != '') ? $si_contact_opt['tooltip_captcha'] : __('CAPTCHA Image', 'si-contact-form'));
    $string .= '" />
';

         $string .= '    <div id="si_refresh_ctf'.$form_id_num.'">
';
         $string .= '      <a href="#" rel="nofollow" title="';
         $string .= esc_attr(($si_contact_opt['tooltip_refresh'] != '') ? $si_contact_opt['tooltip_refresh'] : __('Refresh Image', 'si-contact-form'));

         $string .= '" onclick="document.getElementById(\'si_image_ctf'.$form_id_num.'\').src = \''.esc_url($securimage_show_url).'&amp;sid=\''.' + Math.random(); return false;">
';

         $string .= '      <img src="'.$captcha_url_cf.'/images/refresh.png" width="22" height="20" alt="';
         $string .= esc_attr(($si_contact_opt['tooltip_refresh'] != '') ? $si_contact_opt['tooltip_refresh'] : __('Refresh Image', 'si-contact-form'));
         $string .=  '" ';
         $string .= ($si_contact_opt['reload_image_style'] != '') ? 'style="' . esc_attr( $si_contact_opt['reload_image_style'] ).'"' : '';
         $string .=  ' onclick="this.blur();" /></a>
   </div>
   </div>

      <div '.$this->ctf_title_style.'>
                <label for="si_contact_captcha_code'.$form_id_num.'">';
     $string .= esc_html(($si_contact_opt['title_capt'] != '') ? $si_contact_opt['title_capt'] : __('CAPTCHA Code:', 'si-contact-form'));
     $string .= $req_field_ind.'</label>
        </div>
        <div '.$this->si_contact_convert_css($si_contact_opt['field_div_style']).'>'.$this->ctf_echo_if_error($fsc_error_message['captcha']).'
                <input '.$this->si_contact_convert_css($si_contact_opt['captcha_input_style']).' type="text" value="" id="si_contact_captcha_code'.$form_id_num.'" name="si_contact_captcha_code" '.$this->ctf_aria_required.' size="'.absint($si_contact_opt['captcha_field_size']).'" />
       </div>
';
} else {
      $string .= $this->captchaRequiresError;
}
  return $string;
} // end function si_contact_get_captcha_html

// shows contact form errors
function ctf_echo_if_error($this_error){
  if ($this->si_contact_error) {
    if (!empty($this_error)) {
         return '
         <div '.$this->ctf_error_style.'>'. esc_html($this_error) . '</div>
';
    }
  }
} // end function ctf_echo_if_error

// functions for protecting and validating form input vars
function ctf_clean_input($string, $preserve_space = 0) {
    if (is_string($string)) {
       if($preserve_space)
          return $this->ctf_sanitize_string(strip_tags($this->ctf_stripslashes($string)),$preserve_space);
       return trim($this->ctf_sanitize_string(strip_tags($this->ctf_stripslashes($string))));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = $this->ctf_clean_input($value,$preserve_space);
      }
      return $string;
    } else {
      return $string;
    }
} // end function ctf_clean_input

// functions for protecting and validating form vars
function ctf_sanitize_string($string, $preserve_space = 0) {
    if(!$preserve_space)
      $string = preg_replace("/ +/", ' ', trim($string));

    return preg_replace("/[<>]/", '_', $string);
} // end function ctf_sanitize_string

// functions for protecting and validating form vars
function ctf_stripslashes($string) {
        //if (get_magic_quotes_gpc()) {
          // wordpress always has magic_quotes On regardless of PHP settings!!
                return stripslashes($string);
       // } else {
        //       return $string;
       // }
} // end function ctf_stripslashes

// functions for protecting output against XSS. encode  < > & " ' (less than, greater than, ampersand, double quote, single quote).
function ctf_output_string($string) {
    $string = str_replace('&', '&amp;', $string);
    $string = str_replace('"', '&quot;', $string);
    $string = str_replace("'", '&#39;', $string);
    $string = str_replace('<', '&lt;', $string);
    $string = str_replace('>', '&gt;', $string);
    return $string;
} // end function ctf_output_string

// A function knowing about name case (i.e. caps on McDonald etc)
// $name = name_case($name);
function ctf_name_case($name) {
   global $si_contact_opt;

   if ($si_contact_opt['name_case_enable'] !== 'true') {
        return $name; // name_case setting is disabled for si contact
   }
   if ($name == '') return '';
   $break = 0;
   $newname = strtoupper($name[0]);
   for ($i=1; $i < strlen($name); $i++) {
       $subed = substr($name, $i, 1);
       if (((ord($subed) > 64) && (ord($subed) < 123)) ||
           ((ord($subed) > 48) && (ord($subed) < 58))) {
           $word_check = substr($name, $i - 2, 2);
           if (!strcasecmp($word_check, 'Mc') || !strcasecmp($word_check, "O'")) {
               $newname .= strtoupper($subed);
           }else if ($break){
               $newname .= strtoupper($subed);
           }else{
               $newname .= strtolower($subed);
           }
             $break = 0;
       }else{
             // not a letter - a boundary
             $newname .= $subed;
             $break = 1;
       }
   }
   return $newname;
} // end function ctf_name_case

// checks proper url syntax (not perfect, none of these are, but this is the best I can find)
//   tutorialchip.com/php/preg_match-examples-7-useful-code-snippets/
function ctf_validate_url($url) {

    $regex = "((https?|ftp)\:\/\/)?"; // Scheme
	$regex .= "([a-zA-Z0-9+!*(),;?&=\$_.-]+(\:[a-zA-Z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
    $regex .= "([a-zA-Z0-9-.]*)\.([a-zA-Z]{2,6})"; // Host or IP
    $regex .= "(\:[0-9]{2,5})?"; // Port
    $regex .= "(\/#\!)?"; // Path hash bang  (twitter) (mike challis added)
    $regex .= "(\/([a-zA-Z0-9+\$_-]\.?)+)*\/?"; // Path
    $regex .= "(\?[a-zA-Z+&\$_.-][a-zA-Z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
    $regex .= "(#[a-zA-Z_.-][a-zA-Z0-9+\$_.-]*)?"; // Anchor

	return preg_match("/^$regex$/", $url);

} // end function ctf_validate_url

// checks proper email syntax (not perfect, none of these are, but this is the best I can find)
function ctf_validate_email($email) {
   global $si_contact_opt;

   //check for all the non-printable codes in the standard ASCII set,
   //including null bytes and newlines, and return false immediately if any are found.
   if (preg_match("/[\\000-\\037]/",$email)) {
      return false;
   }
   // regular expression used to perform the email syntax check
   // http://fightingforalostcause.net/misc/2006/compare-email-regex.php
   $pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
   if(!preg_match($pattern, $email)){
      return false;
   }
   // Make sure the domain exists with a DNS check (if enabled in options)
   // MX records are not mandatory for email delivery, this is why this function also checks A and CNAME records.
   // if the checkdnsrr function does not exist (skip this extra check, the syntax check will have to do)
   // checkdnsrr available in Linux: PHP 4.3.0 and higher & Windows: PHP 5.3.0 and higher
   if ($si_contact_opt['email_check_dns'] == 'true') {
      if( function_exists('checkdnsrr') ) {
         list($user,$domain) = explode('@',$email);
         if(!checkdnsrr($domain.'.', 'MX') &&
            !checkdnsrr($domain.'.', 'A') &&
            !checkdnsrr($domain.'.', 'CNAME')) {
            // domain not found in DNS
            return false;
         }
      }
   }
   return true;
} // end function ctf_validate_email

// helps spam protect email input
// finds new lines injection attempts
function ctf_forbidifnewlines($input) {

 // check posted input for email injection attempts
 // Check for these common exploits
 // if you edit any of these do not break the syntax of the regex
 $input_expl = "/(<CR>|<LF>|\r|\n|%0a|%0d|content-type|mime-version|content-transfer-encoding|to:|bcc:|cc:|document.cookie|document.write|onmouse|onkey|onclick|onload)/i";
 // Loop through each POST'ed value and test if it contains one of the exploits fromn $input_expl:
   if (is_string($input)){
     $v = strtolower($input);
     $v = str_replace('donkey','',$v); // fixes invalid input with "donkey" in string
     $v = str_replace('monkey','',$v); // fixes invalid input with "monkey" in string
     if( preg_match($input_expl, $v) ){
                wp_die(__('Illegal characters in POST. Possible email injection attempt', 'si-contact-form'));
     }
   }


} // end function ctf_forbidifnewlines

// helps spam protect email input
// blocks contact form posted from other domains
function ctf_spamcheckpost() {

 if(!isset($_SERVER['HTTP_USER_AGENT'])){
     return __('Invalid User Agent', 'si-contact-form');
 }

 // Make sure the form was indeed POST'ed:
 //  (requires your html form to use: si_contact_action="post")
 if(!$_SERVER['REQUEST_METHOD'] == "POST"){
    return __('Invalid POST', 'si-contact-form');
 }

  // Make sure the form was posted from an approved host name.
 if ($this->ctf_domain_protect == 'true') {
     $print_authHosts = '';
   // Host names from where the form is authorized to be posted from:
   if (is_array($this->ctf_domain)) {
      $this->ctf_domain = array_map(strtolower, $this->ctf_domain);
      $authHosts = $this->ctf_domain;
      foreach ($this->ctf_domain as $each_domain) {
         $print_authHosts .= ' '.$each_domain;
      }
   } else {
      $this->ctf_domain =  strtolower($this->ctf_domain);
      $authHosts = array("$this->ctf_domain");
      $print_authHosts = $this->ctf_domain;
   }

   // Where have we been posted from?
   if( isset($_SERVER['HTTP_REFERER']) and trim($_SERVER['HTTP_REFERER']) != '' ) {
      $fromArray = parse_url(strtolower($_SERVER['HTTP_REFERER']));
      // Test to see if the $fromArray used www to get here.
      $wwwUsed = preg_match("/^www\./i",$fromArray['host']);
      if(!in_array((!$wwwUsed ? $fromArray['host'] : preg_replace("/^www\./i",'',$fromArray['host'])), $authHosts ) ){
         return sprintf( __('Invalid HTTP_REFERER domain. See FAQ. The domain name posted from does not match the allowed domain names of this form: %s', 'si-contact-form'), esc_html($print_authHosts) );
      }
   }
 } // end if domain protect

 // check posted input for email injection attempts
 // Check for these common exploits
 // if you edit any of these do not break the syntax of the regex
 $input_expl = "/(%0a|%0d)/i";
 // Loop through each POST'ed value and test if it contains one of the exploits fromn $input_expl:
 foreach($_POST as $k => $v){
   if (is_string($v)){
     $v = strtolower($v);
     $v = str_replace('donkey','',$v); // fixes invalid input with "donkey" in string
     $v = str_replace('monkey','',$v); // fixes invalid input with "monkey" in string
     if( preg_match($input_expl, $v) ){
       return __('Illegal characters in POST. Possible email injection attempt', 'si-contact-form');
     }
   }
 }

 return 0;
} // end function ctf_spamcheckpost

function si_contact_plugin_action_links( $links, $file ) {
    //Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ){
        $settings_link = '<a href="plugins.php?page=si-contact-form/si-contact-form.php">' .esc_html( __('Settings', 'si-contact-form')) . '</a>';
	    array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
} // end function si_contact_plugin_action_links

function si_contact_form_num() {
     // get options
    $si_contact_gb_mf = get_option("si_contact_form_gb");

    $form_num = '';
    if ( isset($_GET['ctf_form_num']) && is_numeric($_GET['ctf_form_num']) && $_GET['ctf_form_num'] > 1 && $_GET['ctf_form_num'] <= $si_contact_gb_mf['max_forms'] ) {
       $form_num = (int)$_GET['ctf_form_num'];
    }
    return $form_num;
} // end function si_contact_form_num

// load things during init
function si_contact_init() {

   if (function_exists('load_plugin_textdomain')) {
      load_plugin_textdomain('si-contact-form', false, dirname(plugin_basename(__FILE__)).'/languages' );
   }

} // end function si_contact_init

function si_contact_get_options($form_num) {
   global $si_contact_opt, $si_contact_gb, $si_contact_gb_defaults, $si_contact_option_defaults, $ctf_version;

      $si_contact_gb_defaults = array(
         'donated' => 'false',
         'max_forms' => '4',
         'max_fields' => '4',
		 'vcita_auto_install' => 'true', /* --- vCita Global Settings --- */
         'vcita_dismiss' => 'false',
		 'ctf_version' => $ctf_version
      );

     $si_contact_option_defaults = array(
         'form_name' => '',
         'welcome' => __('<p>Comments or questions are welcome.</p>', 'si-contact-form'),
         'email_to' => __('Webmaster', 'si-contact-form').','.get_option('admin_email'),
         'php_mailer_enable' => 'wordpress',
         'email_from' => '',
         'email_from_enforced' => 'false',
         'email_reply_to' => '',
         'email_bcc' => '',
         'email_subject' => get_option('blogname') . ' ' .__('Contact:', 'si-contact-form'),
         'email_subject_list' => '',
         'name_format' => 'name',
         'name_type' => 'required',
         'email_type' => 'required',
         'subject_type' => 'required',
         'message_type' => 'required',
         'preserve_space_enable' => 'false',
         'max_fields' => $si_contact_gb_defaults['max_fields'],
         'double_email' => 'false',
         'name_case_enable' => 'false',
         'sender_info_enable' => 'true',
         'domain_protect' => 'true',
         'email_check_dns' => 'false',
         'email_html' => 'false',
         'akismet_disable' => 'false',
         'akismet_send_anyway' => 'true',
         'captcha_enable' => 'true',
         'captcha_small' => 'false',
         'captcha_difficulty' => 'medium',
         'captcha_no_trans' => 'false',
         'enable_audio' => 'true',
         'enable_audio_flash' => 'false',
         'captcha_perm' => 'false',
         'captcha_perm_level' => 'read',
         'honeypot_enable' => 'false',
         'redirect_enable' => 'true',
         'redirect_seconds' => '3',
         'redirect_url' => get_option('home'),
         'redirect_query' => 'false',
         'redirect_ignore' => '',
         'redirect_rename' => '',
         'redirect_add' => '',
         'redirect_email_off' => 'false',
         'silent_send' => 'off',
         'silent_url' => '',
         'silent_ignore' => '',
         'silent_rename' => '',
         'silent_add' => '',
         'silent_email_off' => 'false',
         'export_enable' => 'true',
         'export_ignore' => '',
         'export_rename' => '',
         'export_add' => '',
         'export_email_off' => 'false',
         'ex_fields_after_msg' => 'false',
         'date_format' => 'mm/dd/yyyy',
         'cal_start_day' => '0',
         'time_format' => '12',
         'attach_types' =>  'doc,pdf,txt,gif,jpg,jpeg,png',
         'attach_size' =>   '1mb',
         'textarea_html_allow' => 'false',
         'enable_areyousure' => 'false',
         'auto_respond_enable' => 'false',
         'auto_respond_html' => 'false',
         'auto_respond_from_name' => 'WordPress',
         'auto_respond_from_email' => get_option('admin_email'),
         'auto_respond_reply_to' => get_option('admin_email'),
         'auto_respond_subject' => '',
         'auto_respond_message' => '',
         'req_field_indicator_enable' => 'true',
         'req_field_label_enable' => 'true',
         'req_field_indicator' => ' *',
         'border_enable' => 'false',
         'form_style' => 'width:375px;',
         'border_style' => 'border:1px solid black; padding:10px;',
         'required_style' => 'text-align:left;',
         'notes_style' => 'text-align:left;',
         'title_style' => 'text-align:left; padding-top:5px;',
         'field_style' => 'text-align:left; margin:0;',
         'field_div_style' => 'text-align:left;',
         'error_style' => 'text-align:left; color:red;',
         'select_style' => 'text-align:left;',
         'captcha_div_style_sm' => 'width:175px; height:50px; padding-top:2px;',
         'captcha_div_style_m' => 'width:250px; height:65px; padding-top:2px;',
         'captcha_input_style' => 'text-align:left; margin:0; width:50px;',
         'submit_div_style' => 'text-align:left; padding-top:2px;',
         'button_style' => 'cursor:pointer; margin:0;',
         'reset_style' => 'cursor:pointer; margin:0;',
         'powered_by_style' => 'font-size:x-small; font-weight:normal; padding-top:5px;',
         'redirect_style' => 'text-align:left;',
         'field_size' => '40',
         'captcha_field_size' => '6',
         'text_cols' => '30',
         'text_rows' => '10',
         'aria_required' => 'false',
         'auto_fill_enable' => 'true',
         'title_border' => __('Contact Form:', 'si-contact-form'),
         'title_dept' => '',
         'title_select' => '',
         'title_name' => '',
         'title_fname' => '',
         'title_mname' => '',
         'title_miname' => '',
         'title_lname' => '',
         'title_email' => '',
         'title_email2' => '',
         'title_email2_help' => '',
         'title_subj' => '',
         'title_mess' => '',
         'title_capt' => '',
         'title_submit' => '',
         'title_reset' => '',
         'title_areyousure' => '',
         'text_message_sent' => '',
         'tooltip_required' => '',
         'tooltip_captcha' => '',
         'tooltip_audio' => '',
         'tooltip_refresh' => '',
         'tooltip_filetypes' => '',
         'tooltip_filesize' => '',
         'enable_reset' => 'false',
         'enable_credit_link' => 'false',
         'error_contact_select' => '',
         'error_name'           => '',
         'error_email'          => '',
         'error_email2'         => '',
         'error_field'          => '',
         'error_subject'        => '',
         'error_message'        => '',
         'error_input'          => '',
         'error_captcha_blank'  => '',
         'error_captcha_wrong'  => '',
         'error_correct'        => '',
         'error_spambot'        => '',
         'vcita_enabled'        => 'false', /* --- vCita Settings --- */
         'vcita_approved'       => 'false', /* --- vCita Settings --- */
         'vcita_uid'            => '',
         'vcita_email'          => '',
         'vcita_confirm_tokens'	=> '',
         'vcita_initialized'	=> 'false',
         'vcita_first_name'	    => '',
         'vcita_last_name'	    => '',
  );

  // optional extra fields
  $si_contact_max_fields = $si_contact_gb_defaults['max_fields'];
  if ($si_contact_opt = get_option("si_contact_form$form_num")) { // when not in admin
     if (isset($si_contact_opt['max_fields'])) // use previous setting if it is set
     $si_contact_max_fields = $si_contact_opt['max_fields'];
  }

  for ($i = 1; $i <= $si_contact_max_fields; $i++) { // initialize new
        $si_contact_option_defaults['ex_field'.$i.'_default'] = '0';
        $si_contact_option_defaults['ex_field'.$i.'_default_text'] = '';
        $si_contact_option_defaults['ex_field'.$i.'_req'] = 'false';
        $si_contact_option_defaults['ex_field'.$i.'_label'] = '';
        $si_contact_option_defaults['ex_field'.$i.'_type'] = 'text';
        $si_contact_option_defaults['ex_field'.$i.'_max_len'] = '';
        $si_contact_option_defaults['ex_field'.$i.'_label_css'] = '';
        $si_contact_option_defaults['ex_field'.$i.'_input_css'] = '';
        $si_contact_option_defaults['ex_field'.$i.'_attributes'] = '';
        $si_contact_option_defaults['ex_field'.$i.'_regex'] = '';
        $si_contact_option_defaults['ex_field'.$i.'_regex_error'] = '';
        $si_contact_option_defaults['ex_field'.$i.'_notes'] = '';
        $si_contact_option_defaults['ex_field'.$i.'_notes_after'] = '';
  }

  // upgrade path from old version
  if (!get_option('si_contact_form') && get_option('si_contact_email_to')) {
    // just now updating, migrate settings
    $si_contact_option_defaults = $this->si_contact_migrate($si_contact_option_defaults);
  }

  // upgrade path from old version  2.0.1 or older
  if (!get_option('si_contact_form_gb') && get_option('si_contact_form')) {
    // just now updating, migrate settings
    $si_contact_gb_defaults = $this->si_contact_migrate2($si_contact_gb_defaults);
  }

  // install the global option defaults
  add_option('si_contact_form_gb',  $si_contact_gb_defaults, '', 'yes');

  // install the option defaults
  add_option('si_contact_form',  $si_contact_option_defaults, '', 'yes');

  // multi-form
  $si_contact_max_forms = ( isset($_POST['si_contact_max_forms']) && is_numeric($_POST['si_contact_max_forms']) ) ? $_POST['si_contact_max_forms'] : $si_contact_gb_defaults['max_forms'];
  for ($i = 2; $i <= $si_contact_max_forms; $i++) {
     add_option("si_contact_form$i", $si_contact_option_defaults, '', 'yes');
  }

  // get the options from the database
  $si_contact_gb = get_option("si_contact_form_gb");
  
  /* --- vCita Migrate - Start --- */
  
  // Upgrade ! - Save state and check if the user already in vCita, happens only once.
  if (!isset($si_contact_gb['vcita_auto_install'])) {
    $si_contact_gb['vcita_auto_install'] = 'false';
  }

  // Upgrade ! - Set initial value for dismiss flag
  if (!isset($si_contact_gb['vcita_dismiss'])) {
    $si_contact_gb['vcita_dismiss'] = 'false';
  }

  /* --- vCita Migrate - End --- */
  
  // Save the previous version 
  if (isset($si_contact_gb['ctf_version'])) {
	$ctf_previous_version = $si_contact_gb['ctf_version'];
  } else {
	$ctf_previous_version = 'new';
  }
 
  // array merge incase this version has added new options
  $si_contact_gb = array_merge($si_contact_gb_defaults, $si_contact_gb);
  
  $si_contact_gb['ctf_version'] = $ctf_version;

  update_option("si_contact_form_gb", $si_contact_gb);

  // get the options from the database
  $si_contact_gb = get_option("si_contact_form_gb");

  // get the options from the database
  $si_contact_opt = get_option("si_contact_form$form_num");

  if (!isset($si_contact_opt['max_fields'])) {  // updated from version < 3.0.3
          $si_contact_opt['max_fields'] = $si_contact_gb['max_fields'];
          update_option("si_contact_form$form_num", $si_contact_opt);
  }
  
  // array merge incase this version has added new options
  $si_contact_opt = array_merge($si_contact_option_defaults, $si_contact_opt);

  // strip slashes on get options array
  foreach($si_contact_opt as $key => $val) {
           $si_contact_opt[$key] = $this->ctf_stripslashes($val);
  }
  if ($si_contact_opt['title_style'] == '' && $si_contact_opt['field_style'] == '') {
     // if styles seem to be blank, reset styles
     $si_contact_opt = $this->si_contact_copy_styles($si_contact_option_defaults,$si_contact_opt);
  }

  // new field type defaults on version 2.6.3
  if ( !isset($si_contact_gb['2.6.3']) ) {
          // optional extra fields
    for ($i = 1; $i <= $si_contact_opt['max_fields']; $i++) {
        if ($si_contact_opt['ex_field'.$i.'_label'] != '' && $si_contact_opt['ex_field'.$i.'_type'] != 'radio' && $si_contact_opt['ex_field'.$i.'_type'] != 'select' ) {
                $si_contact_opt['ex_field'.$i.'_default'] = '0';
        }
        if ($si_contact_opt['ex_field'.$i.'_label'] == '') {
          $si_contact_opt['ex_field'.$i.'_default'] = '0';
          $si_contact_opt['ex_field'.$i.'_default_text'] = '';
          $si_contact_opt['ex_field'.$i.'_req'] = 'false';
          $si_contact_opt['ex_field'.$i.'_label'] = '';
          $si_contact_opt['ex_field'.$i.'_type'] = 'text';
          $si_contact_opt['ex_field'.$i.'_max_len'] = '';
          $si_contact_opt['ex_field'.$i.'_label_css'] = '';
          $si_contact_opt['ex_field'.$i.'_input_css'] = '';
          $si_contact_opt['ex_field'.$i.'_attributes'] = '';
          $si_contact_opt['ex_field'.$i.'_regex'] = '';
          $si_contact_opt['ex_field'.$i.'_regex_error'] = '';
          $si_contact_opt['ex_field'.$i.'_notes'] = '';
          $si_contact_opt['ex_field'.$i.'_notes_after'] = '';
        }
    }
    update_option("si_contact_form", $si_contact_opt);
    for ($i = 2; $i <= $si_contact_gb['max_forms']; $i++) {
       // get the options from the database
       $si_contact_opt{$i} = get_option("si_contact_form$i");
       for ($f = 1; $f <= $si_contact_opt['max_fields']; $f++) {
         if ($si_contact_opt{$i}['ex_field'.$f.'_label'] != '' && $si_contact_opt{$i}['ex_field'.$f.'_type'] != 'radio' && $si_contact_opt{$i}['ex_field'.$f.'_type'] != 'select' ) {
                $si_contact_opt{$i}['ex_field'.$f.'_default'] = '0';
         }
         if ($si_contact_opt{$i}['ex_field'.$f.'_label'] == '') {
          $si_contact_opt{$i}['ex_field'.$f.'_default'] = '0';
         }
       }
       update_option("si_contact_form$i", $si_contact_opt{$i});
       unset($si_contact_opt{$i});
    }
    $si_contact_opt = get_option("si_contact_form$form_num");
    $si_contact_opt = array_merge($si_contact_option_defaults, $si_contact_opt);
    foreach($si_contact_opt as $key => $val) {
           $si_contact_opt[$key] = $this->ctf_stripslashes($val);
    }
    $si_contact_gb['2.6.3'] = 1;
    update_option("si_contact_form_gb", $si_contact_gb);
    $si_contact_gb = get_option("si_contact_form_gb");
    $si_contact_gb = array_merge($si_contact_gb_defaults, $si_contact_gb);
  }
  
  /* --- vCita User Initialization - Start --- */
  
  $si_contact_opt = $this->vcita_validate_initialized_user($form_num,
                                                           $si_contact_opt,
                                                           $si_contact_gb,
                                                           $ctf_previous_version);

  /* --- vCita User Initialization - End --- */
          //print_r($si_contact_opt);
  return $si_contact_gb;

} // end function si_contact_get_options

// used when resetting or copying style settings
function si_contact_copy_styles($this_form_arr,$destination_form_arr) {

     $style_copy_arr = array(
     'border_enable','form_style','border_style','required_style','notes_style',
     'title_style','field_style','field_div_style','error_style','select_style',
     'captcha_div_style_sm','captcha_div_style_m','captcha_input_style','submit_div_style','button_style', 'reset_style',
     'powered_by_style','redirect_style','field_size','captcha_field_size','text_cols','text_rows');
     foreach($style_copy_arr as $style_copy) {
           $destination_form_arr[$style_copy] = $this_form_arr[$style_copy];
     }
     return $destination_form_arr;
}

function si_contact_start_session() {
  // start the PHP session - used by CAPTCHA, the form action logic, and also used by vCita
  // this has to be set before any header output
  // echo "starting session ctf";
  // start cookie session
  if( !isset( $_SESSION ) ) { // play nice with other plugins
    //set the $_SESSION cookie into HTTPOnly mode for better security
    if (version_compare(PHP_VERSION, '5.2.0') >= 0)  // supported on PHP version 5.2.0  and higher
      @ini_set("session.cookie_httponly", 1);
    session_cache_limiter ('private, must-revalidate');
    session_start();
   // echo "session started ctf";
  }
  if(isset($_SESSION['fsc_form_display_html']))
       unset($_SESSION['fsc_form_display_html']); // clear for next page
  if(isset($_SESSION['fsc_sent_mail']))
       unset($_SESSION['fsc_sent_mail']);  // clear for next page
  
  if (is_admin()) {
    $_SESSION["vcita_expert"] = true;
  }
	
} // end function si_contact_start_session

function si_contact_migrate($si_contact_option_defaults) {
  // read the options from the prior version
   $new_options = array ();
   foreach($si_contact_option_defaults as $key => $val) {
      $new_options[$key] = $this->ctf_stripslashes( get_option( "si_contact_$key" ));
      // now delete the options from the prior version
      delete_option("si_contact_$key");
   }
   // delete settings no longer used
   delete_option('si_contact_email_language');
   delete_option('si_contact_email_charset');
   delete_option('si_contact_email_encoding');
   // by returning this the old settings will carry over to the new version
   return $new_options;
} //  end function si_contact_migrate

function si_contact_migrate2($si_contact_gb_defaults) {
  // read the options from the prior version

   $new_options = array ();
   $migrate_opt = get_option("si_contact_form");
   $new_options['donated'] = $migrate_opt['donated'];
   $new_options['max_forms'] = $si_contact_gb_defaults['max_forms'];
   $new_options['max_fields'] = $si_contact_gb_defaults['max_fields'];
   if(defined('SI_CONTACT_FORM_MAX_FORMS') && SI_CONTACT_FORM_MAX_FORMS > $si_contact_gb_defaults['max_forms']) {
    $new_options['max_forms'] = SI_CONTACT_FORM_MAX_FORMS;
   }
   if(defined('SI_CONTACT_FORM_MAX_FIELDS') && SI_CONTACT_FORM_MAX_FIELDS > $si_contact_gb_defaults['max_fields']) {
    $new_options['max_fields'] = SI_CONTACT_FORM_MAX_FIELDS;
   }
   unset($migrate_opt);

   // by returning this the old settings will carry over to the new version
   //print_r($new_options); exit;
   return $new_options;
} //  end function si_contact_migrate2

// restores settings from a contact form settings backup file
function si_contact_form_backup_restore($bk_form_num) {
  global $si_contact_opt, $si_contact_gb, $si_contact_gb_defaults, $si_contact_option_defaults;

   require_once WP_PLUGIN_DIR . '/si-contact-form/admin/si-contact-form-restore.php';

} // end function si_contact_form_backup_restore

// outputs a contact form settings backup file
function si_contact_backup_download() {
  global $si_contact_opt, $si_contact_gb, $si_contact_gb_defaults, $si_contact_option_defaults, $ctf_version;

  require_once WP_PLUGIN_DIR . '/si-contact-form/admin/si-contact-form-backup.php';

} // end function si_contact_backup_download


function get_captcha_url_cf() {

  // The captcha URL cannot be on a different domain as the site rewrites to or the cookie won't work
  // also the path has to be correct or the image won't load.
  // WP_PLUGIN_URL was not getting the job done! this code should fix it.

  //http://media.example.com/wordpress   WordPress address get_option( 'siteurl' )
  //http://tada.example.com              Blog address      get_option( 'home' )

  //http://example.com/wordpress  WordPress address get_option( 'siteurl' )
  //http://example.com/           Blog address      get_option( 'home' )
  // even works on multisite, network activated
  $site_uri = parse_url(get_option('home'));
  $home_uri = parse_url(get_option('siteurl'));

  $captcha_url_cf  = plugins_url( 'captcha' , __FILE__ );

  if ($site_uri['host'] == $home_uri['host']) {
      // use $captcha_url_cf above
  } else {
      $captcha_url_cf  = get_option( 'home' ) . '/'.PLUGINDIR.'/si-contact-form/captcha';
  }
  // set the type of request (SSL or not)
  if ( is_ssl() ) {
		$captcha_url_cf = preg_replace('|http://|', 'https://', $captcha_url_cf);
  }

  return $captcha_url_cf;
}

function si_contact_admin_head() {
 // only load this header stuff on the admin settings page
if(isset($_GET['page']) && is_string($_GET['page']) && preg_match('/si-contact-form.php$/',$_GET['page']) ) {
?>
<!-- begin Fast Secure Contact Form - admin settings page header code -->
<style type="text/css">
div.fsc-star-holder { position: relative; height:19px; width:100px; font-size:19px;}
div.fsc-star {height: 100%; position:absolute; top:0px; left:0px; background-color: transparent; letter-spacing:1ex; border:none;}
.fsc-star1 {width:20%;} .fsc-star2 {width:40%;} .fsc-star3 {width:60%;} .fsc-star4 {width:80%;} .fsc-star5 {width:100%;}
.fsc-star.fsc-star-rating {background-color: #fc0;}
.fsc-star img{display:block; position:absolute; right:0px; border:none; text-decoration:none;}
div.fsc-star img {width:19px; height:19px; border-left:1px solid #fff; border-right:1px solid #fff;}
#main fieldset {border: 1px solid #B8B8B8; padding:19px; margin: 0 0 20px 0;background: #F1F1F1; font:13px Arial, Helvetica, sans-serif;}
.form-tab {background:#F1F1F1; display:block; font-weight:bold; padding:7px 20px; float:left; font-size:13px; margin-bottom:-1px; border:1px solid #B8B8B8; border-bottom:none;}
.submit {padding:7px; margin-bottom:15px;}
.fsc-error{background-color:#ffebe8;border-color:red;border-width:1px;border-style:solid;padding:5px;margin:5px 5px 20px;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;}
.fsc-error a{color:#c00;}
.fsc-notice{background-color:#ffffe0;border-color:#e6db55;border-width:1px;border-style:solid;padding:5px;margin:5px 5px 20px;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;}
.fsc-success{background-color:#E6EFC2;border-color:#C6D880;border-width:1px;border-style:solid;padding:5px;margin:5px 5px 20px;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;}
.vcita-label{width: 93px; display: block; float: left; margin-top: 4px;}
</style>
<!-- end Fast Secure Contact Form - admin settings page header code -->
<?php

  } // end if(isset($_GET['page'])

}

// message sent meta refresh when redirect is on
function si_contact_form_meta_refresh() {
 echo $this->meta_string;
}

function si_contact_form_from_email() {
 return $this->si_contact_from_email;
}

function si_contact_form_from_name() {
 return $this->si_contact_from_name;
}

function si_contact_form_mail_sender($phpmailer) {
 // add Sender for Return-path to wp_mail
 $phpmailer->Sender = $this->si_contact_mail_sender;
}

function ctf_notes($notes) {
           return   '
        <div '.$this->ctf_notes_style.'>
         '.$notes.'
        </div>
        ';
}

function si_contact_convert_css($string) {
    // sanitize admin Modifiable CSS Style Feature
    if( preg_match("/^style=\"(.*)\"$/i", $string, $matches) ){
      return 'style="'.esc_attr($matches[1]).'"';
    }
    if( preg_match("/^class=\"(.*)\"$/i", $string, $matches) ){
      return 'class="'.esc_attr($matches[1]).'"';
    }
    return 'style="'.esc_attr($string).'"';
} // end function si_contact_convert_css

/**
 * Remotely fetch, cache, and display HTML ad for the Fast Secure Contact Form Newsletter plugin addon.
 * To use, either add kws_get_remote_ad() to the plugin, or
 * add `do_action('example_do_action');` where the ad should be, then
 * `add_action('example_do_action', 'kws_get_remote_ad');` elsewhere in the plugin.
 */
function kws_get_remote_ad() {

    // The ad is stored locally for 30 days as a transient. See if it exists.
    $cache = function_exists('get_site_transient') ? get_site_transient('fscf_kws_ad') : get_transient('fscf_kws_ad');

    // If it exists, use that (so we save some request time), unless ?cache is set.
    if(!empty($cache) && !isset($_REQUEST['cache'])) { echo $cache; return; }

    // Grab the FSCF settings for version info
    $si_contact_gb = get_option("si_contact_form_gb");

    // Get the advertisement remotely. An encrypted site identifier, the language of the site, and the version of the FSCF plugin will be sent to katz.co
    $response = wp_remote_post('http://katz.co/ads/', array('timeout' => 45,'body' => array('siteid' => sha1(site_url()), 'language' => get_bloginfo('language'), 'version' => (isset($si_contact_gb) && isset($si_contact_gb['ctf_version'])) ? $si_contact_gb['ctf_version'] : null )));

    // If it was a successful request, process it.
    if(!is_wp_error($response)) {

        // Basically, remove <script>, <iframe> and <object> tags for security reasons
        $body = strip_tags(trim(rtrim($response['body'])), '<b><strong><em><i><span><u><ul><li><ol><div><attr><cite><a><style><blockquote><q><p><form><br><meta><option><textarea><input><select><pre><code><s><del><small><table><tbody><tr><th><td><tfoot><thead><u><dl><dd><dt><col><colgroup><fieldset><address><button><aside><article><legend><label><source><kbd><tbody><hr><noscript><link><h1><h2><h3><h4><h5><h6><img>');

        // If the result is empty, cache it for 8 hours. Otherwise, cache it for 30 days.
        $cache_time = empty($response['body']) ? floatval(60*60*8) : floatval(60*60*30);

        if(function_exists('set_site_transient')) {
            set_site_transient('fscf_kws_ad', $body, $cache_time);
        } else {
            set_transient('fscf_kws_ad', $body, $cache_time);
        }

        // Print the results.
        echo  $body;
    }
}

function fscf_enqueue_scripts() {
 // used when clicking the link to install the Fast Secure Contact Form Newsletter plugin addon.
  if(isset($_GET['page']) && is_string($_GET['page']) && preg_match('/si-contact-form.php$/',$_GET['page']) ) {
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');
  }
}

} // end of class
} // end of if class


if (class_exists("siContactForm")) {
 $si_contact_form = new siContactForm();
}

if (isset($si_contact_form)) {

  $captcha_url_cf  = $si_contact_form->get_captcha_url_cf();
  $captcha_path_cf = WP_PLUGIN_DIR . '/si-contact-form/captcha';

  // only used for the no-session captcha setting
  $ctf_captcha_url = $captcha_url_cf  . '/temp/';
  $ctf_captcha_dir = $captcha_path_cf . '/temp/';

  // si_contact initialize options
  add_action('init', array(&$si_contact_form, 'si_contact_init'),1);

  //$si_contact_gb = get_option("si_contact_form_gb");

  // start the PHP session - used by CAPTCHA, the form action logic, and also used by vCita
  add_action('init', array(&$si_contact_form,'si_contact_start_session'),1);
  // process the form POST logic
  add_action('init', array(&$si_contact_form,'si_contact_check_and_send'),10);

  // si contact form admin options
  add_action('admin_menu', array(&$si_contact_form,'si_contact_add_tabs'),1);
  add_action('admin_head', array(&$si_contact_form,'si_contact_admin_head'),1);

  add_action('wp_footer', array(&$si_contact_form,'vcita_si_contact_add_script'),1);

  // this is for downloading settings backup txt file.
  add_action('admin_init', array(&$si_contact_form,'si_contact_backup_download'),1);

  add_action('admin_init', array(&$si_contact_form,'fscf_enqueue_scripts'),2);

  add_action('admin_enqueue_scripts', array(&$si_contact_form,'vcita_add_admin_js'),1);
  
  add_action('admin_notices', array(&$si_contact_form, 'si_contact_vcita_admin_warning'));

  // adds "Settings" link to the plugin action page
  add_filter( 'plugin_action_links', array(&$si_contact_form,'si_contact_plugin_action_links'),10,2);

  // use shortcode to print the contact form or process contact form logic
  // can use dashes or underscores: [si-contact-form] or [si_contact_form]
  add_shortcode('si_contact_form', array(&$si_contact_form,'si_contact_form_short_code'),1);
  add_shortcode('si-contact-form', array(&$si_contact_form,'si_contact_form_short_code'),1);

  // If you want to use shortcodes in your widgets or footer
  add_filter('widget_text', 'do_shortcode');
  add_filter('wp_footer', 'do_shortcode');

    // options deleted when this plugin is deleted in WP 2.7+
  if ( function_exists('register_uninstall_hook') )
     register_uninstall_hook(__FILE__, 'si_contact_unset_options');

}

?>