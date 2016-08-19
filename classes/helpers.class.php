<?php

require_once('config/config.php');


class Helpers {

	/**
		* Class wrapper for static helper methods.
		* 
		* @author         Martin Latter <copysense.co.uk>
		* @copyright      Martin Latter 03/04/15
		* @version        0.3
		* @license        GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
		* @link           https://github.com/Tinram/noter.git
*/


	const

		CACHE_EXPIRY = CONFIG_CACHE_EXPIRY,
		SESSION_TIMEOUT = CONFIG_SESSION_TIMEOUT,
		ENCODING = CONFIG_ENCODING;


	/**
		* Validate user access for editing pages.
	*/

	public static function validateUser() {

		//ini_set('session.use_strict_mode', TRUE); # PHP v.5.52+
		ini_set('session.cookie_httponly', TRUE);
		ini_set('session.use_only_cookies', TRUE);
		ini_set('session.use_trans_sid', FALSE);
		ini_set('session.cookie_lifetime', self::SESSION_TIMEOUT);
		ini_set('session.gc_maxlifetime', self::SESSION_TIMEOUT);
		ini_set('session.gc_divisor', 5);
		ini_set('session.cache_expire', self::CACHE_EXPIRY);

		session_start();

		if ( ! isset($_SESSION['iToken']) || ! isset($_SESSION['sVerifiedName'])) {
			self::sessionDeath();
			die('<p style="color:#c00;font-weight:bold;"><a href="index.php">' . CONFIG_APP_NAME . ':</a> no access authorisation!</p>');
		}

		if (time() > ($_SESSION['iToken'] + (self::CACHE_EXPIRY * 60))) {
			self::sessionDeath();
			die('<a href="index.php">' . CONFIG_APP_NAME . ':</a> session expired.');
		}

	} # end validateUser()


	/**
		* Used in self::validateUser()
	*/

	private static function sessionDeath() {

		$_SESSION = [];

		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time() - 42000, '/');
		}

		session_destroy();

	} # end sessionDeath()


	/**
		* Output search form HTML.
	*/

	public static function outputSearchForm() {

	?>

		<div id="fsearchcont">

			<form id="fs" method="post" action="<?php echo self::selfSafe(); ?>">

				<div>
					<input type="text" name="term" id="term" maxlength="<?php echo CONFIG_MAX_TITLE_LEN; ?>" placeholder="search">
					<input type="submit" value="search" id="searchbut">
				</div>

				<div id="radiocont">
				<?php
					echo self::generateRadioButtons();
				?>

				</div>

				<input type="hidden" name="search_flag">

			</form>

			<p id="fserror" class="error"></p>

		</div>

	<?php

	} # end outputSearchForm()


	/**
		* Create radio buttons with conditional checked.
		*
		* @return   string
	*/

	public static function generateRadioButtons() {

		$sOut = '';
		$bDefault = TRUE;
		$bBody = FALSE;

		if (isset($_POST['choice']) ) {

			if ($_POST['choice'] === 'body') {
				$bBody = TRUE;
				$bDefault = FALSE;
			}
		}

		$sRadio = '	<label>{CHOICE}</label><input type="radio" name="choice" value="{CHOICE}" checked>';

		if ($bDefault) {
			$sOut .= str_ireplace('{CHOICE}', 'title', $sRadio);
		}
		else {
			$sTemp = str_ireplace('{CHOICE}', 'title', $sRadio);
			$sTemp = str_ireplace(' checked', '', $sTemp);
			$sOut .= $sTemp;
		}

		if ( ! $bBody) {
			$sTemp = str_ireplace('{CHOICE}', 'body', $sRadio);
			$sTemp = str_ireplace(' checked', '', $sTemp);
			$sOut .= $sTemp;
		}
		else {
			$sOut .= str_ireplace('{CHOICE}', 'body', $sRadio);
		}

		return $sOut;

	} # end generateRadioButtons()


	/**
		* Sanitize strings from most (not all) XSS.
		* Preserve <pre> tags for code whitespace.
		*
		* @param   string $sTainted
		* 
		* @return  string
	*/

	public static function webSafe($sTainted) {

		$sClean = htmlentities(strip_tags(trim($sTainted), '<pre>'), ENT_QUOTES, self::ENCODING);

		return html_entity_decode($sClean);
}


	/**
		* Sanitize PHP_SELF.
		*
		* @return   string
	*/

	public static function selfSafe() {

		return htmlentities(strip_tags($_SERVER['PHP_SELF']), ENT_QUOTES, self::ENCODING);
	}

} # end {}

?>