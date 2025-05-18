<?php
session_start();

require_once 'database.php';

if (isset($_SESSION['user_id']))
{
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $username = $_POST['username'];

    $password = $_POST['password'];

    $role = $_POST['role'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");

    $stmt->execute([$username, $role]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) 
    {
        $_SESSION['user_id'] = $user['id'];

        $_SESSION['role'] = $user['role'];
        
        header("Location: dashboard.php");

        exit();

    } else {
        $error = "Invalid credentials!";
    }
}
?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title> Login - Fitness Gym</title>

    <link rel="icon" type="image/jpeg" href="img/LOGO.jpg">

    <link rel="stylesheet" href="login.css">
    

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
<br>

    <div class="container">

        <h1> Welcome </h1>

        <?php if (isset($error)): ?>

            <div class="error"><?php echo $error; ?></div>

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

                <label for="password">Password</label>


                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit">Login</button>

        </form>

        <div class="links">

            <p> Don't have an account? <a href="register.php">Register</a></p>

        </div>

    </div>

</body>

</html>
