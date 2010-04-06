<?php

// the form is being displayed now
 $this->ctf_form_style = $this->si_contact_convert_css($si_contact_opt['form_style']);
 $this->ctf_border_style = $this->si_contact_convert_css($si_contact_opt['border_style']);
 $this->ctf_select_style = $this->si_contact_convert_css($si_contact_opt['select_style']);
 $this->ctf_title_style = $this->si_contact_convert_css($si_contact_opt['title_style']);
 $this->ctf_field_style = $this->si_contact_convert_css($si_contact_opt['field_style']);
 $this->ctf_field_div_style = $this->si_contact_convert_css($si_contact_opt['field_div_style']);
 $this->ctf_error_style = $this->si_contact_convert_css($si_contact_opt['error_style']);
 $this->ctf_required_style = $this->si_contact_convert_css($si_contact_opt['required_style']);

 $ctf_field_size = absint($si_contact_opt['field_size']);

 $this->ctf_aria_required = ($si_contact_opt['aria_required'] == 'true') ? ' aria-required="true" ' : '';

$string .= '
<!-- SI Contact Form plugin begin -->
<div '.$this->ctf_form_style.'>
';


if ($si_contact_opt['border_enable'] == 'true') {
  $string .= '
    <form action="'.get_permalink().'" id="si_contact_form'.$form_id_num.'" method="post">
    <fieldset '.$this->ctf_border_style.'>
        <legend>';
     $string .= ($si_contact_opt['title_border'] != '') ? esc_html($si_contact_opt['title_border']) : esc_html( __('Contact Form', 'si-contact-form'));
     $string .= '</legend>';
} else {

 $string .= '
<form action="'.get_permalink().'" id="si_contact_form'.$form_id_num.'" method="post">
';
}

// print any input errors
if ($this->si_contact_error) {
    $string .= '<div '.$this->ctf_error_style.'>';
    $string .= ($si_contact_opt['error_correct'] != '') ? esc_html($si_contact_opt['error_correct']) : esc_html( __('Please make corrections below and try again.', 'si-contact-form') );
    $string .= '</div>'."\n";
}
if (empty($ctf_contacts)) {
   $string .= '<div '.$this->ctf_error_style.'>'.__('ERROR: Misconfigured E-mail address in options.', 'si-contact-form').'</div>'."\n";
}

if ( $si_contact_opt['req_field_indicator_enable'] == 'true' ) {
   $string .=  '<div '.$this->ctf_required_style.'>';
   $string .= ($si_contact_opt['tooltip_required'] != '') ? esc_html($si_contact_opt['tooltip_required']) : esc_html( __('(*denotes required field)', 'si-contact-form'));
   $string .= '</div>
';
}

if (count($contacts) > 1) {

     $string .= '        <div '.$this->ctf_title_style.'>
                <label for="si_contact_CID'.$form_id_num.'">';
     $string .= ($si_contact_opt['title_dept'] != '') ? esc_html( $si_contact_opt['title_dept']) : esc_html( __('Department to Contact', 'si-contact-form')).':';
     $string .= $req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_contact).'
        <div '.$this->ctf_field_div_style.'>
                <select '.$this->ctf_select_style.' id="si_contact_CID'.$form_id_num.'" name="si_contact_CID" '.$this->ctf_aria_required.'>
';

    $string .= '                        <option value="">';
    $string .= ($si_contact_opt['title_select'] != '') ? esc_attr($si_contact_opt['title_select']) : esc_attr( __('Select', 'si-contact-form'));
    $string .= '</option>'."\n";

     if ( !isset($cid) && isset($_GET['si_contact_CID']) ) {
          $cid = (int)$_GET['si_contact_CID'];
     }

     $selected = '';

      foreach ($contacts as $k => $v)  {
          if (!empty($cid) && $cid == $k) {
                    $selected = ' selected="selected"';
          }
          $string .= '                        <option value="' . esc_attr($k) . '"' . $selected . '>' . esc_attr($v['CONTACT']) . '</option>' . "\n";
          $selected = '';
      }

      $string .= '            </select>
      </div>' . "\n";
}
else {

     $string .= '<div><input type="hidden" name="si_contact_CID" value="1" /></div>'."\n";

}

// find logged in user's WP email address (auto form fill feature):
// http://codex.wordpress.org/Function_Reference/get_currentuserinfo
if ($email == '') {
  if (
  $user_ID != '' &&
  $current_user->user_login != 'admin' &&
  !current_user_can('level_10') &&
  $si_contact_opt['auto_fill_enable'] == 'true'
  ) {
     //user logged in (and not admin rights) (and auto_fill_enable set in options)
     $email = $current_user->user_email;
     $email2 = $current_user->user_email;
     if ($name == '') {
        $name = $current_user->user_login;
     }
  }
}

