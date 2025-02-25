// Global function to switch sections
window.showSection = function (section) {
    // Hide all sections
    document.querySelectorAll("main section").forEach(sec => sec.style.display = "none");
  
    // Show the selected section
    const selectedSection = document.getElementById(section);
    if (selectedSection) {
      selectedSection.style.display = "block";
    }
  
    // Update side-menu active state
    document.querySelectorAll(".side-menu li").forEach(item => item.classList.remove("active"));
    const activeItem = document.querySelector(`.side-menu li[data-section="${section}"]`);
    if (activeItem) {
      activeItem.classList.add("active");
    }
  };
  
  // Define deletePost globally, outside the DOMContentLoaded event
  // This ensures it's available even if some elements aren't loaded
  window.deletePost = function (postId, btn) {
    if (confirm("Are you sure you want to delete this post?")) {
      fetch("/blog/php_files/delete_post.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "post_id=" + encodeURIComponent(postId)
      })
      .then(response => response.text())
      .then(data => {
        if (data.trim() === "success") {
          alert("Post deleted successfully!");
          // Remove the post from the DOM
          const postElem = btn.closest(".post");
          if (postElem) {
            postElem.remove();
          } else {
            // Fallback: if not found, reload the page
            location.reload();
          }
        } else {
          alert("Error deleting post: " + data);
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("Error deleting post.");
      });
    }
  };
  
  
  document.addEventListener("DOMContentLoaded", function () {
    // Cache DOM elements with null checks
    const sideMenu = document.getElementById("sideMenu");
    const mainContent = document.getElementById("mainContent");
    const burger = document.getElementById("burgerButton");
    const modal = document.getElementById("post-modal");
    const titleInput = document.getElementById("post-title");
    const contentInput = document.getElementById("post-content");
    const postContainer = document.getElementById("posts-container");
    const exploreContainer = document.getElementById("explore-posts");
  
    // 🟢 Toggle Menu Functionality
    if (burger && sideMenu && mainContent) {
      burger.addEventListener("click", function () {
        sideMenu.classList.toggle("active");
        mainContent.classList.toggle("shift");
        burger.classList.toggle("active");
      });
    } else {
      console.warn("Burger button, sideMenu, or mainContent not found.");
    }
  
    // 🟢 Modal Functions
    function openPostModal() {
      if (modal && titleInput) {
        modal.style.display = "flex"; // Ensure modal uses flex for centering
        titleInput.focus();
      } else {
        console.warn("Modal or title input not found.");
      }
    }
    
    function closePostModal() {
      if (modal && titleInput && contentInput) {
        modal.style.display = "none";
        titleInput.value = "";
        contentInput.value = "";
      }
    }
    
    // 🟢 Create a New Post
    function createPost() {
      if (!titleInput || !contentInput || !postContainer) {
        console.warn("Post creation elements not found.");
        return;
      }
      const title = titleInput.value.trim();
      const content = contentInput.value.trim();
      if (!title || !content) {
        return alert("Title and content cannot be empty!");
      }
      
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
      if (exploreContainer) {
        exploreContainer.innerHTML = "<div class='loading'>Loading posts...</div>";
        fetch("fetch_all_posts.php")
        .then(response => response.text())
        .then(html => {
          exploreContainer.innerHTML = html;
        })
        .catch(() => {
          exploreContainer.innerHTML = "<div class='error'>Error loading posts. Please try again.</div>";
        });
      }
    }
    
    // 🟢 Close Modal when clicking outside of it
    if (modal) {
      window.addEventListener("click", function (event) {
        if (event.target === modal) {
          closePostModal();
        }
      });
    }
    
    // 🟢 Handle Enter Key for Post Creation - FIXED: Added null checks
    if (titleInput && contentInput) {
      titleInput.addEventListener("keydown", function(event) {
        if (event.key === "Enter" && !event.shiftKey) {
          event.preventDefault();
          createPost();
        }
      });
      
      contentInput.addEventListener("keydown", function(event) {
        if (event.key === "Enter" && !event.shiftKey) {
          event.preventDefault();
          createPost();
        }
      });
    }
    
    // 🟢 Load Explore posts when Explore tab is clicked
    const exploreTab = document.querySelector("[data-section='explore']");
    if (exploreTab) {
      exploreTab.addEventListener("click", loadExplorePosts);
    }
    
    // 🟢 Remove active class from side menu if clicking outside of it
    if (sideMenu && burger) {
      document.addEventListener("click", function (event) {
        if (!sideMenu.contains(event.target) && !burger.contains(event.target)) {
          sideMenu.classList.remove("active");
        }
      });
    }
    
    // Expose modal functions globally
    window.openPostModal = openPostModal;
    window.closePostModal = closePostModal;
    window.createPost = createPost;
    
    // Initially show the home section
    showSection('home');
  });

  function toggleEditProfile() {
    const form = document.getElementById("editProfileForm");
    if (form) {
        // Toggle display style between none and block
        form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
    }
}
