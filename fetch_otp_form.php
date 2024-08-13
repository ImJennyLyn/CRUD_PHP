<?php
session_start();
include "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $conn->real_escape_string($_POST['id']);

    // Fetch user email based on the ID to be deleted
    $sql = "SELECT email FROM users WHERE id = '$id' AND deleted = 0";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userEmail = $user['email'];

        // Send OTP to the user's email
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;

        // Output OTP form
        echo '<form method="POST" action="" onsubmit="verifyOTP(event)">
                <input type="hidden" name="id" value="' . $id . '">
                <label for="otp">Enter OTP:</label>
                <input type="text" name="otp" id="otp" required>
                <button type="submit">Verify OTP</button>
              </form>';
    } else {
        echo "No active user found with the provided ID.";
    }
} else {
    echo "Invalid request.";
}

// Close database connection
$conn->close();
?>
