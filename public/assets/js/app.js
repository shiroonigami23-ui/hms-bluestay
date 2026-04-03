document.addEventListener("DOMContentLoaded", () => {
  const menuBtn = document.getElementById("menuBtn");
  const sidebar = document.getElementById("sidebar");
  if (menuBtn && sidebar) {
    menuBtn.addEventListener("click", () => sidebar.classList.toggle("open"));
  }

  const tabButtons = Array.from(document.querySelectorAll(".tab-btn"));
  const tabPanels = Array.from(document.querySelectorAll(".tab-panel"));
  if (tabButtons.length && tabPanels.length) {
    tabButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const key = button.getAttribute("data-tab");
        tabButtons.forEach((b) => b.classList.remove("active"));
        tabPanels.forEach((panel) => panel.classList.remove("active"));
        button.classList.add("active");
        const target = document.getElementById(key || "");
        if (target) target.classList.add("active");
      });
    });
  }
});
