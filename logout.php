<?php
session_start();
session_unset();
session_destroy();

// Redirect specifically to your login page
header("Location: db_index.php");
exit();
?>