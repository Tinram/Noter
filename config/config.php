<?php

/**
    * Configuration file for Noter.
    * @author  Martin Latter
    * @link    https://github.com/Tinram/noter.git
*/

declare(strict_types=1);

##################################################
## SQLITE DB
##################################################
define('CONFIG_DATABASE', 'db/noter.sqlite3');
define('CONFIG_TABLE', 'notes');
##################################################


##################################################
## APP
##################################################
define('CONFIG_NUM_NOTES_DISPLAYED', 10);
define('CONFIG_MAX_TITLE_LEN', 80);
define('CONFIG_MAX_BODY_LEN', 8192);
define('TIMEZONE', 'Europe/London');
define('CONFIG_APP_NAME', 'Noter');
##################################################


##################################################
## ENCODING
##################################################
define('CONFIG_UNICODE', true);
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
