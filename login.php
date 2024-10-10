<?php
session_start();
include 'config.php'; // Include your database connection file

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check if the email exists
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the email is not found
    if ($result->num_rows == 0) {
        header("Location: user_not_registered.php"); // Redirect to the user_not_registered page
        exit();
    } else {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Check if password matches
        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $user['email']; // Set the session for the logged-in user
            header("Location: dashboard.php"); // Redirect to dashboard on successful login
        } else {
            // Style the incorrect password message and add a "Go back" button
            echo "
            <html lang='en'>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1'>
                <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css' rel='stylesheet'>
                <title>Login Error</title>
                <style>
                    body {
                        background-color: #f8f9fa;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                    }
                    .container {
                        text-align: center;
                    }
                    .alert {
                        background-color: #f44336;
                        color: white;
                        padding: 20px;
                        border-radius: 5px;
                        font-size: 18px;
                        margin-bottom: 20px;
                    }
                    .btn {
                        background-color: #007bff;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 5px;
                    }
                    .btn:hover {
                        background-color: #0056b3;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='alert'>
                        <strong>Error:</strong> Incorrect password. Please try again.
                    </div>
                    <a href='snippet.html'>
                        <button class='btn'>Go Back to Login Page</button>
                    </a>
                </div>
            </body>
            </html>";
        }
    }
}
?>
