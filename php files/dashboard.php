<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

// Fetch only logged-in user's posts for the profile section
$stmt = $conn->prepare("SELECT title, content FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

</head>
<body>
    <div class="dashboard-container">
    <button class="burger-icon" id="burgerButton" aria-label="Toggle Menu">
    <i class="fa-solid fa-bars"></i> <!-- Burger Icon -->
</button>


        
        <nav class="side-menu" id="sideMenu">
            <ul>
            <li onclick="showSection('home')" data-section="home">Home</li>
            <li onclick="showSection('explore')" data-section="explore">Explore</li>
            <li onclick="showSection('profile')" data-section="profile">Profile</li>

                <li class="menu-item logout"><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="content" id="mainContent">
            <section id="home">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            </section>
            <section id="explore" style="display:none;">
                <h2>Explore Section</h2>
                <div id="explore-posts">
                    
                </div>
            </section>
            <section id="profile" style="display:none;">
                <h2>Profile Section</h2>
                <h3>Your Posts:</h3>
                <div id="posts-container">
                    <?php foreach ($posts as $post): ?>
                        <div class="post">
                            <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <button class="create-post" onclick="openPostModal()">+</button>


    <div id="post-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePostModal()">&times;</span>
            <h2>Whats on your mind today....</h2>
            <input type="text" id="post-title" placeholder="Title" required>
            <textarea id="post-content" placeholder="Write something..." required></textarea>
            <button class="post-btn" onclick="createPost()">Post</button>
        </div>
    </div>
    
    <script src="../js_files/dashboard.js"></script>

</body>
</html>
