=== Fast and Secure Contact Form ===
Contributors: Mike Challis
Author URI: http://www.642weather.com/weather/scripts.php
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6105441
Tags: Akismet, captcha, contact, contact form, email, spam
Requires at least: 2.5
Tested up to: 2.8.4
Stable tag: trunk

A Fast and Secure Contact Form for WordPress.

== Description ==

Fast and Secure Contact Form for WordPress. This contact form lets your visitors send you a quick E-mail message. Blocks all common spammer tactics. Spam is no longer a problem. Includes a CAPTCHA and Akismet. Does not require JavaScript. Easy and Quick 3 step install.

[Plugin URI]: (http://www.642weather.com/weather/scripts-wordpress-si-contact.php)

Features:
--------
 * Configure Options from Admin panel.
 * Uses simple error messages.
 * Reloads form data and warns user if user forgets to fill out a field.
 * Validates syntax of E-mail address (user@aol = bad, user@aol.com = good).
 * Optional redirect to home page after message sent.
 * Valid HTML, Section 508 and WAI Accessibility HTML Validation.
 * JavaScript is not required.
 * Setting to hide the CAPTCHA from logged in users and or admins.
 * I18n language translation support (see FAQ)

Security:
--------
 * It has very tight security, it stops all the spammer tricks I have found.
 * Akismet spam protection.
 * Filters all form inputs from html and other nasties.
 * Spamchecks email address input from common spammer tactics...
prevents spammer forcing to:, cc:, bcc:, newlines, and other email injection attempts to spam the world.
 * Makes sure the form was posted from your approved host name only.
 * E-mail message shows IP address and user agent (browser version) of user who contacted you

Captcha Image Support:
---------------------
 * Captcha Image by www.phpcaptcha.org is included
 * Open-source free PHP CAPTCHA script
 * Abstract background with multi colored, angled, and transparent text
 * Arched lines through text
 * Generates audible CAPTCHA files in wav format
 * Refresh button to reload captcha if you cannot read it

Requirements/Restrictions:
-------------------------
 * Works with Wordpress 2.5+
 * PHP 4.0.6 or above with GD2 library support.

== Installation ==

1. Upload the `si-contact-form` folder to the `/wp-content/plugins/` directory, or download through the `Plugins` menu in WordPress

2. Activate the plugin through the `Plugins` menu in WordPress.

3. You must add the shortcode `[si_contact_form]` in a Page. That page will become your Contact Form.

4. Updates are automatic. Click on "Upgrade Automatically" if prompted from the admin menu. If you ever have to manually upgrade, simply deactivate, uninstall, and repeat the installation steps with the new version.


= Troubleshooting if the CAPTCHA image itself is not being shown: =

This can happen if a server has too low a default permission level on new folders.
Check and make sure the permission on all the captcha-secureimage folders are set to permission: 755

all these folders need to be 755:
- si-contact-form
  - captcha-secureimage
     - audio
     - gdfonts
     - images


== Screenshots ==

1. screenshot-1.jpg is the contact form.

2. screenshot-2.jpg is the contact form showing the inline error messages.

3. screenshot-3.jpg is the `Contact Form options` tab on the `Admin Plugins` page.


== Frequently Asked Questions ==

= Do I have to also install the plugin "SI CAPTCHA for Wordpress"? =

No, this plugin includes the CAPTCHA feature code for this contact form.
The "SI CAPTCHA for Wordpress" plugin is used for comment and registration forms spam protection only.

= I use the plugin "SI CAPTCHA for Wordpress" for my comment and registration forms, is it still needed? =

Yes, the plugin "SI CAPTCHA for Wordpress" should be installed if you want protection for the comment and registration forms.
The two plugins are totally separate.

= Does this contact form use Akismet spam protection? =
Yes, it checks the form input with Akismet, but only if Akismet plugin is also installed and activated.

= Is this plugin available in other languages? =

Yes. To use a translated version, you need to obtain or make the language file for it.
At this point it would be useful to read [Installing WordPress in Your Language](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") from the Codex. You will need an .mo file for SI CAPTCHA that corresponds with the "WPLANG" setting in your wp-config.php file. Translations are listed below -- if a translation for your language is available, all you need to do is place it in the `/wp-content/plugins/si-contact-form` directory of your WordPress installation. If one is not available, and you also speak good English, please consider doing a translation yourself (see the next question).

The following translations are included in the download zip file:

* Coming soon.. Please help translate.

= Can I provide a translation? =

Of course! It will be very gratefully received. Please read [Translating WordPress](http://codex.wordpress.org/Translating_WordPress "Translating WordPress") first for background information on translating. Then obtain the latest [.pot file](http://svn.wp-plugins.org/si-contact-form/trunk/si-contact-form.pot ".pot file") and translate it.
* There are some strings with a space in front or end -- please make sure you remember the space!
* When you have a translation ready, please send the .po and .mo files to wp-translation at 642weather dot com.
* If you have any questions, feel free to email me also. Thanks!

== Changelog ==

= 1.0.2 =
- (27 Aug 2009) Added Akismet spam protection. Checks the form input with Akismet, but only if Akismet plugin is also installed.
- added settings link to the plugin action links.

= 1.0.1 =
- (26 Aug 2009) fixed deprecated ereg_replace and eregi functions for PHP 5.3+ compatibility when error warnings are on

= 1.0 =
- (26 Aug 2009) Initial Release



