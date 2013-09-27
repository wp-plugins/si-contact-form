=== Fast Secure Contact Form ===
Contributors: Mike Challis
Author URI: http://www.642weather.com/weather/scripts.php
Donate link: http://www.FastSecureContactForm.com/donate
Tags: Akismet, captcha, contact, contact form, form, mail, email, spam, multilingual, wpmu
Requires at least: 3.4.2
Tested up to: 3.6.1
Stable tag: trunk

An easy and powerful form builder that lets your visitors send you email. Blocks all automated spammers. No templates to mess with.

== Description ==

Easily create and add forms to WordPress. The contact form will let the user send emails to a site's admin, and also send a meeting request to talk over phone or video. An administration panel is present, where the webmaster can create and preview unlimited forms. 

Features: easy form edit, multiple forms, confirmation emails, no templates to mess with, and an option to redirect visitors to any URL after the message is sent. Includes CAPTCHA and Akismet support to block spammers. Spam is no longer a problem. You can add extra fields of any type: text, textarea, checkbox, checkbox-multiple, radio, select, select-multiple, attachment, date, time, hidden, password, and fieldset.

= Fast Secure Contact Form Version 4.0 Beta 5 =

Version 4.0 Beta 5 was released Sept, 27 2013. Please help test it!

* [Download and test the 4.0 Beta](http://www.fastsecurecontactform.com/beta)
* [Donate to the project](http://www.fastsecurecontactform.com/donate)
* [Contribute your ideas in the support forum](http://wordpress.org/support/plugin/si-contact-form)

= Support and Downloads =

* [FastSecureContactForm.com](http://www.fastsecurecontactform.com/)
* [Download WordPress Plugin Version](http://downloads.wordpress.org/plugin/si-contact-form.zip)
* [Download PHP Script Version](http://www.fastsecurecontactform.com/download-php-script)

= Help Keep This Plugin Free =

If you find this plugin useful to you, please consider [__making a small donation__](http://www.fastsecurecontactform.com/donate) to help contribute to my time invested and to further development. Thanks for your kind support! - [__Mike Challis__](http://profiles.wordpress.org/users/MikeChallis/)

Features:
--------
 * Super easy customizable Options from Admin settings page.
 * Multi-Form feature that allows you to have as many different forms as you need.[See FAQ](http://www.fastsecurecontactform.com/multiple-e-mail-recipients)
 * Optional extra fields of any type: text, textarea, checkbox, checkbox-multiple, radio, select, select-multiple, attachment, date, time, hidden, password, fieldset(box). [See FAQ](http://www.fastsecurecontactform.com/how-to-add-extra-fields)
 * File attachments are supported, see here for details: http://wordpress.org/support/topic/416371
 * Backup/restore tool. You can backup/restore all your forms or single forms and settings.[See FAQ](http://www.fastsecurecontactform.com/backup-restore-forms)
 * Easy to hide subject and message fields for use as a newsletter signup.
 * Supports sending mail to multiple departments.[See FAQ](http://www.fastsecurecontactform.com/tip-form-preview)
 * Optional - redirect to any URL after message sent.
 * Optional - posted data can be sent as a query string on the redirect URL. [See faq](http://www.fastsecurecontactform.com/sending-data-by-query-string)
 * Optional - confirmation E-mail message.[See FAQ](http://www.fastsecurecontactform.com/tip-add-email-autoresponder)
 * Valid coding for HTML, XHTML, HTML STRICT, Section 508, and WAI Accessibility.
 * Uses simple inline error messages.
 * Reloads form data and warns user if user forgets to fill out a field.
 * Validates syntax of E-mail address.
 * CAPTCHA can be turned off or hidden from logged in users and or admins.
 * Multi "E-mail to" contact support.
 * Auto form fill for logged in users.
 * Customizable form field titles.
 * Customizable CSS style.
 * Sends E-mail with UTF-8 character encoding for US and International character support.
 * Pre-fill in form fields from a URL query string. [See FAQ](http://www.fastsecurecontactform.com/query-string-parameters)
 * Save emails to the WordPress database, or export to CSV or Excel. [See FAQ](http://www.fastsecurecontactform.com/save-to-database)
 * I18n language translation support. [See FAQ](http://www.fastsecurecontactform.com/how-to-translate)
 
Scheduling, Meeting and Payments via vCita:
-------------------------------------------
 * Offer visitors to Schedule Meetings as part of your Contact Form
 * Set your availability and synchronize your contact form with your Google calendar
 * Meet online with web-based video meeting room
 * Meet over phone conference
 * Record your meetings
 * Collect payments and bill for your time and services
 * Send a payment request once a meeting is completed or secure a fee in advance according to your hourly rate

Security:
---------
 * Akismet spam protection support.
 * Spam checks E-mail address input from common spammer tactics...
prevents spammer forcing to:, cc:, bcc:, newlines, and other E-mail injection attempts to spam the world.
 * Makes sure the contact form was posted from your blog domain name only.
 * Filters all form inputs from HTML and other nasties.
 * E-mail message footer shows blog username(if logged on), Date/Time timestamp, IP address, and user agent (browser version) of user who contacted you.

CAPTCHA Image Support:
---------------------
 * Uses Open-source free PHP CAPTCHA library by www.phpcaptcha.org (customized version included)
 * Abstract background with multi colored, angled, and transparent text
 * Arched lines through text
 * Refresh button to reload CAPTCHA
 * CAPTCHA can be disabled on form edit page

Requirements/Restrictions:
-------------------------
 * Works with Wordpress 3.4.2+ and WPMU (Wordpress 3.5+ is highly recommended)
 * PHP5 
 * PHP register_globals and safe_mode MUST be set to "Off".

== Installation ==

1. Install automatically through the `Plugins`, `Add New` menu in WordPress, or upload the `si-contact-form` folder to the `/wp-content/plugins/` directory. 

2. Activate the plugin through the `Plugins` menu in WordPress. Look for the Settings link to configure the Options. 

3. Add the shortcode `[si-contact-form form='1']` in a Page, Post, or Text Widget. Here is how: Log into your blog admin dashboard. Click `Pages`, click `Add New`, add a title to your page, enter the shortcode `[si-contact-form form='1']` in the page, uncheck `Allow Comments`, click `Publish`. 

4. Test an email from your form.

5. Updates are automatic. Click on "Upgrade Automatically" if prompted from the admin menu. If you ever have to manually upgrade, simply deactivate, uninstall, and repeat the installation steps with the new version.


= I just installed this and do not get any email from it, what could be wrong? =


[See FAQ page: How to troubleshoot mail delivery](http://www.fastsecurecontactform.com/email-does-not-send)


== Screenshots ==

1. screenshot-1.gif is the contact form.

2. screenshot-2.gif is the contact form showing the inline error messages.

3. screenshot-3.gif is the `Contact Form options` tab on the `Admin Plugins` page.

4. screenshot-4.gif adding the shortcode `[si-contact-form form='1']` in a Page.


== Frequently Asked Questions ==

[See the official FAQ at FastSecureContactForm.com](http://www.fastsecurecontactform.com/faq-wordpress-version)

= I just installed this and do not get any email from it, what could be wrong? =

[See FAQ page: How to troubleshoot email delivery](http://www.fastsecurecontactform.com/email-does-not-send)

= Is this plugin available in other languages? =

Yes. To use a translated version, you need to obtain or make the language file for it.
At this point it would be useful to read [Installing WordPress in Your Language](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") from the Codex. You will need an .mo file for this plugin that corresponds with the "WPLANG" setting in your wp-config.php file. Translations are listed below -- if a translation for your language is available, all you need to do is place it in the `/wp-content/plugins/si-contact-form/languages` directory of your WordPress installation. If one is not available, and you also speak good English, please consider doing a translation yourself (see the next question).

The following translations are included in the download zip file:

* Albanian (sq_AL) - Translated by [Romeo Shuka](http://www.romeolab.com)
* Arabic (ar) partial translation - Translated by Jasmine Hassan
* Bulgarian (bg_BG) - Translated by [Dimitar Atanasov](http://chereshka.net)
* Chinese (zh_CN) - Translated by [Awu](http://www.awuit.cn/) 
* Danish (da_DK) - Translated by [GeorgWP](http://wordpress.blogos.dk/wpdadkdownloads/)
* Farsi(Persian)(fa_IR) partial translation - Translated by Ramin Firooz
* Finnish (fi) - Translated by [Mikko Vahatalo](http://www.guimikko.com/) 
* French (fr_FR) - Translated by [Pierre Sudarovich](http://pierre.sudarovich.free.fr/)
* German (de_DE) - Translated by [Sebastian Kreideweiss](http://sebastian.kreideweiss.info/)
* Greek (el) - Translated by [Ioannis](http://www.jbaron.gr/)
* Hebrew, Israel (he_IL) - Translated by Asaf Chertkoff FreeAllWeb GUILD
* Hungarian (hu_HU) - Translated by [Jozsef Burgyan](http://dmgmedia.hu)
* Italian (it_IT) - Translated by [Gianni Diurno](http://gidibao.net/ "Gianni Diurno")
* Japanese (ja) - Translated by [Ichiro Kozuka]
* Norwegian Bokmal (nb_NO) - Translated by [Tore Johnny Bratveit](http://punktlig-ikt.no)
* Polish (pl_PL) - Translated by [Pawel Mezyk]
* Portuguese (pt_PT) - Translated by [AJBFerreira Blog](http://pws.op351.net/)
* Portuguese Brazil (pt_BR) - Translated by [Rui Alao]
* Romanian (ro_RO) - Translated by [Anunturi Jibo](http://www.jibo.ro)
* Russian (ru_RU) - Translated by [Iflexion](http://www.iflexion.com/)
* Spanish (es_ES) - Translated by [Valentin Yonte Rodriguez](http://www.activosenred.com/)
* Swedish (sv_SE) - Translated by [Daniel Persson](http://walktheline.boplatsen.se/)
* Traditional Chinese, Taiwan (zh_TW) - Translated by [Cjh]
* Turkish (tr_TR) - Translated by [Tolga](http://www.tapcalap.com/)
* Ukrainian (uk_UA) - Translated by [Wordpress.Ua](http://wordpress.ua/)
* More are needed... Please help translate.

= Can I provide a translation? =

Yes! 
How to translate Fast Secure Contact Form for WordPress
http://www.fastsecurecontactform.com/how-to-translate

= Is it possible to update the translation files for newest version? =

How to update a translation of Fast Secure Contact Form for WordPress
http://www.fastsecurecontactform.com/how-to-update-translation


= This contact form sends E-mail with UTF-8 character encoding for US and International character support. =

English-language users will experience little to no impact. Any non-English questions or messages submitted will have unicode character encoding so that when you receive the e-mail, the language will still be viewable.

If you receive an email with international characters and the characters look garbled with symbols and strange characters, your e-mail program may need to be set as follows: 

How to set incoming messages character encoding to Unicode(UTF-8) in various mail clients:

Evolution:
View > Character Encoding > Unicode

Outlook Express 6, Windows Mail:
Please check "Tools->Options->Read->International Settings". Un-check "Use default encoding format for all incoming messages" 
Now select "View->Encoding", select "Unicode(UTF-8)"

Mozilla Thunderbird:
Click on Inbox.
Select "View->Character Encoding", select "Unicode(UTF-8)"

Gmail:
No setting necessary, it just works.

For more help... [See the official FAQ at FastSecureContactForm.com](http://www.fastsecurecontactform.com/faq-wordpress-version)

= What is the "Set a meeting" option I have in my form? = 

You can extend your contact form to let your users to Schedule Meetings based on your availability, meet online with web-based video, talk over phone conference, and collect payments for your time and services.

You can enable this option in "Accept Meeting Requests" section at contact form settings page.
You can then configure your meeting preferences, set your availability and more by activating your free vCita account (again at the contact form settings).

You can learn more about vCita at [www.vcita.com](http://www.vcita.com?invite=FSContact)
If you have any question about the Schedule Meetings feature please contact support@vcita.com

== Changelog ==


= 3.1.9.2 =
- (24 Sep 2013) 
- Added announcement of Fast Secure Contact Form Version 4.0 Beta 4 was released September, 24 2013. This could be the last beta, so please help test it!
- [Download and test the 4.0 Beta](http://www.fastsecurecontactform.com/beta)

= 3.1.9.1 =
- (31 Aug 2013) - Fix bug: Custom Label CSS was ignored for checkbox, checkbox-multiple, and radio fields.
- Fix bug: CSS setting 'labels on left' messed up checkbox, checkbox-multiple, and radio fields.
- Fix bug: CSS setting 'labels on left' messed up HTML before/after form field position.
- Added announcement of Fast Secure Contact Form Version 4.0 Beta 2 was released August, 31 2013. Please help test it!
- [Download and test the 4.0 Beta](http://www.fastsecurecontactform.com/beta)

= 3.1.9 =
- (15 Aug 2013) - Added announcement of Fast Secure Contact Form Version 4.0 Beta 1 was released August, 15 2013. Please help test it!
- [Download and test the 4.0 Beta](http://www.fastsecurecontactform.com/beta)

= 3.1.8.6 =
- (13 Aug 2013) - fixed label style for checkbox, checkbox-multiple, and radio field types.
- removed divs for HTML before/after field settings.
- minor bug fixes.

= 3.1.8.5 =
- (18 Jul 2013) - added new settings: "Submit button input attributes" and "Form action attributes". These can be used for Google Analytics tracking code.
- added captcha font randomization.
- fixed date does not have to be required.
- fixed date error message translation.

= 3.1.8.4 =
- (07 Jul 2013) - Fixed CAPTCHA PHP warning on some servers.
- Added better date input validation.


[Fast Secure Contact Form – WordPress changelog archive](http://www.fastsecurecontactform.com/changelog-archive)  