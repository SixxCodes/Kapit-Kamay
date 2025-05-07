<?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: login.html"); // Make sure the path is correct relative to logout.php
    exit();
?>
