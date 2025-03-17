document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll(".unlock-external-resources");

    checkboxes.forEach((checkbox) => {
        const dataId = checkbox.dataset.id;
        let wasUnlocked = false; // Track if the section was unlocked on page load

        // Check localStorage on page load
        if (localStorage.getItem(`unlock-${dataId}`) === "true") {
            checkbox.checked = true; // Keep it checked visually
            wasUnlocked = true; // Mark that this section was unlocked

            // Find the div with this ID and trigger the button inside
            const targetDivs = document.querySelectorAll(`[data-unlock-id="${dataId}"]`);
            if (targetDivs) {
                targetDivs.forEach((targetDiv) => {
                    targetDiv.classList.add("unlocked-resource");
                    const button = targetDiv.querySelector(".ma-content-consent__button.ma-content-consent__button");
                    if (button) {
                        button.click(); // Simulate user clicking the button
                    }
                });
            }
        }

        // Listen for checkbox changes
        checkbox.addEventListener("change", function () {
            if (this.checked) {
                localStorage.setItem(`unlock-${dataId}`, "true");
            } else {
                localStorage.removeItem(`unlock-${dataId}`);

                // If it was previously unlocked, refresh the page
                if (wasUnlocked) {
                    location.reload();
                }
            }
        });
    });
});
