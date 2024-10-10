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

// Fetch user activity count per day for the last 7 days
$activityData = [];
$dates = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = $date;

    // Prepare statement to count logins for that date
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT email) AS user_count FROM user_activity WHERE DATE(timestamp) = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $activityData[$date] = $row['user_count'];
}

// Prepare data for chart
$labels = array_reverse($dates);
$counts = array_reverse(array_map(function ($date) use ($activityData) {
    return $activityData[$date] ?? 0;
}, $dates));
?>

<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>User Activity Chart</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        #userActivityChart {
            width: 80vw; /* Set width to 80% of the viewport */
            height: 40vh; /* Set height to 40% of the viewport */
            max-width: 600px; /* Max width for larger screens */
            max-height: 300px; /* Max height for larger screens */
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
        <h2>User Activity Chart</h2>
        <canvas id="userActivityChart"></canvas>

        <!-- Back Button -->
        <div class="text-center">
            <a href="dashboard.php">
                <button class="back-btn">Back to Dashboard</button>
            </a>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('userActivityChart').getContext('2d');
        const userActivityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Number of Users Logged In',
                    data: <?php echo json_encode($counts); ?>,
                    backgroundColor: 'rgba(0, 121, 107, 0.6)',
                    borderColor: 'rgba(0, 121, 107, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: false, // Disable responsiveness to allow fixed size
                maintainAspectRatio: false, // Allows you to set custom width and height
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Users'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
