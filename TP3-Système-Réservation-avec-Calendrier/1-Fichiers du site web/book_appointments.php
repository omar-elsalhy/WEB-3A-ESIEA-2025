<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Vous devez être connecté pour réserver un rendez-vous.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['date'])) {
    $user_id = $_SESSION['user_id'];
    //$datetime_str = $date . ' ' . $time;
    $date_heure = $_POST['date'] . ' ' . $_POST['time'];

    try {
        // Vérifier si la date et l'heure sont bien alignées sur un intervalle de 15 minutes
        $dateTime = new DateTime($date_heure);
        $minutes = (int)$dateTime->format('i');

        if ($minutes % 15 !== 0) {
            throw new Exception("Veuillez sélectionner une heure valide (intervalle de 15 minutes).");
        }

        // Vérifier la disponibilité du créneau
        $stmt = $conn->prepare("SELECT COUNT(*) FROM reservations WHERE date_heure = ?");
        $stmt->bind_param("s", $date_heure);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            throw new Exception("Ce créneau est déjà réservé. Veuillez en choisir un autre.");
        }

        // Insérer la réservation
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, date_heure) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $date_heure);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Rendez-vous réservé avec succès !</div>";
        } else {
            throw new Exception("Erreur lors de la réservation.");
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}

$conn->close();
?>
