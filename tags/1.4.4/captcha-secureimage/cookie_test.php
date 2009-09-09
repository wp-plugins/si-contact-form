<?php
/*
Cookie Test Script
Version 1.0 Mike Challis 08/30/2009
http://www.642weather.com/weather/scripts.php

Upload this PHP script to your web server and call it from the browser.
The script will tell you if your browser meets the cookie requirements for running Securimage.

cookie test code from:
http://www.coderemix.com/tutorials/php-cookies-enabled-check
*/

$disabled_help = '
This is the cause. The Captcha will not be able work. The contact form will display an error:
"ERROR: Could not read CAPTCHA cookie. Make sure you have cookies enabled."
<br /><br />
Solution: Please configure your browser to allow cookies.
';

$enabled_help = '
This is rules out your web browser as the cause of the contact form
displaying the "ERROR: Could not read CAPTCHA cookie. Make sure you have cookies enabled."<br />
Solution: The problem could be another WordPress plugin is conflicting with the PHP session.
What other plugins do you have installed?
Can you temporarily deactivate them one at a time until the conflicting plugin is pinpointed?
If a conflicting plugin is found I might be able to fix it, then we can notify the plugin author.
Contact me: <a href="http://www.642weather.com/weather/contact_us.php">(Mike Challis)</a><br />
I will need to know this information: (fill in this information on my contact form)<br />
Plugin: Fast and Secure Contact Form<br />
Plugin Version:<br />
Your web site URL:<br />

';

// Define a cookie and reload the page
if(!isset($_GET['redirected']))
{
    setcookie ('mycookie', 'test', time() + 300);
    header('location:'.$_SERVER['PHP_SELF'].'?redirected=1');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Cookies Enabled Check</title>
<style>
body
{
	background-color:#E6E6E6;
	font-family:"Courier New", Arial, sans-serif, monospace;
	font-size:1em;
	color:#333333;
}
.group
{
	background-color:#FFFFFF;
	border:1px #CCCCCC solid;
	margin-top:25px;
	margin-bottom:50px;
	text-align:left;
}
</style>
</head>

<body>

<div class="group" style="margin-left:20%; margin-right:20%; padding:20px;">
<h2>Cookies Enabled Check</h2>

<p>
You should see a message below letting you know if cookies are enabled in your browser.
</p>

<p>
<strong>Web browsers have a setting to enable/disable cookies.
They also have a setting to block/unblock cookies per each web site.

For instructions on how to enable cookies or unblock cookies in your browser, use a search engine</strong>.
Different internet browsers have different sets of instructions on how to change this setting.
</p>

<?php
// Check if the cookie just defined is there
$cookie_message = '';
if(isset($_GET['redirected']) and $_GET['redirected']==1) {
    if(!isset($_COOKIE['mycookie'])) {
        $cookie_message = '<p style="background-color:#CC6666; color:white; padding:10px;">
        Problem found: Cookies are NOT enabled on your browser.<br />
        '.$disabled_help.'
        </p>';
    }
    else{
        $cookie_message = '<p style="background-color:#99CC66; padding:10px;">
        Cookies are enabled on your browser.
        <br /><br />
        '.$enabled_help.'
        </p>';
    }
}
echo $cookie_message;
?>

<p>
<a href="cookie_test.php">Try this test again</a>
</p>


</div>

</body>
</html>