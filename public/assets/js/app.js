function createDialog() {
  const backdrop = document.getElementById("appDialogBackdrop");
  const title = document.getElementById("appDialogTitle");
  const message = document.getElementById("appDialogMessage");
  const input = document.getElementById("appDialogInput");
  const cancelBtn = document.getElementById("appDialogCancel");
  const okBtn = document.getElementById("appDialogOk");

  let resolver = null;

  function close(result) {
    if (backdrop) backdrop.hidden = true;
    if (resolver) resolver(result);
    resolver = null;
  }

  okBtn?.addEventListener("click", () => {
    close(input && !input.hidden ? input.value : true);
  });

  cancelBtn?.addEventListener("click", () => close(false));
  backdrop?.addEventListener("click", (e) => {
    if (e.target === backdrop) close(false);
  });

  function show(options) {
    if (!backdrop || !title || !message || !cancelBtn || !okBtn || !input) {
      return Promise.resolve(false);
    }
    title.textContent = options.title || "Notice";
    message.textContent = options.message || "";
    okBtn.textContent = options.okText || "OK";
    cancelBtn.textContent = options.cancelText || "Cancel";
    cancelBtn.hidden = !!options.hideCancel;
    if (options.type === "prompt") {
      input.hidden = false;
      input.value = options.defaultValue || "";
      input.placeholder = options.placeholder || "";
    } else {
      input.hidden = true;
      input.value = "";
    }
    backdrop.hidden = false;
    return new Promise((resolve) => {
      resolver = resolve;
    });
  }

  return {
    alert(msg, titleText = "Notice") {
      return show({ title: titleText, message: msg, hideCancel: true });
    },
    confirm(msg, titleText = "Confirm") {
      return show({ title: titleText, message: msg });
    },
    prompt(msg, defaultValue = "") {
      return show({ title: "Input Required", message: msg, type: "prompt", defaultValue });
    },
  };
}

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

  const dialog = createDialog();
  window.safeDialog = dialog;

  window.alert = (msg) => dialog.alert(String(msg ?? ""));
  window.confirm = (msg) => dialog.confirm(String(msg ?? ""));
  window.prompt = (msg, def) => dialog.prompt(String(msg ?? ""), String(def ?? ""));

  window.addEventListener("error", (event) => {
    event.preventDefault();
    dialog.alert("An unexpected error occurred. The app recovered safely.", "Recovered Error");
  });

  window.addEventListener("unhandledrejection", (event) => {
    event.preventDefault();
    dialog.alert("A network or script action failed safely. Please retry.", "Recovered Promise Error");
  });
});
