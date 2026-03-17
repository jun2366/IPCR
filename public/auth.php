<?php
require '../config/database.php';
session_start();

// 1. Get the values from the new dropdowns
$user_id   = $_POST['user_id'] ?? null;
$period_id = $_POST['period_id'] ?? null;

// 2. If either is missing, kick them back to the login page
if (!$user_id || !$period_id) {
    header('Location: index.php');
    exit;
}

// 3. Validate that the user actually exists in the database
$stmtUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$stmtUser->store_result();
$userExists = $stmtUser->num_rows === 1;
$stmtUser->close();

// 4. Validate that the period actually exists in the database
$stmtPeriod = $conn->prepare("SELECT id FROM login_periods WHERE id = ?");
$stmtPeriod->bind_param("i", $period_id);
$stmtPeriod->execute();
$stmtPeriod->store_result();
$periodExists = $stmtPeriod->num_rows === 1;
$stmtPeriod->close();

// 5. If both are valid, set the session and let them into the dashboard!
if ($userExists && $periodExists) {
    $_SESSION['user_id']   = (int)$user_id;
    $_SESSION['period_id'] = (int)$period_id;
    
    header('Location: ipcr.php');
    exit;
}

// Fallback: If someone tried to tamper with the form values, send them back
header('Location: index.php');
exit;