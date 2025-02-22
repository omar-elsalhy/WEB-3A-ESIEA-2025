<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'db_connect.php';
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $date_naissance = $_POST['date_naissance'];
    $adresse = $_POST['adresse'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(50));

    $stmt = $conn->prepare("INSERT INTO users (nom, prenom, date_naissance, adresse, telephone, email, password, email_verifie, email_token) VALUES (?, ?, ?, ?, ?, ?, ?, FALSE, ?)");
    $stmt->bind_param("ssssssss", $nom, $prenom, $date_naissance, $adresse, $telephone, $email, $password, $token);
    if ($stmt->execute()) {
        $verification_link = "http://yourwebsite.com/verify_email.php?token=" . $token;
        mail($email, "Vérification de votre email", "Cliquez sur ce lien pour vérifier votre email : " . $verification_link);
        echo "Inscription réussie. Veuillez vérifier votre email.";
    } else {
        echo "Erreur lors de l'inscription.";
    }
    $stmt->close();
    $conn->close();
}
?>
