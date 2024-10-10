<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Updated</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
        }
        .success-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success-container h1 {
            color: #28a745;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .success-container p {
            font-size: 1.2rem;
            color: #333;
        }
        .btn {
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <h1>Password Updated Successfully!</h1>
        <p>Your password has been reset. You can now log in with your new password.</p>
        <a href="snippet.html"><button class="btn">Go to Login</button></a>
    </div>
</body>
</html>
