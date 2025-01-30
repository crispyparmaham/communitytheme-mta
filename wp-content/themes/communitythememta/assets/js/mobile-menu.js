document.addEventListener("DOMContentLoaded", function() {
    const hamburger = document.querySelector('.hamburger-menu');
    const menuWrap = document.querySelector('.menu-wrap');
    const mainHeader = document.querySelector('.main-header');

    function updateMenuPosition() {
        const headerHeight = mainHeader.offsetHeight;
        const windowHeight = window.innerHeight;
        const menuHeight = windowHeight - headerHeight;

        menuWrap.style.top = `${headerHeight}px`;
        menuWrap.style.height = `${menuHeight}px`;
    }

    function isMobileViewport() {
        return window.innerWidth <= 992;
    }

    if (hamburger && menuWrap && mainHeader) {
        hamburger.addEventListener('click', function() {
            const expanded = hamburger.getAttribute('aria-expanded') === 'true' || false;
            hamburger.setAttribute('aria-expanded', !expanded);
            menuWrap.classList.toggle('active');
            hamburger.classList.toggle('active');

            if (isMobileViewport() && menuWrap.classList.contains('active')) {
                updateMenuPosition();
            }
        });

        window.addEventListener('resize', function() {
            if (isMobileViewport() && menuWrap.classList.contains('active')) {
                updateMenuPosition();
            } else if (!isMobileViewport()) {
                // Reset styles for larger viewports
                menuWrap.style.top = '';
                menuWrap.style.height = '';
            }
        });

        if (isMobileViewport() && menuWrap.classList.contains('active')) {
            updateMenuPosition();
        }
    }
});
