<?php

#########################################
require('classes/helpers.class.php');
Helpers::validateUser();

require('classes/sqlite.class.php');
require('classes/sqlitefunc.class.php');
#########################################


require('includes/head.php');

?>

<h1><a href="index.php">Update</a></h1>

<?php

Helpers::outputSearchForm();

?>

<div id="fupdatecont">

<?php

$oNote = new SQLiteFunc();

if (isset($_POST['search_flag'])) {

	$aResults = $oNote->search($_POST['term'], $_POST['choice'], 'update');

	if ($aResults[0]) {
		echo $aResults[1];
	}
	else {
		echo '<p class="error">' . $aResults[1] . '</p>';
	}
}
else if (isset($_POST['edit_flag'])) {

	if (isset($_POST['id'])) {

		$aResult = $oNote->update($_POST['id'], $_POST['title'], $_POST['body']);

		if ($aResult[0]) {
			echo '<p id="complete" class="success">' . $aResult[1] . '</p>';
		}
		else {
			echo '<p class="error">' . $aResult[1] . '</p>';
		}
	}
}

?>

</div>

<?php

require('includes/foot.php');

?>