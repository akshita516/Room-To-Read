<?php
session_start();
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

//---------------------
// Books & User Statistics
//---------------------
$totalBooksQuery = "SELECT COUNT(*) as totalBooks FROM books";
$totalBooksResult = $conn->query($totalBooksQuery);
$totalBooks = ($totalBooksResult && $row = $totalBooksResult->fetch_assoc()) ? $row['totalBooks'] : 0;

$genreQuery = "SELECT COUNT(DISTINCT category) as genreCount FROM books";
$genreResult = $conn->query($genreQuery);
$genreCount = ($genreResult && $row = $genreResult->fetch_assoc()) ? $row['genreCount'] : 0;

$totalCopiesQuery = "SELECT SUM(totalBooks) as totalCopies FROM books";
$totalCopiesResult = $conn->query($totalCopiesQuery);
$totalCopies = ($totalCopiesResult && $row = $totalCopiesResult->fetch_assoc()) ? $row['totalCopies'] : 0;

$adminQuery = "SELECT COUNT(*) as count FROM user WHERE userType = 'admin'";
$adminResult = $conn->query($adminQuery);
$totalAdmins = ($adminResult && $row = $adminResult->fetch_assoc()) ? $row['count'] : 0;

$studentQuery = "SELECT COUNT(*) as count FROM user WHERE userType = 'student'";
$studentResult = $conn->query($studentQuery);
$totalStudents = ($studentResult && $row = $studentResult->fetch_assoc()) ? $row['count'] : 0;

$teacherQuery = "SELECT COUNT(*) as count FROM user WHERE userType = 'teacher'";
$teacherResult = $conn->query($teacherQuery);
$totalTeachers = ($teacherResult && $row = $teacherResult->fetch_assoc()) ? $row['count'] : 0;

//---------------------
// Chart Queries for Books
//---------------------
// Books by Category (Pie Chart)
$categoriesQuery = "SELECT category, COUNT(*) as count FROM books GROUP BY category";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
$categoryCounts = [];
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['category'];
        $categoryCounts[] = $row['count'];
    }
}

// Borrow Count per Book (Bar Chart)
$booksCountQuery = "SELECT b.bookName, COUNT(*) as count 
                    FROM checkOut c
                    JOIN books b ON c.bookId = b.bookId 
                    GROUP BY b.bookName";
$booksCountResult = $conn->query($booksCountQuery);
$bookNames = [];
$bookCounts = [];
if ($booksCountResult) {
    while ($row = $booksCountResult->fetch_assoc()) {
        $bookNames[] = $row['bookName'];
        $bookCounts[] = $row['count'];
    }
}

//---------------------
// Chart Queries for Activity using checkIn and checkOut
//---------------------

// DAILY: Issued (from checkIn) and Returned (from checkOut)
$dailyIssued = [];
$dailyIssuedQuery = "SELECT DATE(checkInDate) as day, COUNT(*) as count FROM checkIn GROUP BY day ORDER BY day";
$dailyIssuedResult = $conn->query($dailyIssuedQuery);
if ($dailyIssuedResult) {
    while ($row = $dailyIssuedResult->fetch_assoc()){
         $dailyIssued[$row['day']] = intval($row['count']);
    }
}
$dailyReturned = [];
$dailyReturnedQuery = "SELECT DATE(checkOutDate) as day, COUNT(*) as count FROM checkOut GROUP BY day ORDER BY day";
$dailyReturnedResult = $conn->query($dailyReturnedQuery);
if ($dailyReturnedResult) {
    while ($row = $dailyReturnedResult->fetch_assoc()){
         $dailyReturned[$row['day']] = intval($row['count']);
    }
}
$dailyDays = array_unique(array_merge(array_keys($dailyIssued), array_keys($dailyReturned)));
sort($dailyDays);
$dailyIssuedCounts = [];
$dailyReturnedCounts = [];
foreach ($dailyDays as $day) {
    $dailyIssuedCounts[] = isset($dailyIssued[$day]) ? $dailyIssued[$day] : 0;
    $dailyReturnedCounts[] = isset($dailyReturned[$day]) ? $dailyReturned[$day] : 0;
}

