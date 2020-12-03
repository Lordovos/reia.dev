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
    "use strict";
    let checkbox = document.querySelector("#input-is-hidden");
    let icon = wikiHideToggle.querySelector(".form-checkbox-icon");

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
    "use strict";
    let checkbox = document.querySelector("#input-is-locked");
    let icon = wikiLockToggle.querySelector(".form-checkbox-icon");

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
let imagesToDouble = document.querySelectorAll(".double");

imagesToDouble?.forEach((image) => {
    image.width = image.width * 2;
    image.height = image.height * 2;
});
let articleCaptions = document.querySelectorAll("blockquote.wiki-article-caption");

articleCaptions?.forEach((caption) => {
    let captionImage = caption.querySelector("img");

    if (captionImage) {
        caption.style.width = `${captionImage.width + 18}px`;
    }
});
let wikiArticleBody = document.querySelector(".wiki-article-body");
/**
 * Because Textile has no way to insert div elements, we have to inject one
 * into the page and then move the adjacent table inside of it. This is to help
 * with constraining the width of the table on mobile devices.
 */
if (wikiArticleBody) {
    let tables = document.querySelectorAll("table");

    tables?.forEach((table) => {
        let div = document.createElement("div");
        div.classList.add("wiki-article-table-container");
        table.before(div);
        div.appendChild(table);
    });
    let links = wikiArticleBody.querySelectorAll("a");

    links?.forEach((link) => {
        let request = new XMLHttpRequest();

        request.addEventListener("readystatechange", () => {
            if (request.readyState === XMLHttpRequest.DONE) {
                if (request.status === 404) {
                    link.classList.add("invalid-link");
                }
            }
        }, false);
        request.open("HEAD", link.href, true);
        request.send();
    });
}
let uploadImagesLabel = document.querySelector(".upload-image");
let uploadImagesInput = document.querySelector("#input-upload");

uploadImagesInput?.addEventListener("change", () => {
    let uploadImagesFileName = uploadImagesLabel.querySelector(".upload-image-file-name");

    uploadImagesFileName.textContent = `: ${uploadImagesInput.files[0].name}`;
}, false);

let uploadedImageDetails = document.querySelectorAll(".uploaded-image-details");

uploadedImageDetails?.forEach((detail) => {
    let copyButton = detail.querySelector(".copy-uploaded-image-url");
    let url = detail.querySelector(".uploaded-image-url");

    copyButton?.addEventListener("click", () => {
        "use strict";
        url.select();
        document.execCommand("copy");
    }, false);
});
