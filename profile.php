<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
}
$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <script src="script.js"></script>
</head>
<body>
    <h2>Welcome, <?php echo $user['name']; ?></h2>
    <form id="profile-pic-form" enctype="multipart/form-data">
        <input type="file" id="profile-pic" name="profile_pic">
        <button type="submit">Upload</button>
    </form>
    <img id="user-pic" src="uploads/<?php echo $user['profile_pic']; ?>" width="150">
</body>
</html>
