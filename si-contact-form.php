<?php
/*
Plugin Name: Fast and Secure Contact Form
Plugin URI: http://www.642weather.com/weather/scripts-wordpress-si-contact.php
Description: Fast and Secure Contact Form for WordPress. The contact form lets your visitors send you a quick email message. Blocks all common spammer tactics. Spam is no longer a problem. Includes a CAPTCHA and Akismet. Does not require JavaScript. <a href="plugins.php?page=si-contact-form/si-contact-form.php">Settings</a> | <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6105441">Donate</a>
Version: 1.2
Author: Mike Challis
Author URI: http://www.642weather.com/weather/scripts.php
*/

/*  Copyright (C) 2008 Mike Challis  (http://www.642weather.com/weather/contact_us.php)

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

if (!class_exists('siContactForm')) {

 class siContactForm {
     var $si_contact_error;

function add_tabs() {
    add_submenu_page('plugins.php', __('SI Contact Form Options', 'si-contact-form'), __('SI Contact Form Options', 'si-contact-form'), 'manage_options', __FILE__,array(&$this,'options_page'));
}

function unset_si_contact_options () {
  delete_option('si_contact_welcome');
  delete_option('si_contact_email_to');
  delete_option('si_contact_email_from');
  delete_option('si_contact_email_bcc');
  delete_option('si_contact_double_email');
  delete_option('si_contact_domain_protect');
  delete_option('si_contact_captcha_enable');
  delete_option('si_contact_captcha_perm');
  delete_option('si_contact_captcha_perm_level');
  delete_option('si_contact_border_enable');
  delete_option('si_contact_title_style');
  delete_option('si_contact_field_style');
  delete_option('si_contact_error_style');
  delete_option('si_contact_field_size');
  delete_option('si_contact_text_cols');
  delete_option('si_contact_text_rows');
  delete_option('si_contact_aria_required');

}

function get_settings($value) {
// gets the settings, providing defaults if need be
if (!get_option($value)) {
        $defaults = array(
         'si_contact_welcome' => __('<p>Comments or questions are welcome.</p>', 'si-contact-form'),
         'si_contact_email_to' => __('Webmaster', 'si-contact-form').','.get_option('admin_email'),
         'si_contact_email_from' => '',
         'si_contact_email_bcc' => '',
         'si_contact_email_language' => 'en-us',
         'si_contact_email_charset' => 'iso-8859-1',
         'si_contact_email_encoding' => 'quoted-printable',
         'si_contact_double_email' => 'false',
         'si_contact_domain_protect' => 'true',
         'si_contact_captcha_enable' => 'true',
         'si_contact_captcha_perm' => 'false',
         'si_contact_captcha_perm_level' => 'read',
         'si_contact_redirect_enable' => 'true',
         'si_contact_redirect_url' => 'index.php',
         'si_contact_border_enable' => 'false',
         'si_contact_title_style' => 'text-align:left;',
         'si_contact_field_style' => 'text-align:left;',
         'si_contact_error_style' => 'color:red; text-align:left;',
         'si_contact_field_size' => '40',
         'si_contact_text_cols' => '40',
         'si_contact_text_rows' => '15',
         'si_contact_aria_required' => 'false',
         );
        update_option($value,$defaults[$value]);
        return $defaults[$value];
} else {
        return get_option($value);
}
} //end get_settings

function options_page() {
  global $si_contact_nonce, $captcha_url_cf;
  if ($_POST['submit']) {
    if ( function_exists('current_user_can') && !current_user_can('manage_options') )
                        die(__('You do not have permissions for managing this option', 'si-contact-form'));

    $possible_options = array_keys($_POST);
    //if the options are part of an array
        foreach($possible_options as $option) {
                update_option($option,str_replace('&quot;','"',trim($_POST[$option])));
        }

    if ( !isset( $_POST['si_contact_welcome'] ) )
         update_option( 'si_contact_welcome', '' );

    if ( !isset( $_POST['si_contact_email_from'] ) )
         update_option( 'si_contact_email_from', '' );

    if ( !isset( $_POST['si_contact_email_bcc'] ) )
         update_option( 'si_contact_email_bcc', '' );

    if ( isset( $_POST['si_contact_double_email'] ) )
         update_option( 'si_contact_double_email', 'true' );
        else
         update_option( 'si_contact_double_email', 'false' );

    if ( isset( $_POST['si_contact_domain_protect'] ) )
         update_option( 'si_contact_domain_protect', 'true' );
        else
         update_option( 'si_contact_domain_protect', 'false' );

    if ( isset( $_POST['si_contact_captcha_enable'] ) )
         update_option( 'si_contact_captcha_enable', 'true' );
        else
         update_option( 'si_contact_captcha_enable', 'false' );

    if ( isset( $_POST['si_contact_redirect_enable'] ) )
         update_option( 'si_contact_redirect_enable', 'true' );
        else
         update_option( 'si_contact_redirect_enable', 'false' );

    if ( isset( $_POST['si_contact_captcha_perm'] ) )
         update_option( 'si_contact_captcha_perm', 'true' );
        else
         update_option( 'si_contact_captcha_perm', 'false' );

    if ( isset( $_POST['si_contact_border_enable'] ) )
         update_option( 'si_contact_border_enable', 'true' );
        else
         update_option( 'si_contact_border_enable', 'false' );

    if ( !isset( $_POST['si_contact_title_style'] ) )
         update_option( 'si_contact_title_style', '' );

    if ( !isset( $_POST['si_contact_field_style'] ) )
         update_option( 'si_contact_field_style', '' );

    if ( !isset( $_POST['si_contact_error_style'] ) )
         update_option( 'si_contact_error_style', '' );

    if ( isset( $_POST['si_contact_aria_required'] ) )
         update_option( 'si_contact_aria_required', 'true' );
        else
         update_option( 'si_contact_aria_required', 'false' );

    if (function_exists('wp_cache_flush')) {
	     wp_cache_flush();
	}

  }
?>
<?php if ( !empty($_POST ) ) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.', 'si-contact-form') ?></strong></p></div>
<?php endif; ?>
<div class="wrap">
<h2><?php _e('Fast and Secure Contact Form Options', 'si-contact-form') ?></h2>

<script type="text/javascript">
    function toggleVisibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
</script>

<h3><?php _e('Donate', 'si-contact-form') ?></h3>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<table style="background-color:#FFE991; border:none; margin: -5px 0;" width="500">
        <tr>
        <td>
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="6105441" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" style="border:none;" name="submit" alt="Paypal Donate" />
<img alt="" style="border:none;" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</td>
<td><?php _e('If you find this plugin useful to you, please consider making a small donation to help contribute to further development. Thanks for your kind support!', 'si-contact-form') ?> - Mike Challis</td>
</tr></table>
</form>

<h3><?php _e('Usage', 'si-contact-form') ?></h3>
	<p>
    <?php _e('You must add the shortcode <b>[si_contact_form]</b> in a Page. That page will become your Contact Form', 'si-contact-form') ?>.
    </p>

<h3><?php _e('Options', 'si-contact-form') ?></h3>

<form name="formoptions" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>&amp;updated=true">
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="form_type" value="upload_options" />
    <?php si_contact_nonce_field($si_contact_nonce) ?>
        <fieldset class="options">

    <table cellspacing="2" cellpadding="5" class="form-table">


    <tr>
         <th scope="row" style="width: 75px;"><?php _e('Form:', 'si-contact-form') ?></th>
      <td>
        <label name="si_contact_welcome" for="si_contact_welcome"><?php _e('Welcome introduction', 'si-contact-form') ?>:</label><br />
        <textarea rows="2" cols="40" name="si_contact_welcome" id="si_contact_welcome"><?php echo $this->ctf_stripslashes($this->ctf_output_string($this->get_settings('si_contact_welcome')));  ?></textarea>
        <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_welcome_tip');"><?php _e('help', 'si-captcha'); ?></a>
        <div style="text-align:left; display:none" id="si_contact_welcome_tip">
        <?php _e('This gets printed when the contact form is first presented. It is not printed when there is an input error and not printed after the form is completed.', 'si-contact-form') ?>
        </div>
      </td>
    </tr>
    <tr>
         <th scope="row" style="width: 75px;"><?php _e('E-mail:', 'si-contact-form') ?></th>
      <td>
<?php
// checks for properly configured E-mail address in options.
$ctf_contacts = array ();
$ctf_contacts_test = trim($this->get_settings('si_contact_email_to'));
if(!preg_match("/,/", $ctf_contacts_test) && $this->ctf_validate_email($ctf_contacts_test)) {
   $ctf_contacts[] = array('CONTACT' => __('Webmaster', 'si-contact-form'),  'EMAIL' => $ctf_contacts_test );
}
$ctf_ct_arr = explode("\n",$ctf_contacts_test);
foreach($ctf_ct_arr as $line) {
    // echo '|'.$line.'|' ;
   list($key, $value) = explode(",",$line);
   $key = trim($key); $value = trim($value);
   if ($key != '' && $value != '' && $this->ctf_validate_email($value)) {
      $ctf_contacts[] = array('CONTACT' => $key,  'EMAIL' => $value);
   }
}
?>
        <label name="si_contact_email_to" for="si_contact_email_to"><?php _e('E-mail To', 'si-contact-form') ?>:</label>
<?php
if (empty($ctf_contacts)) {
   echo '<span style="color:red;">'.__('ERROR: Misconfigured E-mail address in options.', 'si-contact-form').'</span>'."\n";
}
?>
        <br />
        <textarea rows="2" cols="40" name="si_contact_email_to" id="si_contact_email_to"><?php echo $this->get_settings('si_contact_email_to');  ?></textarea>
        <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_email_to_tip');"><?php _e('help', 'si-captcha'); ?></a>
        <div style="text-align:left; display:none" id="si_contact_email_to_tip">
        <?php _e('E-mail address the messages are sent to (your email). Add as many contacts as you need, the drop down list on the contact form will be made automatically. Each contact has a name and an email address separated by a comma. Separate each contact by pressing enter. If you need to add more than one contact, follow this example:', 'si-contact-form') ?><br />
        Webmaster,user1@example.com<br />
        Sales,user2@example.com
        </div>
        <br />

        <label name="si_contact_email_from" for="si_contact_email_from"><?php _e('E-mail From (optional)', 'si-contact-form') ?>:</label><input name="si_contact_email_from" id="si_contact_email_from" type="text" value="<?php echo $this->get_settings('si_contact_email_from');  ?>" size="50" />
        <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_email_from_tip');"><?php _e('help', 'si-captcha'); ?></a>
        <div style="text-align:left; display:none" id="si_contact_email_from_tip">
        <?php _e('E-mail address the messages are sent from. Normally you should leave this blank. Some web hosts do not allow PHP to send E-mail unless the "From:" E-mail address is on the same web domain. If your contact form does not send any E-mail, then set this to an E-mail address on the SAME domain as your web site as a possible fix.', 'si-contact-form') ?>
        </div>
        <br />

        <label name="si_contact_double_bcc" for="si_contact_email_bcc"><?php _e('E-mail Bcc (optional)', 'si-contact-form') ?>:</label><input name="si_contact_email_bcc" id="si_contact_email_bcc" type="text" value="<?php echo $this->get_settings('si_contact_email_bcc');  ?>" size="50" />
        <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_email_bcc_tip');"><?php _e('help', 'si-captcha'); ?></a>
        <div style="text-align:left; display:none" id="si_contact_email_bcc_tip">
        <?php _e('E-mail address(s) to receive Bcc (Blind Carbon Copy) messages. You can send to multiple or single, both methods are acceptable:', 'si-contact-form') ?><br />
        user@example.com<br />
        user@example.com, anotheruser@example.com
        </div>
        <br />

        <input name="si_contact_double_email" id="si_contact_double_email" type="checkbox" <?php if( $this->get_settings('si_contact_double_email') == 'true' ) echo 'checked="checked"'; ?> />
        <label name="si_contact_double_email" for="si_contact_double_email"><?php _e('Enable double E-mail entry required on contact form.', 'si-contact-form') ?></label>
        <br />
        <input name="si_contact_domain_protect" id="si_contact_domain_protect" type="checkbox" <?php if( $this->get_settings('si_contact_domain_protect') == 'true' ) echo 'checked="checked"'; ?> />
        <label name="si_contact_domain_protect" for="si_contact_domain_protect"><?php _e('Enable Form Post security by requiring domain name match for', 'si-contact-form') ?>
        <?php
        $uri = parse_url(get_option('siteurl'));
        $blogdomain = str_replace('www.','',$uri['host']);
        echo " $blogdomain ";
        ?><?php _e('(recommended).', 'si-contact-form') ?>
        </label>
        <br />

        <label name="si_contact_email_language" for="si_contact_email_language"><?php _e('E-mail header field: language', 'si-contact-form') ?>:</label><input name="si_contact_email_language" id="si_contact_email_language" type="text" value="<?php echo $this->get_settings('si_contact_email_language');  ?>" size="10" />
        <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_email_language_tip');"><?php _e('help', 'si-captcha'); ?></a>
        <div style="text-align:left; display:none" id="si_contact_email_language_tip">
        <?php _e('Do not change unless you have good reason to.', 'si-contact-form') ?>
        </div>
        <br />

        <label name="si_contact_email_charset" for="si_contact_email_charset"><?php _e('E-mail header field: charset', 'si-contact-form') ?>:</label><input name="si_contact_email_charset" id="si_contact_email_charset" type="text" value="<?php echo $this->get_settings('si_contact_email_charset');  ?>" size="15" />
        <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_email_charset_tip');"><?php _e('help', 'si-captcha'); ?></a>
        <div style="text-align:left; display:none" id="si_contact_email_charset_tip">
        <?php _e('Do not change unless you have good reason to.', 'si-contact-form') ?>
        </div>
        <br />

        <label name="si_contact_email_encoding" for="si_contact_email_encoding"><?php _e('E-mail header field: encoding', 'si-contact-form') ?>:</label><input name="si_contact_email_encoding" id="si_contact_email_encoding" type="text" value="<?php echo $this->get_settings('si_contact_email_encoding');  ?>" size="20" />
        <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_email_encoding_tip');"><?php _e('help', 'si-captcha'); ?></a>
        <div style="text-align:left; display:none" id="si_contact_email_encoding_tip">
        <?php _e('Do not change unless you have good reason to.', 'si-contact-form') ?>
        </div>

      </td>
    </tr>

    <tr>
       <th scope="row" style="width: 75px;"><?php _e('CAPTCHA:', 'si-contact-form') ?></th>
      <td>
        <input name="si_contact_captcha_enable" id="si_contact_captcha_enable" type="checkbox" <?php if ( $this->get_settings('si_contact_captcha_enable') == 'true' ) echo ' checked="checked" '; ?> />
        <label for="si_contact_captcha_enable"><?php _e('Enable CAPTCHA (recommended).', 'si-contact-form') ?></label><br />

        <input name="si_contact_captcha_perm" id="si_contact_captcha_perm" type="checkbox" <?php if( $this->get_settings('si_contact_captcha_perm') == 'true' ) echo 'checked="checked"'; ?> />
        <label name="si_contact_captcha_perm" for="si_contact_captcha_perm"><?php _e('Hide CAPTCHA for', 'si-contact-form') ?>
        <strong><?php _e('registered', 'si-contact-form') ?></strong> <?php _e('users who can', 'si-contact-form') ?>:</label>
        <?php $this->si_contact_captcha_perm_dropdown('si_contact_captcha_perm_level', $this->get_settings('si_contact_captcha_perm_level'));  ?><br />

        <a href="<?php echo "$captcha_url_cf/secureimage_test.php"; ?>"><?php _e('Test if your PHP installation will support the CAPTCHA', 'si-captcha') ?></a>
      </td>
    </tr>

    <tr>
         <th scope="row" style="width: 75px;"><?php _e('Redirect:', 'si-contact-form') ?></th>
      <td>
        <input name="si_contact_redirect_enable" id="si_contact_redirect_enable" type="checkbox" <?php if( $this->get_settings('si_contact_redirect_enable') == 'true' ) echo 'checked="checked"'; ?> />
        <label name="si_contact_redirect_enable" for="si_contact_redirect_enable"><?php _e('Enable redirect after the message sends', 'si-contact-form') ?>.</label><br  />

        <label name="si_contact_redirect_url" for="si_contact_redirect_url"><?php _e('Redirect URL', 'si-contact-form') ?>:</label><input name="si_contact_redirect_url" id="si_contact_redirect_url" type="text" value="<?php echo $this->get_settings('si_contact_redirect_url');  ?>" size="50" />
        <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_redirect_url_tip');"><?php _e('help', 'si-captcha'); ?></a>
        <div style="text-align:left; display:none" id="si_contact_redirect_url_tip">
        <?php _e('After a user sends a message, the web browser will display "message sent" for 5 seconds, then redirect to this URL.', 'si-contact-form') ?>
        </div>
        <br />
      </td>
    </tr>

    <tr>
       <th scope="row" style="width: 75px;"><?php _e('Style:', 'si-contact-form') ?></th>
      <td>

        <input name="si_contact_border_enable" id="si_contact_border_enable" type="checkbox" <?php if ( $this->get_settings('si_contact_border_enable') == 'true' ) echo ' checked="checked" '; ?> />
        <label for="si_contact_border_enable"><?php _e('Enable border on contact form', 'si-contact-form') ?></label><br />


        <label for="si_contact_title_style"><?php _e('CSS style for form input titles on the contact form', 'si-contact-form') ?>:</label><input name="si_contact_title_style" id="si_contact_title_style" type="text" value="<?php echo $this->ctf_stripslashes($this->ctf_output_string($this->get_settings('si_contact_title_style')));  ?>" size="50" /><br />
        <label for="si_contact_field_style"><?php _e('CSS style for form input fields on the contact form', 'si-contact-form') ?>:</label><input name="si_contact_field_style" id="si_contact_field_style" type="text" value="<?php echo $this->ctf_stripslashes($this->ctf_output_string($this->get_settings('si_contact_field_style')));  ?>" size="50" /><br />
        <label for="si_contact_error_style"><?php _e('CSS style for form input errors on the contact form', 'si-contact-form') ?>:</label><input name="si_contact_error_style" id="si_contact_error_style" type="text" value="<?php echo $this->ctf_stripslashes($this->ctf_output_string($this->get_settings('si_contact_error_style')));  ?>" size="50" /><br />

       <label for="si_contact_field_size"><?php _e('Input Text Field Size', 'si-contact-form') ?>:</label><input name="si_contact_field_size" id="si_contact_field_size" type="text" value="<?php echo $this->get_settings('si_contact_field_size');  ?>" size="3" />
       <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_field_size_tip');"><?php _e('help', 'si-captcha'); ?></a>
       <div style="text-align:left; display:none" id="si_contact_field_size_tip">
       <?php _e('Use to adjust the size of the contact form text input fields.', 'si-contact-form') ?>
       </div>
       <br />

       <label for="si_contact_text_cols"><?php _e('Input Textarea Field Cols', 'si-contact-form') ?>:</label><input name="si_contact_text_cols" id="si_contact_text_cols" type="text" value="<?php echo $this->get_settings('si_contact_text_cols');  ?>" size="3" />
       <label for="si_contact_text_rows"><?php _e('Rows', 'si-contact-form') ?>:</label><input name="si_contact_text_rows" id="si_contact_text_rows" type="text" value="<?php echo $this->get_settings('si_contact_text_rows');  ?>" size="3" />
       <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_text_rows_tip');"><?php _e('help', 'si-captcha'); ?></a>
       <div style="text-align:left; display:none" id="si_contact_text_rows_tip">
       <?php _e('Use to adjust the size of the contact form message textarea.', 'si-contact-form') ?>
       </div>
       <br />

       <input name="si_contact_aria_required" id="si_contact_aria_required" type="checkbox" <?php if( $this->get_settings('si_contact_aria_required') == 'true' ) echo 'checked="checked"'; ?> />
       <label name="si_contact_aria_required" for="si_contact_aria_required"><?php _e('Enable aria-required tags for screen readers', 'si-contact-form') ?>.</label>
       <a style="cursor:pointer;" title="<?php _e('Click for Help!', 'si-captcha'); ?>" onclick="toggleVisibility('si_contact_aria_required_tip');"><?php _e('help', 'si-captcha'); ?></a>
       <div style="text-align:left; display:none" id="si_contact_aria_required_tip">
       <?php _e('aria-required is a form input WAI ARIA tag. Screen readers use it to determine which fields are required. Enabling this is good for accessability, but will cause the HTML to fail the W3C Validation (there is no attribute "aria-required"). WAI ARIA attributes are soon to be accepted by the HTML validator, so you can safely ignore the validation error it will cause.', 'si-contact-form') ?>

      </td>
    </tr>

        </table>
        </fieldset>


        <p class="submit">
                <input type="submit" name="submit" value="<?php _e('Update Options', 'si-contact-form') ?> &raquo;" />
        </p>
</form>
</div>
<?php
}// end options_page

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
        echo '<select name="' . $select_name . '" id="' . $select_name . '">' . "\n";
        foreach ($choices as $text => $capability) :
                if ($capability == $checked_value) $checked = ' selected="selected" ';
                echo "\t". '<option value="' . $capability . '"' . $checked . ">$text</option> \n";
                $checked = '';
        endforeach;
        echo "\t</select>\n";
 }

// this function prints the contact form
// and does all the decision making to send the email or not
function si_contact_form_short_code() {
   global $captcha_path_cf;

// E-mail Contacts
// the drop down list array will be made automatically by this code
$ctf_contacts = array ();
//$ctf_contacts_test = 'Manager,user1@example.com
//Service,user2@example.com';

//$ctf_contacts_test = 'user1@example.com';
$ctf_contacts_test = trim($this->get_settings('si_contact_email_to'));

// check for single e-mail
if(!preg_match("/,/", $ctf_contacts_test) && $this->ctf_validate_email($ctf_contacts_test)) {
   $ctf_contacts[] = array('CONTACT' => __('Webmaster', 'si-contact-form'),  'EMAIL' => $ctf_contacts_test );
}

// check for multiple e-mail
$ctf_ct_arr = explode("\n",$ctf_contacts_test);
foreach($ctf_ct_arr as $line) {
    // echo '|'.$line.'|' ;
   list($key, $value) = explode(",",$line);
   $key = trim($key); $value = trim($value);
   if ($key != '' && $value != '' && $this->ctf_validate_email($value)) {
      $ctf_contacts[] = array('CONTACT' => $key,  'EMAIL' => $value);
   }
}

//print_r($ctf_contacts);

// Email address(s) to receive Bcc (Blind Carbon Copy) messages
$ctf_email_address_bcc = $this->get_settings('si_contact_email_bcc'); // optional

// Normally this setting will be left blank in options.
$ctf_email_on_this_domain =  $this->get_settings('si_contact_email_from'); // optional

// Site Name / Title
$ctf_sitename = get_option('blogname');

// Site Domain without the http://www like this: $domain = '642weather.com';
// Can be a single domain:      $ctf_domain = '642weather.com';
// Can be an array of domains:  $ctf_domain = array('642weather.com','someothersite.com');
        // get blog domain
        $uri = parse_url(get_option('siteurl'));
        $blogdomain = str_replace('www.','',$uri['host']);

$this->ctf_domain = $blogdomain;

// Make sure the form was posted from your host name only.
// This is a security feature to prevent spammers from posting from files hosted on other domain names
// "Input Forbidden" message will result if host does not match
$this->ctf_domain_protect = $this->get_settings('si_contact_domain_protect');

// Double E-mail entry is optional
// enabling this requires user to enter their email two times on the contact form.
$ctf_enable_double_email = $this->get_settings('si_contact_double_email');

// You can ban known IP addresses
// SET  $ctf_enable_ip_bans = 1;  ON,  $ctf_enable_ip_bans = 0; for OFF.
$ctf_enable_ip_bans = 0;

// Add IP addresses to ban here:  (be sure to SET  $ctf_enable_ip_bans = 1; to use this feature
$ctf_banned_ips = array(
'22.22.22.22', // example (add, change, or remove as needed)
'33.33.33.33', // example (add, change, or remove as needed)
);

// Wordwrap E-Mail message text so lines are no longer than 70 characters.
// SET  $ctf_wrap_message = 1;  ON,  $ctf_wrap_message = 0; for OFF.
$ctf_wrap_message = 1;

// Content-language for email message header
$ctf_language = $this->get_settings('si_contact_email_language');

// Charset for email message header
$ctf_charset = $this->get_settings('si_contact_email_charset');

// Content-transfer-encoding for email message header
$ctf_encoding = $this->get_settings('si_contact_email_encoding');

// Redirect to Home Page after message is sent
$ctf_redirect_enable = $this->get_settings('si_contact_redirect_enable');
// Used for the delay timer once the message has been sent
$ctf_redirect_timeout = 5; // time in seconds to wait before loading another Web page
// Web page to send the user to after the time has expired
$ctf_redirect_url = $this->get_settings('si_contact_redirect_url');

// The $ctf_welcome_intro is what gets printed when the contact form is first presented.
// It is not printed when there is an input error and not printed after the form is completed
$ctf_welcome_intro = '

'.$this->get_settings('si_contact_welcome').'

';

// The $thank_you is what gets printed after the form is sent.
$ctf_thank_you = '
<p>
'.__('Your message has been sent, thank you.', 'si-contact-form').'
</p>
';

if ($ctf_redirect_enable == 'true') {
  $wp_plugin_url = WP_PLUGIN_URL;

 $ctf_thank_you .= <<<EOT

<script type="text/javascript" language="javascript">
<!--
var count=$ctf_redirect_timeout;
var time;
function timedCount() {
  document.title='Redirecting in ' + count + ' seconds';
  count=count-1;
  time=setTimeout("timedCount()",1000);
  if (count==-1) {
    clearTimeout(time);
    document.title='Redirecting ...';
    self.location='$ctf_redirect_url';
  }
}
window.onload=timedCount;
//-->
</script>
EOT;

$ctf_thank_you .= '
<img src="'.$wp_plugin_url.'/si-contact-form/ctf-loading.gif" alt="'.esc_attr(__('Redirecting', 'si-contact-form')).'" />&nbsp;&nbsp;
'.__('Redirecting', 'si-contact-form').' ... ';


// do not remove the above EOT line

}

// add numbered keys starting with 1 to the $contacts array
$cont = array();
$ct = 1;
foreach ($ctf_contacts as $v)  {
    $cont["$ct"] = $v;
    $ct++;
}
$contacts = $cont;
unset($cont);

// initialize vars
$this->si_contact_error = 0;
$si_contact_error_print = '';
$message_sent = 0;
$mail_to    = '';
$to_contact = '';
$name       = '';
$email      = '';
$email2     = '';
$subject    = '';
$message       = '';
$captcha_code  = '';
// add another field here like above

$si_contact_error_captcha = '';
$si_contact_error_contact = '';
$si_contact_error_name    = '';
$si_contact_error_email   = '';
$si_contact_error_email2  = '';
$si_contact_error_subject = '';
$si_contact_error_text    = '';
// add another field here like above

// process form now
if (isset($_POST['si_contact_action']) && ($_POST['si_contact_action'] == 'send')) {

    // check all input variables
    $cid = $this->ctf_clean_input($_POST['si_contact_CID']);
    if(empty($cid)) {
       $this->si_contact_error = 1;
       $si_contact_error_contact = __('Selecting a contact is required.', 'si-contact-form');
    }
    else if (!isset($contacts[$cid]['CONTACT'])) {
        $this->si_contact_error = 1;
        $si_contact_error_contact = __('Requested Contact not found.', 'si-contact-form');
    }
    if (empty($ctf_contacts)) {
       $this->si_contact_error = 1;
    }
    $mail_to    = $this->ctf_clean_input($contacts[$cid]['EMAIL']);
    $to_contact = $this->ctf_clean_input($contacts[$cid]['CONTACT']);


    $name    = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_name']));
    $email   = strtolower($this->ctf_clean_input($_POST['si_contact_email']));
    if ($ctf_enable_double_email == 'true') {
       $email2   = strtolower($this->ctf_clean_input($_POST['si_contact_email2']));
    }
    $subject      = $this->ctf_name_case($this->ctf_clean_input($_POST['si_contact_subject']));
    $message      = $this->ctf_clean_input($_POST['si_contact_message']);
    $captcha_code = $this->ctf_clean_input($_POST['si_contact_captcha_code']);
    // add another field here like above

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
   if (!preg_match("/[a-z]/", $message)) {
      $message = $this->ctf_name_case($message);
   }

   if(empty($name)) {
       $this->si_contact_error = 1;
       $si_contact_error_name = __('Your name is required.', 'si-contact-form');
   }
   if (!$this->ctf_validate_email($email)) {
       $this->si_contact_error = 1;
       $si_contact_error_email = __('A proper e-mail address is required.', 'si-contact-form');
   }
   if ($ctf_enable_double_email == 'true' && !$this->ctf_validate_email($email2)) {
       $this->si_contact_error = 1;
       $si_contact_error_email2 = __('A proper e-mail address is required.', 'si-contact-form');
   }
   if ($ctf_enable_double_email == 'true' && ($email != $email2) ) {
       $this->si_contact_error = 1;
       $si_contact_error_double_email = __('The two e-mail addresses did not match, please enter again.', 'si-contact-form');
   }
   if(empty($subject)) {
       $this->si_contact_error = 1;
       $si_contact_error_subject = __('Subject text is required.', 'si-contact-form');
   }
   if(empty($message)) {
       $this->si_contact_error = 1;
       $si_contact_error_message = __('Message text is required.', 'si-contact-form');
   }

   // Check with Akismet, but only if Akismet is installed, activated, and has a KEY. (Recommended for spam control).
   if( function_exists('akismet_http_post') && get_option('wordpress_api_key') ){
			global $akismet_api_host, $akismet_api_port;
			$c['user_ip']    		= preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
			$c['user_agent'] 		= $_SERVER['HTTP_USER_AGENT'];
			$c['referrer']   		= $_SERVER['HTTP_REFERER'];
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
			foreach ( $c as $key => $data )
				$query_string .= $key . '=' . urlencode( stripslashes($data) ) . '&';
			$response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
			if ( 'true' == $response[1] ) {
                $this->si_contact_error = 1; // Akismet says it is spam.
                $si_contact_error_message = __('Contact Form has Invalid Input', 'si-contact-form');
			}
    } // end if(function_exists('akismet_http_post')){

   // add another field here like 4 lines above (only if you want it to be required)

  // begin captcha check if enabled
  // captcha is optional but recommended to prevent spam bots from spamming your contact form
  if ( $this->isCaptchaEnabled() ) {
    if (!isset($_SESSION['securimage_code_value']) || empty($_SESSION['securimage_code_value'])) {
          $this->si_contact_error = 1;
          $si_contact_error_captcha = __('Could not read CAPTCHA cookie. Make sure you have cookies enabled and not blocking in your web browser settings. Or another plugin is conflicting. See plugin FAQ.', 'si-contact-form');
    }else{
       if (empty($captcha_code) || $captcha_code == '') {
         $this->si_contact_error = 1;
         $si_contact_error_captcha = __('Please complete the CAPTCHA.', 'si-contact-form');
       } else {
         include_once "$captcha_path_cf/securimage.php";
         $img = new Securimage();
         $valid = $img->check("$captcha_code");
         // Check, that the right CAPTCHA password has been entered, display an error message otherwise.
         if($valid == true) {
             // ok can continue
         } else {
              $this->si_contact_error = 1;
              $si_contact_error_captcha = __('That CAPTCHA was incorrect.', 'si-contact-form');
         }
    }
   }
  } // end if enable captcha
  // end captcha check

  if (!$this->si_contact_error) {
     // ok to send the email, so prepare the email message
     // lines separated by \n on Unix and \r\n on Windows
     if (!defined('PHP_EOL')) define ('PHP_EOL', strtoupper(substr(PHP_OS,0,3) == 'WIN') ? "\r\n" : "\n");

     $subj = "$ctf_sitename ".__('contact', 'si-contact-form').": $subject";

     $msg =  __('Sent from', 'si-contact-form')." $ctf_sitename ".__('contact form', 'si-contact-form').'

'.__('To', 'si-contact-form').": $to_contact

".__('From', 'si-contact-form').":
$name
$email

".__('Message', 'si-contact-form').":
$message

";
// add another field here (in the $msg code above)

      // add some info about sender to the email message
      $userdomain = '';
      $userdomain = gethostbyaddr($_SERVER['REMOTE_ADDR']);
      $user_info_string  = __('Sent from (ip address)', 'si-contact-form').': '.$_SERVER['REMOTE_ADDR']." ($userdomain)" . PHP_EOL;
      $user_info_string .= __('Coming from (referer)', 'si-contact-form').': '.get_permalink() . PHP_EOL;
      $user_info_string .= __('Using (user agent)', 'si-contact-form').': '.$this->ctf_clean_input($_SERVER['HTTP_USER_AGENT']) . PHP_EOL . PHP_EOL;
      $msg .= $user_info_string;

      // wordwrap email message
      if ($ctf_wrap_message) {
             $msg = wordwrap($msg, 70);
      }

      // remove some characters that mess up From: $name <$email>
      // remove single quote, double quote, semicolon, colon, comma
      $name = $this->ctf_name_case(preg_replace(array ( '/\'/', '/"/', '/;/', '/:/', '/,/' ), '', $name));

      // prepare the email header
      if ($ctf_email_on_this_domain != '') {
           $header =  "From: $email_on_this_domain" . PHP_EOL;
      } else {
           $header =  "From: $name <$email>" . PHP_EOL;
      }
      if ($ctf_email_address_bcc !='') $header .= "Bcc: " . $ctf_email_address_bcc . PHP_EOL;
      $header .= "Reply-To: $email" . PHP_EOL;
      $header .= "Return-Path: $email" . PHP_EOL;
      $header .= 'MIME-Version: 1.0' . PHP_EOL;
      $header .= 'Content-type: text/plain; Content-language: '.$ctf_language.'; charset="'.$ctf_charset.'"' . PHP_EOL;
      $header .= 'Content-transfer-encoding: '.$ctf_encoding . PHP_EOL;

      ini_set('sendmail_from', $email); // needed for some windows servers

      mail($mail_to,$subj,$msg,$header);
      $message_sent = 1;

   } // end if ! error
} // end if posted si_contact_action = send

if($message_sent) {
      // thank you mesage is printed here
      $string .= $ctf_thank_you;
}else{
      if (!$this->si_contact_error) {
        // welcome intro is printed here unless message is sent
        $string .= $ctf_welcome_intro;
      }

 $this->ctf_title_style = 'style="'.$this->get_settings('si_contact_title_style').'"';
 $this->ctf_field_style = 'style="'.$this->get_settings('si_contact_field_style').'"';
 $this->ctf_error_style = 'style="'.$this->get_settings('si_contact_error_style').'"';
 $ctf_field_size = intval($this->get_settings('si_contact_field_size'));


 if ($this->get_settings('si_contact_aria_required') == 'true') {
         $this->ctf_aria_required = ' aria-required="true" ';
 } else {
         $this->ctf_aria_required = '';
 }

$string .= '
<!-- SI Contact Form plugin begin -->
<form action="' . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . '" id="si_contact_form" method="post">
';


if ($this->get_settings('si_contact_border_enable') == 'true') {
  $string .= '
    <fieldset>
        <legend>'.__('Contact Form', 'si-contact-form').'</legend>';
}

// print any input errors
if ($this->si_contact_error) {
    $string .= '<div '.$this->ctf_error_style.'>'.__('INPUT ERROR: Please make corrections below and try again.', 'si-contact-form').'</div>'."\n";
}
if (empty($ctf_contacts)) {
   $string .= '<div '.$this->ctf_error_style.'>'.__('ERROR: Misconfigured E-mail address in options.', 'si-contact-form').'</div>'."\n";
}

if (count($contacts) > 1) {

     $string .= '        <div '.$this->ctf_title_style.'>
                <label for="si_contact_CID">'.__('Department to Contact', 'si-contact-form').':</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_contact).'
        <div '.$this->ctf_field_style.'>
                <select id="si_contact_CID" name="si_contact_CID" '.$this->ctf_aria_required.'>
';

    $string .= '                        <option value="">'.esc_attr(__('Select', 'si-contact-form')).'</option>'."\n";

     if ( !isset($cid) ) {
          $cid = $_GET['si_contact_CID'];
     }

     $selected = '';

      foreach ($contacts as $k => $v)  {
          if (!empty($cid) && $cid == $k) {
                    $selected = 'selected="selected"';
          }
          $string .= '                        <option value="' . $k . '" ' . $selected . '>' . esc_attr($v[CONTACT]) . '</option>' . "\n";
          $selected = '';
      }

      $string .= '            </select>
      </div>' . "\n";
}
else {

     $string .= '<input type="hidden" name="si_contact_CID" value="1" />'."\n";

}

$string .= '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_name">'.__('Name', 'si-contact-form').':</label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_name).'
        <div '.$this->ctf_field_style.'>
                <input type="text" id="si_contact_name" name="si_contact_name" value="' . $this->ctf_output_string($name) .'" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>';
if ($ctf_enable_double_email == 'true') {
 $string .= '
        <div '.$this->ctf_title_style.'>
        <label for="si_contact_email">'.__('E-Mail Address', 'si-contact-form').': </label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_email).'
         '.$this->ctf_echo_if_error($si_contact_error_double_email).'
        <div '.$this->ctf_field_style.'>
                <input type="text" id="si_contact_email" name="si_contact_email" value="' . $this->ctf_output_string($email) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>
        <div '.$this->ctf_title_style.'>
        <label for="si_contact_email2">'.__('E-Mail Address again', 'si-contact-form').': </label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_email2).'
        <div '.$this->ctf_field_style.'>
                <input type="text" id="si_contact_email2" name="si_contact_email2" value="' . $this->ctf_output_string($email2) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
                <br /><span class="small">'.__('Please enter your E-mail Address a second time.', 'si-contact-form').'</span>
        </div>
        ';

 } else {
$string .= '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_email">'.__('E-Mail Address', 'si-contact-form').': </label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_email).'
        <div '.$this->ctf_field_style.'>
                <input type="text" id="si_contact_email" name="si_contact_email" value="' . $this->ctf_output_string($email) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>';

}

$string .=   '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_subject">'.__('Subject', 'si-contact-form').': </label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_subject).'
        <div '.$this->ctf_field_style.'>
                <input type="text" id="si_contact_subject" name="si_contact_subject" value="' . $this->ctf_output_string($subject) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>

        <!-- if you add another field here (similar to the title and field div code above
        be sure to change the label, id, name, string name, and si_contact_error_[string name]) -->

        <div '.$this->ctf_title_style.'>
                <label for="si_contact_message">'.__('Message', 'si-contact-form').': </label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_message).'
        <div '.$this->ctf_field_style.'>
                <textarea id="si_contact_message" name="si_contact_message" '.$this->ctf_aria_required.' cols="'.intval($this->get_settings('si_contact_text_cols')).'" rows="'.intval($this->get_settings('si_contact_text_rows')).'">' . $this->ctf_output_string($message) . '</textarea>
        </div>
';

// captcha is optional but recommended to prevent spam bots from spamming your contact form
if ( $this->isCaptchaEnabled() ) {
    $string .= $this->addCaptchaToContactForm($si_contact_error_captcha);
}

$string .= '
<br />
<br />
<div '.$this->ctf_field_style.'>
  <input type="hidden" name="si_contact_action" value="send" />
  <input type="submit" value="'.__('submit', 'si-contact-form').'" />
</div>
';
if ($this->get_settings('si_contact_border_enable') == 'true') {
  $string .= '
    </fieldset>
  ';
}
$string .= '
</form>

<!-- SI Contact Form plugin end -->
';

}
 return $string;
} // end function si_contact_form_short_code

// checks if captcha is enabled based on the current captcha permission settings set in the plugin options
function isCaptchaEnabled() {
   global $user_ID;

   if ($this->get_settings('si_contact_captcha_enable') !== 'true') {
        return false; // captcha setting is disabled for si contact
   }
   // skip the captcha if user is loggged in and the settings allow
   if (isset($user_ID) && intval($user_ID) > 0 && $this->get_settings('si_contact_captcha_perm') == 'true') {
       // skip the CAPTCHA display if the minimum capability is met
       if ( current_user_can( $this->get_settings('si_contact_captcha_perm_level') ) ) {
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
  if ( !file_exists("$captcha_path_cf/securimage.php") ) {
       $this->captchaRequiresError .= '<p '.$this->ctf_error_style.'>'.__('ERROR: si-contact-form.php plugin says captcha_library not found.', 'si-contact-form').'</p>';
       $ok = 'no';
  }
  if ($ok == 'no')  return false;
  return true;
}

// this function adds the captcha to the contact form
function addCaptchaToContactForm($si_contact_error_captcha) {
   global $user_ID, $captcha_url_cf;

  $string = '';

// Test for some required things, print error message right here if not OK.
if ($this->captchaCheckRequires()) {

// the captch html
$string = '
        <div '.$this->ctf_title_style.'>
                <label for="si_contact_captcha_code">'.__('CAPTCHA Code', 'si-contact-form').': </label>
        </div> '.$this->ctf_echo_if_error($si_contact_error_captcha).'
        <div '.$this->ctf_field_style.'>
                <input type="text" name="si_contact_captcha_code" id="si_contact_captcha_code" '.$this->ctf_aria_required.' style="width:65px;" />
        </div>

<div style="text-align:left; float:left; width: 205px; padding-top: 5px; ">
         <img id="siimage" style="padding-right: 5px; border-style: none; float:left;"
         src="'.$captcha_url_cf.'/securimage_show.php?sid='.md5(uniqid(time())).'"
         alt="'.__('CAPTCHA Image', 'si-contact-form').'" title="'.esc_attr(__('CAPTCHA Image', 'si-contact-form')).'" />
           <a href="'.$captcha_url_cf.'/securimage_play.php" title="'.esc_attr(__('Audible Version of CAPTCHA', 'si-contact-form')).'">
         <img src="'.$captcha_url_cf.'/images/audio_icon.gif" alt="'.esc_attr(__('Audio Version', 'si-contact-form')).'"
          style="border-style: none; vertical-align:top; border-style: none;" onclick="this.blur()" /></a><br />
           <a href="#" title="'.esc_attr(__('Refresh Image', 'si-contact-form')).'" style="border-style: none"
         onclick="document.getElementById(\'siimage\').src = \''.$captcha_url_cf.'/securimage_show.php?sid=\' + Math.random(); return false">
         <img src="'.$captcha_url_cf.'/images/refresh.gif" alt="'.esc_attr(__('Reload Image', 'si-contact-form')).'"
         style="border-style: none; vertical-align:bottom;" onclick="this.blur()" /></a>
</div>
<br /><br />
';
} else {
      $string .= $this->captchaRequiresError;
}
  return $string;
} // end function addCaptchaToContactForm

// shows contact form errors
function ctf_echo_if_error($this_error){
  if ($this->si_contact_error) {
    if (!empty($this_error)) {
         return '
         <div '.$this->ctf_error_style.'>'.esc_html(__('ERROR', 'si-contact-form')).': ' . esc_html($this_error) . '</div>'."\n";
    }
  }
} // end function ctf_echo_if_error

// functions for protecting and validating form input vars
function ctf_clean_input($string) {
    if (is_string($string)) {
      return trim($this->ctf_sanitize_string(strip_tags($this->ctf_stripslashes($string))));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = $this->ctf_clean_input($value);
      }
      return $string;
    } else {
      return $string;
    }
} // end function ctf_clean_input

// functions for protecting and validating form input vars
function ctf_sanitize_string($string) {
    $string = preg_replace("/ +/", ' ', trim($string));
    return preg_replace("/[<>]/", '_', $string);
} // end function ctf_sanitize_string

// functions for protecting and validating form input vars
function ctf_stripslashes($string) {
        if (get_magic_quotes_gpc()) {
                return stripslashes($string);
        } else {
                return $string;
        }
} // end function ctf_stripslashes

// functions for protecting and validating form input vars
function ctf_output_string($string) {
    return str_replace('"', '&quot;', $string);
} // end function ctf_output_string

// A function knowing about name case (i.e. caps on McDonald etc)
// $name = name_case($name);
function ctf_name_case($name) {
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

// checks proper email syntax (not perfect, none of these are, but this is the best I can find)
function ctf_validate_email($email) {

   //check for all the non-printable codes in the standard ASCII set,
   //including null bytes and newlines, and exit immediately if any are found.
   if (preg_match("/[\\000-\\037]/",$email)) {
      return false;
   }
   // regular expression used to perform the email check
   // http://fightingforalostcause.net/misc/2006/compare-email-regex.php
   //$pattern = "/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|asia|cat|jobs|tel|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i";
   //$pattern = "/^([_a-zA-Z0-9-]+)(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+)(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/i";
   $pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
   if(!preg_match($pattern, $email)){
      return false;
   }
   // Validate the domain exists with a DNS check
   list($user,$domain) = explode('@',$email);
   //if(function_exists("getmxrr") && getmxrr($domain, $mxhosts)) {
   if(function_exists('checkdnsrr') && checkdnsrr($domain,"MX")) { // Linux: PHP 4.3.0 and higher & Windows: PHP 5.3.0 and higher
        return true;
   }
   else if(@fsockopen($domain, 25, $errno, $errstr, 5)) {
        return true;
   }
   else {
        return false;
   }
   return true;
} // end function ctf_validate_email

// helps spam protect email input
// finds new lines injection attempts
function ctf_forbidifnewlines($input) {
   if (
       stristr($input, "\r")  !== false ||
       stristr($input, "\n")  !== false ||
       stristr($input, "%0a") !== false ||
       stristr($input, "%0d") !== false) {
         //wp_die(__('Contact Form has Invalid Input', 'si-contact-form'));
         $this->si_contact_error = 1;

   }
} // end function ctf_forbidifnewlines

// helps spam protect email input
// blocks contact form posted from other domains
function ctf_spamcheckpost() {

 if(!isset($_SERVER['HTTP_USER_AGENT'])){
     return 1;
  }

 // Make sure the form was indeed POST'ed:
 //  (requires your html form to use: si_contact_action="post")
 if(!$_SERVER['REQUEST_METHOD'] == "POST"){
    return 2;
 }

  // Make sure the form was posted from an approved host name.
 if ($this->ctf_domain_protect == 'true') {
   // Host names from where the form is authorized to be posted from:
   if (is_array($this->ctf_domain)) {
      $this->ctf_domain = array_map(strtolower, $this->ctf_domain);
      $authHosts = $this->ctf_domain;
   } else {
      $this->ctf_domain =  strtolower($this->ctf_domain);
      $authHosts = array("$this->ctf_domain");
   }

   // Where have we been posted from?
   if( isset($_SERVER['HTTP_REFERER']) and trim($_SERVER['HTTP_REFERER']) != '' ) {
      $fromArray = parse_url(strtolower($_SERVER['HTTP_REFERER']));
      // Test to see if the $fromArray used www to get here.
      $wwwUsed = strpos($fromArray['host'], "www.");
      if(!in_array(($wwwUsed === false ? $fromArray['host'] : substr(stristr($fromArray['host'], '.'), 1)), $authHosts)){
         return 3;
      }
   }
 } // end if domain protect

 // check posted input for email injection attempts
 // Check for these common exploits
 // if you edit any of these do not break the syntax of the regex
 $input_expl = "/(content-type|mime-version|content-transfer-encoding|to:|bcc:|cc:|document.cookie|document.write|onmouse|onkey|onclick|onload)/i";
 // Loop through each POST'ed value and test if it contains one of the exploits fromn $input_expl:
 foreach($_POST as $k => $v){
   $v = strtolower($v);
   if( preg_match($input_expl, $v) ){
     return 4;
   }
 }

 return 0;
} // end function ctf_spamcheckpost

function si_contact_plugin_action_links( $links, $file ) {
	if ( $file != plugin_basename( __FILE__ ))
		return $links;

	$settings_link = '<a href="plugins.php?page=si-contact-form/si-contact-form.php">' . esc_html( __( 'Settings', 'si-contact-form' ) ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

function init() {

  // a PHP session cookie is set so that the captcha can be remembered and function
  // this has to be set before any header output
  //echo "starting session ctf";
  session_cache_limiter ('private, must-revalidate');
  // start cookie session, but do not start session if captcha is disabled in options
  if( !isset( $_SESSION ) && get_option('si_contact_captcha_enable') == 'true' ) { // play nice with other plugins
    session_start();
   // echo "session started ctf";
  }

   if (function_exists('load_plugin_textdomain')) {
      load_plugin_textdomain('si-contact-form', false, dirname(plugin_basename(__FILE__)) );
   }
}

} // end of class
} // end of if class

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

// Pre-2.8 compatibility
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return wp_specialchars( $text );
	}
}

// Pre-2.8 compatibility
if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return attribute_escape( $text );
	}
}

if (class_exists("siContactForm")) {
 $si_contact_form = new siContactForm();
}

if (isset($si_contact_form)) {

  $captcha_url_cf  = WP_PLUGIN_URL . '/si-contact-form/captcha-secureimage';
  $captcha_path_cf = WP_PLUGIN_DIR . '/si-contact-form/captcha-secureimage';

  // wp_nonce_field is used to make the admin option settings more secure
  if ( !function_exists("wp_nonce_field") ) {
        function si_contact_nonce_field($action = -1) { return; }
        $si_contact_nonce = -1;
  } else {
        function si_contact_nonce_field($action = -1) { return wp_nonce_field($action); }
        $si_contact_nonce = 'si-contact-update-key';
  }

  // si_contact_actions
  add_action('init', array(&$si_contact_form, 'init'));

  // si contact form admin options
  add_action('admin_menu', array(&$si_contact_form,'add_tabs'),1);

  // adds "Settings" link to the plugin action page
  add_filter( 'plugin_action_links', array(&$si_contact_form,'si_contact_plugin_action_links'),10,2);

  // use shortcode to print the contact form or process contact form logic
  add_shortcode('si_contact_form', array(&$si_contact_form,'si_contact_form_short_code'),1);

  // uncomment if you want the settings deleted when this plugin is deactivated
  //register_deactivation_hook(__FILE__, array(&$si_contact_form, 'unset_si_contact_options'), 1);
}

?>