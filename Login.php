<?php
session_start();
include "database.php";

// Initialize variables to store user input and errors
$email = $password = "";
$emailErr = $passwordErr = "";

// Function to sanitize and validate input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = sanitize_input($_POST["email"]);
        // Check if email address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    // Validate password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = sanitize_input($_POST["password"]);
    }

    // If all fields are valid, process the login
    if (empty($emailErr) && empty($passwordErr)) {
        // Prepare and execute query
        $sql = "SELECT id, first_name, last_name, profile_image, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $hashed_password = $user['password'];

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Password is correct, store user data in session and redirect to dashboard
                $_SESSION["user_id"] = $user['id'];
                $_SESSION["email"] = $email; // Store user's email in session
                $_SESSION["first_name"] = $user['first_name'];
                $_SESSION["last_name"] = $user['last_name'];
                $_SESSION["profile_image"] = $user['profile_image'];
                header("Location: dashboard.php");
                exit();
            } else {
                // Password is incorrect
                $passwordErr = "Incorrect password";
            }
        } else {
            // User not found
            $emailErr = "No account found with that email";
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
    <title>Login Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-f-100 flex items-center justify-center h-screen">
    <div class="bg-purple-400 p-8 rounded-lg shadow-lg w-full max-w-sm">
        <h2 class="text-2xl text-center font-bold mb-6 text-gray-800">Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700 mb-2" id="email" name="email" type="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                <span class="text-sm text-red-500 mb-4 block"><?php echo $emailErr; ?></span>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700 mb-2" id="password" name="password" type="password" placeholder="Password" required>
                <span class="text-sm text-red-500 mb-4 block"><?php echo $passwordErr; ?></span>
            </div>
            <div class="flex justify-center">
                <button class="bg-purple-700 text-white py-2 px-4 rounded hover:bg-purple-600 focus:outline-none" type="submit">Login</button>
            </div>
            <div class="mt-4 text-center">
                <p class="text-gray-700 text-sm"><a href="forgot_password.php" class="text-purple-600 hover:underline">Forgot Password?</a></p>
            </div>
        </form>
        <div class="mt-4 text-center">
            <p class="text-white text-sm">Don't have an account? <a href="register.php" class="text-purple-600 hover:underline">Register here</a></p>
        </div>
    </div>
</body>
</html>