$string .= '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_name'.$form_id_num.'">';
     $string .= ($si_contact_opt['title_name'] != '') ? esc_html( $si_contact_opt['title_name'] ) : esc_html( __('Name', 'si-contact-form')).':';
     $string .= $req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_name).'
        <div '.$this->ctf_field_div_style.'>
                <input '.$this->ctf_field_style.' type="text" id="si_contact_name'.$form_id_num.'" name="si_contact_name" value="' . $this->ctf_output_string($name) .'" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>';

if ($ctf_enable_double_email == 'true') {
 $string .= '
        <div '.$this->ctf_title_style.'>
        <label for="si_contact_email'.$form_id_num.'">';
     $string .= ($si_contact_opt['title_email'] != '') ? esc_html( $si_contact_opt['title_email'] ) : esc_html( __('E-Mail Address', 'si-contact-form')).':';
     $string .= $req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_email).'
         '.$this->ctf_echo_if_error($si_contact_error_double_email).'
        <div '.$this->ctf_field_div_style.'>
                <input '.$this->ctf_field_style.' type="text" id="si_contact_email'.$form_id_num.'" name="si_contact_email" value="' . $this->ctf_output_string($email) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>
        <div '.$this->ctf_title_style.'>
        <label for="si_contact_email2_'.$form_id_num.'">';
     $string .= ($si_contact_opt['title_email2'] != '') ? esc_html($si_contact_opt['title_email2']) : esc_html( __('E-Mail Address again', 'si-contact-form')).':';
     $string .= $req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_email2).'
        <div '.$this->ctf_field_div_style.'>
                <input '.$this->ctf_field_style.' type="text" id="si_contact_email2_'.$form_id_num.'" name="si_contact_email2" value="' . $this->ctf_output_string($email2) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
                <br /><span class="small">';
     $string .= ($si_contact_opt['title_email2_help'] != '') ? esc_html( $si_contact_opt['title_email2_help'] ) : esc_html( __('Please enter your E-mail Address a second time.', 'si-contact-form'));
     $string .= '</span>
        </div>
        ';

 } else {
$string .= '
        <div '.$this->ctf_title_style.'>
        <label for="si_contact_email'.$form_id_num.'">';
     $string .= ($si_contact_opt['title_email'] != '') ? esc_html( $si_contact_opt['title_email'] ) : esc_html( __('E-Mail Address', 'si-contact-form')).':';
     $string .= $req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_email).'
        <div '.$this->ctf_field_div_style.'>
                <input '.$this->ctf_field_style.' type="text" id="si_contact_email'.$form_id_num.'" name="si_contact_email" value="' . $this->ctf_output_string($email) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>';

}