// MONTHLY: Issued and Returned
$monthlyIssued = [];
$monthlyIssuedQuery = "SELECT DATE_FORMAT(checkInDate, '%Y-%m') as month, COUNT(*) as count FROM checkIn GROUP BY month ORDER BY month";
$monthlyIssuedResult = $conn->query($monthlyIssuedQuery);
if ($monthlyIssuedResult) {
    while ($row = $monthlyIssuedResult->fetch_assoc()){
         $monthlyIssued[$row['month']] = intval($row['count']);
    }
}
$monthlyReturned = [];
$monthlyReturnedQuery = "SELECT DATE_FORMAT(checkOutDate, '%Y-%m') as month, COUNT(*) as count FROM checkOut GROUP BY month ORDER BY month";
$monthlyReturnedResult = $conn->query($monthlyReturnedQuery);
if ($monthlyReturnedResult) {
    while ($row = $monthlyReturnedResult->fetch_assoc()){
         $monthlyReturned[$row['month']] = intval($row['count']);
    }
}
$monthlyMonths = array_unique(array_merge(array_keys($monthlyIssued), array_keys($monthlyReturned)));
sort($monthlyMonths);
$monthlyIssuedCounts = [];
$monthlyReturnedCounts = [];
foreach ($monthlyMonths as $month) {
    $monthlyIssuedCounts[] = isset($monthlyIssued[$month]) ? $monthlyIssued[$month] : 0;
    $monthlyReturnedCounts[] = isset($monthlyReturned[$month]) ? $monthlyReturned[$month] : 0;
}

