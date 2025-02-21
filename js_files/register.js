document.addEventListener("DOMContentLoaded", function() {
    const notif = document.getElementById("notification");
    notif.classList.add("show");

    setTimeout(() => {
        notif.style.display = "none";
    }, 4000);
});