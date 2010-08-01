<?php
/*
Fast and Secure Contact Form
Mike Challis
http://www.642weather.com/weather/scripts.php
*/

// the form is being processed to send the mail now

    // check all input variables
    $cid = $this->ctf_clean_input($_POST['si_contact_CID']);
    if(empty($cid)) {
       $this->si_contact_error = 1;
       $si_contact_error_contact = ($si_contact_opt['error_contact_select'] != '') ? $si_contact_opt['error_contact_select'] : __('Selecting a contact is required.', 'si-contact-form');
    }
    else if (!isset($contacts[$cid]['CONTACT'])) {
        $this->si_contact_error = 1;
        $si_contact_error_contact = __('Requested Contact not found.', 'si-contact-form');
    }
    if (empty($ctf_contacts)) {
       $this->si_contact_error = 1;
    }
    $mail_to    = ( isset($contacts[$cid]['EMAIL']) )   ? $this->ctf_clean_input($contacts[$cid]['EMAIL'])  : '';
    $to_contact = ( isset($contacts[$cid]['CONTACT']) ) ? $this->ctf_clean_input($contacts[$cid]['CONTACT']): '';

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
               $si_contact_error_subject = ($si_contact_opt['error_subject'] != '') ? $si_contact_opt['error_subject'] : __('Selecting a subject is required.', 'si-contact-form');
            }
            else if (empty($subjects) || !isset($subjects[$sid])) {
               $this->si_contact_error = 1;
               $si_contact_error_subject = __('Requested subject not found.', 'si-contact-form');
            } else {
               $subject = $this->ctf_clean_input($subjects[$sid]);
            }
       }
    }

    if ($si_contact_opt['message_type'] != 'not_available') {
       if (isset($_POST['si_contact_message']))
         $message = $this->ctf_clean_input($_POST['si_contact_message']);
    }
    if ( $this->isCaptchaEnabled() ) {
     $captcha_code = $this->ctf_clean_input($_POST['si_contact_captcha_code']);
    }

    // check posted input for email injection attempts
    // fights common spammer tactics
    // look for newline injections
    $this->ctf_forbidifnewlines($name);
    $this->ctf_forbidifnewlines($email);
    if ($ctf_enable_double_email == 'true') {
       $this->ctf_forbidifnewlines($email2);
    }
    $this->ctf_forbidifnewlines($subject);

    // look for lots of other injections
    $forbidden = 0;
    $forbidden = $this->ctf_spamcheckpost();
    if ($forbidden) {
       wp_die(__('Contact Form has Invalid Input', 'si-contact-form'));
    }

   // check for banned ip
   if( $ctf_enable_ip_bans && in_array($_SERVER['REMOTE_ADDR'], $ctf_banned_ips) ) {
      wp_die(__('Your IP is Banned', 'si-contact-form'));
   }

   // CAPS Decapitator
   if ($si_contact_opt['name_case_enable'] == 'true' && !preg_match("/[a-z]/", $message)) {
      $message = $this->ctf_name_case($message);
   }

    switch ($si_contact_opt['name_format']) {
       case 'name':
        if(empty($name) && $si_contact_opt['name_type'] == 'required') {
          $this->si_contact_error = 1;
          $si_contact_error_name =  ($si_contact_opt['error_name'] != '') ? $si_contact_opt['error_name'] : __('Your name is required.', 'si-contact-form');
        }
      break;
      default:
        if(empty($f_name) && $si_contact_opt['name_type'] == 'required') {
          $this->si_contact_error = 1;
          $si_contact_error_f_name =  ($si_contact_opt['error_name'] != '') ? $si_contact_opt['error_name'] : __('Your name is required.', 'si-contact-form');
        }
        if(empty($l_name) && $si_contact_opt['name_type'] == 'required') {
          $this->si_contact_error = 1;
          $si_contact_error_l_name =  ($si_contact_opt['error_name'] != '') ? $si_contact_opt['error_name'] : __('Your name is required.', 'si-contact-form');
        }
    }

   if(!empty($f_name))
     $name .= $f_name;
   if(!empty($mi_name))
     $name .= ' '.$mi_name;
   if(!empty($m_name))
     $name .= ' '.$m_name;
   if(!empty($l_name))
     $name .= ' '.$l_name;

   if($si_contact_opt['email_type'] == 'required') {
     if (!$this->ctf_validate_email($email)) {
         $this->si_contact_error = 1;
         $si_contact_error_email = ($si_contact_opt['error_email'] != '') ? $si_contact_opt['error_email'] : __('A proper e-mail address is required.', 'si-contact-form');
     }
     if ($ctf_enable_double_email == 'true' && !$this->ctf_validate_email($email2)) {
         $this->si_contact_error = 1;
         $si_contact_error_email2 = ($si_contact_opt['error_email'] != '') ? $si_contact_opt['error_email'] : __('A proper e-mail address is required.', 'si-contact-form');
     }
     if ($ctf_enable_double_email == 'true' && ($email != $email2)) {
         $this->si_contact_error = 1;
         $si_contact_error_double_email = ($si_contact_opt['error_email2'] != '') ? $si_contact_opt['error_email2'] : __('The two e-mail addresses did not match, please enter again.', 'si-contact-form');
     }
   }

