<?php
session_start();
include 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid user.");
}

$user_id = intval($_GET['id']); //get the user ID from the URL

// fetch user details
$stmt = $conn->prepare("SELECT username, bio, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$stmt->close();

// fetch user posts
$postStmt = $conn->prepare("SELECT id, title, content, created_at FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$postStmt->bind_param("i", $user_id);
$postStmt->execute();
$posts = $postStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile</title>
    <link rel="stylesheet" href="../style.css"> <!-- Updated path if your CSS is in the parent directory -->
</head>
<body>
<div class="back-button-container">
    <button onclick="history.back()" class="back-button">&larr;</button>
</div>


    <div class="profile-container">
        <img src="<?php echo !empty($user['profile_pic']) ? '../assets/' . htmlspecialchars($user['profile_pic']) : '../assets/default-pfp.jpg'; ?>" alt="Profile Picture" class="profile-pic">
        <h2><?php echo htmlspecialchars($user['username']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
    </div>

    <div class="user-posts">
        <h3><?php echo htmlspecialchars($user['username']); ?>'s Posts</h3>
        
        <?php if ($posts->num_rows > 0): ?>
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="post">
                    <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <small>Posted on: <?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No posts yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$postStmt->close();
$conn->close();
?>