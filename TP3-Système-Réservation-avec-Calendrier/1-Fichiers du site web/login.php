<?php
session_start();
require 'db_connect.php';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, password, email_verifie FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Erreur lors de la préparation de la requête.");
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'exécution de la requête.");
        }

        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Erreur lors de la récupération des résultats.");
        }

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                if ($row['email_verifie']) {
                    $_SESSION['user_id'] = $row['id'];
                    header("Location: dashboard.php");
                    exit();
                } else {
                    throw new Exception("Veuillez vérifier votre email avant de vous connecter.");
                }
            } else {
                throw new Exception("Email ou mot de passe incorrect.");
            }
        } else {
            throw new Exception("Email ou mot de passe incorrect.");
        }
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger text-center'>" . $e->getMessage() . "</div>";
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>
