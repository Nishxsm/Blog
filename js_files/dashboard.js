window.showSection = function (section) {
    document.querySelectorAll("main section").forEach(sec => sec.style.display = "none");
    const selectedSection = document.getElementById(section);
    if (selectedSection) {
        selectedSection.style.display = "block";
    }
    document.querySelectorAll(".side-menu li").forEach(item => item.classList.remove("active"));
    document.querySelector(`.side-menu li[data-section="${section}"]`).classList.add("active");
};

document.addEventListener("DOMContentLoaded", function () {
    const menu = document.getElementById("sideMenu");
    const content = document.getElementById("mainContent");
    const burger = document.getElementById("burgerButton");
    const modal = document.getElementById("post-modal");
    const titleInput = document.getElementById("post-title");
    const contentInput = document.getElementById("post-content");
    const postContainer = document.getElementById("posts-container");
    const exploreContainer = document.getElementById("explore-posts");

    // 🟢 Toggle Menu Functionality
    burger.addEventListener("click", function () {
        menu.classList.toggle("active");
        content.classList.toggle("shift");
        burger.classList.toggle("active");
    });

    // 🟢 Open & Close Post Modal
    function openPostModal() {
        modal.style.display = "block";
        titleInput.focus();
    }

    function closePostModal() {
        modal.style.display = "none";
        titleInput.value = "";
        contentInput.value = "";
    }

    // 🟢 Create a New Post
    function createPost() {
        const title = titleInput.value.trim();
        const content = contentInput.value.trim();
        if (!title || !content) return alert("Title and content cannot be empty!");

        fetch("create_post.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("Post created successfully")) {
                const newPost = document.createElement("div");
                newPost.classList.add("post");
                newPost.innerHTML = `<h4>${title}</h4><p>${content.replace(/\n/g, "<br>")}</p><small>Just now</small>`;
                postContainer.prepend(newPost);
                closePostModal();
            } else {
                alert("Failed to create post: " + data);
            }
        })
        .catch(() => alert("Error creating post. Please try again."));
    }

    // 🟢 Load Posts for Explore Section
    function loadExplorePosts() {
        exploreContainer.innerHTML = "<div class='loading'>Loading posts...</div>";

        fetch("fetch_all_posts.php")
            .then(response => response.text()) // Directly using text
            .then(html => {
                exploreContainer.innerHTML = html; // Directly inserting HTML (no JSON)
            })
            .catch(() => {
                exploreContainer.innerHTML = "<div class='error'>Error loading posts. Please try again.</div>";
            });
    }

    // 🟢 Handle Clicking Outside Modal to Close
    window.addEventListener("click", event => { 
        if (event.target === modal) closePostModal(); 
    });

    // 🟢 Handle Enter Key for Post Creation
    [titleInput, contentInput].forEach(input => input.addEventListener("keydown", event => {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            createPost();
        }
    }));

    // ✅ Make Modal Functions Global
    window.openPostModal = openPostModal;
    window.closePostModal = closePostModal;
    window.createPost = createPost;
});

function loadExplorePosts() {
    fetch("fetch_all_posts.php")
        .then(response => response.text())
        .then(html => {
            console.log("Fetched posts:", html);  // Debugging: Check if posts are being fetched
            let exploreSection = document.getElementById("explore-posts");
            if (exploreSection) {
                exploreSection.innerHTML = html;  // Ensure this ID exists in your HTML
            } else {
                console.error("Element with ID 'explore-posts' not found!");
            }
        })
        .catch(error => {
            console.error("Fetch error:", error);
        });
}

// Ensure it loads when Explore tab is clicked
document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("[data-section='explore']").addEventListener("click", function () {
        loadExplorePosts();
    });
});
