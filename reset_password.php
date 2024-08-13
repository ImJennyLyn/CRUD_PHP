<?php
session_start();
include "database.php";

// Initialize variables
$newPassword = $confirmPassword = "";
$passwordErr = $confirmPasswordErr = "";

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["newPassword"])) {
        $passwordErr = "Password is required";
    } else {
        $newPassword = sanitize_input($_POST["newPassword"]);
    }

    if (empty($_POST["confirmPassword"])) {
        $confirmPasswordErr = "Confirm password is required";
    } else {
        $confirmPassword = sanitize_input($_POST["confirmPassword"]);
        if ($confirmPassword != $newPassword) {
            $confirmPasswordErr = "Passwords do not match";
        }
    }

    if (empty($passwordErr) && empty($confirmPasswordErr)) {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $_SESSION['user_id']);

        if ($stmt->execute()) {
            // Clear the session and redirect to login page
            session_unset();
            session_destroy();
            header("Location: Login.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
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
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-f-100 flex items-center justify-center h-screen">
    <div class="bg-purple-400 p-8 rounded-lg shadow-lg w-full max-w-sm">
        <h2 class="text-2xl text-center font-bold mb-6 text-gray-800">Reset Password</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="newPassword">New Password</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700 mb-2" id="newPassword" name="newPassword" type="password" placeholder="New Password" required>
                <span class="text-sm text-red-500 mb-4 block"><?php echo $passwordErr; ?></span>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="confirmPassword">Confirm Password</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700 mb-2" id="confirmPassword" name="confirmPassword" type="password" placeholder="Confirm Password" required>
                <span class="text-sm text-red-500 mb-4 block"><?php echo $confirmPasswordErr; ?></span>
            </div>
            <div class="flex justify-center">
                <button class="bg-purple-700 text-white py-2 px-4 rounded hover:bg-purple-600 focus:outline-none" type="submit">Reset Password</button>
            </div>
        </form>
    </div>
</body>
</html>
