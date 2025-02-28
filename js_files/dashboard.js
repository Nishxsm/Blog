// global function to switch sections with session storage persistence
window.showSection = function (section) {
  sessionStorage.setItem("lastViewedSection", section);
  document.querySelectorAll("main section").forEach(sec => sec.style.display = "none");
  const selectedSection = document.getElementById(section);
  if (selectedSection) {
      selectedSection.style.display = "block";
  }
  document.querySelectorAll(".side-menu li").forEach(item => item.classList.remove("active"));
  const activeItem = document.querySelector(`.side-menu li[data-section="${section}"]`);
  if (activeItem) {
      activeItem.classList.add("active");
  }
};

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
              const postElem = btn.closest(".post");
              postElem ? postElem.remove() : location.reload();
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
  const sideMenu = document.getElementById("sideMenu");
  const mainContent = document.getElementById("mainContent");
  const burger = document.getElementById("burgerButton");
  const modal = document.getElementById("post-modal");
  const titleInput = document.getElementById("post-title");
  const contentInput = document.getElementById("post-content");
  const postContainer = document.getElementById("posts-container");
  const exploreContainer = document.getElementById("explore-posts");

  if (burger && sideMenu && mainContent) {
      burger.addEventListener("click", function () {
          sideMenu.classList.toggle("active");
          mainContent.classList.toggle("shift");
          burger.classList.toggle("active");
      });
  }

  function openPostModal() {
      if (modal && titleInput) {
          modal.style.display = "flex";
          titleInput.focus();
      }
  }
  
  function closePostModal() {
      if (modal && titleInput && contentInput) {
          modal.style.display = "none";
          titleInput.value = "";
          contentInput.value = "";
      }
  }
  
  function createPost() {
      if (!titleInput || !contentInput || !postContainer) return;
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
  
  if (modal) {
      window.addEventListener("click", event => {
          if (event.target === modal) closePostModal();
      });
  }
  
  if (titleInput && contentInput) {
      [titleInput, contentInput].forEach(input => {
          input.addEventListener("keydown", event => {
              if (event.key === "Enter" && !event.shiftKey) {
                  event.preventDefault();
                  createPost();
              }
          });
      });
  }
  
  const exploreTab = document.querySelector("[data-section='explore']");
  if (exploreTab) exploreTab.addEventListener("click", loadExplorePosts);
  
  if (sideMenu && burger) {
      document.addEventListener("click", event => {
          if (!sideMenu.contains(event.target) && !burger.contains(event.target)) {
              sideMenu.classList.remove("active");
          }
      });
  }
  
  window.openPostModal = openPostModal;
  window.closePostModal = closePostModal;
  window.createPost = createPost;
  
  const lastViewedSection = sessionStorage.getItem("lastViewedSection") || "home";
  showSection(lastViewedSection);
});

function toggleEditProfile() {
  const form = document.getElementById("editProfileForm");
  if (form) form.style.display = form.style.display === "none" || form.style.display === "" ? "block" : "none";
}

function searchUsers() {
  if (window.searchTimeout) clearTimeout(window.searchTimeout);

  const searchQuery = document.getElementById("searchUser").value.trim();
  const searchResults = document.getElementById("searchResults");

  if (!searchQuery) {
      searchResults.style.display = "none"; // Hide when empty
      return;
  }

  searchResults.style.display = "block"; // Show when searching
  searchResults.innerHTML = "<div>Searching...</div>";

  window.searchTimeout = setTimeout(() => {
      fetch("/blog/php_files/search_users.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "query=" + encodeURIComponent(searchQuery)
      })
      .then(response => response.text())
      .then(data => {
          searchResults.innerHTML = data.trim() ? data : "<div>No users found</div>";
      })
      .catch(error => {
          searchResults.innerHTML = "<div>Error searching users</div>";
          console.error("Search error:", error);
      });
  }, 300);
}

