<?php

declare(strict_types=1);


final class SQLiteFunc extends SQLA
{
    /**
        * SQLite application functionality.
        *
        * @author       Martin Latter
        * @copyright    Martin Latter 03/04/2015
        * @version      0.37
        * @license      GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
        * @link         https://github.com/Tinram/noter.git
    */


    /**
        * Search delegator - delegates search and HTML generation.
        * Keeps logic simple and delegates to other methods.
        *
        * @param  string $sKeywords, search terms
        * @param  string $sChoice, title or body search
        * @param  string $sAction, page action
        *
        * @return  array<mixed> [ boolean, string message/HTML ]
    */

    public function search(string $sKeywords = '', string $sChoice, string $sAction = ''): array
    {
        if ($sKeywords === '')
        {
            return [ false, 'No keywords passed.' ];
        }

        $sMode = ($sChoice === 'title') ? 'title' : 'body';

        $aNotes = $this->searchProcessor($sKeywords, $sMode);

        if ($aNotes[0] === false)
        {
            return [ false, 'No results found.' ];
        }
        else
        {
            return [ true, $this->generateHTML($aNotes, $sAction) ];
        }
    }


    /**
        * Query database.
        *
        * @param   string $sKeywords, search terms
        * @param   string $sMode, title or body column search
        *
        * @return  array<mixed>, note row data
    */

    private function searchProcessor(string $sKeywords, string $sMode): array
    {
        $aResults = [];

        $sQuery = '
            SELECT id, title, body, creator, create_ts, updater, update_ts
            FROM ' . $this->sTableName . '
            WHERE ' . $sMode . ' LIKE :term';

        $oStmt = $this->prepare($sQuery);
        $sTerm = '%' . trim($sKeywords) . '%';
        $oStmt->bindValue(':term', $sTerm, SQLITE3_TEXT);
        $rResult = $oStmt->execute();

        while ($aRow = $rResult->fetchArray(SQLITE3_ASSOC))
        {
            $aResults[] = [ 'id' => $aRow['id'], 'title' => $aRow['title'], 'body' => $aRow['body'], 'creator' => $aRow['creator'], 'create_ts' => $aRow['create_ts'], 'updater' => $aRow['updater'], 'update_ts' => $aRow['update_ts'] ];
        }

        $rResult->finalize();

        /* use the initial empty array as an empty results test
          (SQLite prepared stmt has no num_rows, and a preliminary fetchArray() alters the result set) */
        if (count($aResults) === 0)
        {
            return [ false ];
        }
        else
        {
            return $aResults;
        }
    }


    /**
        * Get note details from note id.
        *
        * @param  string $sID, note id
        * @param  string $sAction, page action
        *
        * @return  array<mixed> [ boolean, string message/HTML ]
    */

    public function getID(string $sID, string $sAction): array
    {
        $aResults = [];

        $sQuery = '
            SELECT id, title, body
            FROM ' . $this->sTableName . '
            WHERE id = :id';

        $oStmt = $this->prepare($sQuery);
        $oStmt->bindValue(':id', $sID, SQLITE3_TEXT);
        $rResult = $oStmt->execute();
        $aResults[0] = $rResult->fetchArray(SQLITE3_ASSOC);
        $rResult->finalize();

        if ($aResults[0] === false)
        {
            return [ false, 'Note not found.' ];
        }
        else
        {
            return [ true, $this->generateHTML($aResults, $sAction) ];
        }
    }


    /**
        * Generate results HTML.
        *
        * @param   array<mixed> $aNotes, note row data
        * @param   string $sAction, toggle for update.php page functionality
        *
        * @return  string, HTML
    */

    private function generateHTML(array $aNotes, string $sAction = ''): string
    {
        $sOut = '';

        if ($sAction === '')
        {
            foreach ($aNotes as $aNote)
            {
                $sOut .= '
                <div class="ntitle">' . Helpers::webSafe($aNote['title']) . '</div>
                <div class="nbody">' . ((strpos($aNote['body'], '<pre>') === false) ? str_replace(["\n", "\r\n"], '<br>', Helpers::webSafe($aNote['body'])) : Helpers::webSafe($aNote['body'])) . '</div>
                <div class="nts">' . $aNote['create_ts'] . ' by ' . $aNote['creator'];

                if (isset($_SESSION['sVerifiedName']))
                {
                    $sOut .= ' <span class="ud"><a href="update.php?id=' . Helpers::webSafe((string) $aNote['id']) . '">upd</a> <a href="delete.php?id=' . Helpers::webSafe((string) $aNote['id']) . '">del</a></span>';
                }

                $sOut .= '</div>';

                if ( ! is_null($aNote['update_ts']))
                {
                    $sOut .= '
                <div class="nts">last update: ' . $aNote['update_ts'] . ' by ' . $aNote['updater'] . '</div>';
                }
            }
        }
        else
        {
            echo '
            <div>
                <span>title</span>
                <span id="update_note_heading">note</span>
            </div>';

            foreach ($aNotes as $aNote)
            {
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

                /* no Helpers::webSafe() on values above so that web code can be edited - it's an XSS issue, remaining in the editing form fields - adjust for your purposes */
            }
        }

        return $sOut;
    }


    /**
        * Add note to database, if it doesn't already exist.
        *
        * @param   string $sTitle, note title text
        * @param   string $sBody, note body text
        *
        * @return  array<mixed> [ boolean, string message ]
    */

