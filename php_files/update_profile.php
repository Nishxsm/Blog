<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include 'config.php';

$user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];

// get updated username and bio from POST data
$username = trim($_POST['username']);
$bio = trim($_POST['bio']);

// only check for username uniqueness if the username is actually changing
if ($username !== $current_username) {
    // check if the new username already exists for another user
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $checkStmt->bind_param("si", $username, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // username already exists
        $checkStmt->close();
        $_SESSION['update_error'] = "Username already taken. Please choose a different one.";
        header("Location: dashboard.php#profile");
        exit();
    }
    $checkStmt->close();
}

// opdate profile picture if a file was uploaded
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $targetDir = "../assets/";
    $fileName = basename($_FILES["profile_pic"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    //file types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        // add a unique identifier to prevent overwriting files with the same name
        $uniqueFileName = time() . '_' . $fileName;
        $targetFilePath = $targetDir . $uniqueFileName;
        
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath)) {
            // update profile_pic field
            $stmtPic = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmtPic->bind_param("si", $uniqueFileName, $user_id);
            $stmtPic->execute();
            $stmtPic->close();
        } else {
            // handle error in file upload
            $_SESSION['update_error'] = "Error uploading profile picture.";
            header("Location: dashboard.php#profile");
            exit();
        }
    } else {
        $_SESSION['update_error'] = "Invalid file type. Allowed: jpg, jpeg, png, gif.";
        header("Location: dashboard.php#profile");
        exit();
    }
}

// update username and bio
$stmt = $conn->prepare("UPDATE users SET username = ?, bio = ? WHERE id = ?");
$stmt->bind_param("ssi", $username, $bio, $user_id);
if ($stmt->execute()) {
    //update session username
    $_SESSION['username'] = $username;
    $_SESSION['update_success'] = "Profile updated successfully!";
    header("Location: dashboard.php"); // redirect back to the dashboard
} else {
    $_SESSION['update_error'] = "Error updating profile: " . $conn->error;
    header("Location: dashboard.php#profile");
}
$stmt->close();
$conn->close();
?>