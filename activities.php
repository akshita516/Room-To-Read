<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "areI@03tS";
$dbname = "jpMorgan";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['userId'];

// Fetch Check In History
$checkInQuery = "SELECT checkIn.*, books.bookName, books.category, books.color 
                 FROM checkIn 
                 JOIN books ON checkIn.bookId = books.bookId 
                 WHERE checkIn.userId = $userId 
                 ORDER BY checkIn.checkInDate DESC";
$checkInResult = $conn->query($checkInQuery);

// Fetch Check Out History
$checkOutQuery = "SELECT checkOut.*, books.bookName, books.category, books.color 
                  FROM checkOut 
                  JOIN books ON checkOut.bookId = books.bookId 
                  WHERE checkOut.userId = $userId 
                  ORDER BY checkOut.checkOutDate DESC";
$checkOutResult = $conn->query($checkOutQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities - Room to Read</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
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
            color: white;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        h3 {
            color: #007bff;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        p {
            text-align: center;
            color: #555;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <div class="left">Room to Read</div>
        <div class="right">
            <button onclick="history.back()">Back</button>
            <button onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>

    <div class="container">
        <h2>Activity History</h2>
        
        <!-- Check In History -->
        <h3>Check In History</h3>
        <?php if ($checkInResult && $checkInResult->num_rows > 0): ?>
        <table>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Category</th>
                <th>Color</th>
                <th>Check In Date</th>
            </tr>
            <?php $i = 1; while ($row = $checkInResult->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['bookName']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td style="color: <?= htmlspecialchars($row['color']) ?>"><?= htmlspecialchars($row['color']) ?></td>
                <td><?= $row['checkInDate'] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
            <p>No check in records found.</p>
        <?php endif; ?>

        <!-- Check Out History -->
        <h3>Check Out History</h3>
        <?php if ($checkOutResult && $checkOutResult->num_rows > 0): ?>
        <table>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Category</th>
                <th>Color</th>
                <th>Check Out Date</th>
            </tr>
            <?php $j = 1; while ($row = $checkOutResult->fetch_assoc()): ?>
            <tr>
                <td><?= $j++ ?></td>
                <td><?= htmlspecialchars($row['bookName']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td style="color: <?= htmlspecialchars($row['color']) ?>"><?= htmlspecialchars($row['color']) ?></td>
                <td><?= $row['checkOutDate'] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
            <p>No check out records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
