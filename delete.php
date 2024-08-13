<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require './vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registeruser";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define SMTP configurations
$smtpConfigs = array(
    array(
        'host' => 'smtp.gmail.com',
        'username' => 'valladorjennylyn@gmail.com',
        'password' => 'cuys pgyq onlu ctvm',
        'port' => 587,
        'encryption' => PHPMailer::ENCRYPTION_STARTTLS
    )
);

function sendOTP($email) {
    global $smtpConfigs;
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;

    $smtpConfig = $smtpConfigs[array_rand($smtpConfigs)];
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $smtpConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['username'];
        $mail->Password = $smtpConfig['password'];
        $mail->SMTPSecure = $smtpConfig['encryption'];
        $mail->Port = $smtpConfig['port'];

        $mail->setFrom($smtpConfig['username'], 'Your Name');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Deletion Confirmation';
        $mail->Body = "Your OTP for deletion confirmation is: $otp";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Check if POST data exists
if (isset($_POST['id'])) {
    $id = $conn->real_escape_string($_POST['id']);

    // Fetch user email based on the ID to be deleted
    $sql = "SELECT email FROM users WHERE id = '$id' AND deleted = 0";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userEmail = $user['email'];

        // Check if OTP is provided
        if (!isset($_POST['otp'])) {
            // Send OTP to the user's email
            $otpSent = sendOTP($userEmail);
            if ($otpSent === true) {
                $_SESSION['delete_id'] = $id; // Store the ID to be deleted in the session
                echo json_encode(array('success' => true, 'message' => 'OTP sent to the email of the user to be deleted.'));
            } else {
                echo json_encode(array('success' => false, 'message' => $otpSent));
            }
        } else {
            // Verify the provided OTP
            $providedOtp = $_POST['otp'];
            if ($providedOtp == $_SESSION['otp']) {
                // Perform soft delete operation (update deleted column)
                $sql = "UPDATE users SET deleted = 1 WHERE id = '$id'";

                if ($conn->query($sql) === TRUE) {
                    // Clear the OTP from session
                    unset($_SESSION['otp']); 
                    // Respond with success and redirect information
                    echo json_encode(array('success' => true, 'message' => 'User deleted successfully.', 'redirect' => 'dashboard.php'));
                } else {
                    // Display error message if deletion fails
                    echo json_encode(array('success' => false, 'message' => 'Error deleting record: ' . $conn->error));
                }
            } else {
                echo json_encode(array('success' => false, 'message' => 'Invalid OTP. Please try again.'));
            }
        }
    } else {
        echo json_encode(array('success' => false, 'message' => 'No active user found with the provided ID.'));
    }
} else {
    // No ID received
    echo json_encode(array('success' => false, 'message' => 'No ID received.'));
}

// Close database connection
$conn->close();
?>
