<?php

require_once('config/config.php');


abstract class SQLA extends SQLite3 {

	/**
		* SQLite database access class.
		* 
		* @author         Martin Latter <copysense.co.uk>
		* @copyright      Martin Latter 31/03/15
		* @version        0.2
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
			die(__METHOD__ . ': .sqlite file not found');
		}
	}


	public function __destruct() {

		$this->close();
	}

}

?>