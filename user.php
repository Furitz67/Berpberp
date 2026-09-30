<?php
session_start();

// Security Guard: Ensure the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ensure a CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'db.php';
$message = '';
$current_user_id = intval($_SESSION['user_id']);

// --- CREATE & UPDATE ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header('HTTP/1.1 403 Forbidden');
        die("CSRF token validation failed.");
    }

    // 1. CREATE TASK
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';

        if (!empty($title)) {
            // Hardcode the current user's ID into the query for ownership isolation
            $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description) VALUES (?, ?, ?)");
            if ($stmt->execute([$current_user_id, $title, $description])) {
                $message = "Task added successfully.";
            }
        } else {
            $message = "Task title cannot be empty.";
        }
    }

    // 2. TOGGLE STATUS (UPDATE)
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
        $task_id = intval($_POST['task_id']);
        $new_status = ($_POST['current_status'] === 'pending') ? 'completed' : 'pending';

        // Critical: The WHERE clause checks BOTH task ID and user ID so users cannot update someone else's task
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$new_status, $task_id, $current_user_id])) {
            $message = "Task updated.";
        }
    }
}

// --- DELETE ACTION ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['task_id'])) {

    if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
        header('HTTP/1.1 403 Forbidden');
        die("CSRF token validation failed.");
    }

    $task_id = intval($_GET['task_id']);

    // Critical: Ownership validation via SQL WHERE scope execution
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$task_id, $current_user_id])) {
        $message = "Task deleted successfully.";
    }
}

