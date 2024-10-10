<?php
session_start(); // Start session to track logged-in users

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: snippet.html"); // Redirect to login page if not logged in
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'user_login_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch login report without filtering by month or year
$stmt = $conn->prepare("
    SELECT email, COUNT(*) as login_count, MAX(timestamp) as last_login 
    FROM user_activity 
    WHERE action = 'login_success' 
    GROUP BY email
");
$stmt->execute();
$result = $stmt->get_result();

// Debugging output
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Function to count new users registered today
function countNewUsersToday($conn) {
    // Get today's date
    $today = date('Y-m-d'); // Format: YYYY-MM-DD

    // Prepare the SQL statement
    $query = "SELECT COUNT(*) AS new_users_count FROM users WHERE DATE(registration_date) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the count
    if ($row = $result->fetch_assoc()) {
        return $row['new_users_count'];
    }

    return 0; // Return 0 if there are no new users
}

// Call the function and store the count
$newUsersCount = countNewUsersToday($conn);
?>

<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Login Report</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://use.fontawesome.com/releases/v5.7.2/css/all.css' rel='stylesheet'>
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
        .new-users-count {
            font-size: 18px; /* Font size for new users count display */
            color: #FF5722; /* Color for the count */
            margin-bottom: 20px; /* Space below the count */
            text-align: center; /* Center align the new users count */
        }
        table {
            margin-top: 20px;
            width: 100%;
        }
        th, td {
            text-align: center; /* Center align both headers and data */
            vertical-align: middle;
        }
        th {
            background-color: #00796b; /* Teal header background color */
            color: black; /* Header text color */
        }
        td {
            text-align: center;
            vertical-align: middle;
        }
        .back-btn {
            margin-top: 20px;
            background-color: #009688; /* Teal background for button */
            color: white; /* White text */
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
        <h2>Login Report</h2>

        <!-- Display the count of new users registered today -->
        <div class="new-users-count">
            New users registered today: <strong><?php echo $newUsersCount; ?></strong>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Login Count</th>
                        <th>Last Login</th> <!-- New column for last login time -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo $row['login_count']; ?></td>
                            <td><?php echo $row['last_login'] ? date('Y-m-d H:i:s', strtotime($row['last_login'])) : 'Never'; ?></td> <!-- Format the last login timestamp -->
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No login records found.</p>
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
