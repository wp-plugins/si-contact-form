  Mike Challis fix ChangeLog

  1.0.3.1m.2 - (Aug 28, 2009) Mike Challis fix http://www.642weather.com/weather/scripts.php
  - fixed securimage_play.php Added Auto fail over to $use_gd_font if the PHP installation is configured without "--with-ttf".
  Some users were reporting there was no error indicating this condition and the capcha was not working.

  1.0.3.1m.1 - (Apr 06, 2009) Mike Challis fix http://www.642weather.com/weather/scripts.php
  - Fixed audio CAPTCHA link URL in securimage_play.php, it did not work properly on Safari 3.2.1 (Mac OS X 10.5.6).
  safari was trying to download securimage.wav.php instead of securimage.wav
  - Note: the proper way the audio CAPTCHA is supposed to work is like this: a dialog pops up, You have chosen to open:
  secureimage.wav What should (Firefox, Safari, IE, etc.) do with this file? Open with: (Choose) OR Save File.
  Be sure to select open, then it will play in WMP, Quicktime, Itunes, etc.