// check attachment directory
$attach_dir_error = 0;
if ($have_attach){
	$attach_dir = WP_PLUGIN_DIR . '/si-contact-form/attachments/';
	if ( !is_dir($attach_dir) ) {
        $this->si_contact_error = 1;
		$attach_dir_error = sprintf( __( 'This contact form has file attachment fields, but the temporary folder for the files (%s) does not exist or is not writable. Create the folder or change its permission manually.', 'si-contact-form' ), $attach_dir );
	} else {
       // delete files over 3 minutes old in the attachment directory
       $this->si_contact_clean_attach_dir($attach_dir);
	}
}


   // optional extra fields
      for ($i = 1; $i <= $si_contact_gb['max_fields']; $i++) {
        if ($si_contact_opt['ex_field'.$i.'_label'] != '' && $si_contact_opt['ex_field'.$i.'_type'] != 'fieldset-close') {
          if(preg_match('/^{inline}/',$si_contact_opt['ex_field'.$i.'_label'])) {
            // remove the {inline} modifier tag from the label
              $si_contact_opt['ex_field'.$i.'_label'] = str_replace('{inline}','',$si_contact_opt['ex_field'.$i.'_label']);
          }
          if ($si_contact_opt['ex_field'.$i.'_type'] == 'fieldset') {

          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'attachment') {
              // need to test if a file was selected for attach.
              $ex_field_file['name'] = '';
              if(isset($_FILES["si_contact_ex_field$i"]))
                  $ex_field_file = $_FILES["si_contact_ex_field$i"];
              if ($ex_field_file['name'] == '' && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                   $this->si_contact_error = 1;
                   ${'si_contact_error_ex_field'.$i} = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('This field is required.', 'si-contact-form');
              }
              if($ex_field_file['name'] != ''){  // may not be required
                 // validate the attachment now
                 $ex_field_file_check = $this->si_contact_validate_attach( $ex_field_file );
                 if (!$ex_field_file_check['valid']) {
                     $this->si_contact_error = 1;
                     ${'si_contact_error_ex_field'.$i} = $ex_field_file_check['error'];
                 } else {
                    ${'ex_field'.$i} = $ex_field_file_check['file_name'];  // needed for email message
                 }
              }
              unset($ex_field_file);
          }else if ($si_contact_opt['ex_field'.$i.'_type'] == 'checkbox') {
             // see if checkbox children
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
             if(preg_match("/,/", $exf_array_test) ) {
                  list($exf_opts_label, $value) = explode(",",$exf_array_test);
                  $exf_opts_label   = trim($exf_opts_label);
                  $value = trim($value);
                  if ($exf_opts_label != '' && $value != '') {
                     if(!preg_match("/;/", $value)) {
                        $this->si_contact_error = 1;
                        ${'si_contact_error_ex_field'.$i} = __('Error: A checkbox field is not configured properly in settings.', 'si-contact-form');
                     } else {
                        // multiple options
                         $exf_opts_array = explode(";",$value);
                     }
                     $ex_cnt = 1;
                    foreach ($exf_opts_array as $k) {
                      ${'ex_field'.$i.'_'.$ex_cnt} = ( empty($_POST["si_contact_ex_field$i".'_'.$ex_cnt]) ) ? '' : $this->ctf_clean_input($_POST["si_contact_ex_field$i".'_'.$ex_cnt]);
                      $ex_cnt++;
                    }
                }
             }else{
                ${'ex_field'.$i} = ( empty($_POST["si_contact_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["si_contact_ex_field$i"]);
                if(empty(${'ex_field'.$i}) && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                    $this->si_contact_error = 1;
                    ${'si_contact_error_ex_field'.$i} = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('This field is required.', 'si-contact-form');
                }
             }
           }else{  // end label'] == 'checkbox'
                if ($si_contact_opt['ex_field'.$i.'_type'] == 'textarea' && $si_contact_opt['textarea_html_allow'] == 'true') {
                      ${'ex_field'.$i} = ( empty($_POST["si_contact_ex_field$i"]) ) ? '' : $_POST["si_contact_ex_field$i"];
                }else{
                     ${'ex_field'.$i} = ( empty($_POST["si_contact_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["si_contact_ex_field$i"]);
                }
                if(empty(${'ex_field'.$i}) && $si_contact_opt['ex_field'.$i.'_req'] == 'true') {
                  $this->si_contact_error = 1;
                  ${'si_contact_error_ex_field'.$i} = ($si_contact_opt['error_field'] != '') ? $si_contact_opt['error_field'] : __('This field is required.', 'si-contact-form');
                }
           }
        }  // end if label != ''
      } // end foreach

   if ($si_contact_opt['subject_type'] == 'required' && empty($subject)) {
       $this->si_contact_error = 1;
       if (count($subjects) == 0) {
         $si_contact_error_subject = ($si_contact_opt['error_subject'] != '') ? $si_contact_opt['error_subject'] : __('Subject text is required.', 'si-contact-form');
       }
   }
   if($si_contact_opt['message_type'] == 'required' &&  empty($message)) {
       $this->si_contact_error = 1;
       $si_contact_error_message = ($si_contact_opt['error_message'] != '') ? $si_contact_opt['error_message'] : __('Message text is required.', 'si-contact-form');
   }

   // Check with Akismet, but only if Akismet is installed, activated, and has a KEY. (Recommended for spam control).
   if($si_contact_opt['message_type'] != 'not_available' && function_exists('akismet_http_post') && get_option('wordpress_api_key') ){
			global $akismet_api_host, $akismet_api_port;
			$c['user_ip']    		= preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
			$c['user_agent'] 		= (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
			$c['referrer']   		= (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
			$c['blog']       		= get_option('home');
			$c['permalink']       	= get_permalink();
			$c['comment_type']      = 'sicontactform';
			$c['comment_author']    = $name;
			$c['comment_content']   = $message;
            //$c['comment_content']  = "viagra-test-123";  // uncomment this to test spam detection

			$ignore = array( 'HTTP_COOKIE' );

			foreach ( $_SERVER as $key => $value )
				if ( !in_array( $key, $ignore ) )
					$c["$key"] = $value;

			$query_string = '';
			foreach ( $c as $key => $data ) {
			  if( is_string($data) ) {
				$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';
              }
            }
			$response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
			if ( 'true' == $response[1] ) {
                $this->si_contact_error = 1; // Akismet says it is spam.
                $si_contact_error_message = ($si_contact_opt['error_input'] != '') ? $si_contact_opt['error_input'] : __('Contact Form has Invalid Input', 'si-contact-form');
			}
    } // end if(function_exists('akismet_http_post')){

  // begin captcha check if enabled
  // captcha is optional but recommended to prevent spam bots from spamming your contact form
  if ( $this->isCaptchaEnabled() ) {

// uncomment for temporary advanced debugging only
/*echo "<pre>";
   echo "COOKIE ";
   var_dump($_COOKIE);
   echo "\n\n";
   echo "SESSION ";
   var_dump($_SESSION);
echo "</pre>\n";*/


    if (!isset($_SESSION['securimage_code_ctf_'.$form_id_num]) || empty($_SESSION['securimage_code_ctf_'.$form_id_num])) {
          $this->si_contact_error = 1;
          $si_contact_error_captcha = __('Could not read CAPTCHA cookie. Make sure you have cookies enabled and not blocking in your web browser settings. Or another plugin is conflicting. See plugin FAQ.', 'si-contact-form');
    }else{
       if (empty($captcha_code) || $captcha_code == '') {
         $this->si_contact_error = 1;
         $si_contact_error_captcha = ($si_contact_opt['error_captcha_blank'] != '') ? $si_contact_opt['error_captcha_blank'] : __('Please complete the CAPTCHA.', 'si-contact-form');
       } else {
         require_once "$captcha_path_cf/securimage.php";
         $img = new Securimage();
         $img->form_num = $form_id_num; // makes compatible with multi-forms on same page
         $valid = $img->check("$captcha_code");
         // Check, that the right CAPTCHA password has been entered, display an error message otherwise.
         if($valid == true) {
             // ok can continue
         } else {
              $this->si_contact_error = 1;
              $si_contact_error_captcha = ($si_contact_opt['error_captcha_wrong'] != '') ? $si_contact_opt['error_captcha_wrong'] : __('That CAPTCHA was incorrect.', 'si-contact-form');
         }
    }
   }
  } // end if enable captcha
  // end captcha check

  if (!$this->si_contact_error) {
     // ok to send the email, so prepare the email message

     // new lines should be (\n for UNIX, \r\n for Windows and \r for Mac)
     //$php_eol = ( strtoupper(substr(PHP_OS,0,3) == 'WIN') ) ? "\r\n" : "\n";
	 $php_eol = (!defined('PHP_EOL')) ? (($eol = strtolower(substr(PHP_OS, 0, 3))) == 'win') ? "\r\n" : (($eol == 'mac') ? "\r" : "\n") : PHP_EOL;
	 $php_eol = (!$php_eol) ? "\n" : $php_eol;

     if($subject != '') {
          $subj = $si_contact_opt['email_subject'] ." $subject";
     }else{
          $subj = $si_contact_opt['email_subject'];
     }

     $msg =  __('To', 'si-contact-form').": $to_contact$php_eol$php_eol";
     if ($name != '' || $email != '')  {
        $msg .= __('From', 'si-contact-form').":$php_eol";
        switch ($si_contact_opt['name_format']) {
          case 'name':
              $msg .= "$name$php_eol";
          break;
          case 'first_last':
              $msg .= __('First Name', 'si-contact-form').": $f_name$php_eol";
              $msg .= __('Last Name', 'si-contact-form').": $l_name$php_eol";
          break;
          case 'first_middle_i_last':
              $msg .= __('First Name', 'si-contact-form').": $f_name$php_eol";
              if($mi_name != '')
                 $msg .= __('Middle Initial', 'si-contact-form').": $mi_name$php_eol";
              $msg .= __('Last Name', 'si-contact-form').": $l_name$php_eol";
          break;
          case 'first_middle_last':
              $msg .= __('First Name', 'si-contact-form').": $f_name$php_eol";
              if($m_name != '')
                 $msg .= __('Middle Name', 'si-contact-form').": $m_name$php_eol";
              $msg .= __('Last Name', 'si-contact-form').": $l_name$php_eol";
         break;
      }
      $msg .= "$email$php_eol$php_eol";
   }

    if ($si_contact_opt['ex_fields_after_msg'] == 'true' && $message != '') {
        $msg .= __('Message', 'si-contact-form').":$php_eol$message$php_eol$php_eol";
    }

     // optional extra fields
     for ($i = 1; $i <= $si_contact_gb['max_fields']; $i++) {
        if ( $si_contact_opt['ex_field'.$i.'_label'] != '' && $si_contact_opt['ex_field'.$i.'_type'] != 'fieldset-close') {
           if ($si_contact_opt['ex_field'.$i.'_type'] == 'fieldset') {
                  $msg .= $si_contact_opt['ex_field'.$i.'_label'].$php_eol;
           } else if ($si_contact_opt['ex_field'.$i.'_type'] == 'attachment' && $si_contact_opt['php_mailer_enable'] != 'php' && ${'ex_field'.$i} != '') {
               $msg .= $si_contact_opt['ex_field'.$i.'_label']."$php_eol * ".__('File is attached:', 'si-contact-form')." ${'ex_field'.$i}".$php_eol.$php_eol;
           } else if ($si_contact_opt['ex_field'.$i.'_type'] == 'select' || $si_contact_opt['ex_field'.$i.'_type'] == 'radio') {
              list($exf_opts_label, $value) = explode(",",$si_contact_opt['ex_field'.$i.'_label']);
              $msg .= $exf_opts_label."$php_eol${'ex_field'.$i}".$php_eol.$php_eol;
           } else if ($si_contact_opt['ex_field'.$i.'_type'] == 'checkbox') {
              $exf_opts_array = array();
              $exf_opts_label = '';
              $exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
             if(preg_match("/,/", $exf_array_test) && preg_match("/;/", $exf_array_test) ) {
                  list($exf_opts_label, $value) = explode(",",$exf_array_test);
                  $exf_opts_label   = trim($exf_opts_label);
                 $value = trim($value);
                 if ($exf_opts_label != '' && $value != '') {
                     if(!preg_match("/;/", $value)) {
                        // error
                        //A checkbox field is not configured properly in settings.
                     } else {
                         // multiple options
                         $exf_opts_array = explode(";",$value);
                     }
                     $msg .= $exf_opts_label.$php_eol;
                    // loop
                    $ex_cnt = 1;
                    foreach ($exf_opts_array as $k) {  // checkbox multi
                     if(${'ex_field'.$i.'_'.$ex_cnt} == 'selected')
                       $msg .= ' * '.$k.$php_eol;
                     $ex_cnt++;
                    }
                   $msg .= $php_eol;
                }
             } else {  // checkbox single
                 if(${'ex_field'.$i} == 'selected')
                   $msg .= $si_contact_opt['ex_field'.$i.'_label']."$php_eol * ".__('selected', 'si-contact-form').$php_eol.$php_eol;
             }
           } else {  // text, textarea, date
               if(${'ex_field'.$i} != ''){
                   if ($si_contact_opt['ex_field'.$i.'_type'] == 'textarea' && $si_contact_opt['textarea_html_allow'] == 'true') {
                        $msg .= $si_contact_opt['ex_field'.$i.'_label'].$php_eol.$this->ctf_stripslashes(${'ex_field'.$i}).$php_eol.$php_eol;
                   }else{
                        $msg .= $si_contact_opt['ex_field'.$i.'_label'].$php_eol.${'ex_field'.$i}.$php_eol.$php_eol;
                   }
               }
           }
       }
    }
    if ($si_contact_opt['ex_fields_after_msg'] != 'true' && $message != '') {
        $msg .= __('Message', 'si-contact-form').":$php_eol$message$php_eol$php_eol";
    }

    // add some info about sender to the email message
    $userdomain = '';
    $userdomain = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $user_info_string = '';
    if ($user_ID != '' && !current_user_can('level_10') ) {
        //user logged in
        $user_info_string .= __('From a WordPress user', 'si-contact-form').': '.$current_user->user_login . $php_eol;
    }
    $user_info_string .= __('Sent from (ip address)', 'si-contact-form').': '.$_SERVER['REMOTE_ADDR']." ($userdomain)".$php_eol;
    $user_info_string .= __('Date/Time', 'si-contact-form').': '.date_i18n(get_option('date_format').' '.get_option('time_format'), time() ) . $php_eol;
    $user_info_string .= __('Coming from (referer)', 'si-contact-form').': '.get_permalink() . $php_eol;
    $user_info_string .= __('Using (user agent)', 'si-contact-form').': '.$this->ctf_clean_input($_SERVER['HTTP_USER_AGENT']) . $php_eol.$php_eol;
    if ($si_contact_opt['sender_info_enable'] == 'true')
       $msg .= $user_info_string;

    // wordwrap email message
    if ($ctf_wrap_message)
       $msg = wordwrap($msg, 70,$php_eol);

    $header = '';
    $header_php = '';
    // prepare the email header
    if ($ctf_email_on_this_domain != '' ) {
         if(!preg_match("/,/", $ctf_email_on_this_domain)) {
           // just an email: user1@example.com
           $header_php =  "From: WordPress <$ctf_email_on_this_domain>\n";
           $this->si_contact_from_name = 'WordPress';
           $this->si_contact_mail_from = $ctf_email_on_this_domain;
         } else {
           // name and email: webmaster,user1@example.com
           list($key, $value) = explode(",",$ctf_email_on_this_domain);
           $key   = trim($key);
           $value = trim($value);
           $header_php =  "From: $key <$value>\n";
           $this->si_contact_from_name = $key;
           $this->si_contact_mail_from = $value;
         }
    } else if($email == '' || $name == '') {
         $header_php =  "From: WordPress <". get_option('admin_email') . ">\n";
         $this->si_contact_from_name = 'WordPress';
         $this->si_contact_mail_from = get_option('admin_email');
    } else {
         $header_php = "From: $name <$email>\n";;
         $this->si_contact_mail_from = $email;
         $this->si_contact_from_name = $name;
    }
    add_filter( 'wp_mail_from', array(&$this,'si_contact_form_mail_from'),1);
    add_filter( 'wp_mail_from_name', array(&$this,'si_contact_form_from_name'),1);

    if ($ctf_email_address_bcc != '')
            $header .= "Bcc: $ctf_email_address_bcc\n";

    if($email == ''){
      $header .= "Reply-To: $this->si_contact_mail_from\n";
    } else {
      $header .= "Reply-To: $email\n";
    }

    $header .= "Return-Path: $this->si_contact_mail_from\n";
    $header .= 'Content-type: text/plain; charset='. get_option('blog_charset') . $php_eol;

    // http://www.knowledge-transfers.com/it/the-fifth-parameter-in-php-mail-function

    @ini_set('sendmail_from', $this->si_contact_mail_from);

    // Check for safe mode
    $this->safe_mode = ((boolean)@ini_get('safe_mode') === false) ? 0 : 1;

    if ($si_contact_opt['php_mailer_enable'] == 'php') {
       $header_php .= $header; 
      if ($ctf_email_on_this_domain != '' && !$this->safe_mode) {
          // the fifth parameter is not allowed in safe mode
          // Pass the Return-Path via sendmail's -f command.
        if (!mail($mail_to,$subj,$msg,$header_php, '-f '.$this->si_contact_mail_from)) {
		    die('<p>' . __('The e-mail could not be sent.', 'si-contact-form') . '</p>');
        }
      }else{
        if (!mail($mail_to,$subj,$msg,$header_php)) {
	   	    die('<p>' . __('The e-mail could not be sent.', 'si-contact-form') . '</p>');
        }
      }
    }else if ($si_contact_opt['php_mailer_enable'] == 'geekmail') {
         require_once WP_PLUGIN_DIR . '/si-contact-form/ctf_geekMail-1.0.php';
         $ctf_geekMail = new ctf_geekMail();
         $ctf_geekMail->setMailType('text');
         $ctf_geekMail->_setcharSet(get_option('blog_charset'));
         $ctf_geekMail->_setnewLine($php_eol);
         $ctf_geekMail->from($this->si_contact_mail_from, $this->si_contact_from_name);
         $ctf_geekMail->to($mail_to);
         if($email == ''){
            $ctf_geekMail->_replyTo($this->si_contact_mail_from);
         } else {
            $ctf_geekMail->_replyTo($email);
         }
         if ($ctf_email_address_bcc != '')
           $ctf_geekMail->bcc($ctf_email_address_bcc);
         $ctf_geekMail->subject($subj);
         $ctf_geekMail->message($msg);
         if ( $this->uploaded_files ) {
			    foreach ( $this->uploaded_files as $path ) {
				    $ctf_geekMail->attach($path);
			    }
         }
         if (!$ctf_geekMail->send()) {
              die('<p>' . __('The e-mail could not be sent.', 'si-contact-form') . '</p>');
             //$errors = $geekMail->getDebugger();
             //print_r($errors);
         }

    } else {
        if ( $this->uploaded_files ) {
			    $attach_this_mail = array();
			    foreach ( $this->uploaded_files as $path ) {
				    $attach_this_mail[] = $path;
			    }
			    if (!wp_mail($mail_to,$subj,$msg,$header,$attach_this_mail))
		            die('<p>' . __('The e-mail could not be sent.', 'si-contact-form') . '</p>');
		} else {
		        if (!wp_mail($mail_to,$subj,$msg,$header))
		            die('<p>' . __('The e-mail could not be sent.', 'si-contact-form') . '</p>');
		}
    }

   // autoresponder feature
   if ($si_contact_opt['auto_respond_enable'] == 'true' && $email != '' && $si_contact_opt['auto_respond_subject'] != '' && $si_contact_opt['auto_respond_message'] != ''){
       $subj = $si_contact_opt['auto_respond_subject'];
       $msg =  $si_contact_opt['auto_respond_message'];
       // wordwrap email message
       if ($ctf_wrap_message)
             $msg = wordwrap($msg, 70,$php_eol);

       $header = '';
       $header_php = '';
       $auto_respond_from_name = $si_contact_opt['auto_respond_from_name'];
       $auto_respond_reply_to = $si_contact_opt['auto_respond_reply_to'];
       // prepare the email header
       if ($ctf_email_on_this_domain != '' ) {
            if(!preg_match("/,/", $ctf_email_on_this_domain)) {
              // just an email: user1@example.com
              $header_php =  "From: $auto_respond_from_name <$ctf_email_on_this_domain>\n";
              $this->si_contact_from_name = $auto_respond_from_name;
              $this->si_contact_mail_from = $ctf_email_on_this_domain;
            } else {
              // name and email: webmaster,user1@example.com
              list($key, $value) = explode(",",$ctf_email_on_this_domain);
              $key   = trim($key);
              $value = trim($value);
              $header_php =  "From: $key <$value>\n";
              $this->si_contact_from_name = $key;
              $this->si_contact_mail_from = $value;
            }
      } else {
            $header_php =  "From: $auto_respond_from_name <". get_option('admin_email') . ">\n";
            $this->si_contact_from_name = $auto_respond_from_name;
            $this->si_contact_mail_from = get_option('admin_email');
       }
       add_filter( 'wp_mail_from_name', array(&$this,'si_contact_form_from_name'),1);
       add_filter( 'wp_mail_from', array(&$this,'si_contact_form_mail_from'),1);

       $header .= "Reply-To: $auto_respond_reply_to\n";
       $header .= "Return-Path: $this->si_contact_mail_from\n";
       $header .= 'Content-type: text/plain; charset='. get_option('blog_charset') . $php_eol;
       @ini_set('sendmail_from' , $this->si_contact_mail_from);
       if ($si_contact_opt['php_mailer_enable'] == 'php') {
             $header_php .= $header;
            if ($ctf_email_on_this_domain != '' && !$this->safe_mode) {
                   // the fifth parameter is not allowed in safe mode
                   // Pass the Return-Path via sendmail's -f command.
              if (!mail($email,$subj,$msg,$header_php, '-f '.$this->si_contact_mail_from))
		         die('<p>' . __('The autoresponder e-mail could not be sent.', 'si-contact-form') . '</p>');
            } else {
              if (!mail($email,$subj,$msg,$header_php))
		         die('<p>' . __('The autoresponder e-mail could not be sent.', 'si-contact-form') . '</p>');
            }
       }else if ($si_contact_opt['php_mailer_enable'] == 'geekmail') {
            require_once WP_PLUGIN_DIR . '/si-contact-form/ctf_geekMail-1.0.php';
            $ctf_geekMail = new ctf_geekMail();
            $ctf_geekMail->setMailType('text');
            $ctf_geekMail->_setcharSet(get_option('blog_charset'));
            $ctf_geekMail->_setnewLine($php_eol);
            $ctf_geekMail->from($this->si_contact_mail_from, $this->si_contact_from_name);
            $ctf_geekMail->_replyTo($auto_respond_reply_to);
            $ctf_geekMail->to($email);
            $ctf_geekMail->subject($subj);
            $ctf_geekMail->message($msg);
            if (!$ctf_geekMail->send()) {
                 die('<p>' . __('The autoresponder e-mail could not be sent.', 'si-contact-form') . '</p>');
                //$errors = $geekMail->getDebugger();
                //print_r($errors);
           }
       } else {
	        if (!wp_mail($email,$subj,$msg,$header))
		       die('<p>' . __('The autoresponder e-mail could not be sent.', 'si-contact-form') . '</p>');
       }
  }

    $message_sent = 1;

  } // end if ! error

if ($have_attach){
  // unlink attachment temp files
  foreach ( (array) $this->uploaded_files as $path ) {
   @unlink( $path );
  }
}

?>