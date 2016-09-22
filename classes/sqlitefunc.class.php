<?php

final class SQLiteFunc extends SQLA {

	/**
		* SQLite application functionality.
		*
		* @author         Martin Latter <copysense.co.uk>
		* @copyright      Martin Latter 03/04/2015
		* @version        0.33
		* @license        GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
		* @link           https://github.com/Tinram/noter.git
	*/


	/**
		* Search delegator - delegates search and HTML generation.
		* Keeps logic simple and delegates to other methods.
		*
		* @param  string $sKeywords, search terms
		* @param  string $sChoice, title or body search
		* @param  string $sAction, page action
		*
		* @return  array [ boolean, string message/HTML ]
	*/

	public function search($sKeywords, $sChoice, $sAction = '') {

		if (empty($sKeywords)) {
			return;
		}

		$sMode = ($sChoice === 'title') ? 'title' : 'body';

		$aNotes = $this->searchProcessor($sKeywords, $sMode);

		if ( ! $aNotes) {
			return [ FALSE, 'No results found.' ];
		}
		else {
			return [ TRUE, $this->generateHTML($aNotes, $sAction) ];
		}

	} # end search()


	/**
		* Query database.
		*
		* @param   string $sKeywords, search terms
		* @param   string $sMode, title or body column search
		*
		* @return  boolean false (no results) or array of note row data
	*/

	private function searchProcessor($sKeywords, $sMode) {

		$aResults = [];

		$sQuery = '
			SELECT id, title, body, creator, create_ts, updater, update_ts
			FROM ' . $this->sTableName . '
			WHERE ' . $sMode . ' LIKE :term';

		$oStmt = $this->prepare($sQuery);
		$sTerm = '%' . trim($sKeywords) . '%';
		$oStmt->bindValue(':term', $sTerm, SQLITE3_TEXT);
		$rResult = $oStmt->execute();

		while ($aRow = $rResult->fetchArray(SQLITE3_ASSOC)) {
			$aResults[] = [ 'id' => $aRow['id'], 'title' => $aRow['title'], 'body' => $aRow['body'], 'creator' => $aRow['creator'], 'create_ts' => $aRow['create_ts'], 'updater' => $aRow['updater'], 'update_ts' => $aRow['update_ts'] ];
		}

		$rResult->finalize();

		# use the initial empty array as an empty results test
		# (SQLite prepared stmt has no num_rows, and a preliminary fetchArray() alters the result set)
		if (empty($aResults)) {
			return FALSE;
		}
		else {
			return $aResults;
		}

	} # end searchProcessor()


	/**
		* Generate results HTML.
		*
		* @param   array $aNotes, note row data
		* @param   boolean $sAction, toggle for update.php page functionality
		*
		* @return  string, HTML
	*/

	private function generateHTML(array $aNotes, $sAction = '') {

		$sOut = '';

		if (empty($sAction)) {

			foreach ($aNotes as $aNote) {

				$sOut .= '
				<div class="ntitle">' . Helpers::webSafe($aNote['title']) . '</div>
				<div class="nbody">' . Helpers::webSafe($aNote['body']) . '</div>
				<div class="nts">' . $aNote['create_ts'] . ' by ' . $aNote['creator'] . '</div>';

				if ( ! is_null($aNote['update_ts'])) {
					$sOut .= '
				<div class="nts">last update: ' . $aNote['update_ts'] . ' by ' . $aNote['updater'] . '</div>';
				}
			}
		}
		else {

			echo '
			<div>
				<span>title</span>
				<span id="update_note_heading">note</span>
			</div>';

			foreach ($aNotes as $aNote) {

				$sOut .= '
			<form class="fupdate" action="' . Helpers::selfSafe() . '" method="post">
				<div>
					<input type="text" name="title" id="update_title" value="' . $aNote['title'] . '" maxlength="' . $this->iMaxTitleLen . '">
					<textarea name="body" maxlength="' . $this->iMaxBodyLen . '" cols="80" rows="2">' . $aNote['body'] . '</textarea>
					<input type="hidden" name="edit_flag">
					<input type="hidden" name="id" value="' . $aNote['id'] . '">
					<input type="submit" class="updatebut" value="' . ($sAction === 'update' ? 'update' : 'delete'). '">
				</div>
			</form>';

				# no Helpers::webSafe() on values above so that web code can be edited - it's an XSS issue, remaining in the editing form fields - adjust for your purposes
			}
		}

		return $sOut;

	} # end generateHTML()


	/**
		* Add note to database, if it doesn't already exist.
		*
		* @param   string $sTitle, note title text
		* @param   string $sBody, note body text
		*
		* @return  array [ boolean, string message ]
	*/

