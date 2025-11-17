<?php
require_once 'config.php';
requireLogin();

$pageTitle = "My Reservations - Library System";
$message = "";
$message_type = "";

// Handle unreserve action
if (isset($_GET['action']) && $_GET['action'] == 'unreserve' && isset($_GET['isbn'])) {
    $isbn = sanitizeInput($_GET['isbn']);
    $username = getCurrentUser();
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM reservedbooks WHERE ISBN = ? AND username = ?");
    $stmt->bind_param("ss", $isbn, $username);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "Book reservation has been successfully removed.";
            $message_type = "success";
        } else {
            $message = "Reservation not found.";
            $message_type = "error";
        }
    } else {
        $message = "Failed to remove reservation. Please try again.";
        $message_type = "error";
    }
    
    $stmt->close();
    $conn->close();
    
    // Redirect to remove the action from URL
    header("Location: myreservations.php?msg=" . urlencode($message) . "&type=" . $message_type);
    exit();
}

// Display message from redirect
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = isset($_GET['type']) ? $_GET['type'] : 'info';
}

// Get user's reserved books
$conn = getDBConnection();
$username = getCurrentUser();

$sql = "SELECT b.ISBN, b.booktitle, b.author, b.edition, b.year, 
        c.categoryDescription, rb.reservedDate
        FROM reservedbooks rb
        JOIN books b ON rb.ISBN = b.ISBN
        LEFT JOIN category c ON b.categoryID = c.categoryID
        WHERE rb.username = ?
        ORDER BY rb.reservedDate DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$reservations = [];
while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
}

$stmt->close();
$conn->close();

include 'header.php';
?>

<h2>My Reserved Books</h2>

<?php if ($message): ?>
    <div class="message <?php echo $message_type; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if (count($reservations) > 0): ?>
    <div class="books-table">
        <table>
            <thead>
                <tr>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Edition</th>
                    <th>Year</th>
                    <th>Category</th>
                    <th>Reserved Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                        <td><?php echo htmlspecialchars($book['booktitle']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['edition']); ?></td>
                        <td><?php echo htmlspecialchars($book['year']); ?></td>
                        <td><?php echo htmlspecialchars($book['categoryDescription']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($book['reservedDate'])); ?></td>
                        <td>
                            <a href="myreservations.php?action=unreserve&isbn=<?php echo urlencode($book['ISBN']); ?>" 
                               class="btn btn-small btn-danger"
                               onclick="return confirm('Are you sure you want to remove this reservation?');">
                                Remove
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="message info">
        You have no reserved books at the moment. <a href="search.php">Search for books to reserve</a>.
    </div>
<?php endif; ?>

<div style="margin-top: 2rem;">
    <a href="search.php" class="btn">Search for Books</a>
</div>

<?php include 'footer.php'; ?>