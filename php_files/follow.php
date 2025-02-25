<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Not logged in
    http_response_code(403);
    echo "Not authorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config.php';
    
    $follower_id = $_SESSION['user_id'];       // The user who is following
    $following_id = $_POST['following_id'];    // The user to be followed/unfollowed

    // Check if user is trying to follow themselves
    if ($follower_id == $following_id) {
        echo "You cannot follow yourself!";
        exit();
    }

    // Check if there's an existing follow relationship
    $check_stmt = $conn->prepare("
        SELECT id 
        FROM followers 
        WHERE follower_id = ? AND following_id = ?
        LIMIT 1
    ");
    $check_stmt->bind_param('ii', $follower_id, $following_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $check_stmt->close();

    if ($result->num_rows > 0) {
        // Already following -> Unfollow (DELETE)
        $del_stmt = $conn->prepare("
            DELETE FROM followers
            WHERE follower_id = ? AND following_id = ?
        ");
        $del_stmt->bind_param('ii', $follower_id, $following_id);
        $del_stmt->execute();
        $del_stmt->close();
        echo 'unfollowed';
    } else {
        // Not following -> Follow (INSERT)
        $ins_stmt = $conn->prepare("
            INSERT INTO followers (follower_id, following_id)
            VALUES (?, ?)
        ");
        $ins_stmt->bind_param('ii', $follower_id, $following_id);
        $ins_stmt->execute();
        $ins_stmt->close();
        echo 'followed';
    }

    $conn->close();
} else{
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}
?>