<?php
session_start();
require_once 'database.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        
        if ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            $stmt->execute([$user_id]);
        }
    }
}


$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.username,
        u.email,
        u.created_at,
        m.type as membership_type,
        m.end_date as membership_end,
        CASE WHEN m.end_date >= CURDATE() THEN 'Active' ELSE 'Inactive' END as status
    FROM users u
    LEFT JOIN memberships m ON u.id = m.user_id
    WHERE u.role = 'user'
    ORDER BY u.username
");


$stmt->execute();

$members = $stmt->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Members - Fitness Gym</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(90deg, #1f1c2c, #928dab);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: linear-gradient(135deg, rgba(31, 28, 44, 0.9), rgba(146, 141, 171, 0.9));
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #00ffd5;;;
            margin-bottom: 20px;
        }

        .nav-links {
            margin-bottom: 20px;
        }

        .nav-links a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #00ffd5;
            color:black;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            color: white;
            font-weight: bold;
            
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: linear-gradient(135deg, rgba(31, 28, 44, 0.9), rgba(146, 141, 171, 0.9));
        }

        .status {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            background-color: #dc3545;
            color: white;
        }

        .action-btn:hover {
            background-color: #c82333;
        }

    </style>

</head>

<body>

    <div class="container">
        
        <h1>Manage Members</h1>
        
        <div class="nav-links">

            <a href="dashboard.php">Back to Dashboard</a>

        </div>

        <table>

            <thead>

                <tr>
                    
                    <th>Username</th>
                    <th>Email</th>
                    <th>Join Date</th>
                    <th>Membership</th>
                    <th>Status</th>
                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($members as $member): ?>

                <tr>
                    <td><?php echo htmlspecialchars($member['username']); ?></td>

                    <td><?php echo htmlspecialchars($member['email']); ?></td>

                    <td><?php echo date('Y-m-d', strtotime($member['created_at'])); ?></td>

                    <td><?php echo $member['membership_type'] ? htmlspecialchars($member['membership_type']) : 'None'; ?></td>

                    <td>
                        <span class="status <?php echo strtolower($member['status']) === 'active' ? 'status-active' : 'status-inactive'; ?>">

                            <?php echo $member['status']; ?>

                        </span>

                    </td>

                    <td>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this member?');">

                            <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">

                            <input type="hidden" name="action" value="delete">

                            <button type="submit" class="action-btn">Delete</button>

                        </form>

                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    
</body>
</html>
