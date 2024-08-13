<?php
// Include database connection configuration
include "database.php";

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to manage user login
session_start();

// Redirect to login if user is not logged in
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

// Handle logout request
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: Login.php");
    exit;
}

// Fetch logged-in user data from session
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$profile_image = $_SESSION['profile_image'];

// Fetch users data excluding soft deleted records
$sql = "SELECT id, first_name, last_name, email, age, birthday, gender, profile_image FROM users WHERE deleted = 0";

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
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        table.dataTable thead th {
            background-color: #A855F7; 
            color: white;
        }
       
        .profile-container {
            margin-bottom: 30px; 
            margin-top: 50px;
            
        }
    </style>
</head>

<body class="bg-gray-100  items-center justify-center h-screen">
<div class="bg-purple-400 w-[700px] h-[150px] p-6 rounded-lg justify-center shadow relative profile-container mx-auto">
   
        <div class="flex items-center">
            <div class="w-20 h-20">
                <?php if (!empty($profile_image)): ?>
                    <img src="uploads/<?php echo htmlspecialchars($profile_image); ?>" class="w-20 h-20 rounded-full object-cover" alt="Profile Image">
                <?php else: ?>
                    <img src="default_profile_image.png" class="w-20 h-20 rounded-full object-cover" alt="Profile Image">
                <?php endif; ?>
            </div>
            <div class="ml-4">
                <h2 class="text-xl text-white font-bold">HELLO!</h2>
                <h2 class="text-[45px] text-white font-bold"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h2>
            </div>
        </div>
        <div class="absolute top-0 right-0 mt-2 mr-2">
    <form id="logoutForm" method="post">
        <button type="button" id="logoutButton" class="bg-white text-gray-400 w-15 py-1 px-4 rounded hover:bg-purple-600 focus:outline-none">Logout</button>
    </form>
 </div>
    </div>

    <div class="container mt-8">
        <table id="myTable" class="stripe min-w-full rounded-xl overflow-hidden">
            <thead class="bg-purple-400">
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
                <?php while ($row = $result->fetch_assoc()): ?>
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
                            <div class="flex gap-2">
                              
                                <button type="button" class="bg-blue-600 text-white py-1 px-3 rounded hover:bg-blue-500" data-toggle="modal" data-target="#editUserModal<?php echo $row['id']; ?>">Update</button>
                               
                                <button type="button" class="bg-red-600 text-white py-1 px-3 rounded hover:bg-red-500" data-toggle="modal" data-target="#deleteUserModal<?php echo $row['id']; ?>">Delete</button>
                            </div>
                        </td>
                    </tr>
                     <!-- Modal for Delete User -->
<div class="modal fade" id="deleteUserModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content rounded-lg">
            <div class="modal-header bg-purple-500 text-white rounded-t-lg">
                <h5 class="modal-title" id="deleteUserModalLabel<?php echo $row['id']; ?>">Confirm Deletion</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>An OTP has been sent to the email of the user to be deleted.</p>
                <p>You are about to delete: <span id="deleteUserName<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></span></p>
                <form id="otpForm<?php echo $row['id']; ?>" method="POST">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <div class="form-group">
                        <label for="otp<?php echo $row['id']; ?>">Enter OTP:</label>
                        <div class="input-group">
                            <input type="text" name="otp" id="otp<?php echo $row['id']; ?>" required class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:border-purple-500">
                            <button type="button" class="ml-2 bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700" id="getOtpBtn<?php echo $row['id']; ?>">Get OTP</button>
                        </div>
                    </div>
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:bg-purple-700 mt-3">Verify OTP</button>
                </form>
            </div>
        </div>
    </div>
</div>

                                     <!-- Modal for Update User -->
                    <div class="modal fade" id="editUserModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content rounded-lg">
                                <div class="modal-header bg-purple-500 text-white rounded-t-lg">
                                    <h5 class="modal-title" id="editUserModalLabel<?php echo $row['id']; ?>">Edit User: <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                   <!-- Edit form  -->
