<?php
session_start();

include "database.php";

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

// Handle logout (if needed)
if (isset($_POST['logout'])) {
    session_destroy();
    header("location: login.php");
    exit;
}

// Fetch logged-in user data from session
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$profile_image = $_SESSION['profile_image'];

// Fetch soft-deleted users data
$sql = "SELECT id, first_name, last_name, email, age, birthday, gender, profile_image FROM users WHERE deleted = 1";
$result = $conn->query($sql);

// Check if query executed successfully
if (!$result) {
    die("Query failed: " . $conn->error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-f-100 flex items-center justify-center h-screen">
    
    <div class="bg-purple-400 p-8 rounded-lg shadow-lg w-full max-w-5xl">
      
        <h2 class="text-2xl text-center font-bold mb-6 text-gray-800">Deleted Users</h2>
      
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">ID</th>
                    <th class="py-2 px-4 border-b">First Name</th>
                    <th class="py-2 px-4 border-b">Last Name</th>
                    <th class="py-2 px-4 border-b">Email</th>
                    <th class="py-2 px-4 border-b">Age</th>
                    <th class="py-2 px-4 border-b">Birthday</th>
                    <th class="py-2 px-4 border-b">Gender</th>
                    <th class="py-2 px-4 border-b">Profile Image</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Reset result pointer and fetch data for display
                $result->data_seek(0);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['first_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['last_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['age']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['birthday']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['gender']); ?></td>
                            <td class="py-2 px-4 border-b">
                                <?php if (!empty($row['profile_image'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($row['profile_image']); ?>" class="w-20 h-20 rounded-full object-cover" alt="Profile Image">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td class="py-2 px-4 border-b">
                                <form method="post" action="recover.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="bg-green-600 text-white py-1 px-3 rounded hover:bg-green-500">Recover</button>
                                </form>
                               
                            </td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="9" class="py-2 px-4 border-b text-center">No deleted records found</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
