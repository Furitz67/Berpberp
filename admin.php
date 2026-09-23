<?php
session_start();
require 'db.php';

// Security Guard: Check for login AND admin role
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied. You must be an administrator.");
}

// Fetch all registered users
$stmt = $pdo->query("SELECT id, username, role FROM Users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Electronic Red</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            color: white;
            background: radial-gradient(circle at center, #1c0c0e 0%, #0a0405 100%);
            padding: 30px 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Background decorations */
        body::before,
        body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
        }

        body::before {
            width: 300px;
            height: 300px;
            top: -130px;
            left: -120px;
            background: #ff1a40;
            opacity: 0.15;
            filter: blur(40px);
        }

        body::after {
            width: 280px;
            height: 280px;
            bottom: -120px;
            right: -100px;
            background: #b3001f;
            opacity: 0.15;
            filter: blur(40px);
        }

        .container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        /* Header */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding: 22px 25px;
            border-radius: 12px;
            background: rgba(255, 0, 51, 0.03);
            border: 1px solid rgba(255, 51, 85, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(255, 0, 51, 0.15), inset 0 0 15px rgba(255, 51, 85, 0.05);
            animation: slideDown 0.6s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-icon {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: linear-gradient(135deg, #ff1a40 0%, #b3001f 100%);
            font-size: 23px;
            box-shadow: 0 4px 15px rgba(255, 26, 64, 0.4);
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: -0.4px;
            color: #ff3355;
            text-shadow: 0 0 10px rgba(255, 51, 85, 0.3);
        }

        .subtitle {
            color: #a08a8e;
            font-size: 13px;
        }

        /* Logout */
        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 17px;
            border-radius: 6px;
            color: #ff6b6b;
            background: rgba(255, 51, 51, 0.1);
            border: 1px solid rgba(255, 51, 51, 0.3);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.25s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 51, 51, 0.2);
            border-color: rgba(255, 51, 51, 0.5);
            transform: translateY(-1px);
            box-shadow: 0 0 10px rgba(255, 51, 51, 0.2);
        }

        /* Statistics */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 22px;
        }

        .stat-card {
            padding: 20px;
            border-radius: 12px;
            background: rgba(255, 0, 51, 0.03);
            border: 1px solid rgba(255, 51, 85, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(255, 0, 51, 0.1);
            animation: fadeUp 0.6s ease;
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .stat-label {
            color: #a08a8e;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .stat-icon {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 51, 85, 0.15);
            color: #ff3355;
        }

        .stat-number {
            font-size: 26px;
            font-weight: 700;
            color: #fff;
        }

        /* Main card */
        .card {
            padding: 25px;
            border-radius: 12px;
            background: rgba(255, 0, 51, 0.03);
            border: 1px solid rgba(255, 51, 85, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(255, 0, 51, 0.15);
            animation: fadeUp 0.7s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title-icon {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 51, 85, 0.15);
            color: #ff3355;
        }

        .card h2 {
            font-size: 17px;
            font-weight: 600;
            color: #fff;
        }

        .user-count {
            padding: 6px 11px;
            border-radius: 999px;
            background: rgba(255, 51, 85, 0.15);
            color: #ff3355;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid rgba(255, 51, 85, 0.3);
        }

        /* Table */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid rgba(255, 51, 85, 0.2);
        }

        table {
            width: 100%;
            min-width: 550px;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 15px 16px;
            font-size: 13px;
        }

        th {
            color: #a08a8e;
            font-weight: 600;
            background: rgba(20, 5, 8, 0.6);
            border-bottom: 1px solid rgba(255, 51, 85, 0.2);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.7px;
        }

        tbody tr {
            border-bottom: 1px solid rgba(255, 51, 85, 0.1);
            transition: 0.2s ease;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba(255, 51, 85, 0.05);
        }

        td {
            color: #d1b3b8;
        }

        /* ID */
        .user-id {
            color: #8c7378;
            font-family: monospace;
            font-size: 12px;
        }

        /* Username */
        .username-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(255, 51, 85, 0.25), rgba(179, 0, 31, 0.15));
            color: #ff99aa;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid rgba(255, 51, 85, 0.3);
        }

        /* Role */
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .role-admin {
            background: rgba(255, 26, 64, 0.15);
            color: #ff6b81;
            border: 1px solid rgba(255, 26, 64, 0.3);
            box-shadow: 0 0 8px rgba(255, 26, 64, 0.15);
        }

        .role-user {
            background: rgba(255, 255, 255, 0.05);
            color: #a08a8e;
            border: 1px solid rgba(255, 51, 85, 0.15);
        }

        .role-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #8c7378;
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
            opacity: 0.7;
        }

        /* Footer */
        .footer {
            text-align: center;
            color: #8c7378;
            font-size: 11px;
            margin-top: 20px;
        }

        /* Responsive */
        @media (max-width: 700px) {
            body {
                padding: 20px 14px;
            }

            .top-bar {
                align-items: flex-start;
                padding: 20px;
                flex-direction: column;
            }

            .logout-btn {
                width: 100%;
                justify-content: center;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .card {
                padding: 18px;
            }

            .card-header {
                align-items: flex-start;
                gap: 10px;
                flex-direction: column;
            }

            h1 {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <!-- Header -->
    <div class="top-bar">
        <div class="welcome-section">
            <div class="admin-icon">
                🛡️
            </div>
            <div>
                <h1>
                    Welcome,
                    <?php echo htmlspecialchars($_SESSION['username']); ?>!
                </h1>
                <p class="subtitle">
                    Administrator Dashboard
                </p>
            </div>
        </div>

        <a href="logout.php" class="logout-btn">
            ↪ Log Out
        </a>
    </div>

    <!-- Statistics -->
    <?php
        $totalUsers = count($users);

        $adminCount = 0;
        $regularUsers = 0;

        foreach ($users as $user) {
            if ($user['role'] === 'admin') {
                $adminCount++;
            } else {
                $regularUsers++;
            }
        }
    ?>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">
                    Total Users
                </span>
                <div class="stat-icon">
                    👥
                </div>
            </div>
            <div class="stat-number">
                <?php echo $totalUsers; ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">
                    Administrators
                </span>
                <div class="stat-icon">
                    🛡️
                </div>
            </div>
            <div class="stat-number">
                <?php echo $adminCount; ?>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-label">
                    Regular Users
                </span>
                <div class="stat-icon">
                    👤
                </div>
            </div>
            <div class="stat-number">
                <?php echo $regularUsers; ?>
            </div>
        </div>
    </div>

    <!-- Users -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <div class="card-title-icon">
                    👥
                </div>
                <h2>
                    Registered Users
                </h2>
            </div>
            <span class="user-count">
                <?php echo $totalUsers; ?> accounts
            </span>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) === 0): ?>
                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        👥
                                    </div>
                                    No registered users found.
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <span class="user-id">
                                        #<?php echo htmlspecialchars($u['id']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="username-cell">
                                        <div class="user-avatar">
                                            <?php
                                                echo strtoupper(
                                                    substr(
                                                        htmlspecialchars($u['username']),
                                                        0,
                                                        1
                                                    )
                                                );
                                            ?>
                                        </div>
                                        <span>
                                            <?php
                                                echo htmlspecialchars(
                                                    $u['username']
                                                );
                                            ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge
                                        <?php
                                            echo $u['role'] === 'admin'
                                                ? 'role-admin'
                                                : 'role-user';
                                        ?>
                                    ">
                                        <span class="role-dot"></span>
                                        <?php
                                            echo htmlspecialchars(
                                                $u['role']
                                            );
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Secure Portal &bull; Administrator Access
    </div>

</div>

</body>
</html>