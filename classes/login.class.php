<?php

declare(strict_types=1);

require('config/config.php');


final class LoginGateway
{
    /**
        * Simple log-in gateway.
        *
        * Circumvents the need on simple systems for a MySQL DB.
        * Key hash obfuscation: no encryption needed, but lacks key-strengthening.
        *
        * @author       Martin Latter
        * @copyright    Martin Latter 11/07/2012
        * @version      1.14
        * @license      GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
        * @link         https://github.com/Tinram/noter.git
    */


    /** @const string PAGE_REDIRECT */
    const PAGE_REDIRECT = 'menu.php';
    /** @const string FILENAME, log file */
    const FILENAME = 'log/badlog.txt';

    /* from config/config.php */
    /** @const string USER1, first username */
    const USER1 = CONFIG_USER1;
    /** @const string USER2, second username */
    const USER2 = CONFIG_USER2;
    /** @const string USER1_PASS, first user password */
    const USER1_PASS = CONFIG_USER1_PASS;
    /** @const string USER2_PASS, second user password */
    const USER2_PASS = CONFIG_USER2_PASS;

    /** @const integer HACK_DELAY */
    const HACK_DELAY = CONFIG_HACK_DELAY;
    /** @const integer CACHE_EXPIRY */
    const CACHE_EXPIRY = CONFIG_CACHE_EXPIRY;
    /** @const integer SESSION_TIMEOUT */
    const SESSION_TIMEOUT = CONFIG_SESSION_TIMEOUT;
    /** @const string ENCODING */
    const ENCODING = CONFIG_ENCODING;
    /** @const string HASH */
    const HASH = CONFIG_HASH;

    /** @var boolean $bSubmitted */
    private $bSubmitted = false;


    public function __construct()
    {
        $this->init();
    }


    /**
        * Initialise set-up.
        *
        * @return  void
    */

    private function init(): void
    {
        ini_set('date.timezone', 'Europe/London');

        ini_set('session.use_only_cookies', 'On');
        ini_set('session.use_strict_mode', 'On'); /* PHP v.5.52+ */
        ini_set('session.use_trans_sid', 'Off');
        ini_set('session.cookie_httponly', 'On');
        ini_set('session.cookie_lifetime', (string) self::SESSION_TIMEOUT);
        //ini_set('session.cookie_secure', 'On'); /* enable if on HTTPS */
        //ini_set('session.cookie_samesite', 'Strict'); /* PHP v.7.3+ */
        ini_set('session.gc_divisor', '5');
        ini_set('session.gc_maxlifetime', (string) self::SESSION_TIMEOUT);
        ini_set('session.cache_limiter', 'nocache');
        ini_set('session.cache_expire', (string) self::CACHE_EXPIRY);
        ini_set('session.sid_length', '48');
        ini_set('session.sid_bits_per_character', '6');
        ini_set('session.hash_function', 'sha256');

        session_start();

        $this->bSubmitted = (isset($_POST['submit_check'])) ? true : false;
        $sBytes = '';
        $sBytesLen = 128;

        if ( ! isset($_SESSION['sKey']))
        {
            /* seek crypto-secure random bytes for session key */
            if (function_exists('random_bytes'))
            {
                $sBytes = random_bytes($sBytesLen);
            }
            else if (function_exists('openssl_random_pseudo_bytes'))
            {
                $sBytes = openssl_random_pseudo_bytes($sBytesLen);
            }
            else if (function_exists('mcrypt_create_iv'))
            {
                $sBytes = mcrypt_create_iv($sBytesLen, MCRYPT_DEV_URANDOM);
            }

            if ($sBytes !== '')
            {
                $_SESSION['sKey'] = hash(self::HASH, $sBytes);
            }
            else /* worst fallback case: uniqid() is not crypto-secure */
            {
                $_SESSION['sKey'] = hash(self::HASH, uniqid('', true));
            }
        }

        if (isset($_SESSION['iBadLog']))
        {
            if (isset($_SESSION['iHackerTimeout']))
            {
                if (time() > ($_SESSION['iHackerTimeout'] + self::HACK_DELAY))
                {
                    $_SESSION['iHackerTimeout'] = time();
                    $_SESSION['iBadLog'] = 0;
                }
            }

            if ($_SESSION['iBadLog'] > 2)
            {
                die('<p style="color:#c00;font-weight:bold;">Please stop attacking!<br>Your IP address has been logged.</p>');
            }
        }

        if ($this->bSubmitted)
        {
            $aFormErrors = $this->validateForm();

            if (count($aFormErrors) !== 0)
            {
                $iTimeStamp = date('j F Y, H:m:i');
                $sMessage = 'unsuccessful: ' . $this->webSafe($_POST['un']) . ' | ' . $_SERVER['REMOTE_ADDR'] . ' | ' . $iTimeStamp . "\n";
                $sFileContents = file_get_contents(self::FILENAME);
                $sFileContents .= $sMessage;

                file_put_contents(self::FILENAME, $sFileContents);

                if ( ! isset($_SESSION['iBadLog']))
                {
                    $_SESSION['iBadLog'] = 1;
                }
                else
                {
                    $_SESSION['iBadLog']++;

                    if ($_SESSION['iBadLog'] > 2)
                    {
                        $_SESSION['iHackerTimeout'] = time();
                    }
                }

                $this->showPage($aFormErrors);
            }
            else
            {
                header('location:' . self::PAGE_REDIRECT);
            }
        }
        else
        {
            $this->showPage([]);
        }
    }


