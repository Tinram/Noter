<?php

declare(strict_types=1);

#########################################
require('classes/helpers.class.php');
require('classes/sqlite.class.php');
require('classes/sqlitefunc.class.php');
#########################################

Helpers::validateUser();

require('includes/head.php');

?>

        <h1><a href="index.php">Delete</a></h1>

<?php

Helpers::outputSearchForm();

?>

        <div id="fupdatecont">

<?php

$oNote = new SQLiteFunc();

if (isset($_POST['search_flag']))
{
    $aResults = $oNote->search($_POST['term'], $_POST['choice'], 'delete');

    if ($aResults[0])
    {
        echo $aResults[1];
    }
    else
    {
        echo '<p class="error">' . $aResults[1] . '</p>';
    }
}
else if (isset($_POST['edit_flag']))
{
    if (isset($_POST['id']))
    {
        $aResult = $oNote->delete($_POST['id']);

        if ($aResult[0])
        {
            echo '<p id="complete" class="success">' . $aResult[1] . '</p>';
        }
        else
        {
            echo '<p class="error">' . $aResult[1] . '</p>';
        }
    }
}
else if (isset($_GET['id']))
{
    $aResults = $oNote->getID($_GET['id'], 'delete');

    if ($aResults[0])
    {
        echo $aResults[1];
    }
    else
    {
        echo '<p class="error">' . $aResults[1] . '</p>';
    }
}

?>

        </div>

<?php

require('includes/foot.php');
