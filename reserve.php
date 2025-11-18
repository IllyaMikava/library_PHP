<?php
require_once 'config.php';
requireLogin();

$isbn = isset($_GET['isbn']) ? sanitizeInput($_GET['isbn']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$title = isset($_GET['title']) ? $_GET['title'] : '';
$author = isset($_GET['author']) ? $_GET['author'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$error = "";
$success = "";

if (empty($isbn)) {
    $error = "Invalid book selection";
} else {
    $conn = getDBConnection();
    $username = getCurrentUser();
    
    // Check if book exists
    $stmt = $conn->prepare("SELECT booktitle FROM books WHERE ISBN = ?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $error = "Book not found";
    } else {
        $book = $result->fetch_assoc();
        
        // Check if book is already reserved
        $stmt = $conn->prepare("SELECT username FROM reservedbooks WHERE ISBN = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $reserved_result = $stmt->get_result();
        
        if ($reserved_result->num_rows > 0) {
            $error = "This book is already reserved by another user";
        } else {
            // Reserve the book
            $reserved_date = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO reservedbooks (ISBN, username, reservedDate) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $isbn, $username, $reserved_date);
            
            if ($stmt->execute()) {
                $success = "Book '" . htmlspecialchars($book['booktitle']) . "' has been successfully reserved!";
            } else {
                $error = "Failed to reserve book. Please try again.";
            }
        }
    }
    
    $stmt->close();
    $conn->close();
}

// Build return URL
$return_url = "search.php?page=" . $page . "&title=" . urlencode($title) . "&author=" . urlencode($author) . "&category=" . $category;

$pageTitle = "Reserve Book - Library System";
include 'header.php';
?>

<div class="form-container">
    <h2>Reserve Book</h2>
    
    <?php if ($error): ?>
        <div class="message error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 1.5rem;">
        <a href="<?php echo $return_url; ?>" class="btn btn-secondary">Back to Search Results</a>
        <a href="myreservations.php" class="btn">View My Reservations</a>
    </div>
</div>

<?php include 'footer.php'; ?>