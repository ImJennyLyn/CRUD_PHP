<?php
ob_start(); // Start output buffering

session_start();
include "database.php";
require './vendor/autoload.php'; // Include Composer's autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$email = $emailErr = $otpSent = "";

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Define multiple Gmail SMTP configurations
$smtpConfigs = array(
    array(
        'host' => 'smtp.gmail.com',
        'username' => 'valladorjennylyn@gmail.com',
        'password' => 'cuys pgyq onlu ctvm',
        'port' => 587,
        'encryption' => PHPMailer::ENCRYPTION_STARTTLS
    ),
    array(
        'host' => 'smtp.gmail.com',
        'username' => 'valladorjennylyn@gmail.com',
        'password' => 'cuys pgyq onlu ctvm',
        'port' => 587,
        'encryption' => PHPMailer::ENCRYPTION_STARTTLS
    ),
    // Add more configurations as needed
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = sanitize_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    if (empty($emailErr)) {
        // Check if email exists in the database
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $userId = $user['id'];

            // Generate OTP
            $otp = rand(100000, 999999);

            // Store OTP in session
            $_SESSION['otp'] = $otp;
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $email;

            // Choose a random SMTP configuration from $smtpConfigs
            $smtpConfig = $smtpConfigs[array_rand($smtpConfigs)];

            // Send OTP email
            $mail = new PHPMailer(true); // Enable exceptions

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = $smtpConfig['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpConfig['username'];
                $mail->Password   = $smtpConfig['password'];
                $mail->SMTPSecure = $smtpConfig['encryption'];
                $mail->Port       = $smtpConfig['port'];

                // Optional debugging
                $mail->SMTPDebug = 2; // Enable verbose debug output
                $mail->Debugoutput = 'html'; // Print debug output as HTML

                //Recipients
                $mail->setFrom($smtpConfig['username'], 'Mailer'); // Use the sender's email here
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP for Password Reset';
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
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
        <img class='logo' src='https://trimexcolleges.edu.ph/public/assets/images/icons/forgot-2.png' alt='Company Logo'>
        <div class='header'>
            <h2>OTP for Password Reset</h2>
        </div>
        <p class='message'>Please use the OTP below to reset your password:</p>
        <p class='otp'>$otp</p>
      
    </div>
</body>
</html>";

                $mail->send();
                $otpSent = "OTP sent to your email.";
                header("Location: verify_otp.php");
                exit();
            } catch (Exception $e) {
                $emailErr = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $emailErr = "No account found with that email.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-f-100 flex items-center justify-center h-screen">
    <div class="bg-purple-400 p-8 rounded-lg shadow-lg w-full max-w-sm">
        <h2 class="text-2xl text-center font-bold mb-6 text-gray-800">Forgot Password</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700 mb-2" id="email" name="email" type="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                <span class="text-sm text-red-500 mb-4 block"><?php echo $emailErr; ?></span>
                <span class="text-sm text-green-500 mb-4 block"><?php echo $otpSent; ?></span>
            </div>
            <div class="flex justify-center">
                <button class="bg-purple-700 text-white py-2 px-4 rounded hover:bg-purple-600 focus:outline-none" type="submit">Send OTP</button>
            </div>
        </form>
    </div>
</body>
</html>

<?php
ob_end_flush(); // Flush the output buffer and send it to the browser
?>
