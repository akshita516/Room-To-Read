<?php
session_start();
if (!isset($_SESSION['userId'])) {
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

// Fetch categories for dropdown
$category_result = $conn->query("SELECT DISTINCT category FROM books");
$categories = [];
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Handle search and category filter
$search_query = "";
$search_value = "";
$selected_category = "";

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search_value = $conn->real_escape_string($_GET['search']);
    $search_query = " WHERE bookName LIKE '%$search_value%' OR category LIKE '%$search_value%'";
} elseif (isset($_GET['category']) && $_GET['category'] !== '') {
    $selected_category = $conn->real_escape_string($_GET['category']);
    $search_query = " WHERE category = '$selected_category'";
}

// Fetch books based on filters
$sql = "SELECT * FROM books" . $search_query;
$result = $conn->query($sql);

// Handle book check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin'])) {
    if (!empty($_POST['bookId'])) {
        $bookId = intval($_POST['bookId']); // Ensure bookId is an integer
        $userId = $_SESSION['userId']; // Use session user ID

        // Check if the user already has this book
        $alreadyQuery = "SELECT * FROM checkIn WHERE userId = $userId AND bookId = $bookId";
        $alreadyResult = $conn->query($alreadyQuery);
        if ($alreadyResult && $alreadyResult->num_rows > 0) {
            echo "<script>alert('You already have this book!'); window.location.href='displayBooks.php';</script>";
            exit();
        }

        // Check if book exists and has available copies
        $checkQuery = "SELECT bookCount FROM books WHERE bookId = $bookId";
        $checkResult = $conn->query($checkQuery);
        if ($checkResult && $checkResult->num_rows > 0) {
            $book = $checkResult->fetch_assoc();
            if ($book['bookCount'] > 0) {
                // Decrease book count
                $updateQuery = "UPDATE books SET bookCount = bookCount - 1 WHERE bookId = $bookId AND bookCount > 0";
                if ($conn->query($updateQuery)) {
                    // Insert into checkIn table
                    $insertQuery = "INSERT INTO checkIn (userId, bookId) VALUES ($userId, $bookId)";
                    if ($conn->query($insertQuery)) {
                        echo "<script>alert('Book checked in successfully!'); window.location.href='displayBooks.php';</script>";
                        exit();
                    } else {
                        echo "<script>alert('Error inserting into checkIn: " . $conn->error . "');</script>";
                    }
                } else {
                    echo "<script>alert('Error updating book count: " . $conn->error . "');</script>";
                }
            } else {
                echo "<script>alert('No copies available for this book!');</script>";
            }
        } else {
            echo "<script>alert('Invalid book selection!');</script>";
        }
    } else {
        echo "<script>alert('Book ID is missing!');</script>";
    }
}

// Handle clearing search/category filter
if (isset($_POST['clear_filter'])) {
    header("Location: displayBooks.php"); // Reset filters
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management</title>
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
            width: 100%; 
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
        form { 
            display: inline; 
        }
        input[type="text"], select { 
            padding: 8px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
        }
        button { 
            padding: 8px 12px; 
            border: none; 
            cursor: pointer; 
            border-radius: 5px; 
        }
        .search-btn { 
            background-color: #007bff; 
            color: white; 
        }
        .clear-btn { 
            background-color: #ff4d4d; 
            color: white; 
        }
        .checkin-btn { 
            background-color: #28a745; 
            color: white; 
        }
        .out-of-stock { 
            color: red; 
            font-weight: bold; 
        }
        .search-bar { 
            display: flex; 
            gap: 10px; 
            justify-content: center; 
            margin-bottom: 20px; 
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="left">Room to Read</div>
        <div class="right">
            <button onclick="window.location.href='index.php'">Back</button>
            <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>

    <div class="container">
        <h2>Library Books</h2>

        <!-- Search & Category Filter -->
        <form method="GET" class="search-bar">
            <input type="text" name="search" id="search" placeholder="Enter book title or category" value="<?= htmlspecialchars($search_value) ?>">
            <select name="category">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category) { ?>
                    <option value="<?= htmlspecialchars($category) ?>" <?= ($selected_category === $category) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category) ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit" class="search-btn">Filter</button>
        </form>

        <form method="POST" class="search-bar">
            <button type="submit" name="clear_filter" class="clear-btn">Clear Filter</button>
        </form>

        <!-- Book List -->
        <table>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Available Copies</th>
                <th>Action</th>
            </tr>
            <?php 
            $i = 1;
            while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $i++ ?></td>
                <td style="color: <?= htmlspecialchars($row['color']) ?>"><?= htmlspecialchars($row['bookName']) ?></td>
                <td><?= htmlspecialchars($row['bookAuthor']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= $row['bookCount'] . "/" . $row['totalBooks'] ?></td>
                <td>
                    <?php if ($row['bookCount'] > 0) { ?>
                        <form method="POST">
                            <input type="hidden" name="bookId" value="<?= $row['bookId'] ?>">
                            <button type="submit" name="checkin" class="checkin-btn">Check In (<?= $row['bookCount'] ?>)</button>
                        </form>
                    <?php } else { ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
