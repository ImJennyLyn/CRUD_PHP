<?php
// Start session if not already started
session_start();

// Include database connection
include "database.php";

// Initialize variables to store user input and errors
$firstName = $lastName = $email = $password = $confirm_password = $age = $birthday = $gender = "";
$firstNameErr = $lastNameErr = $emailErr = $passwordErr = $confirm_passwordErr = "";

// Function to sanitize and validate input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate first name
    if (empty($_POST["firstName"])) {
        $firstNameErr = "First Name is required";
    } else {
        $firstName = sanitize_input($_POST["firstName"]);
        // Check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/", $firstName)) {
            $firstNameErr = "Only letters and white space allowed";
        }
    }

    // Validate last name
    if (empty($_POST["lastName"])) {
        $lastNameErr = "Last Name is required";
    } else {
        $lastName = sanitize_input($_POST["lastName"]);
        // Check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/", $lastName)) {
            $lastNameErr = "Only letters and white space allowed";
        }
    }

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
        // Password validation can be added here if needed
    }

    // Validate confirm password
    if (empty($_POST["confirm_password"])) {
        $confirm_passwordErr = "Confirm password is required";
    } else {
        $confirm_password = sanitize_input($_POST["confirm_password"]);
        if ($confirm_password !== $password) {
            $confirm_passwordErr = "Passwords do not match";
        }
    }

    // Validate age
    $age = isset($_POST["age"]) ? sanitize_input($_POST["age"]) : null;

    // Validate birthday
    $birthday = isset($_POST["birthday"]) ? sanitize_input($_POST["birthday"]) : null;

    // Validate gender
    $gender = isset($_POST["gender"]) ? sanitize_input($_POST["gender"]) : null;

    // Validate profile image upload
    $profile_image = null;
    if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        $filename = $_FILES['profile_image']['name'];
        $profile_image = $filename; // Store the filename
        move_uploaded_file($tmp_name, "uploads/" . $filename); // Move the uploaded file to 'uploads' directory
    } else {
        echo "<p class='text-center text-red-500'>Error uploading image</p>";
    }

    // If all fields are valid, process the registration
    if (empty($firstNameErr) && empty($lastNameErr) && empty($emailErr) && empty($passwordErr) && empty($confirm_passwordErr)) {
       
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user data into the database, including profile image filename
        $sql = "INSERT INTO users (first_name, last_name, email, password, age, birthday, gender, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssisss", $firstName, $lastName, $email, $hashed_password, $age, $birthday, $gender, $profile_image);

        if ($stmt->execute()) {
            // Set session variable for successful registration
            $_SESSION['registration_success'] = true;
            // Close statement
            $stmt->close();
        } else {
            echo "<p class='text-center text-red-500'>Error: " . $stmt->error . "</p>";
        }

        // Close database connection
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-purple-400 p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h2 class="text-2xl text-center font-bold mb-6 text-gray-800">Register Form</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="firstName">First Name</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700" id="firstName" name="firstName" type="text" placeholder="First Name" value="<?php echo htmlspecialchars($firstName); ?>" required>
                <span class="text-sm text-red-500"><?php echo $firstNameErr; ?></span>
            </div>
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="lastName">Last Name</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700" id="lastName" name="lastName" type="text" placeholder="Last Name" value="<?php echo htmlspecialchars($lastName); ?>" required>
                <span class="text-sm text-red-500"><?php echo $lastNameErr; ?></span>
            </div>
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700" id="email" name="email" type="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                <span class="text-sm text-red-500"><?php echo $emailErr; ?></span>
            </div>
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700" id="password" name="password" type="password" placeholder="Password" required>
                <span class="text-sm text-red-500"><?php echo $passwordErr; ?></span>
            </div>
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">Confirm Password</label>
                <input class="border rounded w-full py-2 px-3 text-gray-700" id="confirm_password" name="confirm_password" type="password" placeholder="Confirm Password" required>
                <span class="text-sm text-red-500"><?php echo $confirm_passwordErr; ?></span>
            </div>
            <div class="flex mb-3">
                <div class="w-1/3 mr-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="age">Age</label>
                    <input class="border rounded w-full py-2 px-3 text-gray-700" id="age" name="age" type="number" placeholder="Age" value="<?php echo htmlspecialchars($age); ?>">
                </div>
                <div class="w-1/3 mr-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="birthday">Birthday</label>
                    <input class="border rounded w-full py-2 px-3 text-gray-700" id="birthday" name="birthday" type="date" value="<?php echo htmlspecialchars($birthday); ?>">
                </div>
                <div class="w-1/3">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="gender">Gender</label>
                    <select id="gender" name="gender" class="border rounded w-full py-2 px-3 text-gray-700">
                        <option value="male" <?php if ($gender === "male") echo "selected"; ?>>Male</option>
                        <option value="female" <?php if ($gender === "female") echo "selected"; ?>>Female</option>
                        <option value="other" <?php if ($gender === "other") echo "selected"; ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="profile_image">Profile Image</label>
                <input type="file" class="border rounded w-full py-2 px-3 text-gray-700" id="profile_image" name="profile_image" accept="image/*">
            </div>
            <div class="flex justify-center">
                <button class="bg-purple-700 text-white py-2 px-4 rounded hover:bg-purple-600 focus:outline-none" type="submit">Register</button>
            </div>
        </form>
        <div class="mt-4 text-center">
            <p class="text-gray-700 text-sm">Already have an account? <a href="login.php" class="text-purple-600 hover:underline">Login here</a></p>
        </div>
    </div>

    <?php if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Registration Successful!',
                    text: 'You have successfully registered.',
                    showConfirmButton: true // Show a button for user interaction
                }).then(function(result) {
                    if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                        window.location.href = 'login.php'; // Redirect after SweetAlert
                    }
                });
            });
        </script>
        <?php unset($_SESSION['registration_success']); // Clear the session variable ?>
    <?php endif; ?>
</body>
</html>