// optional extra fields
      for ($i = 1; $i <= $si_contact_gb['max_fields']; $i++) {
        if ($si_contact_opt['ex_field'.$i.'_label'] != '') {
           $ex_req_field_ind = ($si_contact_opt['ex_field'.$i.'_req'] == 'true') ? $req_field_ind : '';
           $ex_req_field_aria = ($si_contact_opt['ex_field'.$i.'_req'] == 'true') ? $this->ctf_aria_required : '';
           if(!$si_contact_opt['ex_field'.$i.'_type'] ) $si_contact_opt['ex_field'.$i.'_type'] = 'text';

          switch ($si_contact_opt['ex_field'.$i.'_type']) {
           case 'text':

                 $string .=   '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_ex_field'.$form_id_num.'_'.$i.'">' . esc_html( $si_contact_opt['ex_field'.$i.'_label'] ).$ex_req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error(${'si_contact_error_ex_field'.$i}).'
        <div '.$this->ctf_field_div_style.'>
                <input '.$this->ctf_field_style.' type="text" id="si_contact_ex_field'.$form_id_num.'_'.$i.'" name="si_contact_ex_field'.$i.'" value="' . $this->ctf_output_string(${'ex_field'.$i}) . '" '.$ex_req_field_aria.' size="'.$ctf_field_size.'" />
        </div>';
              break;
           case 'textarea':

                $string .=   '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_ex_field'.$form_id_num.'_'.$i.'">' . esc_html( $si_contact_opt['ex_field'.$i.'_label'] ).$ex_req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error(${'si_contact_error_ex_field'.$i}).'
        <div '.$this->ctf_field_div_style.'>
                <textarea '.$this->ctf_field_style.' id="si_contact_ex_field'.$form_id_num.'_'.$i.'" name="si_contact_ex_field'.$i.'" '.$ex_req_field_aria.' cols="'.absint($si_contact_opt['text_cols']).'" rows="'.absint($si_contact_opt['text_rows']).'">' . $this->ctf_output_string(${'ex_field'.$i}) . '</textarea>
        </div>';
              break;
           case 'checkbox':

               $string .=   '
        <div '.$this->ctf_title_style.'>
            <input type="checkbox" id="si_contact_ex_field'.$form_id_num.'_'.$i.'" name="si_contact_ex_field'.$i.'" value="selected" ';
                 if ( ${'ex_field'.$i} == 'selected' )
                    $string .= ' checked="checked" ';
                 $string .= '/>
                <label for="si_contact_ex_field'.$form_id_num.'_'.$i.'">' . esc_html( $si_contact_opt['ex_field'.$i.'_label'] ).'</label>
        </div> '.$this->ctf_echo_if_error(${'si_contact_error_ex_field'.$i}).'
';

             break;
           case 'select':

           // find the label and the options inside $si_contact_opt['ex_field'.$i.'_label']
           // the drop down list array will be made automatically by this code
$exf_opts_array = array();
$exf_opts_label = '';
$exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
if(!preg_match("/,/", $exf_array_test) ) {
       // error
       $this->si_contact_error = 1;
       $string .= $this->ctf_echo_if_error(__('Error: A select field is not configured properly in settings.', 'si-contact-form'));
} else {
       list($exf_opts_label, $value) = explode(",",$exf_array_test);
       $exf_opts_label   = trim($exf_opts_label);
       $value = trim($value);
       if ($exf_opts_label != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
               // error
               $this->si_contact_error = 1;
               $string .= $this->ctf_echo_if_error(__('Error: A select field is not configured properly in settings.', 'si-contact-form'));
          } else {
               // multiple options
               $exf_opts_array = explode(";",$value);
         }
      }
} // end else

           $string .=   '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_ex_field'.$form_id_num.'_'.$i.'">' . esc_html( $exf_opts_label ).$ex_req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error(${'si_contact_error_ex_field'.$i}).'
        <div '.$this->ctf_field_div_style.'>
               <select '.$this->ctf_field_style.' id="si_contact_ex_field'.$form_id_num.'_'.$i.'" name="si_contact_ex_field'.$i.'">
        ';

$selected = '';
foreach ($exf_opts_array as $k) {
 if (${'ex_field'.$i} == "$k")  $selected = ' selected="selected"';
 $string .= '<option value="'.$this->ctf_output_string($k).'"'.$selected.'>'.$this->ctf_output_string($k).'</option>'."\n";
 $selected = '';
}
$string .= '</select>
        </div>';
             break;
           case 'radio':

           // find the label and the options inside $si_contact_opt['ex_field'.$i.'_label']
           // the radio list array will be made automatically by this code
$exf_opts_array = array();
$exf_opts_label = '';
$exf_array_test = trim($si_contact_opt['ex_field'.$i.'_label'] );
if(!preg_match('/,/', $exf_array_test) ) {
       // error
       $this->si_contact_error = 1;
       $string .= $this->ctf_echo_if_error(__('Error: A radio field is not configured properly in settings.', 'si-contact-form'));
} else {
       list($exf_opts_label, $value) = explode(",",$exf_array_test);
       $exf_opts_label   = trim($exf_opts_label);
       $value = trim($value);
       if ($exf_opts_label != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
               // error
               $this->si_contact_error = 1;
               $string .= $this->ctf_echo_if_error(__('Error: A radio field is not configured properly in settings.', 'si-contact-form'));
          } else {
               // multiple options
               $exf_opts_array = explode(";",$value);
         }
      }
} // end else

           $string .=   '
        <div '.$this->ctf_title_style.'>
         '.esc_html( $exf_opts_label ).$ex_req_field_ind.'
        ';

$selected = '';
$ex_cnt = 0;
foreach ($exf_opts_array as $k) {
 if (${'ex_field'.$i} == "$k")  $selected = ' checked="checked"';
 $string .= '<br /><input type="radio" '.$this->ctf_field_style.' id="si_contact_ex_field'.$form_id_num.'_'.$i.'_'.$ex_cnt.'" name="si_contact_ex_field'.$i.'" value="'.$this->ctf_output_string($k).'"'.$selected.' />
 <label for="si_contact_ex_field'.$form_id_num.'_'.$i.'_'.$ex_cnt.'">' . esc_html( $k ).'</label>'."\n";
 $selected = '';
 $ex_cnt++;
}
$string .= $this->ctf_echo_if_error(${'si_contact_error_ex_field'.$i}).'
        </div>';
             break;
          }

        } // end if label
      } // end foreach

