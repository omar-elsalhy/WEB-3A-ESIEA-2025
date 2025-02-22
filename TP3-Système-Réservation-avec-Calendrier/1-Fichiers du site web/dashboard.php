<?php
session_start();
require 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT nom, prenom, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch user appointments
$stmt = $conn->prepare("SELECT id, date_heure FROM reservations WHERE user_id = ? ORDER BY date_heure ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Reservation System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">Reservation System</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Welcome, <?php echo htmlspecialchars($user['prenom']) . " " . htmlspecialchars($user['nom']); ?>!</h2>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>

        <h3>Your Appointments</h3>
        <?php if ($appointments->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <?php echo date("d/m/Y H:i", strtotime($row['date_heure'])); ?>
                        <a href="cancel_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm float-end">Cancel</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No appointments booked yet.</p>
        <?php endif; ?>

        <h3 class="mt-4">Book a New Appointment</h3>
        <form action="book_appointment.php" method="POST">
            <label for="date">Select Date & Time:</label>
            <input type="datetime-local" name="date" required class="form-control mb-2">
            <button type="submit" class="btn btn-success">Book Appointment</button>
        </form>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
