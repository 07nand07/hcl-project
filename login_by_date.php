<?php
session_start();
include 'config.php'; // Include your database connection file

// Set the timezone to India/Kolkata
date_default_timezone_set('Asia/Kolkata');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: snippet.html"); // Redirect to login page if not logged in
    exit();
}

// Initialize variables
$date_selected = null;
$login_data = [];
$login_count = 0;

// Check if a date is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_date'])) {
    $date_selected = $_POST['login_date'];

    // Fetch login activity for the specified date
    // Assumes the 'timestamp' in database is stored in UTC
    $query = "SELECT email, CONVERT_TZ(timestamp, '+00:00', '+05:30') AS timestamp_ist FROM user_activity WHERE action = 'login_success' AND DATE(CONVERT_TZ(timestamp, '+00:00', '+05:30')) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date_selected);
    $stmt->execute();
    $result = $stmt->get_result();

    // Collect the data
    $login_data = $result->fetch_all(MYSQLI_ASSOC);
    $login_count = $result->num_rows;
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logins by Date</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('bg.png');
            background-color: White; /* Light background color */
        }
        .container {
            margin-top: 50px;
            padding: 40px;
            background: white; /* White background */
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2.dashboard-title {
            color:  #00796b;
            margin-bottom: 30px;
            font-weight: bold;
            text-align: center;
        }
        .date-form {
            margin-bottom: 30px;
        }
        .login-details {
            margin-top: 30px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse; /* Ensures borders are merged */
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6; /* Light border for table cells */
        }
        th {
            background-color:  #00796b; /* Header background color */
            color: #00796b ; /* Header text color */
        }
        tr:nth-child(even) {
            background-color: #f2f2f2; /* Alternating row color */
        }
        tr:hover {
            background-color: #e0e0e0; /* Row hover color */
        }
        .btn-primary {
            background-color: #00796b; /* Button background color */
            border: none;
        }
        .btn-primary:hover {
            background-color:  #005b4d; /* Button hover color */
        }
        .back-btn {
            margin-top: 2px;
            background-color: #009688; /* Teal background for button */
            color: white; /* White text */
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="dashboard-title">Logins by Date</h2>
    <div class="text-center">
        <a href="dashboard.php">
            <button class="back-btn">Back to Dashboard</button><br><br>
        </a>
    </div>

    <!-- Date Selection Form -->
    <form method="POST" action="login_by_date.php" class="date-form">
        <div class="form-group text-center">
            <label for="login_date" class="form-label"><b>Select Date:</b></label>
            <div class="row justify-content-center">
                <div class="col-sm-6 col-md-4">
                    <input type="date" id="login_date" name="login_date" class="form-control" required>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary btn-lg">View Logins</button>
        </div>
    </form>

    <!-- Display login details if date is selected -->
    <?php if ($date_selected): ?>
        <div class="login-details">
            <h4 class="text-center mt-4">Login Activity for <?php echo htmlspecialchars($date_selected); ?>:</h4>
            <p class="text-center">Total logins: <strong><?php echo $login_count; ?></strong></p>

            <?php if ($login_count > 0): ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Login Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($login_data as $login): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($login['email']); ?></td>
                                <td><?php echo date('d-m-Y H:i:s', strtotime($login['timestamp_ist'])); // Convert to IST format ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No logins found for the selected date.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<script type='text/javascript' src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
