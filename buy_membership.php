<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST")
 {
    $type = $_POST['membership_type'];

    $membership_info = $pdo->prepare("SELECT * FROM membership_types WHERE id = ?");

    $membership_info->execute([$type]);

    $membership = $membership_info->fetch();

    $stmt = $pdo->prepare("SELECT * FROM memberships WHERE user_id = ? AND status = 'active'");

    $stmt->execute([$user_id]);

    $active_membership = $stmt->fetch();

    if ($active_membership)

     {
        $error = "You already have an active membership.";

    } else 
    
    {
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$membership['duration']} days"));

        $stmt = $pdo->prepare("INSERT INTO memberships (user_id, type, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$user_id, $membership['name'], $start_date, $end_date]);
        $success = "Membership purchased successfully!";
        header("refresh:2;url=dashboard.php");
    }
}

$stmt = $pdo->prepare("SELECT * FROM memberships WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$active_membership = $stmt->fetch();

$membership_types = $pdo->query("SELECT * FROM membership_types ORDER BY price ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buy Membership</title>

    <style>

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(120deg, #1f1c2c, #928dab);
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 2rem;
        }

        h1 {
            text-align: center;
            font-size: 2.8rem;
            color: #00ffd5;
            margin-bottom: 1rem;
        }

        .membership-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 2rem;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.4);
            padding: 1.8rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h2 {
            font-size: 1.4rem;
            margin-bottom: 10px;
            color: #00ffd5;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
            color: #00ffd5;
            margin-bottom: 10px;
        }

        .duration {
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .features {
            list-style: none;
            padding-left: 0;
            margin-bottom: 1.5rem;
        }

        .features li {
            margin: 8px 0;
            color: #eee;
            position: relative;
            padding-left: 24px;
        }

        .features li::before {
            content: "✔";
            position: absolute;
            left: 0;
            color: #00ff99;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            padding: 0.7rem 1.2rem;
            background-color: #00ffd5;
            color: #000;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .btn:disabled {
            background-color: #aaa;
            cursor: not-allowed;
        }

        .message {
            padding: 1rem;
            margin: 1rem auto;
            max-width: 600px;
            text-align: center;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .nav-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #00ffd5;
            text-decoration: none;
            font-weight: bold;
        }


    </style>

</head>

<body>

<div class="container">
    <a href="dashboard.php" class="nav-link">← Back to Dashboard</a>

    <h1>Choose Your Membership Plan</h1>

    <?php if (isset($success)): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($active_membership): ?>
        <div class="message error">
            You already have an active membership valid until <?php echo $active_membership['end_date']; ?>.
        </div>
    <?php endif; ?>

    <div class="membership-grid">
        <?php foreach ($membership_types as $type): ?>
            <div class="card">
                <h2><?php echo htmlspecialchars($type['name']); ?></h2>
                <div class="price">LKR<?php echo number_format($type['price'], 0); ?></div>
                <div class="duration"><?php echo $type['duration']; ?> Days</div>
                <ul class="features">
                    <?php foreach (explode("\n", $type['description']) as $feature): ?>
                        <li><?php echo htmlspecialchars(trim($feature)); ?></li>
                    <?php endforeach; ?>
                </ul>
                <form method="POST">
                    <input type="hidden" name="membership_type" value="<?php echo $type['id']; ?>">
                    <button class="btn" <?php echo $active_membership ? 'disabled' : ''; ?>>
                        <?php echo $active_membership ? 'Already a Member' : 'Select Plan'; ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