    public function add(string $sTitle, string $sBody): array
    {
        if (mb_strlen($sBody) > $this->iMaxBodyLen || mb_strlen($sBody) > $this->iMaxBodyLen) /* avoid data truncation */
        {
            return [ false, 'Input data is too long!' ];
        }

        if ($sTitle === $sBody)
        {
            return [ false, 'Title and body data input are identical!' ];
        }

        /* quotes break form values on output */
        $sTitle = str_replace('"', '&quot;', $sTitle);
        $sBody = str_replace('"', '&quot;', $sBody);

        /* check for existing note */
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

        if ($aResult !== false)
        {
            return [ false, 'Title already exists!<br><small>"' . Helpers::webSafe($aResult['title']) . '"<small>' ];
        }

        /* format most URLs */
        $sURL = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^‌​,.\s])@'; /* credit: Andrew Ellis */
        $sBody = preg_replace($sURL, '<a href="http$2://$4" target="_blank">$0</a>', $sBody);

        $sInsert = '
            INSERT INTO ' . $this->sTableName . '
            (title, body, creator)
            VALUES (:ti, :bd, :cr)';

        $oStmt = $this->prepare($sInsert);
        $oStmt->bindValue(':ti', trim($sTitle), SQLITE3_TEXT);
        $oStmt->bindValue(':bd', trim($sBody), SQLITE3_TEXT);
        $oStmt->bindValue(':cr', $_SESSION['sVerifiedName'], SQLITE3_TEXT);
        $rResult = $oStmt->execute();

        if ($rResult !== false)
        {
            return [ true, 'Note inserted.' ];
        }
        else
        {
            return [ false, 'Note insertion failure!' ];
        }
    }


    /**
        * Submit updated notes to database.
        *
        * @param   string $sID, note ID
        * @param   string $sTitle, note title text
        * @param   string $sBody, note body text
        *
        * @return  array<mixed> [ boolean, string message ]
    */

    public function update(string $sID = '', string $sTitle, string $sBody): array
    {
        if ($sID === '')
        {
            return [ false, 'No ID!' ];
        }

        $iID = (int) $sID;

        if ($iID === 0)
        {
            return [ false, 'Hacker!' ];
        }

        if (mb_strlen($sTitle) > $this->iMaxTitleLen || mb_strlen($sBody) > $this->iMaxBodyLen) /* avoid data truncation */
        {
            return [ false, 'Input data is too long!' ];
        }

        /* raw quotes break the form values on output */
        $sTitle = str_replace('"', '&quot;', $sTitle);
        $sBody = str_replace('"', '&quot;', $sBody);

        /* remove quote entities from links, which otherwise corrupt the links */
        $sBody = preg_replace('<a href=&quot;(.+)&quot; target=&quot;_blank&quot;>', '<a href="$1" target="_blank">', $sBody);

        /* remove characters in textareas caused by raw quotes on links */
        $sBody = str_replace('<<', '<', $sBody);
        $sBody = str_replace('>>', '>', $sBody);

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

        if ($rResult !== false)
        {
            return [ true, 'Note updated.' ];
        }
        else
        {
            return [ false, 'Update failed!' ];
        }
    }


    /**
        * Delete note.
        *
        * @param   string $sID, note ID
        *
        * @return  array<mixed> [ boolean, string message ]
    */

    public function delete(string $sID = ''): array
    {
        if ($sID === '')
        {
            return [ false, 'No ID!' ];
        }

        $iID = (int) $sID;

        if ($iID === 0)
        {
            return [ false, 'Hacker!' ];
        }

        $sDelete = '
            DELETE FROM ' . $this->sTableName . '
            WHERE id = :id';

        $oStmt = $this->prepare($sDelete);
        $oStmt->bindValue(':id', $iID, SQLITE3_INTEGER);
        $rResult = $oStmt->execute();

        if ($rResult !== false)
        {
            return [ true, 'Note deleted.' ];
        }
        else
        {
            return [ false, 'Note deletion failed!' ];
        }
    }


    /**
        * Return last notes entered.
        *
        * @return  string, HTML
    */

    public function lastNotesEntered(): string
    {
        $sOut = '';

        $sQuery = '
            SELECT id, title, body, creator, create_ts, updater, update_ts
            FROM ' . $this->sTableName . '
            ORDER BY id DESC
            LIMIT ' . $this->iNumNotesDisplayed;

        $rResult = $this->query($sQuery);

        while ($aRow = $rResult->fetchArray(SQLITE3_ASSOC))
        {
            $sOut .= '
            <div class="ntitle">' . Helpers::webSafe($aRow['title']) . '</div>
            <div class="nbody">' . ((strpos($aRow['body'], '<pre>') === false) ? str_replace(["\n", "\r\n"], '<br>', Helpers::webSafe($aRow['body'])) : Helpers::webSafe($aRow['body'])) . '</div>
            <div class="nts">' . $aRow['create_ts'] . ' by ' . $aRow['creator'];

            if (isset($_SESSION['sVerifiedName']))
            {
                $sOut .= ' <span class="ud"><a href="update.php?id=' . Helpers::webSafe((string) $aRow['id']) . '">upd</a> <a href="delete.php?id=' . Helpers::webSafe((string) $aRow['id']) . '">del</a></span>';
            }

            $sOut .= '</div>';

            if ( ! is_null($aRow['update_ts']))
            {
                $sOut .= '
                <div class="nts">last update: ' . $aRow['update_ts'] . ' by ' . $aRow['updater'] . '</div>';
            }
        }

        return $sOut;
    }
}
