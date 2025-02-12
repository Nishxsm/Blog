<?php
session_start(); // Start the session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login if not authenticated
    exit();
}

include 'config.php'; // Database connection

// Fetch user's posts
$sql = "SELECT * FROM posts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Welcome, " . $_SESSION['username'] . "!</h2>";
echo "<h3>Your Posts:</h3>";

while ($post = $result->fetch_assoc()) {
    echo "<h4>" . $post['title'] . "</h4>";
    echo "<p>" . $post['content'] . "</p>";
}

$stmt->close();
$conn->close();
?>

<!-- Logout Link -->
<a href="logout.php">Logout</a>
