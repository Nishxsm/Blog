<?php
header("Content-Type: text/html");
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config.php';

// Fetch posts
$sql = "SELECT posts.*, users.username FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC";
        

$result = $conn->query($sql);

if (!$result) {
    die("<p class='error'>Error in SQL query: " . $conn->error . "</p>");
}

$output = "";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $title = htmlspecialchars($row['title']);
        $content = nl2br(htmlspecialchars($row['content']));
        $username = htmlspecialchars($row['username']);
        $created_at = date("F j, Y, g:i a", strtotime($row['created_at']));

        $output .= "
            <div class='post'>
                <h4>{$title}</h4>
                <p>{$content}</p>
                <small>Posted by <b>{$username}</b> on {$created_at}</small>
            </div>
        ";
    }
} else {
    $output = "<p class='no-posts'>No posts available.</p>";
}

echo $output;
$conn->close();
?>
