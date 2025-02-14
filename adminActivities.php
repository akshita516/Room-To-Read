<?php
session_start();

// Only allow access if the admin is logged in (assuming you stored userType during login)
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

// Fetch all Check In History with book details and userId from checkIn table
$checkInQuery = "SELECT ci.userId, ci.bookId, ci.checkInDate, b.bookName, b.bookAuthor, b.category, b.color 
                 FROM checkIn ci 
                 JOIN books b ON ci.bookId = b.bookId 
                 ORDER BY ci.checkInDate DESC";
$checkInResult = $conn->query($checkInQuery);

// Fetch all Check Out History with book details and userId from checkOut table
$checkOutQuery = "SELECT co.userId, co.bookId, co.checkOutDate, b.bookName, b.bookAuthor, b.category, b.color 
                  FROM checkOut co 
                  JOIN books b ON co.bookId = b.bookId 
                  ORDER BY co.checkOutDate DESC";
$checkOutResult = $conn->query($checkOutQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Activities - Room to Read</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: #f9f9f9; 
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
        }
        h3 { 
            color: #007bff; 
            margin-top: 30px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f4f4f4; 
        }
        tr:hover { 
            background-color: #f1f1f1; 
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
        <h2>Admin Activities</h2>
        
        <h3>Check In History</h3>
        <?php if ($checkInResult && $checkInResult->num_rows > 0): ?>
            <table>
                <tr>
                    <th>#</th>
                    <th>User ID</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Color</th>
                    <th>Check In Date</th>
                </tr>
                <?php $i = 1; while ($row = $checkInResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['userId']) ?></td>
                        <td><?= htmlspecialchars($row['bookName']) ?></td>
                        <td><?= htmlspecialchars($row['bookAuthor']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td style="color: <?= htmlspecialchars($row['color']) ?>"><?= htmlspecialchars($row['color']) ?></td>
                        <td><?= $row['checkInDate'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p class="message">No check in records found.</p>
        <?php endif; ?>
        
        <h3>Check Out History</h3>
        <?php if ($checkOutResult && $checkOutResult->num_rows > 0): ?>
            <table>
                <tr>
                    <th>#</th>
                    <th>User ID</th>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Color</th>
                    <th>Check Out Date</th>
                </tr>
                <?php $j = 1; while ($row = $checkOutResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= $j++ ?></td>
                        <td><?= htmlspecialchars($row['userId']) ?></td>
                        <td><?= htmlspecialchars($row['bookName']) ?></td>
                        <td><?= htmlspecialchars($row['bookAuthor']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td style="color: <?= htmlspecialchars($row['color']) ?>"><?= htmlspecialchars($row['color']) ?></td>
                        <td><?= $row['checkOutDate'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p class="message">No check out records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
