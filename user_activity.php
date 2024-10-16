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

// Set timezone to Asia/Kolkata for consistency
date_default_timezone_set('Asia/Kolkata');

// Pagination variables
$records_per_page = 8; // Number of records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Fetch user activity report with pagination
$stmt = $conn->prepare("SELECT email, action, timestamp FROM user_activity ORDER BY timestamp DESC LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total number of records
$total_records_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM user_activity");
$total_records_stmt->execute();
$total_records_result = $total_records_stmt->get_result();
$total_records_row = $total_records_result->fetch_assoc();
$total_records = $total_records_row['total'];

// Calculate total pages
$total_pages = ceil($total_records / $records_per_page);

// Fetch count of distinct users logged in today
$today = date('Y-m-d'); // Get current date in Y-m-d format
$countStmt = $conn->prepare("
    SELECT COUNT(DISTINCT email) AS user_count 
    FROM user_activity 
    WHERE action = 'login_success' 
    AND DATE(CONVERT_TZ(timestamp, '+00:00', '+05:30')) = ?
");
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
            background: url('bg.png');
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
            margin-top: 7px;
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
        <div class="text-center">
            <a href="dashboard.php">
                <button class="back-btn">Back to Dashboard</button>
            </a>
        </div>

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
                            <td>
                                <?php
                                // Convert the timestamp to Asia/Kolkata timezone
                                $datetime = new DateTime($row['timestamp'], new DateTimeZone('UTC')); // Assuming the stored time is in UTC
                                $datetime->setTimezone(new DateTimeZone('Asia/Kolkata')); // Convert to Asia/Kolkata timezone
                                echo $datetime->format('Y-m-d H:i:s'); // Format the output
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination controls -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <!-- Previous page button -->
                    <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="<?php if ($page > 1) echo "?page=" . ($page - 1); else echo '#'; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <!-- Page numbers -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next page button -->
                    <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                        <a class="page-link" href="<?php if ($page < $total_pages) echo "?page=" . ($page + 1); else echo '#'; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php else: ?>
            <p class="text-muted">No activity records found.</p>
        <?php endif; ?>

    </div>

    <script type='text/javascript' src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>

<?php
$stmt->close();
$countStmt->close();
$total_records_stmt->close();
$conn->close(); // Close the database connection
?>
