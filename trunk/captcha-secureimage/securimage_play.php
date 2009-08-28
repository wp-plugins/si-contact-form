<?php

/*
Mike Challis 04/06/2009
http://www.642weather.com/weather/scripts.php
- Fixed audio CAPTCHA link URL, it did not work properly on Safari 3.2.1 (Mac OS X 10.5.6).
safari was trying to download securimage.wav.php instead of securimage.wav
- Note: the proper way the audio CAPTCHA is supposed to work is like this: a dialog pops up, You have chosen to open:
secureimage.wav What should (Firefox, Safari, IE, etc.) do with this file? Open with: (Choose) OR Save File. Be sure to select open, then it will play in WMP, Quicktime, Itunes, etc.

Here is the change I made.

Changed:
header('Content-Disposition: attachment; name="securimage.wav"');

To:
header('Content-Disposition: attachment; filename="securimage.wav"');*/

include 'securimage.php';

$img = new Securimage();

header('Content-type: audio/x-wav');
header('Content-Disposition: attachment; filename="securimage.wav"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Expires: Sun, 1 Jan 2000 12:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');

echo $img->getAudibleCode();
exit;

?>