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
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addBook'])) {
    $bookName   = $conn->real_escape_string($_POST['bookName']);
    $bookAuthor = $conn->real_escape_string($_POST['bookAuthor']);
    $category   = $conn->real_escape_string($_POST['category']);
    $totalBooks = intval($_POST['totalBooks']);
    $color      = $conn->real_escape_string($_POST['color']);
    
    // Initialize bookCount to totalBooks
    $bookCount  = $totalBooks;
    
    $sqlInsert = "INSERT INTO books (bookName, bookAuthor, category, bookCount, totalBooks, color) 
                  VALUES ('$bookName', '$bookAuthor', '$category', $bookCount, $totalBooks, '$color')";
    if ($conn->query($sqlInsert)) {
        $success = "Book added successfully!";
    } else {
        $error = "Error adding book: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Book - Room to Read</title>
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
          max-width: 600px; 
          margin: 30px auto; 
          background: white; 
          padding: 20px; 
          border-radius: 10px; 
          box-shadow: 0px 0px 10px rgba(0,0,0,0.1); 
      }
      h2 { 
          text-align: center; 
          color: #333; 
      }
      .form-group { 
          margin-bottom: 15px; 
      }
      .form-group label { 
          display: block; 
          margin-bottom: 5px; 
          font-weight: bold; 
      }
      .form-group input, .form-group select { 
          width: 100%; 
          padding: 8px; 
          margin-bottom: 10px; 
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
          width: 100%; 
          font-size: 16px;
      }
      .btn:hover { 
          background: #0056b3; 
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
      <h2>Add a New Book</h2>
      <?php 
      if (!empty($error)) { 
          echo "<p class='message error'>$error</p>"; 
      }
      if (!empty($success)) { 
          echo "<p class='message success'>$success</p>"; 
      }
      ?>
      <form method="POST" action="">
          <div class="form-group">
             <label for="bookName">Book Name:</label>
             <input type="text" id="bookName" name="bookName" required>
          </div>
          <div class="form-group">
             <label for="bookAuthor">Book Author:</label>
             <input type="text" id="bookAuthor" name="bookAuthor" required>
          </div>
          <div class="form-group">
             <label for="category">Category:</label>
             <input type="text" id="category" name="category" placeholder="Enter category" required>
          </div>
          <div class="form-group">
             <label for="totalBooks">Total Number of Copies:</label>
             <input type="number" id="totalBooks" name="totalBooks" min="1" required>
          </div>
          <div class="form-group">
             <label for="color">Color:</label>
             <select id="color" name="color" required>
                <option value="">Select Color</option>
                <option value="red">Red</option>
                <option value="blue">Blue</option>
                <option value="orange">Orange</option>
                <option value="yellow">Yellow</option>
                <option value="green">Green</option>
             </select>
          </div>
          <button type="submit" name="addBook" class="btn">Add Book</button>
      </form>
  </div>
</body>
</html>

<?php $conn->close(); ?>
