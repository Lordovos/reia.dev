let navToggle = document.querySelector(".nav-toggle");

navToggle?.addEventListener("click", () => {
    "use strict";
    navToggle.classList.toggle("nav-toggle-open");

    if (navToggle.classList.contains("nav-toggle-open")) {
        navToggle.innerHTML = `<i class="las la-times"></i>`;
    } else {
        navToggle.innerHTML = `<i class="las la-bars"></i>`;
    }
    document.querySelectorAll(".nav-item")?.forEach((navItem) => {
        navItem.classList.toggle("nav-item-show");
    });
}, false);
let wikiHideToggle = document.querySelector(".wiki-hide");

wikiHideToggle?.addEventListener("click", () => {
    checkbox = document.querySelector("#input-is-hidden");
    icon = wikiHideToggle.querySelector(".form-checkbox-icon");

    if (checkbox.checked) {
        icon.classList.remove("la-check-square");
        icon.classList.add("la-square");
    } else {
        icon.classList.remove("la-square");
        icon.classList.add("la-check-square");
    }
}, false);
let wikiLockToggle = document.querySelector(".wiki-lock");

wikiLockToggle?.addEventListener("click", () => {
    checkbox = document.querySelector("#input-is-locked");
    icon = wikiLockToggle.querySelector(".form-checkbox-icon");

    if (checkbox.checked) {
        icon.classList.remove("la-check-square");
        icon.classList.add("la-square");
    } else {
        icon.classList.remove("la-square");
        icon.classList.add("la-check-square");
    }
}, false);

function toLocalTime(utcTime) {
    "use strict";
    let localTime = new Date(`${utcTime} UTC`);
    let options = {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "numeric"
    };
    return localTime.toLocaleDateString("en-US", options);
}
let dateTimes = document.querySelectorAll(".datetime");

dateTimes?.forEach((dateTime) => {
    dateTime.textContent = toLocalTime(dateTime.textContent);
});
