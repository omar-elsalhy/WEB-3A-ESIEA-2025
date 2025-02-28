<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Generate a CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur CSRF : le formulaire est invalide.");
    }

    // Process the form (e.g., sanitize input, update database)
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch user info
$stmt = $conn->prepare("SELECT nom, prenom, date_naissance, adresse, telephone, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle user info update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $date_naissance = $_POST['date_naissance'];
    $adresse = trim($_POST['adresse']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);

    // Check if email is unique
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Cet email est déjà utilisé par un autre compte.</div>";
    } else {
        // Update user info
        $stmt = $conn->prepare("UPDATE users SET nom = ?, prenom = ?, date_naissance = ?, adresse = ?, telephone = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nom, $prenom, $date_naissance, $adresse, $telephone, $email, $user_id);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Informations mises à jour avec succès.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Erreur lors de la mise à jour.</div>";
        }
    }
    $stmt->close();
}

// Fetch user's reservations
$stmt = $conn->prepare("SELECT id, date_heure FROM reservations WHERE user_id = ? ORDER BY date_heure ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations = $stmt->get_result();
$stmt->close();


// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            session_destroy();
            header("Location: login.html");
            exit();
        } else {
            throw new Exception("Erreur lors de la suppression du compte.");
        }
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Bienvenue, <?= htmlspecialchars($user['prenom']) ?>!</h2>
        <?= $message ?>

        <h3 class="mt-4">Modifier mes informations</h3>
        <form method="POST" class="w-50 mx-auto">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Prénom</label>
                <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Date de naissance</label>
                <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($user['date_naissance']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Adresse</label>
                <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($user['adresse']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Téléphone</label>
                <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <button type="submit" name="update_info" class="btn btn-primary w-100">Mettre à jour</button>
        </form>


        <h3 class="mt-4">Prendre un nouveau rendez-vous</h3>
        <form action="book_appointments.php" method="POST">
            <label for="date">Sélectionnez la date:</label>
            <input type="date" name="date" required class="form-control mb-2">

            <label for="time">Sélectionnez l'heure:</label>
            <select name="time" required class="form-control mb-2">
                <option value="08:00">08:00</option>
                <option value="08:15">08:15</option>
                <option value="08:30">08:30</option>
                <option value="08:45">08:45</option>
                <option value="09:00">09:00</option>
                <option value="09:15">09:15</option>
                <option value="09:30">09:30</option>
                <option value="09:45">09:45</option>
                <option value="10:00">10:00</option>
                <option value="10:15">10:15</option>
                <option value="10:30">10:30</option>
                <option value="10:45">10:45</option>
                <option value="11:00">11:00</option>
                <option value="11:15">11:15</option>
                <option value="11:30">11:30</option>
                <option value="11:45">11:45</option>
                <option value="12:00">12:00</option>
                <option value="12:15">12:15</option>
                <option value="12:30">12:30</option>
                <option value="12:45">12:45</option>
                <option value="13:00">13:00</option>
                <option value="13:15">13:15</option>
                <option value="13:30">13:30</option>
                <option value="13:45">13:45</option>
                <option value="14:00">14:00</option>
                <option value="14:15">14:15</option>
                <option value="14:30">14:30</option>
                <option value="14:45">14:45</option>
                <option value="15:00">15:00</option>
                <option value="15:15">15:15</option>
                <option value="15:30">15:30</option>
                <option value="15:45">15:45</option>
                <option value="16:00">16:00</option>
                <option value="16:15">16:15</option>
                <option value="16:30">16:30</option>
                <option value="16:45">16:45</option>
                <option value="17:00">17:00</option>
                <option value="17:15">17:15</option>
                <option value="17:30">17:30</option>
                <option value="17:45">17:45</option>
            </select>

            <button type="submit" class="btn btn-success">Réserver</button>
        </form>



        <h3 class="mt-4">Vos rendez-vous</h3>
        <ul class="list-group">
            <?php while ($row = $reservations->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= htmlspecialchars($row['date_heure']) ?>
                    <a href="cancel_appointment.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Annuler</a>
                </li>
            <?php endwhile; ?>
        </ul>



        <h3 class="mt-4 text-danger">Supprimer mon compte</h3>
        <p>Cette action est irréversible et supprimera tous vos rendez-vous.</p>
        <form method="POST">
            <button type="submit" name="delete_account" class="btn btn-danger">Supprimer mon compte</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
