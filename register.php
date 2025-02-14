<?php
$servername = "localhost";
$userName = "root";
$password = "areI@03tS";
$dbname = "jpMorgan";

// Create connection
$conn = new mysqli($servername, $userName, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['userName'];
    $pass = $_POST['password'];
    $confirmPass = $_POST['confirmPassword'];
    $userType = $_POST['userType'];
    
    // Check if passwords match
    if ($pass !== $confirmPass) {
        $error = "Passwords do not match!";
    } else {
        // Check if username already exists
        $sql = "SELECT * FROM user WHERE userName = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "User already exists!";
        } else {
            // Insert new user into the database including userType
            $insertSql = "INSERT INTO user (userName, password, userType) VALUES (?, ?, ?)";
            $stmtInsert = $conn->prepare($insertSql);
            $stmtInsert->bind_param("sss", $user, $pass, $userType);
            if ($stmtInsert->execute()) {
                $_SESSION['userName'] = $user;
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $stmtInsert->error;
            }
            $stmtInsert->close();
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
            margin: 0;
            padding: 0;
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
        /* Registration Container */
        .register-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }
        .register-container form {
            display: flex;
            flex-direction: column;
        }
        .register-container label {
            margin-bottom: 5px;
            font-weight: 600;
        }
        .register-container input[type="text"],
        .register-container input[type="password"],
        .register-container select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .register-container button {
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .register-container button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .body{
            background: url("./bgImage.png");
            background-size: cover;
            opacity: 1.75; /* Adjust opacity as needed */
            z-index: -1;
        }
    </style>
</head>
<body class="body">
    <nav class="navbar">
        <div class="title">Room to Read</div>
        <div class="nav-links">
            <a href="login.php">Login</a>
        </div>
    </nav>

    <div class="register-container">
        <h2>Register</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label>Username:</label>
            <input type="text" name="userName" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <label>Confirm Password:</label>
            <input type="password" name="confirmPassword" required>
            <label>User Type:</label>
            <select name="userType" required>
                <option value="student">Student</option>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
            </select>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
