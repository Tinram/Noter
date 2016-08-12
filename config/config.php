<?php

/**
	* Configuration file of constants for noter.
	* @author   Martin Latter <copysense.co.uk>
	* @link     https://github.com/Tinram/noter.git
*/


##################################################
## SQLITE DB
##################################################
define('CONFIG_DATABASE', 'db/noter.sqlite3');
define('CONFIG_TABLE', 'notes');
##################################################


##################################################
## APP
##################################################
define('CONFIG_NUM_NOTES_DISPLAYED', 7);
define('CONFIG_MAX_TITLE_LEN', 20);
define('CONFIG_MAX_BODY_LEN', 512);
define('TIMEZONE', 'Europe/London');
define('CONFIG_APP_NAME', 'Noter');
##################################################


##################################################
## ENCODING
##################################################
define('CONFIG_UNICODE', TRUE);
define('CONFIG_ENCODING', 'UTF-8');
##################################################


##################################################
## SESSION
##################################################
define('CONFIG_CACHE_EXPIRY', 30); # 30 mins
define('CONFIG_SESSION_TIMEOUT', 1800); # 30 mins
define('CONFIG_HACK_DELAY', 3600);
define('CONFIG_HASH', 'sha256');
##################################################

?>