<?php
require_once 'config.php';
requireLogin();

$pageTitle = "Search Books - Library System";
$books = [];
$total_books = 0;
$books_per_page = 5;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $books_per_page;

// Get search parameters
$search_title = isset($_GET['title']) ? sanitizeInput($_GET['title']) : '';
$search_author = isset($_GET['author']) ? sanitizeInput($_GET['author']) : '';
$search_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Get all categories for dropdown
$conn = getDBConnection();
$categories = [];
$cat_result = $conn->query("SELECT categoryID, categoryDescription FROM category ORDER BY categoryDescription");
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row;
}

// Build search query
$search_performed = !empty($search_title) || !empty($search_author) || !empty($search_category);

if ($search_performed) {
    $where_conditions = [];
    $params = [];
    $types = "";
    
    if (!empty($search_title)) {
        $where_conditions[] = "b.booktitle LIKE ?";
        $params[] = "%" . $search_title . "%";
        $types .= "s";
    }
    
    if (!empty($search_author)) {
        $where_conditions[] = "b.author LIKE ?";
        $params[] = "%" . $search_author . "%";
        $types .= "s";
    }
    
    if (!empty($search_category)) {
        $where_conditions[] = "b.categoryID = ?";
        $params[] = $search_category;
        $types .= "i";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Count total results
    $count_sql = "SELECT COUNT(*) as total FROM books b WHERE " . $where_clause;
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total_books = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Get paginated results
    $sql = "SELECT b.*, c.categoryDescription,
            (SELECT COUNT(*) FROM reservedbooks rb WHERE rb.ISBN = b.ISBN) as is_reserved
            FROM books b
            LEFT JOIN category c ON b.categoryID = c.categoryID
            WHERE " . $where_clause . "
            ORDER BY b.booktitle
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $params[] = $books_per_page;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $stmt->close();
}

$conn->close();

// Calculate total pages
$total_pages = $total_books > 0 ? ceil($total_books / $books_per_page) : 0;

include 'header.php';
?>

<h2>Search for Books</h2>

<div class="search-form">
    <form method="GET" action="search.php">
        <div class="search-form-row">
            <div class="form-group">
                <label for="title">Book Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($search_title); ?>" placeholder="Enter book title">
            </div>
            
            <div class="form-group">
                <label for="author">Author</label>
                <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($search_author); ?>" placeholder="Enter author name">
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['categoryID']; ?>" 
                                <?php echo $search_category == $cat['categoryID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['categoryDescription']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn">Search</button>
        <a href="search.php" class="btn btn-secondary">Clear</a>
    </form>
</div>

<?php if ($search_performed): ?>
    <h3>Search Results (<?php echo $total_books; ?> books found)</h3>
    
    <?php if (count($books) > 0): ?>
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
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['ISBN']); ?></td>
                            <td><?php echo htmlspecialchars($book['booktitle']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['edition']); ?></td>
                            <td><?php echo htmlspecialchars($book['year']); ?></td>
                            <td><?php echo htmlspecialchars($book['categoryDescription']); ?></td>
                            <td>
                                <?php if ($book['is_reserved'] > 0): ?>
                                    <span class="status-badge status-reserved">Reserved</span>
                                <?php else: ?>
                                    <span class="status-badge status-available">Available</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($book['is_reserved'] == 0): ?>
                                    <a href="reserve.php?isbn=<?php echo urlencode($book['ISBN']); ?>&page=<?php echo $current_page; ?>&title=<?php echo urlencode($search_title); ?>&author=<?php echo urlencode($search_author); ?>&category=<?php echo $search_category; ?>" 
                                       class="btn btn-small">Reserve</a>
                                <?php else: ?>
                                    <span style="color: #999;">Not Available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=1&title=<?php echo urlencode($search_title); ?>&author=<?php echo urlencode($search_author); ?>&category=<?php echo $search_category; ?>">First</a>
                    <a href="?page=<?php echo $current_page - 1; ?>&title=<?php echo urlencode($search_title); ?>&author=<?php echo urlencode($search_author); ?>&category=<?php echo $search_category; ?>">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $current_page): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&title=<?php echo urlencode($search_title); ?>&author=<?php echo urlencode($search_author); ?>&category=<?php echo $search_category; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?>&title=<?php echo urlencode($search_title); ?>&author=<?php echo urlencode($search_author); ?>&category=<?php echo $search_category; ?>">Next</a>
                    <a href="?page=<?php echo $total_pages; ?>&title=<?php echo urlencode($search_title); ?>&author=<?php echo urlencode($search_author); ?>&category=<?php echo $search_category; ?>">Last</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="message info">
            No books found matching your search criteria. Please try different search terms.
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="message info">
        Enter search criteria above to find books.
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>