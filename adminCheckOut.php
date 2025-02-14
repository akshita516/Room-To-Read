<?php
session_start();

// Only allow access if the admin is logged in.
// (Assuming that you store a 'userType' in session)
if (!isset($_SESSION['userId']) || ($_SESSION['userType'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$dbUser     = "root";
$dbPass     = "areI@03tS";
$dbname     = "jpMorgan";

// Create connection
$conn = new mysqli($servername, $dbUser, $dbPass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// --------------------------
// Handle Check Out (Return) Request
// --------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkOut'])) {
    if (!empty($_POST['bookId']) && !empty($_POST['targetUserId'])) {
        $bookId       = intval($_POST['bookId']);
        $targetUserId = intval($_POST['targetUserId']);
        $checkOutDate = date('Y-m-d H:i:s');

        // Verify that the user currently has this book (active record in checkIn)
        $checkQuery = "SELECT * FROM checkIn WHERE userId = $targetUserId AND bookId = $bookId";
        $checkResult = $conn->query($checkQuery);
        if ($checkResult && $checkResult->num_rows > 0) {
            // Insert a record into checkOut table with the current date
            $insertQuery = "INSERT INTO checkOut (userId, bookId, checkOutDate) VALUES ($targetUserId, $bookId, '$checkOutDate')";
            if ($conn->query($insertQuery)) {
                // Delete the record from checkIn table (i.e. mark it as returned)
                $deleteQuery = "DELETE FROM checkIn WHERE userId = $targetUserId AND bookId = $bookId";
                if ($conn->query($deleteQuery)) {
                    // Increase the available book count in the books table by 1
                    $updateBooks = "UPDATE books SET bookCount = bookCount + 1 WHERE bookId = $bookId";
                    if ($conn->query($updateBooks)) {
                        $success = "Book checked out (returned) successfully for user $targetUserId!";
                    } else {
                        $error = "Error updating book count: " . $conn->error;
                    }
                } else {
                    $error = "Error deleting checkIn record: " . $conn->error;
                }
            } else {
                $error = "Error inserting into checkOut: " . $conn->error;
            }
        } else {
            $error = "This user does not currently have this book.";
        }
    } else {
        $error = "Missing book ID or target user ID.";
    }
}

// --------------------------
// Load Issued Books for a Given User
// --------------------------
$targetUserId = "";
$issuedBooks = [];
if (isset($_GET['targetUserId']) && !empty($_GET['targetUserId'])) {
    $targetUserId = intval($_GET['targetUserId']);
    // Retrieve all active issued books for that user from the checkIn table
    $sqlIssued = "SELECT ci.*, b.bookName, b.bookAuthor, b.category, b.color, ci.checkInDate 
                  FROM checkIn ci 
                  JOIN books b ON ci.bookId = b.bookId 
                  WHERE ci.userId = $targetUserId";
    $issuedResult = $conn->query($sqlIssued);
    if ($issuedResult && $issuedResult->num_rows > 0) {
        while ($row = $issuedResult->fetch_assoc()) {
            $issuedBooks[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Check Out - Room to Read</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f9f9f9; 
            margin: 0; 
            padding: 0; 
        }
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
        .checkout-btn { 
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
        <h2>Admin Check Out System</h2>
        
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
            <button type="submit" class="btn">Load Issued Books</button>
        </form>
        
        <?php if (!empty($targetUserId)): ?>
            <h2>Issued Books for User ID: <?= $targetUserId ?></h2>
            <?php if (!empty($issuedBooks)): ?>
                <table>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Color</th>
                        <th>Issue Date</th>
                        <th>Action</th>
                    </tr>
                    <?php $i = 1; foreach ($issuedBooks as $book): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($book['bookName']) ?></td>
                        <td><?= htmlspecialchars($book['bookAuthor']) ?></td>
                        <td><?= htmlspecialchars($book['category']) ?></td>
                        <td style="color: <?= htmlspecialchars($book['color']) ?>"><?= htmlspecialchars($book['color']) ?></td>
                        <td><?= $book['checkInDate'] ?></td>
                        <td class="action-buttons">
                            <form method="POST" onsubmit="return confirm('Check out (return) this book for user <?= $targetUserId ?>?');">
                                <input type="hidden" name="targetUserId" value="<?= $targetUserId ?>">
                                <input type="hidden" name="bookId" value="<?= $book['bookId'] ?>">
                                <button type="submit" name="checkOut" class="checkout-btn">Check Out</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No issued books found for this user.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
