<?php

#########################################
require('classes/sqlite.class.php');
require('classes/sqlitefunc.class.php');
require('classes/helpers.class.php');
#########################################

session_start();

require('includes/head.php');

?>

		<h1 id="indextitle">
			<a href="index.php" id="index">Noter</a>
			<a href="<?php echo (isset($_SESSION['sVerifiedName'])) ? 'menu.php' : 'edit.php'; ?>" id="edit">&#9998;</a>
		</h1>

<?php
Helpers::outputSearchForm();
?>

		<div id="linecont">

<?php

$oNote = new SQLiteFunc();


if ( ! isset($_POST['search_flag'])) {
	echo $oNote->lastNotesEntered();
}
else {

	$aResults = $oNote->search($_POST['term'], $_POST['choice']);

	if ($aResults[0]) {
		echo $aResults[1];
	}
	else {
		echo '<p class="error">' . $aResults[1] . '</p>';
	}
}

?>

		</div>

<?php

require('includes/foot.php');

?>