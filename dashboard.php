<?php
session_start();
include 'config.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: snippet.html"); // Redirect to login page if not logged in
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('background.jpg') no-repeat center center fixed; /* Add your background image */
            background-size: cover; /* Cover the entire page */
        }
        .container {
            margin-top: 100px; /* Adjust margin to position container */
            padding: 20px;
            background: transparent; /* Remove white background */
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2.dashboard-title {
            margin-bottom: 10px;
            color: #FF5722; /* Change to desired color for the title */
        }
        h3 {
            color: #00796b; /* Change to desired color for the greeting */
        }
        .email {
            margin-bottom: 20px;
            color: white; /* Change color to white */
            font-size: 18px; /* Slightly larger font size */
        }
        .options {
            margin-top: 20px;
            display: flex;
            justify-content: center; /* Center the buttons */
            flex-direction: column;
            align-items: center;
        }
        .option-btn {
            width: 200px; /* Set a width for buttons */
            padding: 10px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            background-color: #009688; /* Teal background for buttons */
            color: white; /* White text */
            font-size: 16px; /* Font size */
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s; /* Add transition for lifting effect */
        }
        .option-btn:hover {
            background-color: #00796b; /* Darker teal on hover */
            transform: translateY(-2px); /* Lift effect on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="dashboard-title">DASHBOARD</h2>
        <h3><i>hello!</i></h3>
        
        <div class="email">You are logged in as: <?php echo htmlspecialchars($_SESSION['email']); ?></div>

        <div class="options">
            <a href="login_report.php">
                <button class="option-btn">View Login Report</button>
            </a>
            <a href="user_activity.php">
                <button class="option-btn">View User Activity</button>
            </a>
            <a href="user_activity_chart.php">
                <button class="option-btn">View User Activity Chart</button>
            </a>
            <a href="login_by_date.php">
                <button class="option-btn">View Logins by Date</button>
            </a>
            <a href="logout.php">
                <button class="option-btn">Logout</button>
            </a>
        </div>
    </div>

    <script type='text/javascript' src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>
