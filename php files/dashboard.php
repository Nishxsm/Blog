<?php
session_start(); // Start the session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login if not authenticated
    exit();
}

include 'config.php'; // Database connection

// Fetch user's posts
$sql = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Side Menu</h2>
            <ul>
                <li><a href="#" onclick="showSection('home')">Home</a></li>
                <li><a href="#" onclick="showSection('explore')">Explore</a></li>
                <li><a href="#" onclick="showSection('profile')">Profile</a></li>
            </ul>
            <a class="logout" href="logout.php">Logout</a>
        </aside>
        <main class="content">
            <section id="home">
                <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
                <h3>Your Posts:</h3>
                <div id="posts-container">
                    <?php while ($post = $result->fetch_assoc()): ?>
                        <div class="post">
                            <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <section id="explore" style="display:none;">
                <h2>Explore Section</h2>
            </section>
            <section id="profile" style="display:none;">
                <h2>Profile Section</h2>
            </section>
        </main>
    </div>
    
    <button class="create-post" onclick="openPostModal()">+</button>
    
    <div id="post-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePostModal()">&times;</span>
            <h2>Create Post</h2>
            <input type="text" id="post-title" placeholder="Title" required>
            <textarea id="post-content" placeholder="Write something..." required></textarea>
            <button onclick="createPost()">Post</button>
        </div>
    </div>
    
    <script>
        function showSection(section) {
            document.querySelectorAll('main section').forEach(sec => sec.style.display = 'none');
            document.getElementById(section).style.display = 'block';
        }

        function openPostModal() {
            document.getElementById('post-modal').style.display = 'block';
        }

        function closePostModal() {
            document.getElementById('post-modal').style.display = 'none';
        }

        function createPost() {
    let title = document.getElementById('post-title').value.trim();
    let content = document.getElementById('post-content').value.trim();

    if (title && content) {
        fetch('create_post.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}`
        })
        .then(response => response.text())
        .then(data => {
            console.log("Server response:", data); // Debugging
            if (data.includes("Post created successfully")) { // Match response
                let postContainer = document.getElementById('posts-container');
                let newPost = document.createElement('div');
                newPost.classList.add('post');
                newPost.innerHTML = `<h4>${title}</h4><p>${content}</p>`;
                postContainer.prepend(newPost);
                closePostModal();
            } else {
                alert("Failed to create post: " + data);
            }
        })
        .catch(error => console.error('Error:', error));
    } else {
        alert("Title and content cannot be empty!");
    }
}

    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
