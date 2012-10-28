<?php
require_once "comment.php";

print "Creating a new comment database\n";
$db = new MS_Comment('../var/comment.db');
$db->init();

?>

