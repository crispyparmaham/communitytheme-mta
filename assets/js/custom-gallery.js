document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".wp-block-gallery").forEach((gallery) => {
        gallery.querySelectorAll("figure.wp-block-image img").forEach((img) => {
            const src = img.getAttribute("src");

            // Skip if already wrapped
            if (img.parentElement.tagName.toLowerCase() === "a") return;

            // Create <a> element
            const link = document.createElement("a");
            link.setAttribute("href", src);
            link.setAttribute("data-lg-size", `${img.getAttribute('width')}-${img.getAttribute('height')}`);

            // Insert <a> before <img> and move <img> inside <a>
            img.parentNode.insertBefore(link, img);
            link.appendChild(img);
        });

        // Initialize LightGallery
        lightGallery(gallery, {
            selector: "a",
            download: false,
            zoom: true,
            fullScreen: true,
            thumbnail: true,
            autoplay: false,
            licenseKey: "F6EU6-2F2Q2-UYKX3-VGVVE",
        });
    });
});

