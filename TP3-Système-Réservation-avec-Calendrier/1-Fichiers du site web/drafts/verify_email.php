<?php
require 'db_connect.php';
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $conn->prepare("UPDATE users SET email_verifie = TRUE, email_token = NULL WHERE email_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "Email vérifié avec succès. Vous pouvez maintenant vous connecter.";
    } else {
        echo "Lien invalide ou email déjà vérifié.";
    }
    $stmt->close();
}
$conn->close();
?>
