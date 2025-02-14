<?php
session_start();

$servername = "localhost";
$dbUsername = "root"; // renamed for clarity
$dbPassword = "areI@03tS";
$dbname = "jpMorgan";

// Create connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['userName'];
    $pass = $_POST['password'];
    
    $sql = "SELECT * FROM user WHERE userName = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If user exists, store the entire user data in the session
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();  // get full user record
        
        $_SESSION['userId']   = $userData['userId'];    // Ensure your database has a "userId" column
        $_SESSION['userName'] = $userData['userName'];
        $_SESSION['userType'] = $userData['userType'];    // Stores the user's type (e.g., admin)
        // Optionally store additional details here, for example:
        // $_SESSION['email'] = $userData['email'];
        
        if ($userData['userType'] === 'admin') {
            header("Location: statistics.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Invalid userName or password!";
    }
    
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f4f4;
            color: #333;
            /* Background image settings */
            background: url("./bgImage.png") no-repeat center center fixed;
            background-size: cover;
        }
        /* Navigation Bar */
        .navbar {
            background: #007bff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar .title {
            font-size: 24px;
            color: white;
            font-weight: bold;
        }
        .navbar .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-size: 16px;
        }
        /* Login Container */
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
        }
        .login-container label {
            margin-bottom: 5px;
            font-weight: 600;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .login-container button {
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-container button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="title">Room to Read</div>
        <div class="nav-links">
            <a href="register.php">Register</a>
        </div>
    </nav>

    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label>userName:</label>
            <input type="text" name="userName" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
