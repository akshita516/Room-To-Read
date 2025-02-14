<?php
session_start();

// Only allow access if the admin is logged in.
// (Assuming you store a userType in session; if not, you can remove this check.)
if (!isset($_SESSION['userId']) || ($_SESSION['userType'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$dbUser = "root";
$dbPass = "areI@03tS";
$dbname = "jpMorgan";

// Create connection
$conn = new mysqli($servername, $dbUser, $dbPass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// -------------------------
// Handle Issue (Check-In) Request
// -------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['issueBook'])) {
    $targetUserId = intval($_POST['targetUserId']);
    $bookId       = intval($_POST['bookId']);

    // Check if the user already has the book
    $alreadyQuery = "SELECT * FROM checkIn WHERE userId = $targetUserId AND bookId = $bookId";
    $alreadyResult = $conn->query($alreadyQuery);
    if ($alreadyResult && $alreadyResult->num_rows > 0) {
        $error = "User already has this book!";
    } else {
        // Check if book has available copies
        $bookQuery = "SELECT bookCount FROM books WHERE bookId = $bookId";
        $bookResult = $conn->query($bookQuery);
        if ($bookResult && $bookResult->num_rows > 0) {
            $bookRow = $bookResult->fetch_assoc();
            if ($bookRow['bookCount'] > 0) {
                // Decrease the available copy count
                $updateQuery = "UPDATE books SET bookCount = bookCount - 1 WHERE bookId = $bookId AND bookCount > 0";
                if ($conn->query($updateQuery)) {
                    // Insert the issuance record in checkIn table for the target user
                    $insertQuery = "INSERT INTO checkIn (userId, bookId) VALUES ($targetUserId, $bookId)";
                    if ($conn->query($insertQuery)) {
                        $success = "Book issued to user successfully!";
                    } else {
                        $error = "Error issuing book: " . $conn->error;
                    }
                } else {
                    $error = "Error updating book count: " . $conn->error;
                }
            } else {
                $error = "No copies available for this book.";
            }
        } else {
            $error = "Invalid book selection.";
        }
    }
}

// -------------------------
// If a target user ID is provided via GET, load available books
// -------------------------
$targetUserId = "";
$availableBooks = [];
if (isset($_GET['targetUserId']) && !empty($_GET['targetUserId'])) {
    $targetUserId = intval($_GET['targetUserId']);
    // Retrieve books that have available copies and are NOT already issued to that user
    $sqlBooks = "SELECT * FROM books 
                 WHERE bookCount > 0 
                 AND bookId NOT IN (SELECT bookId FROM checkIn WHERE userId = $targetUserId)";
    $booksResult = $conn->query($sqlBooks);
    if ($booksResult && $booksResult->num_rows > 0) {
        while ($row = $booksResult->fetch_assoc()) {
            $availableBooks[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Check In - Room to Read</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f9f9f9; 
            margin: 0; 
            padding: 0; 
        }
        /* Header Styling */
        .header { 
            background: #007bff; 
            color: white; 
            padding: 15px 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .header .left { 
            font-size: 24px; 
            font-weight: bold; 
        }
        .header .right { 
            display: flex; 
            gap: 10px; 
        }
        .header .right button { 
            background: #007bff; 
            color: white; 
            padding: 8px 12px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-weight: bold; 
        }
        .header .right button:hover { 
            background: #0056b3; 
        }
        .container { 
            max-width: 1000px; 
            margin: 20px auto; 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1); 
        }
        h2 { 
            text-align: center; 
            color: #333; 
        }
        .message { 
            text-align: center; 
            margin-bottom: 15px; 
        }
        .error { 
            color: red; 
        }
        .success { 
            color: green; 
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
        }
        .form-group input { 
            padding: 8px; 
            width: 100%; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
        }
        .btn { 
            padding: 10px 15px; 
            background: #007bff; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
        }
        .btn:hover { 
            background: #0056b3; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f4f4f4; 
        }
        .action-buttons form { 
            display: inline; 
        }
        .action-buttons button { 
            padding: 5px 10px; 
            margin-right: 5px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        .issue-btn { 
            background-color: #28a745; 
            color: white; 
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="left">Room to Read</div>
        <div class="right">
            <button onclick="window.location.href='statistics.php'">Back</button>
            <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>
    
    <div class="container">
        <h2>Admin Book Issue System</h2>
        
        <?php if (!empty($error)): ?>
            <p class="message error"><?= $error ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="message success"><?= $success ?></p>
        <?php endif; ?>
        
        <!-- Form to enter target user id -->
        <form method="GET" class="form-group">
            <label for="targetUserId">Enter User ID:</label>
            <input type="number" name="targetUserId" id="targetUserId" value="<?= htmlspecialchars($targetUserId) ?>" required>
            <button type="submit" class="btn">Load Available Books</button>
        </form>
        
        <?php if (!empty($targetUserId)): ?>
            <h2>Available Books for User ID: <?= $targetUserId ?></h2>
            <?php if (!empty($availableBooks)): ?>
                <table>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Available Copies</th>
                        <th>Color</th>
                        <th>Action</th>
                    </tr>
                    <?php $i = 1; foreach ($availableBooks as $book): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($book['bookName']) ?></td>
                        <td><?= htmlspecialchars($book['bookAuthor']) ?></td>
                        <td><?= htmlspecialchars($book['category']) ?></td>
                        <td><?= $book['bookCount'] ?></td>
                        <td style="color: <?= htmlspecialchars($book['color']) ?>"><?= htmlspecialchars($book['color']) ?></td>
                        <td class="action-buttons">
                            <form method="POST" onsubmit="return confirm('Issue this book to user <?= $targetUserId ?>?');">
                                <input type="hidden" name="targetUserId" value="<?= $targetUserId ?>">
                                <input type="hidden" name="bookId" value="<?= $book['bookId'] ?>">
                                <button type="submit" name="issueBook" class="issue-btn">Issue Book</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No available books for this user or the user already has all books.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
