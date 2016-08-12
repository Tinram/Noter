<?php

#########################################
require('classes/helpers.class.php');
Helpers::validateUser();

require('classes/sqlite.class.php');
require('classes/sqlitefunc.class.php');
#########################################


require('includes/head.php');

?>

<h1><a href="index.php">Add</a></h1>

<div id="faddcont">

	<form action="<?php echo Helpers::selfSafe(); ?>" method="post">

		<div>
			<label for="title">Title</label>
			<input type="text" name="title" id="title" maxlength="<?php echo CONFIG_MAX_TITLE_LEN; ?>">
		</div>

		<div id="tacont">
			<label for="body">Note</label>
			<textarea id="body" name="body" cols="80" rows="2" maxlength="<?php echo CONFIG_MAX_BODY_LEN; ?>"></textarea>
		</div>

		<input type="hidden" name="add_flag">
		<input type="submit" value="add">

	</form>

</div>


<?php

if (isset($_POST['add_flag'])) {

	$oNote = new SQLiteFunc();

	if ( ! empty($_POST['title']) && ! empty($_POST['body']) ) {

		$aResults = $oNote->add($_POST['title'], $_POST['body']);

		if ($aResults[0]) {
			echo '<p id="complete" class="success">' . $aResults[1] . '</p>';
		}
		else {
			echo '<p class="error">' . $aResults[1] . '</p>';
		}
	}
	else {
		echo '<p class="error">Incomplete or blank note entry.</p>';
	}

}

require('includes/foot.php');

?>