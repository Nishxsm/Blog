<?php
include 'config.php';

if (isset($_POST['query'])) {
    $searchQuery = trim($_POST['query']);
    $searchQuery = "%$searchQuery%";

    $stmt = $conn->prepare("SELECT id, username FROM users WHERE username LIKE ?");
    $stmt->bind_param("s", $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div><a href='user_profile.php?id=" . $row['id'] . "'>" . htmlspecialchars($row['username']) . "</a></div>";
        }
    } else {
        echo "<div>No users found.</div>";
    }

    $stmt->close();
}

// close connection outside the if statement
if (isset($conn)) {
    $conn->close();
}
?>