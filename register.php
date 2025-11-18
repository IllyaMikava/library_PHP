<?php
require_once 'config.php';

$pageTitle = "Register - Library System";
$errors = [];
$success = "";

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: search.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST['username']);
    $firstname = sanitizeInput($_POST['firstname']);
    $surname = sanitizeInput($_POST['surname']);
    $mobile = sanitizeInput($_POST['mobile']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($firstname)) {
        $errors[] = "First name is required";
    }
    
    if (empty($surname)) {
        $errors[] = "Surname is required";
    }
    
    if (empty($mobile)) {
        $errors[] = "Mobile number is required";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Mobile number must be exactly 10 digits";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) != 6) {
        $errors[] = "Password must be exactly 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if username already exists
    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Username already exists. Please choose another.";
        }
        $stmt->close();
    }
    
    // Insert user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, firstname, surname, password, mobile) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $firstname, $surname, $hashed_password, $mobile);
        
        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
            // Clear form fields
            $username = $firstname = $surname = $mobile = "";
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
        $conn->close();
    }
}

include 'header.php';
?>

<div class="form-container">
    <h2>Register New Account</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="message error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success">
            <?php echo $success; ?>
            <p><a href="login.php">Click here to login</a></p>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="username">Username*</label>
            <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="firstname">First Name*</label>
            <input type="text" id="firstname" name="firstname" value="<?php echo isset($firstname) ? htmlspecialchars($firstname) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="surname">Surname*</label>
            <input type="text" id="surname" name="surname" value="<?php echo isset($surname) ? htmlspecialchars($surname) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="mobile">Mobile Number* (10 digits)</label>
            <input type="text" id="mobile" name="mobile" value="<?php echo isset($mobile) ? htmlspecialchars($mobile) : ''; ?>" pattern="[0-9]{10}" required>
            <small>Example: 0851234567</small>
        </div>
        
        <div class="form-group">
            <label for="password">Password* (6 characters)</label>
            <input type="password" id="password" name="password" minlength="6" maxlength="6" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password*</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="6" maxlength="6" required>
        </div>
        
        <button type="submit" class="btn">Register</button>
        <a href="login.php" class="btn btn-secondary">Back to Login</a>
    </form>
</div>

<?php include 'footer.php'; ?>