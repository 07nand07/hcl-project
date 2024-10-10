<?php
session_start();
session_destroy(); // Destroy the session
header("Location: snippet.html"); // Redirect to login page
exit();
?>
