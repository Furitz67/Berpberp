<?php
session_start();
require 'db.php';

// 1. login check + get $userId + CSRF token + $allowedStatus
//    (top of tasks.php)

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // CSRF check
    // $action = $_POST['action'] ?? '';

    // 2. PUT THE PHP HANDLER HERE (the "CREATE" block)
}
?>

<!DOCTYPE html>
<html>
<body>

    <!-- 3. PUT THE HTML FORM HERE, inside <body> -->

</body>
</html>