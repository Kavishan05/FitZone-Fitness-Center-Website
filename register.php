<?php
session_start();
require_once 'database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Username or email already exists!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $role]);
                
                $success = "Registration successful! Please login.";
            }
        } catch(PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Registration - Fitness Gym</title>
    <link rel="icon" type="image/jpeg" href="img/LOGO.jpg">
    <link rel="stylesheet" href="login.css">


    <style>

        
        .container {
            background: linear-gradient(135deg, rgba(31, 28, 44, 0.9), rgba(146, 141, 171, 0.9));
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            margin: 60px auto 0;
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #00ffd5;
            
            
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #00ffd5;
        }

        input, select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        button {
            width: 100%;
            padding: 1rem;
            background: #00ffd5;
            color: black;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background:rgb(10, 86, 118);
        }

        .links {
            text-align: center;
            margin-top: 1rem;
        }
        
        .links a {
            color: #00ffd5;
            text-decoration: none;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }

        .success {
            color: green;
            text-align: center;
            margin-bottom: 1rem;
        }

    </style>

    
</head>

<body>
    <nav class="navbackground">

        <div class="navbar">

            <ul class="navlinks">

                <li><a href="Home.php">Home</a></li>
                <li><a href="AboutUs.html">About Us</a></li>
                <li><a href="Services.html">Services</a></li>
                <li><a href="Schedule.php">Schedule</a></li>
                <li><a href="Packages.html">Packages</a></li>
                <li><a href="Contact.html">Contact Us</a></li>
                <li><a href="login.php" class="login-btn">Login</a></li>

            </ul>
        </div>
    </nav>
<br>


<div class="container">

        <h1>Create Account</h1>


        <?php if (isset($error)): ?>

            <div class="error"><?php echo $error; ?></div>

        <?php endif; ?>

        <?php if (isset($success)): ?>

            <div class="success"><?php echo $success; ?></div>

        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">

                <label for="role">Select Role</label>

                <select name="role" id="role" required>

                    <option value="user">User</option>

                    <option value="admin">Admin</option>

                </select>

            </div>

            <div class="form-group">

                <label for="username">Username</label>

                <input type="text" name="username" id="username" required>

            </div>

            <div class="form-group">

                <label for="email">Email:</label>

                <input type="email" name="email" id="email" required>

            </div>

            <div class="form-group">

                <label for="password">Password</label>

                <input type="password" name="password" id="password" required>

            </div>

            <div class="form-group">


                <label for="confirm_password">Confirm Password</label>

                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>

            <button type="submit">Register</button>

        </form>

        <div class="links">

            <p>Already have an account? <a href="login.php">Login</a></p>

        </div>

    </div>

</body>

</html>




