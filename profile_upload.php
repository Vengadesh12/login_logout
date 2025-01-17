<?php
session_start();
$conn = new mysqli("localhost", "root", "", "user_system");

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user']['id'];

if (isset($_FILES['profile_pic'])) {
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

    // Delete old profile picture (if not default)
    $oldPic = $_SESSION['user']['profile_pic'];
    if ($oldPic && $oldPic !== "default.png" && file_exists($oldPic)) {
        unlink($oldPic);
    }

    if (move_uploaded_file($file["tmp_name"], $newFileName)) {
        $conn->query("UPDATE users SET profile_pic='$newFileName' WHERE id=$userId");
        $_SESSION['user']['profile_pic'] = $newFileName;
        echo json_encode(['status' => 'success', 'filename' => $newFileName]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
    }
    exit();
}
?>
