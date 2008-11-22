=== SI CAPTCHA for Wordpress ===
Contributors: Mike Challis
Donate link: http://www.642weather.com/weather/scripts.php
Tags: captcha, comments, spam
Requires at least: 2.3
Tested up to: 2.6.3
Stable tag: trunk

Adds CAPTCHA anti-spam methods to WordPress on the comment form, registration form, or both.

== Description ==

Adds CAPTCHA anti-spam methods to WordPress on the comment form, registration form, or both.
In order to post comments, users will have to type in the phrase shown on the image. 
This can help prevent spam from automated bots.

[Plugin URI]: (http://www.642weather.com/weather/scripts-wordpress-captcha.php)

Requirements/Restrictions:
-------------------------

- Works with Wordpress 2.x.
- PHP 4.0.6 or above with GD2 library support.
- Your theme must have a `<?php do_action('comment_form', $post->ID); ?>` tag inside your comments.php form. Most themes do. 
  The best place to locate the tag is before the comment textarea, you may want to move it if it is below the comment textarea.

Captcha Image Support:
---------------------
 * Captcha Image by www.phpcaptcha.org is included
 * Open-source free PHP CAPTCHA script
 * Abstract background with multi colored, angled, and transparent text
 * Arched lines through text
 * Generates audible CAPTCHA files in wav format
 * Refresh button to reload captcha if you cannot read it

Features:
--------
 * Configure from Admin panel
 * JavaScript is not required
 * Allows Trackbacks and Pingbacks
 * Setting to hide the CAPTCHA from logged in users and or admins
 * Setting to show the CAPTCHA on the comment form, registration form, or both


== Installation ==

1. Upload the `si-captcha` folder to the `/wp-content/plugins/` directory

2. Activate the plugin through the `Plugins` menu in WordPress

3. If you ever have to upgrade, simply repeat the installation steps with the new version.


== Screenshots ==

1. screenshot-1.jpg is the captcha on the comment form.

2. screenshot-2.jpg is the captcha on the registration form.

3. screenshot-3.jpg is the `Captcha options` tab on the `Admin Plugins` page.


== Configuration ==

After the plugin is activated, you can configure it by selecting the `Captcha options` tab on the `Admin Plugins` page.
Here is a list of the options:

1. CAPTCHA on Register Form: - Enable CAPTCHA on the register form.

2. CAPTCHA on Comment Form:  - Enable CAPTCHA on the comment form.

3. CAPTCHA on Comment Form:  - Hide CAPTCHA for registered users (select permission level)  

4. Comment Form Rearrange: - Changes the display order of the catpcha input field on the comment form


== Usage ==

Once activated, a captcha image and captcha code entry is added to the comment and register forms. 


== Frequently Asked Questions ==

= Sometimes the captcha image and captcha input field are displayed AFTER the submit button on the comment form. =

Edit your current theme comments.php file and locate this line:
`<?php do_action('comment_form', $post->ID); ?>`
This tag is exactly where the captcha image and captcha code entry will display on the form, so
move the line to before the comment textarea, uncheck the 'Comment Form Rearrange' box on the 'Captcha options' page, 
and the problem should be fixed.

= Alternate Fix for the captcha image display order =

You can just check the 'Comment Form Rearrange' box on the admin plugins 'Captcha options' page and javascript will attempt to rearrange it for you. Editing the comments.php, moving the tag, and uncheck the 'Comment Form Rearrange' box on the 'Captcha options' page is the best solution.

= Why is it better to uncheck the 'Comment Form Rearrange' box and move the tag? =
Because the XHTML will no longer validate if it is checked.

== Version History ==

rel 1.0 (21 Aug 2008)
-------
- Initial Release