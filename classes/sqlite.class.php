<?php

require_once('config/config.php');


abstract class SQLA extends SQLite3 {

	/**
		* SQLite database access class.
		*
		* @author         Martin Latter
		* @copyright      Martin Latter 31/03/2015
		* @version        0.21
		* @license        GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
		* @link           https://github.com/Tinram/noter.git
	*/


	###############################################################
	# SQLite database configuration from config.php constants
	###############################################################
	private $sDB = CONFIG_DATABASE;
	protected $sTableName = CONFIG_TABLE;
	###############################################################


	###############################################################
	# App and encoding configuration
	###############################################################
	protected $iMaxTitleLen = CONFIG_MAX_TITLE_LEN;
	protected $iMaxBodyLen = CONFIG_MAX_BODY_LEN;
	protected $iNumNotesDisplayed = CONFIG_NUM_NOTES_DISPLAYED;
	private $bUnicode = CONFIG_UNICODE;
	private $sEncoding = CONFIG_ENCODING;
	###############################################################


	public function __construct() {

		if ($this->bUnicode) {
			mb_internal_encoding($this->sEncoding);
		}

		if (file_exists($this->sDB)) {
			$this->open($this->sDB);
		}
		else {
			die('<p class="error">' . __METHOD__ . '() &ndash; <em>' . CONFIG_DATABASE . '</em> file not found!</p>');
		}
	}


	public function __destruct() {

		$this->close();
	}

}

?>