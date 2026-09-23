<?php
session_start();

// Security Guard: Just check for the wristband
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Electronic Red</title>

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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
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
            max-width: 480px;
            position: relative;
            z-index: 2;
        }

        .card {
            padding: 35px 30px;
            border-radius: 16px;
            background: rgba(255, 0, 51, 0.03);
            border: 1px solid rgba(255, 51, 85, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(255, 0, 51, 0.15), inset 0 0 15px rgba(255, 51, 85, 0.05);
            animation: fadeIn 0.6s ease;
            text-align: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: linear-gradient(135deg, #ff1a40 0%, #b3001f 100%);
            font-size: 30px;
            box-shadow: 0 4px 20px rgba(255, 26, 64, 0.4);
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #ff3355;
            text-shadow: 0 0 10px rgba(255, 51, 85, 0.3);
            letter-spacing: -0.4px;
        }

        .subtitle {
            color: #a08a8e;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(255, 51, 85, 0.1);
            color: #ff6b81;
            border: 1px solid rgba(255, 51, 85, 0.3);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            color: #ff6b6b;
            background: rgba(255, 51, 51, 0.1);
            border: 1px solid rgba(255, 51, 51, 0.3);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.25s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 51, 51, 0.2);
            border-color: rgba(255, 51, 51, 0.5);
            transform: translateY(-1px);
            box-shadow: 0 0 12px rgba(255, 51, 51, 0.25);
        }

        .footer {
            text-align: center;
            color: #8c7378;
            font-size: 11px;
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="card">
        <div class="user-icon">
            👤
        </div>
        
        <h1>
            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </h1>
        
        <p class="subtitle">
            You are successfully logged into your account.
        </p>

        <div class="badge">
            Standard User Portal
        </div>

        <a href="logout.php" class="logout-btn">
            ↪ Log Out
        </a>
    </div>

    <div class="footer">
        Secure Portal &bull; Electronic Red
    </div>
</div>

</body>
</html>