	public function add($sTitle, $sBody) {

		if (mb_strlen($sBody) > $this->iMaxBodyLen || mb_strlen($sBody) > $this->iMaxBodyLen) { # avoid data truncation
			return [ FALSE, 'Input data is too long!' ];
		}

		if ($sTitle === $sBody) {
			return [ FALSE, 'Title and body data input are identical!' ];
		}

		# quotes break form values on output
		$sTitle = str_ireplace('"', '&quot;', $sTitle);
		$sBody = str_ireplace('"', '&quot;', $sBody);

		# check for existing note
		$aResult = [];

		$sQuery = '
			SELECT title
			FROM ' . $this->sTableName . '
			WHERE title = :ti';

		$oStmt = $this->prepare($sQuery);
		$oStmt->bindValue(':ti', trim($sTitle), SQLITE3_TEXT);
		$rResult = $oStmt->execute();
		$aResult = $rResult->fetchArray(SQLITE3_ASSOC);
		$rResult->finalize();

		if ( ! empty($aResult)) {
			return [ FALSE, 'Title already exists!<br><small>"' . Helpers::webSafe($aResult['title']) . '"<small>' ];
		}
		##

		$sInsert = '
			INSERT INTO ' . $this->sTableName . '
			(title, body, creator)
			VALUES (:ti, :bd, :cr)';

		$oStmt = $this->prepare($sInsert);
		$oStmt->bindValue(':ti', trim($sTitle), SQLITE3_TEXT);
		$oStmt->bindValue(':bd', trim($sBody), SQLITE3_TEXT);
		$oStmt->bindValue(':cr', $_SESSION['sVerifiedName'], SQLITE3_TEXT);
		$rResult = $oStmt->execute();

		if ($rResult) {
			return [ TRUE, 'Note inserted.' ];
		}
		else {
			return [ FALSE, 'Note insertion failure!' ];
		}

	} # end add()


	/**
		* Submit updated notes to database.
		*
		* @param   string $sID, note ID
		* @param   string $sTitle, note title text
		* @param   string $sBody, note body text
		*
		* @return  array [ boolean, string message ]
	*/

	public function update($sID, $sTitle, $sBody) {

		if (empty($sID)) {
			return [ FALSE, 'No ID!' ];
		}

		$iID = (int) $sID;

		if ( ! is_int($iID) || ! $iID) {
			return [ FALSE, 'Hacker!' ];
		}

		if (mb_strlen($sTitle) > $this->iMaxTitleLen || mb_strlen($sBody) > $this->iMaxBodyLen) { # avoid data truncation
			return [ FALSE, 'Input data is too long!' ];
		}

		# quotes break the form values on output
		$sTitle = str_ireplace('"', '&quot;', $sTitle);
		$sBody = str_ireplace('"', '&quot;', $sBody);

		$sUpdate = '
			UPDATE ' . $this->sTableName . '
			SET 
				title = :ti,
				body = :bd,
				updater = :ud,
				update_ts = DATETIME("now", "localtime")
			WHERE id = :id';

		$oStmt = $this->prepare($sUpdate);
		$oStmt->bindValue(':ti', trim($sTitle), SQLITE3_TEXT);
		$oStmt->bindValue(':bd', trim($sBody), SQLITE3_TEXT);
		$oStmt->bindValue(':ud', $_SESSION['sVerifiedName'], SQLITE3_TEXT);
		$oStmt->bindValue(':id', $iID, SQLITE3_INTEGER);
		$rResult = $oStmt->execute();

		if ($rResult) {
			return [ TRUE, 'Note updated.' ];
		}
		else {
			return [ FALSE, 'Update failed!' ];
		}

	} # end update()


	/**
		* Delete note.
		*
		* @param   string $sID, note ID
		*
		* @return  array [ boolean, string message ]
	*/

	public function delete($sID) {

		if (empty($sID)) {
			return [ FALSE, 'No ID!' ];
		}

		$iID = (int) $sID;

		if ( ! is_int($iID) || ! $iID) {
			return [ FALSE, 'Hacker!' ];
		}

		$sDelete = '
			DELETE FROM ' . $this->sTableName . '
			WHERE id = :id';

		$oStmt = $this->prepare($sDelete);
		$oStmt->bindValue(':id', $iID, SQLITE3_INTEGER);
		$rResult = $oStmt->execute();

		if ($rResult) {
			return [ TRUE, 'Note deleted.' ];
		}
		else {
			return [ FALSE, 'Note deletion failed!' ];
		}

	} # end delete()


	/**
		* Return last notes entered.
		*
		* @return  string, HTML
	*/

	public function lastNotesEntered() {

		$sOut = '';

		$sQuery = '
			SELECT title, body, creator, create_ts, updater, update_ts
			FROM ' . $this->sTableName . '
			ORDER BY id DESC
			LIMIT ' . $this->iNumNotesDisplayed;

		$rResult = $this->query($sQuery);

		while ($aRow = $rResult->fetchArray(SQLITE3_ASSOC)) {

			$sOut .= '
			<div class="ntitle">' . Helpers::webSafe($aRow['title']) . '</div>
			<div class="nbody">' . Helpers::webSafe($aRow['body']) . '</div>
			<div class="nts">' . $aRow['create_ts'] . ' by ' . $aRow['creator'] . '</div>';

			if ( ! is_null($aRow['update_ts'])) {
				$sOut .= '
				<div class="nts">last update: ' . $aRow['update_ts'] . ' by ' . $aRow['updater'] . '</div>';
			}
		}

		return $sOut;

	} # end lastNotesEntered()

} # end {}

?>