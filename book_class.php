<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM memberships WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()");

$stmt->execute([$user_id]);

$membership = $stmt->fetch();

if (!$membership) {
    $_SESSION['redirect_after_membership'] = 'book_class.php';
    header("Location: buy_membership.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule_id'])) {
    $schedule_id = $_POST['schedule_id'];
    

    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? AND schedule_id = ? AND status = 'active'");
    $stmt->execute([$user_id, $schedule_id]);
    
    if ($stmt->rowCount() === 0) 
    {


        $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id = ? AND current_bookings < capacity");
        $stmt->execute([$schedule_id]);
        
        if ($stmt->rowCount() > 0) {

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO bookings (user_id, schedule_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $schedule_id]);
                
                $stmt = $pdo->prepare("UPDATE schedules SET current_bookings = current_bookings + 1 WHERE id = ?");
                $stmt->execute([$schedule_id]);
                
                $pdo->commit();
                $success = "Class booked successfully!";
                
            } catch (Exception $e) {


                $pdo->rollBack();
                $error = "Booking failed. Please try again.";
            }


        } else {
            $error = "Class is full!";
        }


    } else {
        $error = "You have already booked this class!";
    }
}


$selectedDay = isset($_GET['day']) ? $_GET['day'] : '';
$selectedClass = isset($_GET['class']) ? $_GET['class'] : '';
$selectedTrainer = isset($_GET['trainer']) ? $_GET['trainer'] : '';

$query = "
    SELECT s.*, (SELECT COUNT(*) FROM bookings b 
    WHERE b.schedule_id = s.id AND b.user_id = ? AND b.status = 'active') as is_booked
    FROM schedules s 
    WHERE s.date >= CURDATE() 
    AND s.current_bookings < s.capacity
";

$params = [$user_id];

if ($selectedDay) {
    $query .= " AND LOWER(DAYNAME(s.date)) = ?";
    $params[] = $selectedDay;
}
if ($selectedClass) {
    $query .= " AND s.class_name = ?";
    $params[] = $selectedClass;
}
if ($selectedTrainer) {
    $query .= " AND s.trainer = ?";
    $params[] = $selectedTrainer;
}

$query .= " ORDER BY s.date, s.time";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$schedules = $stmt->fetchAll();



$trainers = $pdo->query("SELECT DISTINCT trainer FROM schedules ORDER BY trainer")->fetchAll(PDO::FETCH_COLUMN);
$classes = $pdo->query("SELECT DISTINCT class_name FROM schedules ORDER BY class_name")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Book Class - Fitness Gym</title>


    <style>


        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(90deg, #1f1c2c, #928dab);
            color: #e0e0e0;
            
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color:  #00ffd5;
            margin-bottom: 2rem;
            font-weight: bold;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color:  #00ffd5;
            font-weight: bold;
        }

        .nav-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 1rem 0;
        }

        .nav-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            color:  #00ffd5;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }



        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 40px;
        }
        

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 180px;
        }

        
        .filter-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color:  #00ffd5;
        }

        
        .filter-group select {
            padding: 10px;
            border: none;
            border-radius: 6px;
            background:linear-gradient(135deg, rgba(31, 28, 44, 0.9), rgba(146, 141, 171, 0.9));
            color:#fff;
            outline: none;
            transition: background 0.3s;
        }

        .filter-group select:focus {
        background-color: #2c2c2c;
        }



        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }


        .class-card {
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }


        .class-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
        }


        .class-card h3 {
            color:  #00ffd5;
            margin-bottom: 10px;
        }


        .class-info p {
            margin: 6px 0;
        }


        .book-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 16px;
            background-color: #00ffd5;
            color: #000000;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s ease;
            font-weight: bold;
        }


        .book-btn:hover {
            background-color:  #0b5b63;;
        }

        
        .book-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }

        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            font-weight: bold;
        }
        
        .spots-left {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: bold;
            background: #d4edda;
            color: #155724;
        }


        .low-spots {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: bold;
            background: #fff3cd;
            color: #856404;
        }
        
        .lasses {
            text-align: center;
            padding: 2rem;
            color: #666;
            font-size: 1.2rem;
        }


        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            .filter-group {
                min-width: 100%;
            }


        }


    </style>

</head>


<body>


    <div class="container">

        <div class="header">
            
            <h1>Book a Class</h1>

            <p>Choose from our available fitness classes</p>

        </div>

        <div class="nav-container">

            <a href="dashboard.php" class="nav-btn">← Back to Dashboard</a>

            <a href="schedule.php" class="nav-btn">View Schedule</a>

        </div>

        <div class="main-content">

            <?php if (isset($success)): ?>

                <div class="message success"><?php echo $success; ?></div>

            <?php endif; ?>
            
            <?php if (isset($error)): ?>

                <div class="message error"><?php echo $error; ?></div>

            <?php endif; ?>


            <div class="filters">

                <div class="filter-group">

                    <label for="trainer">Trainer</label>

                    <select id="trainer" name="trainer" onchange="updateFilters()">

                        <option value="">All Trainers</option>

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

                    <select id="class" name="class" onchange="updateFilters()">

                        <option value="">All Classes</option>

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


                    <select id="day" name="day" onchange="updateFilters()">

                        <option value="">All Days</option>
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

            <?php if (empty($schedules)): ?>

                <div class="no-classes">

                    <p>No classes available matching your criteria.</p>

                    <p>Try adjusting your filters or check back later for new classes.</p>

                </div>

            <?php else: ?>

                <div class="schedule-grid">

                    <?php foreach ($schedules as $schedule): ?>

                        <div class="class-card">

                            <h3><?php echo htmlspecialchars($schedule['class_name']); ?></h3>

                            <div class="class-info">

                                <p><strong>Trainer:</strong> <?php echo htmlspecialchars($schedule['trainer']); ?></p>

                                <p><strong>Date:</strong> <?php echo date('l, F j', strtotime($schedule['date'])); ?></p>

                                <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($schedule['time'])); ?></p>

                                <p>
                                    <strong>Availability:</strong> 

                                    <span class="<?php echo ($schedule['capacity'] - $schedule['current_bookings'] <= 3) ? 'low-spots' : 'spots-left'; ?>">
                                        <?php echo ($schedule['capacity'] - $schedule['current_bookings']); ?> spots left

                                    </span>
                                </p>

                            </div>

                            <form method="POST" action="">

                                <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">

                                <button type="submit" class="book-btn" <?php echo $schedule['is_booked'] ? 'disabled' : ''; ?>>

                                    <?php echo $schedule['is_booked'] ? 'Already Booked' : 'Book Now'; ?>

                                </button>

                            </form>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>


    <script>

    function updateFilters()
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
</body>
</html>
