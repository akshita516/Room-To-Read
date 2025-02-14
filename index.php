<?php
session_start();

// If user is not logged in, redirect to login page.
if (!isset($_SESSION['userName'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Room to Read - Home</title>
  <style>
      /* Reset and Base Styles */
      * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
      }
      body {
          font-family: 'Poppins', sans-serif;
          background: #f4f4f4;
          color: #333;
      }
      /* Navigation Bar */
      .navbar {
          background: #007bff;
          color: white;
          padding: 15px 20px;
          display: flex;
          justify-content: space-between;
          align-items: center;
      }
      .navbar .title {
          font-size: 24px;
          font-weight: bold;
      }
      .navbar .nav-links a {
          color: white;
          text-decoration: none;
          margin-left: 15px;
          font-size: 16px;
      }
      /* Jumbotron */
      .jumbotron {
          background: #e9ecef;
          padding: 50px;
          text-align: center;
          margin: 20px;
          border-radius: 8px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      }
      .jumbotron h1 {
          font-size: 36px;
          margin-bottom: 20px;
          color: #007bff;
      }
      .jumbotron p {
          font-size: 20px;
          color: #555;
          margin-bottom: 20px;
      }
      .jumbotron .about {
          font-size: 16px;
          color: #333;
          margin-top: 30px;
          line-height: 1.6;
      }
      /* Options Buttons */
      .options {
          display: flex;
          justify-content: center;
          gap: 20px;
          margin: 30px;
      }
      .options a {
          display: inline-block;
          padding: 15px 30px;
          background: #28a745;
          color: white;
          text-decoration: none;
          border-radius: 5px;
          font-size: 18px;
          transition: background 0.3s ease;
      }
      .options a:hover {
          background: #218838;
      }
  </style>
</head>
<body>
  <!-- Navigation Bar -->
  <div class="navbar">
      <div class="title">Room to Read</div>
      <div class="nav-links">
          <a href="logout.php">Logout</a>
      </div>
  </div>
  
  <!-- Jumbotron with Motto and About -->
  <div class="jumbotron">
      <h1>Welcome to Room to Read</h1>
      <p>Empowering minds, building futures through education.</p>
      <div class="about">
          <p>At Room to Read, we believe that education is the foundation for a brighter future. Our mission is to transform lives by promoting literacy and providing quality educational resources to students around the world.</p>
          <p>We are dedicated to ensuring that every child has access to books, learning opportunities, and a supportive community. Join us in our journey to inspire and empower future generations.</p>
      </div>
  </div>
  
  <!-- Options Buttons -->
  <div class="options">
      <a href="displayBooks.php">Check In</a>
      <a href="checkOut.php">Check Out</a>
      <a href="activities.php">Activities</a>
  </div>
</body>
</html>
