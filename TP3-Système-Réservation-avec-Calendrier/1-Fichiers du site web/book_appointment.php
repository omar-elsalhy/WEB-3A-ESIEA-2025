<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $date_heure = $_POST['date'];

    // Check if the slot is available
    $stmt = $conn->prepare("SELECT id FROM reservations WHERE date_heure = ?");
    $stmt->bind_param("s", $date_heure);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Book the appointment
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, date_heure) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $date_heure);
        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error booking appointment.";
        }
    } else {
        echo "This slot is already taken. Please choose another.";
    }
}

$stmt->close();
$conn->close();
?>
