<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $appointment_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Delete only if the appointment belongs to the user
    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointment_id, $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error canceling appointment.";
    }
}

$stmt->close();
$conn->close();
?>