<form id="updateForm<?php echo $row['id']; ?>" action="update.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    <div class="form-group">
        <label for="editFirstName<?php echo $row['id']; ?>">First Name</label>
        <input type="text" class="form-control" id="editFirstName<?php echo $row['id']; ?>" name="firstName" value="<?php echo htmlspecialchars($row['first_name']); ?>">
    </div>
    <div class="form-group">
        <label for="editLastName<?php echo $row['id']; ?>">Last Name</label>
        <input type="text" class="form-control" id="editLastName<?php echo $row['id']; ?>" name="lastName" value="<?php echo htmlspecialchars($row['last_name']); ?>">
    </div>
    <div class="form-group">
        <label for="editEmail<?php echo $row['id']; ?>">Email</label>
        <input type="email" class="form-control" id="editEmail<?php echo $row['id']; ?>" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="editAge<?php echo $row['id']; ?>">Age</label>
            <input type="number" class="form-control" id="editAge<?php echo $row['id']; ?>" name="age" value="<?php echo htmlspecialchars($row['age']); ?>">
        </div>
        <div class="form-group col-md-4">
            <label for="editBirthday<?php echo $row['id']; ?>">Birthday</label>
            <input type="date" class="form-control" id="editBirthday<?php echo $row['id']; ?>" name="birthday" value="<?php echo htmlspecialchars($row['birthday']); ?>">
        </div>
        <div class="form-group col-md-4">
            <label for="editGender<?php echo $row['id']; ?>">Gender</label>
            <select class="form-control" id="editGender<?php echo $row['id']; ?>" name="gender">
                <option value="Male" <?php if ($row['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if ($row['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                <option value="Other" <?php if ($row['gender'] === 'Other') echo 'selected'; ?>>Other</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="editProfileImage<?php echo $row['id']; ?>">Profile Image</label>
        <input type="file" class="form-control-file" id="editProfileImage<?php echo $row['id']; ?>" name="profileImage">
    </div>
    <div class="text-right">
        <button type="button" id="saveChangesButton<?php echo $row['id']; ?>" class="bg-purple-500 text-white py-2 px-4 rounded hover:bg-purple-400">Save Changes</button>
    </div>
</form>


<script>
    $(document).ready(function() {
        // Function to set user information dynamically in the delete modal
        $('#deleteUserModal<?php echo $row['id']; ?>').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var firstName = "<?php echo htmlspecialchars($row['first_name']); ?>";
            var lastName = "<?php echo htmlspecialchars($row['last_name']); ?>";
            var modal = $(this);
            modal.find('.modal-body #deleteUserName<?php echo $row['id']; ?>').text(firstName + ' ' + lastName);
        });

        // Handle Get OTP button click
        $('#getOtpBtn<?php echo $row['id']; ?>').on('click', function() {
            var userId = "<?php echo $row['id']; ?>";
            $.ajax({
                type: 'POST',
                url: 'send_otp.php',
                data: { id: userId },
                success: function(response) {
                    alert('OTP has been sent to the user\'s email.');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        document.getElementById('saveChangesButton<?php echo $row['id']; ?>').addEventListener('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to save the changes?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, save it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('updateForm<?php echo $row['id']; ?>').submit();
            }
        });
    });
        //Logout
    document.getElementById('logoutButton').addEventListener('click', function() {
        Swal.fire({
            title: 'Confirm Logout',
            text: 'Are you sure you want to logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to Login.php after logout confirmation
                window.location.href = 'Login.php';
            }
        });
    });
        // Function to handle form submission for delete action
        $('#otpForm<?php echo $row['id']; ?>').on('submit', function(event) {
                event.preventDefault(); // Prevent default form submission
                var form = $(this);
                var formData = form.serialize(); // Serialize form data
                $.ajax({
                    type: form.attr('method'), // Use form's method attribute
                    url: 'delete.php', // Directly handle deletion in this script
                    data: formData, // Use serialized form data
                    dataType: 'json', // Expect JSON response from server
                    success: function(response) {
                        // Handle success response
                        console.log(response);
                        if (response.success) {
                            // Optionally show a success message
                            alert(response.message);
                            // Redirect to the dashboard after successful deletion
                            window.location.href = response.redirect;
                        } else {
                            // Handle error case if needed
                            alert('Failed to delete user: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle AJAX error case
                        alert('An error occurred: ' + error);
                    }
                });
            });
    });
</script>

<?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                "dom": '<"top"lf>rt<"bottom"ip><"clear">',
                "language": {
                    "lengthMenu": "Show _MENU_ entries"
                }
            });
        });

    </script>
    <script>
    document.getElementById('saveChangesButton<?php echo $row['id']; ?>').addEventListener('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to save the changes?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, save it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('updateForm<?php echo $row['id']; ?>').submit();
            }
        });
    });
</script>

</body>

</html>

<?php $conn->close(); ?>
