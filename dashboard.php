<?php
session_start();

require_once 'database.php';

if (!isset($_SESSION['user_id'])) 
{
    header("Location: login.php");

    exit();
}

$user_id = $_SESSION['user_id'];

$role = $_SESSION['role'];


$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");

$stmt->execute([$user_id]);

$user = $stmt->fetch();


$stmt = $pdo->prepare("SELECT * FROM memberships WHERE user_id = ? AND status = 'active' ORDER BY end_date DESC LIMIT 1");

$stmt->execute([$user_id]);

$membership = $stmt->fetch();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Fitness Gym</title>

    <link rel="icon" type="image/jpeg" href="img/LOGO.jpg">

    <link rel="stylesheet" href="dashboard.css">

    
</head>

</head>

<body>
    <nav class="navbar">

        <div class="navbar-content">

            <div class="user-info">

                <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>

                <span class="status <?php echo $membership ? 'status-active' : 'status-inactive'; ?>">

                    <?php echo $membership ? 'Active Member' : 'Not a Member'; ?>

                </span>

            </div>

            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

    </nav>

    <div class="container">

        <div class="dashboard-header">

            <h1><?php echo $role === 'admin' ? 'Admin Dashboard' : 'Member Dashboard'; ?></h1>


        </div>

        <?php if ($role === 'admin'): ?>

        <div class="quick-actions">

            <a href="manage_schedules.php" class="btn">Manage Class Schedule</a>

            <a href="manage_members.php" class="btn">Manage Members</a>

        </div>
        
        <div class="grid">

            <div class="card">

                <h2>Recent Bookings</h2>

                <table>
                    
                    <thead>

                        <tr>
                            
                            <th>User</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php
                        $stmt = $pdo->query("
                            SELECT b.*, u.username, s.class_name, s.date 
                            FROM bookings b 
                            JOIN users u ON b.user_id = u.id 
                            JOIN schedules s ON b.schedule_id = s.id 
                            ORDER BY b.booking_date DESC LIMIT 5
                        ");
                        while ($booking = $stmt->fetch()): ?>

                        <tr>
                            <td><?php echo htmlspecialchars($booking['username']); ?></td>
                            
                            <td><?php echo htmlspecialchars($booking['class_name']); ?></td>

                            <td><?php echo htmlspecialchars($booking['date']); ?></td>

                            <td>
                                <span class="status <?php echo $booking['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">

                                    <?php echo ucfirst($booking['status']); ?>

                                </span>

                            </td>

                        </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>
            
            <div class="card">

                <h2>Class Statistics</h2>

                <div class="card-content">

                    <?php
                    $stats = $pdo->query("
                        SELECT 
                            COUNT(*) as total_classes,
                            SUM(current_bookings) as total_bookings,
                            COUNT(DISTINCT trainer) as total_trainers
                        FROM schedules 
                        WHERE date >= CURDATE()
                    ")->fetch();
                    ?>

                    <p><strong>Total Classes:</strong> <?php echo $stats['total_classes']; ?></p>

                    <p><strong>Total Bookings:</strong> <?php echo $stats['total_bookings']; ?></p>

                    <p><strong>Active Trainers:</strong> <?php echo $stats['total_trainers']; ?></p>

                </div>

            </div>

        </div>
        

        <?php else: ?>


        <div class="quick-actions">

            <a href="book_class.php" class="btn">Book a Class</a>

            <?php if (!$membership): ?>

            <a href="buy_membership.php" class="btn">Get Membership</a>

            <?php endif; ?>

        </div>


        <div class="grid">

            <div class="card">

                <h2>My Membership</h2>

                <div class="card-content">

                    <?php if ($membership): ?>

                        <p><strong>Type:</strong> <?php echo htmlspecialchars($membership['type']); ?></p>

                        <p><strong>Valid until:</strong> <?php echo htmlspecialchars($membership['end_date']); ?></p>

                        <p><strong>Status:</strong> 

                            <span class="status status-active">Active</span>
                        </p>

                    <?php else: ?>


                        <p>You don't have an active membership.</p>

                        <a href="buy_membership.php" class="btn">Get Started</a>

                    <?php endif; ?>
                    
                </div>

            </div>

            <div class="card">

                <h2>My Upcoming Classes</h2>

                <table>

                    <thead>

                        <tr>

                            <th>Class</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Trainer</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $stmt = $pdo->prepare("
                            SELECT s.* 
                            FROM schedules s 
                            JOIN bookings b ON s.id = b.schedule_id 
                            WHERE b.user_id = ? AND b.status = 'active' 
                            AND s.date >= CURDATE()
                            ORDER BY s.date, s.time LIMIT 5
                        ");

                        
                        $stmt->execute([$user_id]);
                        while ($class = $stmt->fetch()): ?>

                        <tr>
                            <td><?php echo htmlspecialchars($class['class_name']); ?></td>

                            <td><?php echo htmlspecialchars($class['date']); ?></td>

                            <td><?php echo date('g:i A', strtotime($class['time'])); ?></td>

                            <td><?php echo htmlspecialchars($class['trainer']); ?></td>

                        </tr>
                        <?php endwhile; ?>

                    </tbody>

                </table>

                <a href="book_class.php" class="btn" style="margin-top: 1rem;">Book More Classes</a>

            </div>
        </div>

        <?php endif; ?>
    </div>
    
</body>
</html>
