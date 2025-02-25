<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include 'config.php';

$user_id = $_SESSION['user_id'];

// Get updated username and bio from POST data
$username = trim($_POST['username']);
$bio = trim($_POST['bio']);

// Update profile picture if a file was uploaded
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $targetDir = "../assets/";
    $fileName = basename($_FILES["profile_pic"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath)) {
            // Update profile_pic field
            $stmtPic = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmtPic->bind_param("si", $fileName, $user_id);
            $stmtPic->execute();
            $stmtPic->close();
        } else {
            // Handle error in file upload if necessary
            echo "Error uploading profile picture.";
            exit();
        }
    } else {
        echo "Invalid file type. Allowed: jpg, jpeg, png, gif.";
        exit();
    }
}

// Update username and bio
$stmt = $conn->prepare("UPDATE users SET username = ?, bio = ? WHERE id = ?");
$stmt->bind_param("ssi", $username, $bio, $user_id);
if ($stmt->execute()) {
    // Update session username if needed
    $_SESSION['username'] = $username;
    header("Location: dashboard.php"); // Redirect back to the dashboard
} else {
    echo "Error updating profile: " . $conn->error;
}
$stmt->close();
$conn->close();
?>
