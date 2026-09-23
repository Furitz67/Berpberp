<?php
session_start();
require 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($username === "" || $password === "") {
        $error = "Username and password are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if username is already taken
        $stmt = $pdo->prepare("SELECT id FROM Users WHERE username = :username");
        $stmt->execute(array('username' => $username));

        if ($stmt->fetch()) {
            $error = "That username is already taken.";
        } else {
            // New accounts default to role 'user'
            $stmt = $pdo->prepare("INSERT INTO Users (username, password, role) VALUES (:username, :password, 'user')");
            $stmt->execute(array(
                'username' => $username,
                'password' => $password
            ));

            $success = "Account created! You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Electronic Red</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        body {
            background: radial-gradient(circle at center, #1c0c0e 0%, #0a0405 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
        }

        .login-card {
            background: rgba(255, 0, 51, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 51, 85, 0.2);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(255, 0, 51, 0.15), inset 0 0 15px rgba(255, 51, 85, 0.05);
            width: 100%;
            max-width: 400px;
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

        .success-message {
            background-color: rgba(0, 255, 153, 0.1);
            color: #00ff99;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(0, 255, 153, 0.3);
            box-shadow: 0 0 10px rgba(0, 255, 153, 0.1);
        }

        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #a08a8e;
        }

        .footer-link a {
            color: #ff3355;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <form method="POST">
            <h2>System Registration</h2>
            <p class="subtitle">Initialize a new secure account</p>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Choose a username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required minlength="6">
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="••••••••" required minlength="6">
            </div>

            <button type="submit">Register</button>
        </form>

        <p class="footer-link">Already authorized? <a href="login.php">Log in</a></p>
    </div>

</body>
</html>