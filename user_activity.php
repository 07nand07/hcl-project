<?php
session_start(); // Start session to track logged-in users

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: snippet.html"); // Redirect to login page if not logged in
    exit();
}

// Database connection (replace with your own connection code)
$conn = new mysqli('localhost', 'root', '', 'user_login_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user activity report
$stmt = $conn->prepare("SELECT email, action, timestamp FROM user_activity ORDER BY timestamp DESC");
$stmt->execute();
$result = $stmt->get_result();

// Fetch count of users logged in today
$today = date('Y-m-d'); // Get current date in Y-m-d format
$countStmt = $conn->prepare("SELECT COUNT(DISTINCT email) AS user_count FROM user_activity WHERE DATE(timestamp) = ?");
$countStmt->bind_param("s", $today);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$userCount = $countRow['user_count'];

?>

<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>User Activity Report</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8; /* Light background color */
        }
        .container {
            margin-top: 30px;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            color: #00796b; /* Teal color for title */
            text-align: center; /* Center align the title */
        }
        table {
            margin-top: 20px;
            width: 100%;
        }
        th {
            background-color: #00796b; /* Teal header background color */
            color: black; /* Header text color */
            text-align: center; /* Center align header text */
        }
        td {
            text-align: center; /* Center align data text */
            vertical-align: middle;
        }
        .back-btn {
            margin-top: 20px;
            background-color: #009688; /* Teal background for button */
            color: black; /* White text */
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #00796b; /* Darker teal on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Activity Report</h2>
        
        <!-- Display count of users logged in today -->
        <h4 class="text-center">Number of users logged in today: <?php echo $userCount; ?></h4>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Action</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['action']); ?></td>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No activity records found.</p>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="text-center">
            <a href="dashboard.php">
                <button class="back-btn">Back to Dashboard</button>
            </a>
        </div>
    </div>

    <script type='text/javascript' src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
