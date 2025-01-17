<?php
session_start();
$conn = new mysqli("localhost", "root", "", "user_system");

// If logout is requested
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// If user is logged in
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $isAdmin = $user['is_admin'];
}

// Handle registration
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dob = $_POST['dob'];
    $age = date_diff(date_create($dob), date_create('today'))->y;
    $mobile = $_POST['mobile'];

    // Validate email and mobile
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format');</script>";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        echo "<script>alert('Mobile number must be 10 digits');</script>";
    } else {
        // Check if user is the first registered (Admin)
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        $isAdmin = ($row['count'] == 0) ? 1 : 0;

        $conn->query("INSERT INTO users (name, email, password, dob, age, mobile, is_admin) 
                      VALUES ('$name', '$email', '$password', '$dob', '$age', '$mobile', '$isAdmin')");
        echo "<script>alert('Registration successful. Please login.'); window.location='index.php';</script>";
    }
}

// Handle login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Invalid password');</script>";
        }
    } else {
        echo "<script>alert('User not found');</script>";
    }
}

// Profile Picture Upload via AJAX
if (isset($_FILES['profile_pic']) && isset($_SESSION['user'])) {
    $userId = $_SESSION['user']['id'];
    $file = $_FILES['profile_pic'];
    $maxFileSize = 150000; // 150KB
    if ($file["size"] > $maxFileSize) {
        echo json_encode(['status' => 'error', 'message' => 'File size exceeds 150KB']);
        exit();
    }
    
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExtension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $newFileName = $uploadDir . "profile_" . $userId . "." . $fileExtension;

    // Delete old profile picture
    $result = $conn->query("SELECT profile_pic FROM users WHERE id = $userId");
    $oldPic = $result->fetch_assoc()['profile_pic'];

    if ($oldPic && $oldPic !== "default.png" && file_exists($oldPic)) {
        unlink($oldPic);
    }

    if (move_uploaded_file($file["tmp_name"], $newFileName)) {
        $conn->query("UPDATE users SET profile_pic='$newFileName' WHERE id=$userId");
        $_SESSION['user']['profile_pic'] = $newFileName; // Update session data
        echo json_encode(['status' => 'success', 'filename' => $newFileName]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User System</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .container { max-width: 400px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        input, button { margin: 5px 0; padding: 8px; width: 100%; }
        img { width: 100px; height: 100px; border-radius: 50%; margin: 10px; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['user'])): ?>
    <div class="container">
        <h2>Login</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <p><a href="#" onclick="toggleForms()">Register</a></p>
    </div>

    <div class="container" id="register-form" style="display:none;">
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="date" name="dob" id="dob" required>
            <input type="text" name="mobile" placeholder="Mobile (10 digits)" required>
            <button type="submit" name="register">Register</button>
        </form>
    </div>
<?php else: ?>
    <div class="container">
        <h2>Welcome, <?= $_SESSION['user']['name'] ?></h2>
        <img id="user-pic" src="<?= $_SESSION['user']['profile_pic'] ?? 'default.png' ?>" alt="Profile Picture">
        <form id="profile-pic-form" enctype="multipart/form-data">
            <input type="file" id="profile-pic" name="profile_pic">
            <button type="submit">Upload</button>
        </form>
        <form method="POST"><button type="submit" name="logout">Logout</button></form>

        <?php if ($isAdmin): ?>
            <h3>Admin Panel</h3>
            <table border="1">
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Age</th></tr>
                <?php
                $users = $conn->query("SELECT * FROM users");
                while ($row = $users->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['age']}</td>
                          </tr>";
                }
                ?>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
function toggleForms() {
    document.querySelector(".container").style.display = "none";
    document.getElementById("register-form").style.display = "block";
}

document.getElementById('profile-pic-form').addEventListener('submit', function (event) {
    event.preventDefault();

    let formData = new FormData(this); // Capture the form data correctly

    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('user-pic').src = data.filename;
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

</body>
</html>
