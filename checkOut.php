<?php
session_start();

// Check if user is logged in by verifying session variable
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "areI@03tS", "jpMorgan");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['userId']; // Now we use the logged-in user's id

if (isset($_POST['checkOut'])) { 
    $bookId = $_POST['bookId'];
    $checkOutDate = date('Y-m-d H:i:s');

    $insertQuery = "INSERT INTO checkOut (userId, bookId) VALUES ($userId, $bookId)";
    $deleteQuery = "DELETE FROM checkIn WHERE userId = $userId AND bookId=$bookId";
    $updateQuery = "UPDATE books SET bookCount = bookCount + 1 WHERE bookId = $bookId";
    
    if ($conn->query($insertQuery) && $conn->query($deleteQuery) && $conn->query($updateQuery)) {
        echo "<script>alert('Book checked out successfully!'); window.location.href='checkOut.php';</script>";
    } else {
        echo "<script>alert('Error checking out book: " . $conn->error . "');</script>";
    }
}

$sql = "SELECT checkIn.bookId, books.bookName, books.category, books.color, checkIn.checkInDate 
        FROM checkIn 
        JOIN books ON checkIn.bookId = books.bookId 
        WHERE checkIn.userId = $userId";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Books</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #007bff;
            padding: 15px 20px;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-size: 16px;
            font-weight: normal;
        }
        .container {
            width: 90%;
            max-width: 1000px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        button {
            padding: 8px 14px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            background-color: #28a745;
            color: white;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        button:hover {
            background-color: #218838;
        }
        .no-books {
            text-align: center;
            color: gray;
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="header">
    <span>Room to Read</span>
    <div>
        <a href="index.php">Back</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Books Checked Out by You</h2>

    <?php if ($result->num_rows > 0) { ?>
        <table>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Category</th>
                <th>Color</th>
                <th>Checked In Date</th>
                <th>Action</th>
            </tr>
            <?php 
            $i = 1;
            while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td style="color: <?= htmlspecialchars($row['color']) ?>; font-weight: bold;">
                        <?= htmlspecialchars($row['bookName']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td style="color: <?= htmlspecialchars($row['color']) ?>; font-weight: bold;">
                        <?= htmlspecialchars($row['color']) ?>
                    </td>
                    <td><?= $row['checkInDate'] ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="bookId" value="<?= $row['bookId'] ?>">
                            <button type="submit" name="checkOut">Checkout</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p class="no-books">No books currently checked out.</p>
    <?php } ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
