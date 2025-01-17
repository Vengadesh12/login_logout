<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $dob = $_POST['dob'];
    $mobile = $_POST['mobile'];

    $age = date_diff(date_create($dob), date_create('today'))->y;

    $checkAdmin = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc();
    $is_admin = ($checkAdmin['count'] == 0) ? 1 : 0;

    $sql = "INSERT INTO users (name, email, password, dob, age, mobile, is_admin) 
            VALUES ('$name', '$email', '$password', '$dob', '$age', '$mobile', '$is_admin')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Registration successful. <a href='index.php'>Login</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <form method="POST">
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="date" name="dob" id="dob" required>
        <input type="text" name="mobile" placeholder="Mobile (10 digits)" required pattern="\d{10}">
        <button type="submit">Register</button>
    </form>
</body>
</html>
