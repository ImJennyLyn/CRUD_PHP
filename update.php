<?php

include "database.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender']; // Assuming this comes from a text input

    // Handle profile image upload
    $profileImage = null;
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['profileImage']['name']);
        
        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadFile)) {
            $profileImage = $_FILES['profileImage']['name'];
        } else {
            echo "Error uploading profile image.";
            exit;
        }
    }

    // Prepare and execute update query
    if ($profileImage) {
        $sql = "UPDATE users SET first_name=?, last_name=?, email=?, age=?, birthday=?, gender=?, profile_image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssisssi", $firstName, $lastName, $email, $age, $birthday, $gender, $profileImage, $id);
    } else {
        $sql = "UPDATE users SET first_name=?, last_name=?, email=?, age=?, birthday=?, gender=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssisss", $firstName, $lastName, $email, $age, $birthday, $gender, $id);
    }

    // Execute SQL statement
    if ($stmt->execute()) {
        echo "Record updated successfully";
        header("Location: dashboard.php");
        exit; // Ensure to exit after redirection
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
