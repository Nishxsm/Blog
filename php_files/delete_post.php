<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "unauthorized";
    exit();
}

include 'config.php';

if (isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    
    // Verify that the post belongs to the current user
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['user_id'] == $_SESSION['user_id']) {
            // User owns the post, proceed with deletion
            $delete_stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
            $delete_stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
            
            if ($delete_stmt->execute()) {
                echo "success";
            } else {
                echo "Error: " . $conn->error;
            }
            $delete_stmt->close();
        } else {
            echo "You do not have permission to delete this post.";
        }
    } else {
        echo "Post not found.";
    }
    
    $stmt->close();
} else {
    echo "No post ID provided.";
}

$conn->close();
?>