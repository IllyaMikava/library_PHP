<?php
require_once 'config.php';

$pageTitle = "Login - Library System";
$error = "";

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: search.php");
    exit();
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['username'] = $user['username'];
                header("Location: search.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
        
        $stmt->close();
        $conn->close();
    }
}

include 'header.php';
?>

<div class="form-container">
    <h2>Login to Library System</h2>
    
    <?php if ($error): ?>
        <div class="message error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
    
    <p style="margin-top: 1rem; text-align: center;">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>

<?php include 'footer.php'; ?>