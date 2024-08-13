<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

// Database connection details
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "registeruser"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle recovery action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $user_id = $_POST['id'];

    // Update the 'deleted' flag to 0 (not deleted)
    $update_sql = "UPDATE users SET deleted = 0 WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Redirect back to deleted users page after successful recovery
        header("Location: deleted_users.php");
        exit;
    } else {
        echo "Error recovering user: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
