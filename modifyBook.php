<?php
session_start();
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

// -----------------------------
// Handle Delete Request
// -----------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $bookId = intval($_POST['bookId']);
    
    // Delete related records from checkIn and checkOut tables first
    $conn->query("DELETE FROM checkIn WHERE bookId = $bookId");
    $conn->query("DELETE FROM checkOut WHERE bookId = $bookId");
    
    // Then delete the book record from books table
    if ($conn->query("DELETE FROM books WHERE bookId = $bookId")) {
        echo "<script>alert('Book deleted successfully!'); window.location.href='modifyBook.php';</script>";
        exit();
    } else {
        $error = "Error deleting book: " . $conn->error;
    }
}

// -----------------------------
// Handle Update Request (Modify)
// -----------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $bookId     = intval($_POST['bookId']);
    $bookName   = $conn->real_escape_string($_POST['bookName']);
    $bookAuthor = $conn->real_escape_string($_POST['bookAuthor']);
    $bookCount  = intval($_POST['bookCount']);
    $totalBooks = intval($_POST['totalBooks']);
    
    $sqlUpdate = "UPDATE books 
                  SET bookName = '$bookName', 
                      bookAuthor = '$bookAuthor', 
                      bookCount = $bookCount, 
                      totalBooks = $totalBooks 
                  WHERE bookId = $bookId";
    if ($conn->query($sqlUpdate)) {
        echo "<script>alert('Book updated successfully!'); window.location.href='modifyBook.php';</script>";
        exit();
    } else {
        $error = "Error updating book: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Books - Room to Read</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: #f9f9f9; 
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
            width: 90%; 
            max-width: 1200px; 
            margin: 20px auto; 
            background: white; 
            padding: 20px; 
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1); 
            border-radius: 10px; 
        }
        h2 { 
            text-align: center; 
            color: #333; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background: white; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f4f4f4; 
        }
        .error { 
            color: red; 
            text-align: center; 
            margin-bottom: 15px; 
        }
        /* Modify Form Styling */
        .modify-form { 
            max-width: 500px; 
            margin: 20px auto; 
            background: #f4f4f4; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1); 
        }
        .modify-form label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
        }
        .modify-form input[type="text"],
        .modify-form input[type="number"] { 
            width: 100%; 
            padding: 8px; 
            margin-bottom: 10px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
        }
        .modify-form button { 
            padding: 10px 15px; 
            background: #28a745; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        .modify-form button:hover { 
            background: #218838; 
        }
        /* Action Buttons in Table */
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
        .modify-btn { 
            background-color: #ffc107; 
            color: white; 
        }
        .delete-btn { 
            background-color: #dc3545; 
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
        <h2>Modify Books</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

        <?php
        // If a modification request is set via GET, display the modification form for that book.
        if (isset($_GET['modify'])) {
            $bookId = intval($_GET['modify']);
            $sqlFetch = "SELECT * FROM books WHERE bookId = $bookId";
            $res = $conn->query($sqlFetch);
            if ($res && $res->num_rows > 0) {
                $book = $res->fetch_assoc();
                ?>
                <div class="modify-form">
                    <h2>Modify Book Details</h2>
                    <form method="POST">
                        <input type="hidden" name="bookId" value="<?= $book['bookId'] ?>">
                        <label for="bookName">Book Title:</label>
                        <input type="text" id="bookName" name="bookName" value="<?= htmlspecialchars($book['bookName']) ?>" required>
                        
                        <label for="bookAuthor">Book Author:</label>
                        <input type="text" id="bookAuthor" name="bookAuthor" value="<?= htmlspecialchars($book['bookAuthor']) ?>" required>
                        
                        <label for="bookCount">Book Count:</label>
                        <input type="number" id="bookCount" name="bookCount" value="<?= $book['bookCount'] ?>" required>
                        
                        <label for="totalBooks">Total Books:</label>
                        <input type="number" id="totalBooks" name="totalBooks" value="<?= $book['totalBooks'] ?>" required>
                        
                        <button type="submit" name="update">Update Book</button>
                    </form>
                </div>
                <?php
            } else {
                echo "<script>alert('Invalid book selection!'); window.location.href='modifyBook.php';</script>";
                exit();
            }
        } else {
            // Display all books with Modify and Delete options.
            $sqlAll = "SELECT * FROM books";
            $allResult = $conn->query($sqlAll);
            if ($allResult && $allResult->num_rows > 0) {
                ?>
                <table>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Book Count</th>
                        <th>Total Books</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    $i = 1;
                    while ($row = $allResult->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['bookName']) ?></td>
                            <td><?= htmlspecialchars($row['bookAuthor']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= $row['bookCount'] ?></td>
                            <td><?= $row['totalBooks'] ?></td>
                            <td class="action-buttons">
                                <form method="GET" style="display:inline;">
                                    <input type="hidden" name="modify" value="<?= $row['bookId'] ?>">
                                    <button type="submit" class="modify-btn">Modify</button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                    <input type="hidden" name="bookId" value="<?= $row['bookId'] ?>">
                                    <button type="submit" name="delete" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <?php
            } else {
                echo "<p>No books found.</p>";
            }
        }
        ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
