"use strict";
let navToggle = document.querySelector(".nav-toggle");

navToggle?.addEventListener("click", () => {
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
let rememberMeToggle = document.querySelector(".remember-me");

rememberMeToggle?.addEventListener("click", () => {
    let checkbox = document.querySelector("#input-remember-me");
    let icon = rememberMeToggle.querySelector(".form-checkbox-icon");

    if (checkbox.checked) {
        icon.classList.remove("la-check-square");
        icon.classList.add("la-square");
    } else {
        icon.classList.remove("la-square");
        icon.classList.add("la-check-square");
    }
}, false);
let wikiHideToggle = document.querySelector(".wiki-hide");

wikiHideToggle?.addEventListener("click", () => {
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
let showPasswordToggle = document.querySelector(".show-password");

showPasswordToggle?.addEventListener("click", () => {
    let input = document.querySelector(".password-input");

    input.type = (input.type === "password" ? "text" : "password");
}, false);

function toLocalTime(utcTime) {
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
function doubleImages() {
    let imagesToDouble = document.querySelectorAll(".double");

    imagesToDouble?.forEach((image) => {
        image.width = image.width * 2;
        image.height = image.height * 2;
    });
}
function captionImages() {
    let articleCaptions = document.querySelectorAll("blockquote.wiki-article-caption");

    articleCaptions?.forEach((caption) => {
        let captionImage = caption.querySelector("img");
    
        if (captionImage) {
            caption.style.width = `${captionImage.width + 18}px`;
        }
    });
}
/**
 * Because Textile has no way to insert div elements, we have to inject one
 * into the page and then move the adjacent table inside of it. This is to help
 * with constraining the width of the table on mobile devices.
 */
function wrapTables() {
    let tables = document.querySelectorAll("table");

    tables?.forEach((table) => {
        let div = document.createElement("div");
        div.classList.add("wiki-article-table-container");
        table.before(div);
        div.appendChild(table);
    });
}
/**
 * Wraps the table of contents in a container.
 */
function wrapTableOfContents() {
    let tableOfContents = document.querySelector("#table-of-contents");

    if (tableOfContents) {
        let list = document.querySelector("#table-of-contents + ol");

        if (list) {
            let div = document.createElement("div");
            div.classList.add("wiki-article-toc-container");
            tableOfContents.before(div);
            div.appendChild(tableOfContents);
            div.appendChild(list);
        }
    }
}
// Strips a URL down to its base domain and returns it as text.
function getDomain(url) {
    return url.replace("http://", "").replace("https://", "").split("/")[0];
}
function validateExternalLinks(body) {
    let links = body.querySelectorAll("a");

    links?.forEach((link) => {
        // If a link does not have the CSS class "safe-external-url", proceed with altering the link text.
        if (!link.classList.contains("safe-external-url") && !link.href.includes("mailto:")) {
            if (getDomain(link.href) !== getDomain(document.location.href)) {
                link.innerHTML += `<i class="las la-external-link-alt"></i>`;
            }
        }
    });
}
/**
 * Check each link to see if it leads to a wiki article, and if so check to
 * see if the article exists. If the article does not exist, then the link
 * is given a CSS class of "invalid-link".
 */
function validateLinks(body) {
    let links = body.querySelectorAll("a");

    links?.forEach((link) => {
        if (link.href.match(/wiki\/([a-z0-9-]+)/)) {
            fetch(link.href, {
                method: "HEAD"
            }).then((response) => {
                if (response.status === 404) {
                    link.classList.add("invalid-link");
                }
            });
        }
    });
}
let wikiArticleBody = document.querySelector(".wiki-article-body");

if (wikiArticleBody) {
    doubleImages();
    captionImages();
    wrapTables();
    wrapTableOfContents();
    validateLinks(wikiArticleBody);
}
validateExternalLinks(document.body);
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
        url.select();
        document.execCommand("copy");
    }, false);
});
let wikiArticleBodyReadOnly = document.querySelector(".wiki-article-body-read-only");

if (wikiArticleBodyReadOnly) {
    let copyButton = document.querySelector(".copy-wiki-article-body");
    let body = wikiArticleBodyReadOnly.querySelector("#input-body");

    copyButton?.addEventListener("click", () => {
        body.select();
        document.execCommand("copy");
    }, false);
}
let wikiArticlePreviewButton = document.querySelector(".preview-article");

wikiArticlePreviewButton?.addEventListener("click", (event) => {
    const previewDialog = document.querySelector(".preview");
    const wikiArticleBodyPreview = document.querySelector(".wiki-article-body-preview");

    event.preventDefault();

    fetch("/wiki/preview", {
        method: "POST",
        body: new FormData(document.querySelector(".wiki-article-form"))
    }).then((response) => {
        return response.text();
    }).then((text) => {
        if (wikiArticleBodyPreview) {
            wikiArticleBodyPreview.innerHTML = text;
            doubleImages();
            captionImages();
            wrapTables();
            wrapTableOfContents();
            validateLinks(wikiArticleBodyPreview);
            previewDialog?.showModal();
        }
    }).catch((error) => {
        console.error("Error: " + error);
    });
}, false);
