<?php
require 'db_connect.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email_token = ? AND email_verifie = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE users SET email_verifie = 1, email_token = NULL WHERE email_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        echo "<div class='alert alert-success'>Email verified successfully! You can now log in.</div>";
    } else {
        echo "<div class='alert alert-danger'>Invalid or expired token.</div>";
    }
    $stmt->close();
    $conn->close();

    // Redirect to the login page
    header("Location: login.html");
    exit();
}
?>
