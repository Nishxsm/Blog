<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

    // Check if the username already exists
    $checkSql = "SELECT id FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if($result->num_rows > 0) {
        $_SESSION['message'] = "Username already taken. Please choose another.";
        $_SESSION['msg_type'] = "error";
        header("Location: register.php");
        exit();
    }
    $checkStmt->close();

    // Proceed with the registration
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Registration successful!";
        $_SESSION['msg_type'] = "success";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['msg_type'] = "error";
        header("Location: register.php");
        exit();
    }
    
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../style.css">
    <style>
.notification {
    position: fixed;
    bottom: 20px;             /* 20px margin from the bottom */
    left: 50%;
    transform: translateX(-50%);
    padding: 15px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    z-index: 1000;
    opacity: 0;               /* start hidden */
}

/* Different background colors for each type */
.notification.success { background: green; }
.notification.error { background: red; }

/* Combined fade in and fade out animation */
@keyframes fadeInOut {
    0% {
        opacity: 0;
        transform: translate(-50%, 20px);
    }
    20% {
        opacity: 1;
        transform: translate(-50%, 0);
    }
    80% {
        opacity: 1;
        transform: translate(-50%, 0);
    }
    100% {
        opacity: 0;
        transform: translate(-50%, 20px);
    }
}

/* Apply the animation */
.notification.show {
    animation: fadeInOut 4s forwards; /* adjust duration as needed */
}


    </style>
</head>
<body>
    <div class="wrapper1">
        <div class="register-container">
            <form action="register.php" method="post">
                <h2>Register</h2>
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button class="register" type="submit">Register</button>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="notification <?php echo $_SESSION['msg_type']; ?>" id="notification">
            <?php echo $_SESSION['message']; ?>
        </div>
        <script>
document.addEventListener("DOMContentLoaded", function() {
    const notif = document.getElementById("notification");
    notif.classList.add("show");

    setTimeout(() => {
        notif.style.display = "none";
    }, 4000);
});
</script>

        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
</body>
</html>
