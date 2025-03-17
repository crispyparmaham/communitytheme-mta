document.addEventListener("DOMContentLoaded", function() {
    const header = document.querySelector('.main-header');
    const logo = header?.querySelector('.logo-wrap img');
    const mainContainer = document.querySelector('.main-container');

    function updateHeaderState() {
        if (header) {
            // Ändere die Bedingung auf 200px Scrollhöhe
            if (window.scrollY > window.innerHeight * 0.1) {
                header.classList.add('scrolled');
                if (logo) {
                    logo.classList.add('scrolled');
                }
            } else {
                header.classList.remove('scrolled');
                if (logo) {
                    logo.classList.remove('scrolled');
                }
            }
        }
    }

    function updateMarginTop() {
        if (header && mainContainer) {
            const headerHeight = header.offsetHeight;
            mainContainer.style.marginTop = `${headerHeight}px`;
        }
    }

    // Initiales Setzen der margin-top und Scrollzustand
    updateMarginTop();
    updateHeaderState();

    // Scroll-Event für Header-Klassen
    window.addEventListener('scroll', updateHeaderState);

    // Resize-Event für margin-top
    window.addEventListener('resize', updateMarginTop);
});
