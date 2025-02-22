<?php
session_start();
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, email_verifie FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            if ($row['email_verifie']) {
                $_SESSION['user_id'] = $row['id'];
                header("Location: dashboard.php");
                exit();
            } else {
                echo "<div class='alert alert-warning text-center'>Veuillez vérifier votre email avant de vous connecter.</div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center'>Email ou mot de passe incorrect.</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Email ou mot de passe incorrect.</div>";
    }
    $stmt->close();
    $conn->close();
}
?>
