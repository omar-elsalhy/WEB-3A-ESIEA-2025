<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE date = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO reservations (user_id, date) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $date);
            $stmt->execute();
            echo "<script>alert('Rendez-vous pris avec succès !'); window.location.href = 'dashboard.php';</script>";
        } else {
            echo "<script>alert('Créneau déjà réservé.'); window.location.href = 'dashboard.php';</script>";
        }
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage();
    }
}
?>
