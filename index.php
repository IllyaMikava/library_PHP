<?php
require_once 'config.php';

// Redirect to appropriate page based on login status
if (isLoggedIn()) {
    header("Location: search.php");
} else {
    header("Location: login.php");
}
exit();
?>