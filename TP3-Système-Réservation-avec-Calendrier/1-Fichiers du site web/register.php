<?php
session_start();
require 'db_connect.php'; // Database connection

// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require 'vendor/autoload.php'; // Include PHPMailer


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $date_naissance = htmlspecialchars($_POST['date_naissance']);;
        $adresse = htmlspecialchars($_POST['adresse']);;
        $telephone = htmlspecialchars($_POST['telephone']);;
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        #$password = $_POST['password'];
        $password=htmlspecialchars($_POST['password']);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(50)); // Generate a unique token

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            throw new Exception("Email already registered.");
        }
        $stmt->close();

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (nom, prenom, date_naissance, adresse, telephone, email, password, email_verifie, email_token) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)");
        $stmt->bind_param("ssssssss", $nom, $prenom, $date_naissance, $adresse, $telephone, $email, $hashed_password, $token);
        if (!$stmt->execute()) {
            throw new Exception("Error during registration.");
        }
        $stmt->close();

        // Send verification email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'localhost'; // Use Laragon's SMTP server
        $mail->SMTPAuth = false;   // No authentication for local server
        $mail->Port = 587;        // Default mail port in Laragon
        $mail->setFrom('noreply@example.com', 'Reservation System');
        $mail->addAddress($email);
        $mail->Subject = "Email Verification";
        $mail->isHTML(true);
        $mail->Body = "Hello $prenom, <br><br> Click the link below to verify your email: <br>
                      <a href='http://localhost/verify.php?token=$token'>Verify Email</a>";

        if (!$mail->send()) {
            throw new Exception("Error sending email.");
        }

        echo "<div class='alert alert-success'>Registration successful! Check your email to verify your account.</div>";

    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }

    // Redirect to the login page
    header("Location: login.html");
    exit();
}
?>
