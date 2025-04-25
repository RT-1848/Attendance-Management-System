<?php
//ending session and returning to the login page if the logout link is pressed.
session_start();
session_destroy();
header('Location: index.php?page=login');
exit();
?> 