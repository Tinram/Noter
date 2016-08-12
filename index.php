<?php

#########################################
require('classes/sqlite.class.php');
require('classes/sqlitefunc.class.php');
require('classes/helpers.class.php');
#########################################


require('includes/head.php');

?>

<h1><a href="index.php" id="index">Noter</a></h1>

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