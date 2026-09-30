<?php
session_start();
require 'db.php';

$error = "";

// If already logged in, send them to the right page
if (isset($_SESSION['logged_in'], $_SESSION['user_id']) && $_SESSION['logged_in'] === true) {
    header("Location: " . (($_SESSION['role'] ?? '') === 'admin' ? "admin.php" : "user.php"));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // 1. Fetch user safely using PDO
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = :username");
    $stmt->execute(array('username' => $username));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Verify password (supports hashed passwords, and plain text for older accounts)
    $valid = false;
    if ($user) {
        $stored = (string)$user['password'];
        $valid = password_verify($password, $stored) || hash_equals($stored, $password);
    }

    // 3. Hand out the wristband
    if ($valid) {
        session_regenerate_id(true); // prevents session fixation

        $_SESSION['logged_in'] = true;
        // NOTE: change 'id' to your real primary key column name if it's different
        $_SESSION['user_id']   = $user['id'] ?? $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role']; // Crucial for authorization!

        // 4. The Traffic Cop (Redirection)
        if ($user['role'] === 'admin') {
            header("Location: admin.php");
            exit();
        } else {
            header("Location: user.php");
            exit();
        }
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Login | Electronic Red</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #fff;
            background: radial-gradient(circle at center, #1c0c0e 0%, #0a0405 100%);
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
            max-width: 400px;
            position: relative;
            z-index: 2;
        }

        .login-card {
            background: rgba(255, 0, 51, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 51, 85, 0.2);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(255, 0, 51, 0.15), inset 0 0 15px rgba(255, 51, 85, 0.05);
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
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

        .login-card h2 {
            margin-bottom: 8px;
            color: #ff3355;
            text-align: center;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-shadow: 0 0 10px rgba(255, 51, 85, 0.3);
        }

        .subtitle {
            text-align: center;
            color: #a08a8e;
            font-size: 13px;
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #d1b3b8;
            font-size: 13px;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(20, 5, 8, 0.6);
            border: 1px solid rgba(255, 51, 85, 0.25);
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-group input::placeholder {
            color: #6b5559;
        }

        .input-group input:focus {
            outline: none;
            border-color: #ff3355;
            background: rgba(30, 8, 12, 0.8);
            box-shadow: 0 0 0 3px rgba(255, 51, 85, 0.25), 0 0 10px rgba(255, 51, 85, 0.2);
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #ff1a40 0%, #b3001f 100%);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, opacity 0.2s, box-shadow 0.2s;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(255, 26, 64, 0.4);
        }

        button[type="submit"]:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(255, 26, 64, 0.6);
        }

        .error-message {
            background-color: rgba(255, 51, 51, 0.15);
            color: #ff6b6b;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255, 51, 51, 0.4);
            box-shadow: 0 0 10px rgba(255, 51, 51, 0.1);
        }

        .footer {
            text-align: center;
            color: #8c7378;
            font-size: 11px;
            margin-top: 20px;
        }

        @media (max-width: 480px) {
            .login-card { padding: 30px 22px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-card">
        <div class="user-icon">🔐</div>

        <form method="POST" autocomplete="on">
            <h2>System Access</h2>
            <p class="subtitle">Authenticate to enter the neural network</p>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit">Authorize</button>
        </form>
    </div>

    <div class="footer">
        Secure Portal &bull; Electronic Red
    </div>
</div>

</body>
</html>