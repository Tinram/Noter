<?php

#########################################
require('classes/helpers.class.php');
Helpers::validateUser();
#########################################


require('includes/head.php');

?>

		<a href="add.php">add</a><br>
		<a href="update.php">update</a><br>
		<a href="delete.php">delete</a>

<?php

require('includes/foot.php');

?>