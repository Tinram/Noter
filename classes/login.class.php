<?php

require('config/config.php');


final class LoginGateway {

	/**
		* Simple log-in gateway.
		* 
		* Circumvents the need on simple systems for a MySQL DB.
		* Key hash obfuscation: no encryption needed, but lacks key-strengthening.
		*
		* @author            Martin Latter <copysense.co.uk>
		* @copyright         Martin Latter 11/07/2012
		* @version           1.07
		* @license           GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
		* @link              https://github.com/Tinram/noter.git
*/


	const

		USER1 = 'martin',
		USER2 = 'alison',

		PAGE_REDIRECT = 'menu.php',
		FILENAME = 'log/badlog.txt',

		HACK_DELAY = CONFIG_HACK_DELAY,
		CACHE_EXPIRY = CONFIG_CACHE_EXPIRY,
		SESSION_TIMEOUT = CONFIG_SESSION_TIMEOUT,
		ENCODING = CONFIG_ENCODING,
		HASH = CONFIG_HASH;


	private 

		$USER1_PASS = 'fd74bdd901857b89f5737e5352a2a8a2d1f000aa4bed4aee47c95afaa37d0f99', # SHA-256
		$USER2_PASS = 'fd74bdd901857b89f5737e5352a2a8a2d1f000aa4bed4aee47c95afaa37d0f99',
		$bSubmitted = FALSE;


	public function __construct() {

		$this->init();
	}


	private function init() {

		ini_set('date.timezone', 'Europe/London');
		//ini_set('session.use_strict_mode', TRUE); # PHP v.5.52+
		ini_set('session.cookie_httponly', TRUE);
		ini_set('session.use_only_cookies', TRUE);
		ini_set('session.use_trans_sid', FALSE);
		ini_set('session.gc_divisor', 5);
		ini_set('session.gc_maxlifetime', self::SESSION_TIMEOUT);
		ini_set('session.cookie_lifetime', self::SESSION_TIMEOUT);
		ini_set('session.cache_limiter', 'nocache');
		ini_set('session.cache_expire', self::CACHE_EXPIRY);

		session_start();

		$this->bSubmitted = (isset($_POST['submit_check'])) ? TRUE : FALSE;

		if ( ! isset($_SESSION['sKey'])) {

			$sBytes = mcrypt_create_iv(128, MCRYPT_DEV_URANDOM);

			if ($sBytes) {
				$_SESSION['sKey'] = hash(self::HASH, $sBytes);
			}
			else {
				$_SESSION['sKey'] = hash(self::HASH, uniqid(mt_rand(), TRUE));
				# uniqid() is not crypto-secure, mcrypt_create_iv() above is for concise Windows support
			}
		}

		if (isset($_SESSION['iBadLog'])) {

			if (isset($_SESSION['iHackerTimeout'])) {

				if (time() > ($_SESSION['iHackerTimeout'] + self::HACK_DELAY)) {
					$_SESSION['iHackerTimeout'] = time();
					$_SESSION['iBadLog'] = 0;
				}
			}

			if ($_SESSION['iBadLog'] > 2) {
				die('<p style="color:#c00;font-weight:bold;">Please stop attacking!<br>Your IP address has been logged.</p>');
			}
		}

		if ($this->bSubmitted) {

			$aFormErrors = $this->validateForm();

			if ( ! empty($aFormErrors)) {

				$iTimeStamp = date('j F Y, H:m:i');
				$sMessage = 'unsuccessful: ' . $this->webSafe($_POST['un']) . ' | ' . $_SERVER['REMOTE_ADDR'] . ' | ' . $iTimeStamp . "\n";
				$sFileContents = file_get_contents(self::FILENAME);
				$sFileContents .= $sMessage;

				file_put_contents(self::FILENAME, $sFileContents);

				if ( ! isset($_SESSION['iBadLog'])) {
					$_SESSION['iBadLog'] = 1;
				}
				else {

					$_SESSION['iBadLog']++;

					if ($_SESSION['iBadLog'] > 2) {
						$_SESSION['iHackerTimeout'] = time();
					}
				}

				$this->showPage($aFormErrors);
			}
			else {
				header('location:' . self::PAGE_REDIRECT);
			}
		}
		else {
			$this->showPage();
		}

	} # end init()


	private function validateForm() {

		$sUserName = $this->webSafe($_POST['un']);
		$sPassword = $this->webSafe($_POST['pw']);
		$aErrors = [];

		$aCredentials = [
			self::USER1 => $this->USER1_PASS,
			self::USER2 => $this->USER2_PASS
			//, self::USER3 => $this->USER3_PASS
		];

		if ( ! array_key_exists($sUserName, $aCredentials)) {
			$aErrors[] = 'Please enter a valid username and password.'; 
		}
		else {

			if ( ! isset($_SESSION['sKey'])) {
				$_SESSION['sKey'] = '';
			}

			if (hash(self::HASH, ($aCredentials[$sUserName] . $_SESSION['sKey'])) !== $sPassword) {
				$aErrors[] = 'Please enter a valid username and password.';
			}
			else {
				$_SESSION['sVerifiedName'] = $sUserName;
				$_SESSION['iToken'] = time();
			}
		}

		return $aErrors;

	} # end validateForm()


	private function webSafe($sTainted) {
		return htmlentities(strip_tags(trim($sTainted)), ENT_QUOTES, self::ENCODING);
	}


	private function selfSafe() {
		return htmlentities(strip_tags($_SERVER['PHP_SELF']), ENT_QUOTES, self::ENCODING);
	}


	private function showPage(array $aErrors = NULL) {

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
		if ($this->bSubmitted) {
			if (isset($_POST['un'])) {
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
			if ( ! empty($aErrors)) {
				echo '<p class="error">' . join('\n', $aErrors) . '</p>';
			}
		?>

	</body>

</html>

<?php

	} # end showPage()

} # end {}

?>