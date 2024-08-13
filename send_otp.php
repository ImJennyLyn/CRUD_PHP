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

// Database connection
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

        $mail->setFrom($smtpConfig['username'], 'Mailer');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Deletion Confirmation';

        // Styled email body
        $mail->Body = "
            <html>
            <head>
                <style>
                     body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            text-align: center; /* Center align all content */
        }
        .logo {
            width: 100px; /* Adjust as per your logo size */
            margin-bottom: 20px;
        }
          .header h2 {
            color: purple; /* Text color */
            font-weight: bold; /* Bold font weight */
            font-size: 24px; /* Font size */
            margin-bottom: 20px; /* Margin bottom for spacing */
        }
        .otp {
            display: inline-block;
            padding: 10px;
            font-size: 24px;
            font-weight: bold;
            color: white;
            background-color: purple;
            border-radius: 5px;
            margin-top: 10px;
        }
        .message {
            font-size: 16px;
            color: #333;
            line-height: 1.6;
            margin-bottom: 10px; /* Add margin bottom to message paragraphs */
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <img class='logo' src='https://cdn-icons-png.freepik.com/512/10229/10229259.png' alt='Company Logo'>
        <div class='header'>
            <h2>OTP for Deletion Confirmation</h2>
        </div>
        <p class='message'>Please enter this OTP to confirm your deletion request</p>
        <p class='otp'>$otp</p>

    </div>
</body>
</html>";

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
                echo "OTP sent to the email of the user to be deleted.";
                // Show OTP input form
                echo '<form method="POST">
                        <input type="hidden" name="id" value="' . $id . '">
                        <label for="otp">Enter OTP:</label>
                        <input type="text" name="otp" id="otp" required>
                        <button type="submit" name="verify_otp">Verify OTP</button>
                      </form>';
            } else {
                echo $otpSent;
            }
        } else {
            // Verify the provided OTP
            $providedOtp = $_POST['otp'];
            if ($providedOtp == $_SESSION['otp']) {
                // Perform soft delete operation (update deleted column)
                $sql = "UPDATE users SET deleted = 1 WHERE id = '$id'";

                if ($conn->query($sql) === TRUE) {
                    // Redirect to dashboard after successful deletion
                    unset($_SESSION['otp']); // Clear the OTP from session
                    header("Location: dashboard.php");
                    exit;
                } else {
                    // Display error message if deletion fails
                    echo "Error deleting record: " . $conn->error;
                }
            } else {
                echo "Invalid OTP. Please try again.";
            }
        }
    } else {
        echo "No active user found with the provided ID.";
    }
} else {
    // No ID received
    echo "No ID received";
}

// Close database connection
$conn->close();
?>
