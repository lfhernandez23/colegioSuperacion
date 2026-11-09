/* Comportamiento común del menú administrativo. Sin dependencias externas. */
(() => {
  "use strict";

  document.addEventListener("DOMContentLoaded", () => {
    const header = document.querySelector(".site-header");
    if (!header) return;

    const menuToggle = header.querySelector(".menu-toggle");
    const navigation = header.querySelector(".site-nav");
    const groups = Array.from(header.querySelectorAll(".nav-item.has-submenu"));

    function closeGroups(except = null) {
      groups.forEach((group) => {
        if (group === except) return;
        group.classList.remove("is-open");
        const trigger = group.querySelector(".nav-trigger");
        if (trigger) trigger.setAttribute("aria-expanded", "false");
      });
    }

    if (menuToggle && navigation) {
      menuToggle.addEventListener("click", () => {
        const isOpen = navigation.classList.toggle("is-open");
        menuToggle.setAttribute("aria-expanded", String(isOpen));
        if (!isOpen) closeGroups();
      });
    }

    groups.forEach((group) => {
      const trigger = group.querySelector(".nav-trigger");
      if (!trigger) return;
      trigger.addEventListener("click", () => {
        const willOpen = !group.classList.contains("is-open");
        closeGroups(group);
        group.classList.toggle("is-open", willOpen);
        trigger.setAttribute("aria-expanded", String(willOpen));
      });
    });

    document.addEventListener("click", (event) => {
      if (!header.contains(event.target)) {
        closeGroups();
        if (navigation && menuToggle) {
          navigation.classList.remove("is-open");
          menuToggle.setAttribute("aria-expanded", "false");
        }
      }
    });

    header.addEventListener("keydown", (event) => {
      if (event.key !== "Escape") return;
      const focusedTrigger = header.querySelector(".nav-trigger[aria-expanded='true']");
      closeGroups();
      if (focusedTrigger) {
        focusedTrigger.focus();
      } else if (navigation && menuToggle && navigation.classList.contains("is-open")) {
        navigation.classList.remove("is-open");
        menuToggle.setAttribute("aria-expanded", "false");
        menuToggle.focus();
      }
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth >= 992 && navigation && menuToggle) {
        navigation.classList.remove("is-open");
        menuToggle.setAttribute("aria-expanded", "false");
      }
    });
  });
})();
