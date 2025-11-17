<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Library System'; ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="main-header">
        <div class="container">
            <h1>📚 Library Book Reservation System</h1>
            <nav>
                <?php if (isLoggedIn()) : ?>
                    <a href="search.php">Search Books</a>
                    <a href="myreservations.php">My Reservations</a>
                    <div class="nav-right">
                        <span class="user-info">Welcome, oooo</span>
                        <a class="logout-btn" href="logout.php">Logout</a>
                    </div>
                <?php else : ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container">