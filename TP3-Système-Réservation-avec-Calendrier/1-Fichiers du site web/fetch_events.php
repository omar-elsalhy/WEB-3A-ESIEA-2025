<?php
session_start();
require 'db_connect.php';
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT id, date as start FROM reservations WHERE user_id = $user_id");
$events = [];
while ($row = $result->fetch_assoc()) {
    $row['title'] = 'Rendez-vous';
    $events[] = $row;
}
echo json_encode($events);
?>
