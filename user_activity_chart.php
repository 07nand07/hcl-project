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

// Prepare data for bar chart
$labels = array_reverse($dates);
$counts = array_reverse(array_map(function ($date) use ($activityData) {
    return $activityData[$date] ?? 0;
}, $dates));

// Fetch new user registration count for each day over the last 7 days
$newUserCounts = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    // Prepare statement to count new registrations for that date
    $stmt = $conn->prepare("SELECT COUNT(*) AS new_user_count FROM users WHERE DATE(registration_date) = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $newUserCounts[$date] = $row['new_user_count'];
}

// Prepare data for line chart
$newUserCounts = array_reverse(array_map(function ($date) use ($newUserCounts) {
    return $newUserCounts[$date] ?? 0;
}, $dates));

// Fetch login count for each user over the last 7 days
$userActivityCounts = [];
$stmt = $conn->prepare("SELECT email, COUNT(*) AS login_count FROM user_activity WHERE DATE(timestamp) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY email");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $userActivityCounts[$row['email']] = $row['login_count'];
}

// Prepare data for pie chart
$userLabels = array_keys($userActivityCounts);
$userCounts = array_values($userActivityCounts);
$colors = [
    'rgba(255, 99, 132, 0.6)', // Red
    'rgba(54, 162, 235, 0.6)', // Blue
    'rgba(255, 206, 86, 0.6)', // Yellow
    'rgba(75, 192, 192, 0.6)', // Teal
    'rgba(153, 102, 255, 0.6)', // Purple
    'rgba(255, 159, 64, 0.6)', // Orange
    'rgba(255, 0, 255, 0.6)', // Magenta
    'rgba(0, 255, 0, 0.6)', // Green
    'rgba(0, 255, 255, 0.6)', // Cyan
    'rgba(128, 0, 128, 0.6)', // Dark Purple
];

// Create an array of colors for the pie chart
$pieColors = array_slice($colors, 0, count($userLabels));

?>

<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>User Activity Charts</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Add Chart.js DataLabels plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
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
        .charts-container {
            display: flex; /* Use flexbox for side by side layout */
            justify-content: space-around; /* Space out the charts */
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
        }
        .chart {
            width: 50%; /* Set width to 50% for each chart */
            height: 60vh; /* Set height for the charts */
            max-width: 300px; /* Max width for larger screens */
            margin: 10px; /* Margin around each chart */
        }
        .back-btn {
            margin-top: 2.7px;
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
        <h2>User Activity Charts</h2>
        <div class="text-center">
            <a href="dashboard.php">
                <button class="back-btn">Back to Dashboard</button><br><br>
            </a>
        </div>

        <div class="charts-container">
            <div class="chart">
                <canvas id="userActivityChart"></canvas>
            </div>
            <div class="chart">
                <canvas id="newUserRegistrationChart"></canvas>
            </div>
            <div class="chart">
                <canvas id="userActivityPieChart"></canvas>
            </div>
        </div>

    </div>
    
    <script>
        const ctxBar = document.getElementById('userActivityChart').getContext('2d');
        const userActivityChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Number of Users Logged In',
                    data: <?php echo json_encode($counts); ?>,
                    backgroundColor: 'rgba(255, 182, 193, 0.8)', // Light pink color for the bar graph
                    borderColor: 'rgba(255, 105, 180, 1)', // Darker pink border color
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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

        const ctxLine = document.getElementById('newUserRegistrationChart').getContext('2d');
        const newUserRegistrationChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'New Users Registered',
                    data: <?php echo json_encode($newUserCounts); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Light teal color for the line graph
                    borderColor: 'rgba(75, 192, 192, 1)', // Darker teal color for the line
                    borderWidth: 2,
                    fill: true // Fill the area under the line
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of New Users'
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

        const ctxPie = document.getElementById('userActivityPieChart').getContext('2d');
        const userActivityPieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($userLabels); ?>,
                datasets: [{
                    label: 'User Logins',
                    data: <?php echo json_encode($userCounts); ?>,
                    backgroundColor: <?php echo json_encode($pieColors); ?>,
                    borderColor: 'rgba(255, 255, 255, 1)', // White border for the pie chart slices
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'User Login Distribution Over the Last 7 Days', // Add the heading here
                        font: {
                            size: 15,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    legend: {
                        display: false // Disable the legend
                    },
                    datalabels: {
                        color: '#fff', // Label color
                        formatter: (value, ctx) => {
                            let percentage = ((value / ctx.chart._metasets[0].total) * 100).toFixed(2) + '%'; // Show percentage
                            return percentage;
                        },
                        font: {
                            weight: 'bold',
                            size: 0
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    </script>
</body>
</html>