// YEARLY: Issued and Returned
$yearlyIssued = [];
$yearlyIssuedQuery = "SELECT YEAR(checkInDate) as year, COUNT(*) as count FROM checkIn GROUP BY year ORDER BY year";
$yearlyIssuedResult = $conn->query($yearlyIssuedQuery);
if ($yearlyIssuedResult) {
    while ($row = $yearlyIssuedResult->fetch_assoc()){
         $yearlyIssued[$row['year']] = intval($row['count']);
    }
}
$yearlyReturned = [];
$yearlyReturnedQuery = "SELECT YEAR(checkOutDate) as year, COUNT(*) as count FROM checkOut GROUP BY year ORDER BY year";
$yearlyReturnedResult = $conn->query($yearlyReturnedQuery);
if ($yearlyReturnedResult) {
    while ($row = $yearlyReturnedResult->fetch_assoc()){
         $yearlyReturned[$row['year']] = intval($row['count']);
    }
}
$yearlyYears = array_unique(array_merge(array_keys($yearlyIssued), array_keys($yearlyReturned)));
sort($yearlyYears);
$yearlyIssuedCounts = [];
$yearlyReturnedCounts = [];
foreach ($yearlyYears as $year) {
    $yearlyIssuedCounts[] = isset($yearlyIssued[$year]) ? $yearlyIssued[$year] : 0;
    $yearlyReturnedCounts[] = isset($yearlyReturned[$year]) ? $yearlyReturned[$year] : 0;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Statistics - Room to Read</title>
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
    /* Navigation Buttons Below Header */
    .nav-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      background: #e9ecef;
      padding: 10px 0;
    }
    .nav-buttons button {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      background: #007bff;
      color: white;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .nav-buttons button:hover {
      background: #0056b3;
    }
    .container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 20px;
    }
    .cards {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 20px;
      justify-content: center;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
      flex: 1 1 200px;
      text-align: center;
    }
    .card h3 {
      margin: 0;
      font-size: 24px;
      color: #333;
    }
    .card p {
      font-size: 18px;
      color: #666;
      margin-top: 5px;
    }
    .charts {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .chart-container {
      background: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
      flex: 1 1 300px;
      min-width: 300px;
    }
    .chart-container h4 {
      text-align: center;
      margin-bottom: 10px;
      font-size: 18px;
      color: #333;
    }
    /* Enlarge the first two charts */
    #categoryChart, #bookCountChart {
      height: 350px !important;
    }
    canvas {
      width: 100% !important;
      height: 250px !important;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="header">
    <span>Room to Read</span>
    <div>
      <a href="logout.php">Logout</a>
    </div>
  </div>
  
  <!-- Navigation Buttons -->
  <div class="nav-buttons">
    <button onclick="window.location.href='adminCheckIn.php'">Issue Book</button>
    <button onclick="window.location.href='adminCheckOut.php'">Take Back Book</button>
    <button onclick="window.location.href='addBook.php'">Add Book</button>
    <button onclick="window.location.href='modifyBook.php'">Modify Book</button>
    <button onclick="window.location.href='adminActivities.php'">View Activities</button>
  </div>
  
  <div class="container">
    <!-- Cards Section -->
    <div class="cards">
      <div class="card">
        <h3><?= $totalAdmins; ?></h3>
        <p>Total Admins</p>
      </div>
      <div class="card">
        <h3><?= $totalStudents; ?></h3>
        <p>Total Students</p>
      </div>
      <div class="card">
        <h3><?= $totalTeachers; ?></h3>
        <p>Total Teachers</p>
      </div>
      <div class="card">
        <h3><?= $totalBooks; ?></h3>
        <p>Total Books</p>
      </div>
      <div class="card">
        <h3><?= $genreCount; ?></h3>
        <p>Genres</p>
      </div>
      <div class="card">
        <h3><?= $totalCopies; ?></h3>
        <p>Total Copies</p>
      </div>
    </div>
    
    <!-- Charts Section -->
    <div class="charts">
      <div class="chart-container">
        <h4>Books by Category</h4>
        <canvas id="categoryChart" style="height:350px !important;"></canvas>
      </div>
      <div class="chart-container">
        <h4>Borrow Count per Book</h4>
        <canvas id="bookCountChart" style="height:350px !important;"></canvas>
      </div>
      <div class="chart-container">
        <h4>Daily Activity (Issued vs Returned)</h4>
        <canvas id="dailyActivityChart"></canvas>
      </div>
      <div class="chart-container">
        <h4>Monthly Activity (Issued vs Returned)</h4>
        <canvas id="monthlyActivityChart"></canvas>
      </div>
      <div class="chart-container">
        <h4>Yearly Activity (Issued vs Returned)</h4>
        <canvas id="yearlyActivityChart"></canvas>
      </div>
    </div>
  </div>
  
  <script>
    // Books by Category Chart (Pie)
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($categories); ?>,
            datasets: [{
                data: <?php echo json_encode($categoryCounts); ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Borrow Count per Book Chart (Bar)
    const bookCountCtx = document.getElementById('bookCountChart').getContext('2d');
    new Chart(bookCountCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($bookNames); ?>,
            datasets: [{
                label: 'Borrow Count',
                data: <?php echo json_encode($bookCounts); ?>,
                backgroundColor: '#36A2EB'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Daily Activity Chart (Bar with Stacked Bars)
    const dailyCtx = document.getElementById('dailyActivityChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dailyDays); ?>,
            datasets: [{
                label: 'Issued',
                data: <?php echo json_encode($dailyIssuedCounts); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.8)'
            },
            {
                label: 'Returned',
                data: <?php echo json_encode($dailyReturnedCounts); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.8)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    // Monthly Activity Chart (Bar with Stacked Bars)
    const monthlyCtx = document.getElementById('monthlyActivityChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($monthlyMonths); ?>,
            datasets: [{
                label: 'Issued',
                data: <?php echo json_encode($monthlyIssuedCounts); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.8)'
            },
            {
                label: 'Returned',
                data: <?php echo json_encode($monthlyReturnedCounts); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.8)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });

    // Yearly Activity Chart (Bar with Stacked Bars)
    const yearlyCtx = document.getElementById('yearlyActivityChart').getContext('2d');
    new Chart(yearlyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($yearlyYears); ?>,
            datasets: [{
                label: 'Issued',
                data: <?php echo json_encode($yearlyIssuedCounts); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.8)'
            },
            {
                label: 'Returned',
                data: <?php echo json_encode($yearlyReturnedCounts); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.8)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            }
        }
    });
  </script>
</body>
</html>
