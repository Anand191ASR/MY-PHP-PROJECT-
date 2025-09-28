<?php
session_start();
require_once __DIR__ . '/../db/config.php';

// Only logged-in users can book
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: /renteasy/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /renteasy/user/browse.php');
    exit;
}

// Get POST values safely
$pid = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
$start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$end_date   = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';

// Basic validation
if (!$pid || $start_date === '' || $end_date === '') {
    die('❌ Invalid booking request: missing fields.');
}

// Validate date format (YYYY-MM-DD)
$sd = DateTime::createFromFormat('Y-m-d', $start_date);
$ed = DateTime::createFromFormat('Y-m-d', $end_date);

if (!$sd || $sd->format('Y-m-d') !== $start_date || !$ed || $ed->format('Y-m-d') !== $end_date) {
    die('❌ Invalid date format. Use YYYY-MM-DD.');
}

if ($ed < $sd) {
    die('❌ End date must be the same or after the start date.');
}

// Ensure property exists and is approved
try {
    $check = $pdo->prepare("SELECT id FROM properties WHERE id = ? AND status = 'approved' LIMIT 1");
    $check->execute([$pid]);
    $property = $check->fetch();
} catch (PDOException $e) {
    die('❌ Database error: ' . $e->getMessage());
}

if (!$property) {
    die('❌ Property not found or not available.');
}

// Insert booking (status = pending)
try {
    $ins = $pdo->prepare("INSERT INTO bookings 
        (user_id, property_id, start_date, end_date, status, created_at) 
        VALUES (?, ?, ?, ?, 'pending', NOW())");
    $ins->execute([$_SESSION['user_id'], $pid, $start_date, $end_date]);

    // Success → redirect
    header('Location: /renteasy/user/bookings.php');
    exit;
} catch (PDOException $e) {
    die('❌ Booking error: ' . $e->getMessage());
}