// --- READ ACTION ---
// Isolate records so the user only pulls rows belonging to their specific account ID
$stmt = $pdo->prepare("SELECT id, title, description, status FROM tasks WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$current_user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks | Electronic Red</title>

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
            background-attachment: fixed;
            padding: 40px 20px;
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
            max-width: 760px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .card {
            padding: 30px;
            border-radius: 16px;
            background: rgba(255, 0, 51, 0.03);
            border: 1px solid rgba(255, 51, 85, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px 0 rgba(255, 0, 51, 0.15), inset 0 0 15px rgba(255, 51, 85, 0.05);
            animation: fadeIn 0.6s ease;
            margin-bottom: 22px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .user-icon {
            width: 55px;
            height: 55px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: linear-gradient(135deg, #ff1a40 0%, #b3001f 100%);
            font-size: 26px;
            box-shadow: 0 4px 20px rgba(255, 26, 64, 0.4);
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #ff3355;
            text-shadow: 0 0 10px rgba(255, 51, 85, 0.3);
            letter-spacing: -0.4px;
        }

        h2 {
            font-size: 18px;
            font-weight: 700;
            color: #ff3355;
            text-shadow: 0 0 10px rgba(255, 51, 85, 0.3);
            margin-bottom: 18px;
            letter-spacing: -0.2px;
        }

        .subtitle {
            color: #a08a8e;
            font-size: 14px;
            margin-top: 4px;
        }

        /* Navigation */
        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            color: #ff6b81;
            background: rgba(255, 51, 85, 0.1);
            border: 1px solid rgba(255, 51, 85, 0.3);
            transition: 0.25s ease;
        }

        .nav-link:hover {
            background: rgba(255, 51, 85, 0.2);
            transform: translateY(-1px);
            box-shadow: 0 0 12px rgba(255, 51, 85, 0.25);
        }

        .nav-link.admin {
            color: #ffb3c0;
            background: rgba(255, 26, 64, 0.2);
            border-color: rgba(255, 51, 85, 0.5);
        }

        .nav-link.logout {
            color: #ff6b6b;
            background: rgba(255, 51, 51, 0.1);
            border-color: rgba(255, 51, 51, 0.3);
        }

        /* Message */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 22px;
            font-size: 14px;
            font-weight: 600;
            color: #ff6b81;
            background: rgba(255, 51, 85, 0.1);
            border: 1px solid rgba(255, 51, 85, 0.3);
            animation: fadeIn 0.4s ease;
        }

        /* Form */
        .field {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #a08a8e;
            margin-bottom: 6px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 51, 85, 0.25);
            outline: none;
            transition: 0.25s ease;
            resize: vertical;
        }

        input[type="text"]::placeholder,
        textarea::placeholder {
            color: #6b5559;
        }

        input[type="text"]:focus,
        textarea:focus {
            border-color: #ff3355;
            box-shadow: 0 0 12px rgba(255, 51, 85, 0.3);
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(135deg, #ff1a40 0%, #b3001f 100%);
            box-shadow: 0 4px 20px rgba(255, 26, 64, 0.4);
            transition: 0.25s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(255, 26, 64, 0.55);
        }

        /* Table */
        .table-wrap {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid rgba(255, 51, 85, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 12px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #ff6b81;
            background: rgba(255, 51, 85, 0.08);
            border-bottom: 1px solid rgba(255, 51, 85, 0.2);
        }

        td {
            padding: 14px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(255, 51, 85, 0.1);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr {
            transition: 0.2s ease;
        }

        tbody tr:hover {
            background: rgba(255, 51, 85, 0.04);
        }

        tr.completed {
            opacity: 0.55;
        }

        .col-center {
            text-align: center;
        }

        .empty {
            text-align: center;
            color: #8c7378;
            font-size: 14px;
            padding: 30px 14px;
        }

        .task-title {
            font-size: 15px;
            font-weight: 600;
            color: white;
        }

        .task-title.done {
            text-decoration: line-through;
            color: #a08a8e;
        }

        .task-desc {
            margin-top: 5px;
            font-size: 13px;
            color: #a08a8e;
            line-height: 1.5;
        }

        /* Status button */
        .status-btn {
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            color: #ff6b81;
            background: rgba(255, 51, 85, 0.1);
            border: 1px solid rgba(255, 51, 85, 0.3);
            transition: 0.25s ease;
        }

        .status-btn:hover {
            background: rgba(255, 51, 85, 0.2);
            box-shadow: 0 0 12px rgba(255, 51, 85, 0.25);
        }

        .status-btn.done {
            color: #7dd9a0;
            background: rgba(60, 200, 120, 0.1);
            border-color: rgba(60, 200, 120, 0.3);
        }

        .status-btn.done:hover {
            background: rgba(60, 200, 120, 0.2);
            box-shadow: 0 0 12px rgba(60, 200, 120, 0.25);
        }

        /* Delete link */
        .delete-link {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            color: #ff6b6b;
            background: rgba(255, 51, 51, 0.1);
            border: 1px solid rgba(255, 51, 51, 0.3);
            transition: 0.25s ease;
        }

        .delete-link:hover {
            background: rgba(255, 51, 51, 0.2);
            border-color: rgba(255, 51, 51, 0.5);
            transform: translateY(-1px);
            box-shadow: 0 0 12px rgba(255, 51, 51, 0.25);
        }

        .footer {
            text-align: center;
            color: #8c7378;
            font-size: 11px;
            margin-top: 10px;
        }

        @media (max-width: 520px) {
            .card { padding: 22px 18px; }
            h1 { font-size: 20px; }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="card">
        <div class="header">
            <div class="user-icon">👤</div>
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>!</h1>
                <p class="subtitle">Manage your tasks below.</p>
            </div>
        </div>

        <!-- Navigation based on role architecture -->
        <nav>
            <a href="user.php" class="nav-link">My Tasks</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="nav-link admin">Admin Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-link logout">↪ Log Out</a>
        </nav>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- CREATE FORM -->
    <div class="card">
        <h2>Add a New Task</h2>
        <form action="user.php" method="POST">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <div class="field">
                <label for="title">Task Title</label>
                <input type="text" id="title" name="title" placeholder="What needs to be done?" required>
            </div>
            <div class="field">
                <label for="description">Description (Optional)</label>
                <textarea id="description" name="description" rows="3" placeholder="Add some details..."></textarea>
            </div>
            <button type="submit" class="btn-primary">Add Task</button>
        </form>
    </div>

    <!-- READ & INTERACT TABLE -->
    <div class="card">
        <h2>My Active Workspace</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-center">Status</th>
                        <th>Task Details</th>
                        <th class="col-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)): ?>
                        <tr>
                            <td colspan="3" class="empty">You haven't added any tasks yet!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tasks as $task): ?>
                            <tr class="<?php echo $task['status'] === 'completed' ? 'completed' : ''; ?>">
                                <td width="15%" class="col-center">
                                    <!-- UPDATE STATUS FORM -->
                                    <form action="user.php" method="POST">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8'); ?>">

                                        <button type="submit" class="status-btn <?php echo $task['status'] === 'completed' ? 'done' : ''; ?>">
                                            <?php echo $task['status'] === 'completed' ? '✅ Completed' : '⏳ Pending'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="task-title <?php echo $task['status'] === 'completed' ? 'done' : ''; ?>">
                                        <?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <?php if (!empty($task['description'])): ?>
                                        <p class="task-desc">
                                            <?php echo nl2br(htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8')); ?>
                                        </p>
                                    <?php endif; ?>
                                </td>
                                <td width="15%" class="col-center">
                                    <!-- SECURE DELETE LINK -->
                                    <?php
                                    $deleteUrl = "user.php?action=delete&task_id=" . urlencode((string)$task['id']) . "&token=" . urlencode($_SESSION['csrf_token']);
                                    ?>
                                    <a href="<?php echo htmlspecialchars($deleteUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                       class="delete-link"
                                       onclick="return confirm('Are you sure you want to delete this task?');">
                                        ✕ Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        Secure Portal &bull; Electronic Red
    </div>
</div>

</body>
</html>