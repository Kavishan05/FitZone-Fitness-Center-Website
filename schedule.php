<?php
require_once 'database.php';



$trainers = $pdo->query("SELECT DISTINCT trainer FROM schedules ORDER BY trainer")->fetchAll(PDO::FETCH_COLUMN);

$classes = $pdo->query("SELECT DISTINCT class_name FROM schedules ORDER BY class_name")->fetchAll(PDO::FETCH_COLUMN);


$selectedTrainer = isset($_GET['trainer']) ? $_GET['trainer'] : '';

$selectedClass = isset($_GET['class']) ? $_GET['class'] : '';

$selectedDay = isset($_GET['day']) ? $_GET['day'] : '';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Fitness Gym</title>
    <link rel="icon" type="image/jpeg" href="img/LOGO.jpg">
    <link rel="stylesheet" href="schedule.css">
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


<div class="container">

        <div class="header">


            <h1>Class Schedule</h1>

        </div>

        <div class="schedule-container">

            <div class="schedule-filters">

                <div class="filter-group">

                    <label for="trainer">Trainer</label>

                    <select id="trainer" name="trainer" onchange="updateSchedule()">

                        <option value="">Select Trainer</option>

                        <?php foreach ($trainers as $trainer): ?>

                            <option value="<?php echo htmlspecialchars($trainer); ?>" 


                                <?php echo $selectedTrainer === $trainer ? 'selected' : ''; ?>>

                                <?php echo htmlspecialchars($trainer); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="filter-group">

                    <label for="class">Class Type</label>

                    <select id="class" name="class" onchange="updateSchedule()">

                        <option value=""> Select Classe</option>

                        <?php foreach ($classes as $class): ?>

                            <option value="<?php echo htmlspecialchars($class); ?>"

                                <?php echo $selectedClass === $class ? 'selected' : ''; ?>>

                                <?php echo htmlspecialchars($class); ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="filter-group">

                    <label for="day">Day</label>

                    <select id="day" name="day" onchange="updateSchedule()">

                        <option value="">Select Day</option>
                        <option value="monday" <?php echo $selectedDay === 'monday' ? 'selected' : ''; ?>>Monday</option>
                        <option value="tuesday" <?php echo $selectedDay === 'tuesday' ? 'selected' : ''; ?>>Tuesday</option>
                        <option value="wednesday" <?php echo $selectedDay === 'wednesday' ? 'selected' : ''; ?>>Wednesday</option>
                        <option value="thursday" <?php echo $selectedDay === 'thursday' ? 'selected' : ''; ?>>Thursday</option>
                        <option value="friday" <?php echo $selectedDay === 'friday' ? 'selected' : ''; ?>>Friday</option>
                        <option value="saturday" <?php echo $selectedDay === 'saturday' ? 'selected' : ''; ?>>Saturday</option>
                        <option value="sunday" <?php echo $selectedDay === 'sunday' ? 'selected' : ''; ?>>Sunday</option>

                    </select>

                </div>

            </div>

            <div class="schedule-grid">

                <?php
                $query = "SELECT * FROM schedules WHERE date >= CURDATE()";

                $params = [];

                if ($selectedTrainer) {
                    $query .= " AND trainer = ?";
                    $params[] = $selectedTrainer;
                }
                if ($selectedClass) {
                    $query .= " AND class_name = ?";
                    $params[] = $selectedClass;
                }
                if ($selectedDay) {
                    $query .= " AND LOWER(DAYNAME(date)) = ?";
                    $params[] = $selectedDay;
                }

                $query .= " ORDER BY date, time LIMIT 12";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $schedules = $stmt->fetchAll();

                if (empty($schedules)): ?>

                    <div class="no-classes">
                        <p>No classes found matching your criteria.</p>

                        <p>Try adjusting your filters or check back later for new classes.</p>

                    </div>

                <?php else:
                    foreach ($schedules as $schedule):

                        $availableSpots = $schedule['capacity'] - $schedule['current_bookings'];
                        
                        $availabilityClass = $availableSpots === 0 ? 'full' : 

                                        ($availableSpots <= 3 ? 'limited' : 'available');

                        $availabilityText = $availableSpots === 0 ? 'Class Full' : 

                                        ($availableSpots . ' spots available');

                ?>
                <div class="schedule-card">

                    <h3><?php echo htmlspecialchars($schedule['class_name']); ?></h3>

                    <div class="schedule-info">
                        <p><strong>Trainer:</strong> <?php echo htmlspecialchars($schedule['trainer']); ?></p>

                        <p><strong>Date:</strong> <?php echo date('l, F j', strtotime($schedule['date'])); ?></p>

                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($schedule['time'])); ?></p>


                    </div>

                    <div class="availability <?php echo $availabilityClass; ?>">

                        <?php echo $availabilityText; ?>

                    </div>

                    <a href="login.php" class="login-btn1">Login to Book</a>

                </div>

                <?php endforeach;

                endif; ?>

            </div>

        </div>

    </div>

    <script>

    function updateSchedule() 
    {
        const trainer = document.getElementById('trainer').value;

        const classType = document.getElementById('class').value;

        const day = document.getElementById('day').value;
        
        let url = new URL(window.location.href);

        url.searchParams.set('trainer', trainer);

        url.searchParams.set('class', classType);
        
        url.searchParams.set('day', day);
        
        window.location.href = url.toString();
    }
    </script>

<br> <br> 

<footer class="footer">

<div class="footer-content">

    <div class="footer-left">

        <p > 2025 FitZone Fitness Center. All Rights Reserved.</p>
    </div>


    <div class="footer-center">

        <p><strong>Contact Us </strong></p>
        <p>Email: <a href="mailto:FitZoneFitnessCenter@gmail.com">FitZoneFitnessCenter@gmail.com</a></p>
        <p>Phone: <a href="tel:+94769948008">0769948008</a> | <a href="tel:+94115252789">011 5252789</a></p>
        <p>Address: Colombo 04, Cofford Place, Sri Lanka</p>
    </div>


    <div class="footer-right">
        <p><strong>Follow Us </strong></p>
        <p><a href="https://facebook.com" target="_blank">Facebook</a></p>
        <p><a href="https://instagram.com" target="_blank">Instagram</a></p>
        <p><a href="https://twitter.com" target="_blank">Twitter</a></p>
    </div>


</div>

</footer>

</body>
</html>
