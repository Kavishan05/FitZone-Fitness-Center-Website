<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST")
 {
    if (isset($_POST['action'])) 
    {
        if ($_POST['action'] === 'add') 
        {
            $stmt = $pdo->prepare("INSERT INTO schedules (class_name, trainer, date, time, capacity) VALUES (?, ?, ?, ?, ?)");

            $stmt->execute([

                $_POST['class_name'],
                $_POST['trainer'],
                $_POST['date'],
                $_POST['time'],
                $_POST['capacity']

            ]);
        } elseif ($_POST['action'] === 'delete' && isset($_POST['schedule_id'])) {

            $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");

            $stmt->execute([$_POST['schedule_id']]);

        }
    }
}




$stmt = $pdo->query("SELECT * FROM schedules WHERE date >= CURDATE() ORDER BY date, time");

$schedules = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title> Manage Schedules - Fitness Gym</title>

    <style>
       * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(90deg, #1f1c2c, #928dab);
    padding: 30px;
    color:rgb(255, 255, 255);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

h1 {
    font-size: 2.5rem;
    color: #00ffd5;
    font-weight: bold;
}

h2 {
    color:rgb(241, 240, 240);
    font-weight: bold;
}

.nav-btn {
    display: inline-block;
    padding: 12px 20px;
    background-color: #00ffd5;
    color: black;
    text-decoration: none;
    border-radius: 4px;
    font-size: 1rem;
    transition: background-color 0.3s ease;
    font-weight: bold;
}

.nav-btn:hover {
    background-color:rgb(10, 50, 93);
}

.form-container {
    background: linear-gradient(135deg, rgba(31, 28, 44, 0.9), rgba(146, 141, 171, 0.9));
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    font-size: 1rem;
    color:rgb(255, 255, 255);
}

input,
select {
    width: 100%;
    padding: 12px;
    border: 2px solid #ced4da;
    border-radius: 6px;
    font-size: 1rem;
    margin-top: 8px;
    transition: border-color 0.3s ease;
}

input:focus,
select:focus {
    border-color: #80bdff;
    outline: none;
}

button[type="submit"] {
    background-color: #00ffd5;;
    color:black;
    padding: 12px 25px;
    font-size: 1.1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-weight: bold;
}

button[type="submit"]:hover {
    background-color:rgb(10, 80, 78);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: linear-gradient(135deg, rgba(31, 28, 44, 0.9), rgba(146, 141, 171, 0.9));
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    margin-top: 30px;
}

th, td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    font-size: 1rem;
}

th {
    background: linear-gradient(135deg, rgba(31, 28, 44, 0.9), rgba(146, 141, 171, 0.9));
    font-weight: bold;
    color: #495057;
}

tbody tr:hover {
    background-color:rgb(167, 38, 38);
}



    </style>

</head>

<body>

    <div class="container">

        <div class="header">

            <h1>Manage Class Schedules</h1>

            <div>

                <a href="dashboard.php" class="nav-btn">Dashboard</a>

                <a href="logout.php" class="nav-btn">Logout</a>

            </div>

        </div>


        <div class="form-container">

            <h2>Add New Class Schedule</h2>

            <form method="POST" action="">

                <input type="hidden" name="action" value="add">

                <div class="form-group">

                    <label for="class_name">Class Name:</label>

                    <input type="text" id="class_name" name="class_name" required>

                </div>

                <div class="form-group">

                    <label for="trainer">Trainer:</label>

                    <input type="text" id="trainer" name="trainer" required>

                </div>
                <div class="form-group">

                    <label for="date">Date:</label>

                    <input type="date" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>

                </div>

                <div class="form-group">

                    <label for="time">Time:</label>

                    <input type="time" id="time" name="time" required>

                </div>

                <div class="form-group">


                    <label for="capacity">Capacity:</label>

                    <input type="number" id="capacity" name="capacity" min="1" required>

                </div>

                <button type="submit" class="btn">Add Schedule</button>

            </form>

        </div>

        <h2>Current Schedules</h2>

        <table>

            <thead>

                <tr>

                    <th>Class Name</th>
                    <th>Trainer</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Capacity</th>
                    <th>Bookings</th>
                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($schedules as $schedule): ?>

                <tr>
                    <td><?php echo htmlspecialchars($schedule['class_name']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['trainer']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['date']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['time']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['capacity']); ?></td>
                    <td><?php echo htmlspecialchars($schedule['current_bookings']); ?></td>
                    <td>
                        <form method="POST" action="" style="display: inline;">

                            <input type="hidden" name="action" value="delete">

                            <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">

                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this schedule?')">Delete</button>
                        </form>

                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>
    
</body>
</html>
