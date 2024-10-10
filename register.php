<?php
// Include database connection
include('config.php'); // Make sure config.php contains $conn for database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Error flag
    $error = false;
    $errorMessage = '';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $errorMessage = "Invalid email format.";
    }

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        $error = true;
        $errorMessage = "Passwords do not match.";
    }

    // If no errors, proceed to register the user
    if (!$error) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL query to insert data into the users table
        $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashed_password);

        // Check if the query executes successfully
        if ($stmt->execute()) {
            // Registration successful, redirect to success page
            header("Location: success.php");
            exit();
        } else {
            // If there's an issue with the query (e.g. duplicate email)
            $errorMessage = "Error: Could not register. Try again or use a different email.";
        }

        // Close the statement
        $stmt->close();
    }

    // Close the database connection
    $conn->close();

    // If there was an error, show the error message on the registration page
    if ($error) {
        header("Location: register.php?error=" . urlencode($errorMessage));
        exit();
    }
}
?>
