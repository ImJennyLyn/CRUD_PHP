
<?php
session_start();
include "database.php";

// Initialize variables
$otpErr = $otp = "";

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["otp"])) {
        $otpErr = "OTP is required";
    } else {
        $otp = sanitize_input($_POST["otp"]);
        if ($otp != $_SESSION['delete_otp']) {
            $otpErr = "Invalid OTP";
        } else {
            // OTP is valid, delete the user account
            $userId = $_SESSION['delete_user_id'];
            $sql = "UPDATE users SET is_deleted = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "Account deleted successfully.";
                // Redirect to dashboard or any other page
                header("Location: dashboard.php");
                exit();
            } else {
                $otpErr = "Failed to delete account.";
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>

