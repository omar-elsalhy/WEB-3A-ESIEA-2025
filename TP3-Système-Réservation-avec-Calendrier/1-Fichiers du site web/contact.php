<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user email from database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT email, nom FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_email = $user['email'];
$user_name = $user['nom'];

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("<div class='alert alert-danger'>Invalid CSRF token.</div>");
    }

    $sujet = trim($_POST['sujet']);
    $message_content = trim($_POST['message']);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'localhost'; // Replace with your SMTP server
        $mail->SMTPAuth = false;
        //$mail->SMTPAuth = true;
        //$mail->Username = 'your_email@example.com'; // Replace with your email
        //$mail->Password = 'your_password'; // Replace with your email password
        //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($user_email, $user_name);
        $mail->addAddress('root@example.com'); // Admin email

        $mail->Subject = "Contact: $sujet";
        $mail->Body = "Nom: $user_name\nEmail: $user_email\nMessage:\n$message_content";

        $mail->send();
        $message = "<div class='alert alert-success'>Message envoyé avec succès !</div>";

        // Regenerate CSRF token after successful submission
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>Erreur: " . $mail->ErrorInfo . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">Réservation</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Contactez-nous</h2>
        <?= $message ?>
        <form action="contact.php" method="POST" class="w-50 mx-auto">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user_name) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_email) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Sujet</label>
                <input type="text" name="sujet" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Envoyer</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