if ($si_contact_opt['hidden_subject_enable'] != 'true') {

   if (count($subjects) > 0) {

       $string .=   '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_subject_ID'.$form_id_num.'">';
     $string .= ($si_contact_opt['title_subj'] != '') ? esc_html( $si_contact_opt['title_subj'] ) : esc_html( __('Subject', 'si-contact-form')).':';
     $string .= $req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_subject).'
        <div '.$this->ctf_field_div_style.'>

                <select '.$this->ctf_select_style.' id="si_contact_subject_ID'.$form_id_num.'" name="si_contact_subject_ID" '.$this->ctf_aria_required.'>
';

    $string .= '                        <option value="">';
    $string .= ($si_contact_opt['title_select'] != '') ? esc_attr($si_contact_opt['title_select']) : esc_attr( __('Select', 'si-contact-form'));
    $string .= '</option>'."\n";

     if ( !isset($sid) && isset($_GET['si_contact_SID']) ) {
          $sid = (int)$_GET['si_contact_SID'];
     }

     $selected = '';

      foreach ($subjects as $k => $v)  {
          if (!empty($sid) && $sid == $k) {
                    $selected = ' selected="selected"';
          }
          $string .= '                        <option value="' . esc_attr($k) . '"' . $selected . '>' . esc_attr($v) . '</option>' . "\n";
          $selected = '';
      }

      $string .= '            </select>';

       } else {
            $string .=   '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_subject'.$form_id_num.'">';
     $string .= ($si_contact_opt['title_subj'] != '') ? esc_html( $si_contact_opt['title_subj'] ) : esc_html( __('Subject', 'si-contact-form')).':';
     $string .= $req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_subject).'
        <div '.$this->ctf_field_div_style.'>';
          $string .= '<input '.$this->ctf_field_style.' type="text" id="si_contact_subject'.$form_id_num.'" name="si_contact_subject" value="' . $this->ctf_output_string($subject) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />';
       }

        $string .= '</div>';
}

if ($si_contact_opt['hidden_message_enable'] != 'true') {
$string .=   '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_message'.$form_id_num.'">';
     $string .= ($si_contact_opt['title_mess'] != '') ? esc_html( $si_contact_opt['title_mess'] ) : esc_html( __('Message', 'si-contact-form')).':';
     $string .= $req_field_ind.'</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_message).'
        <div '.$this->ctf_field_div_style.'>
                <textarea '.$this->ctf_field_style.' id="si_contact_message'.$form_id_num.'" name="si_contact_message" '.$this->ctf_aria_required.' cols="'.absint($si_contact_opt['text_cols']).'" rows="'.absint($si_contact_opt['text_rows']).'">' . $this->ctf_output_string($message) . '</textarea>
        </div>
';
}

 $this->ctf_submit_style = $this->si_contact_convert_css($si_contact_opt['button_style']);
// captcha is optional but recommended to prevent spam bots from spamming your contact form
$string .= ( $this->isCaptchaEnabled() ) ? $this->addCaptchaToContactForm($si_contact_error_captcha,$form_id_num)."\n</div>\n<br clear=\"all\" />\n"  : '';
$string .= '
<div '.$this->ctf_title_style.'>
  <input type="hidden" name="si_contact_action" value="send" />
  <input type="hidden" name="si_contact_form_id" value="'.$form_id_num.'" />
  <input type="submit" '.$this->ctf_submit_style.' value="';
     $string .= ($si_contact_opt['title_submit'] != '') ? esc_attr( $si_contact_opt['title_submit'] ) : esc_attr( __('Submit', 'si-contact-form'));
     $string .= '" />
</div>
';
if ($si_contact_opt['border_enable'] == 'true') {
  $string .= '
    </fieldset>
  ';
}
$string .= '
</form>
</div>
';
if ($si_contact_opt['enable_credit_link'] == 'true') {
$string .= '
<p><small>'.__('Powered by', 'si-contact-form'). ' <a href="http://wordpress.org/extend/plugins/si-contact-form/" target="_new">'.__('Fast and Secure Contact Form', 'si-contact-form'). '</a></small></p>
<br clear="all" />
';
}
$string .= '<!-- SI Contact Form plugin end -->
';

?>