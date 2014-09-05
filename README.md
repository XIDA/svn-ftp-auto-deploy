svn-ftp-auto-deploy
===================

php script to facilitate automatic deploy from SVN to FTP.

Synopsis
========

The purpose of this project is to make a one-click deploy after making an SVN commit. This saves a lot of time which would otherwise be spent opening an FTP client and manually uploading each file that was changed with the previous commit(s).


###Install###
1. You need php to use this.
You can download it for windows here: [http://windows.php.net/download/#php-5.5](http://windows.php.net/download/#php-5.5 "PHP CLI")
2. copy config/config.example.php and name it like this config/config_test.php
3. edit your new config file and fill out all values
4. then run php.exe -f deploy.php [CONFIG NAME] [TARGET REVISION]
where [CONFIG NAME] is the name you gave your config (if the config name is config_test.php then just use "test") and [TARGET REVISION] is the revision you would like to update the ftp to. If you do not set a target revision it will be updated to the latest version.

so the command looks like this php.exe -f deploy.php test 22
to use config_test.php and update to revision 22


###Demovideo###
[https://www.youtube.com/watch?v=ZbSpEUpUnyQ&feature=youtu.be](https://www.youtube.com/watch?v=ZbSpEUpUnyQ&feature=youtu.be "Demo Video")