    /**
        * Validate the log-in form.
        *
        * @return  array<string>
    */

    private function validateForm(): array
    {
        $sUserName = $this->webSafe($_POST['un']);
        $sPassword = $this->webSafe($_POST['pw']);
        $aErrors = [];

        $aCredentials =
        [
            self::USER1 => self::USER1_PASS,
            self::USER2 => self::USER2_PASS
            //, self::USER3 => self::USER3_PASS
        ];

        if ( ! array_key_exists($sUserName, $aCredentials))
        {
            $aErrors[] = 'Please enter a valid username and password.';
        }
        else
        {
            if ( ! isset($_SESSION['sKey']))
            {
                $_SESSION['sKey'] = '';
            }

            if (hash(self::HASH, ($aCredentials[$sUserName] . $_SESSION['sKey'])) !== $sPassword)
            {
                $aErrors[] = 'Please enter a valid username and password.';
            }
            else
            {
                $_SESSION['sVerifiedName'] = $sUserName;
                $_SESSION['iToken'] = time();
            }
        }

        return $aErrors;
    }


    /**
        * Sanitise a string for HTML output.
        *
        * @param   string $sTainted, input string
        *
        * @return  string
    */

    private function webSafe(string $sTainted): string
    {
        return htmlentities(strip_tags(trim($sTainted)), ENT_QUOTES, self::ENCODING);
    }


    /**
        * Sanitise PHP_SELF.
        *
        * @return  string
    */

    private function selfSafe(): string
    {
        return htmlentities(strip_tags($_SERVER['PHP_SELF']), ENT_QUOTES, self::ENCODING);
    }


    /**
        * Output log-in page.
        *
        * @param   array<string> $aErrors
        *
        * @return  void
    */

    private function showPage(array $aErrors): void
    {

?><!DOCTYPE html>

<html lang="en">

    <head>
        <title><?php echo CONFIG_APP_NAME; ?> Manager</title>
        <meta charset="utf-8">
        <meta name="copyright" content="&copy; <?php echo date('Y'); ?> CopySense">
        <link rel="stylesheet" type="text/css" href="css/noter.css">
        <style type="text/css">
            form#nm {display:none;}
            form#nm div {width:125px; height:22px;}
            form#nm input {height:19px; width:120px; float:right;}
            form#nm input#login {width:55px; height:25px; margin-top:8px;}
            .error {clear:both; margin-top:40px; color:#c00;}
            p#nojs {color:#c00;}
        </style>
        <script type="text/javascript" src="js/edit.js"></script>
    </head>

    <body>

        <p id="nojs">Please enable your browser's JavaScript.</p>

        <form id="nm" method="post" action="<?php echo $this->selfSafe(); ?>">
            <div><input type="text" id="un" name="un" class="curved" placeholder="username" value="<?php
        if ($this->bSubmitted)
        {
            if (isset($_POST['un']))
            {
                echo $this->webSafe($_POST['un']);
            }
        }
        ?>"></div>
            <div><input type="password" id="pw" name="pw" placeholder="password" autocomplete="off"></div>
            <input type="hidden" id="pk" value="<?php echo $_SESSION['sKey']; ?>">
            <input type="hidden" name="submit_check">
            <div><input type="submit" id="login" name="login" value="login"></div>
        </form>

        <p class="error" id="jserrors"></p>

        <?php
            if (count($aErrors) !== 0)
            {
                echo '<p class="error">' . join("\n", $aErrors) . '</p>';
            }
        ?>

    </body>

</html>

<?php

    }
}
