<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'config.php';

// Fetch user details and posts using LEFT JOIN
$stmt = $conn->prepare("SELECT users.username, users.bio, users.profile_pic, posts.id AS post_id, posts.title, posts.content, posts.created_at 
                        FROM users 
                        LEFT JOIN posts ON users.id = posts.user_id 
                        WHERE users.id = ? 
                        ORDER BY posts.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the first row to extract user details
$firstRow = $result->fetch_assoc();
if ($firstRow === null) {
    $userDetails = [];
    $posts = [];
} else {
    $userDetails = [
        'username'    => $firstRow['username'],
        'bio'         => $firstRow['bio'],
        'profile_pic' => $firstRow['profile_pic']
    ];
    
    $posts = [];
    if (!empty($firstRow['title'])) {
        $posts[] = [
            'id'         => $firstRow['post_id'],
            'title'      => $firstRow['title'],
            'content'    => $firstRow['content'],
            'created_at' => $firstRow['created_at']
        ];
    }
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['title'])) {
            $posts[] = [
                'id'         => $row['post_id'],
                'title'      => $row['title'],
                'content'    => $row['content'],
                'created_at' => $row['created_at']
            ];
        }
    }
}
$stmt->close();

// Fetch all posts for Explore section
$explore_stmt = $conn->prepare("SELECT posts.id AS post_id, posts.title, posts.content, posts.created_at, users.username, users.profile_pic 
                                FROM posts 
                                JOIN users ON posts.user_id = users.id 
                                ORDER BY posts.created_at DESC");
$explore_stmt->execute();
$explore_posts = $explore_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$explore_stmt->close();

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
            <i class="fa-solid fa-bars"></i>
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
                    <?php if (!empty($explore_posts)): ?>
                        <?php foreach ($explore_posts as $post): ?>
                            <div class="post">
                                <div class="post-header">
                                    <img src="<?php echo !empty($post['profile_pic']) ? '../assets/' . htmlspecialchars($post['profile_pic']) : '../assets/default-pfp.jpg'; ?>" alt="Profile Picture" class="profile-pic">
                                    <span class="post-username"> <?php echo htmlspecialchars($post['username']); ?> </span>
                                </div>
                                <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                <small>Created on <?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No posts available yet.</p>
                    <?php endif; ?>
                </div>
            </section>
            
            <section id="profile" style="display:none;">
                
    <div class="profile-header">
        <img src="<?php echo !empty($userDetails['profile_pic']) ? '../assets/' . htmlspecialchars($userDetails['profile_pic']) : '../assets/default-pfp.jpg'; ?>" alt="Profile Picture" class="profile-pic">
        <div class="profile-details">
            <h3 class="username"><?php echo htmlspecialchars($userDetails['username']); ?></h3>
            <p class="bio"><?php echo !empty($userDetails['bio']) ? htmlspecialchars($userDetails['bio']) : "No bio yet."; ?></p>
        </div>
        <!-- Edit Profile Button -->
        <button onclick="toggleEditProfile()">Edit Profile</button>
    </div>
    
    <!-- Hidden Edit Profile Form -->
    <form id="editProfileForm" action="update_profile.php" method="POST" enctype="multipart/form-data" style="display:none;">
        <label for="newUsername">Username:</label>
        <input type="text" id="newUsername" name="username" value="<?php echo htmlspecialchars($userDetails['username']); ?>" required>
        
        <label for="newBio">Bio:</label>
        <textarea id="newBio" name="bio" placeholder="Enter your bio..."><?php echo htmlspecialchars($userDetails['bio']); ?></textarea>
        
        <label for="newProfilePic">Profile Picture:</label>
        <input type="file" id="newProfilePic" name="profile_pic" accept="image/*">
        
        <button type="submit">Save Changes</button>
    </form>
    
    <h3 class="posts-title">Posts</h3>
    <div id="posts-container">
        <!-- Your posts loop remains here -->
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                    <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <small>Created on <?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></small>
                    <button class="delete-post" onclick="deletePost(<?php echo $post['id']; ?>, this)">Delete</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-posts">You haven't posted anything yet.</p>
        <?php endif; ?>
    </div>
</section>

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
        </main>
    </div>
    <script src="../js_files/dashboard.js"></script>
</body>
</html>
