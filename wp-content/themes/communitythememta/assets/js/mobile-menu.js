document.addEventListener("DOMContentLoaded", function() {
    const hamburger = document.querySelector('.hamburger-menu');
    const navigation = document.querySelector('.main-navigation');
    const mainHeader = document.querySelector('.main-header');

    function updateMenuPosition() {
        const headerHeight = mainHeader.offsetHeight;
        const windowHeight = window.innerHeight;
        const menuHeight = windowHeight - headerHeight;

        navigation.style.top = `${headerHeight}px`;
        navigation.style.height = `${menuHeight}px`;
    }

    function isMobileViewport() {
        return window.innerWidth <= 992;
    }

    if (hamburger && navigation && mainHeader) {
        hamburger.addEventListener('click', function() {
            const expanded = hamburger.getAttribute('aria-expanded') === 'true' || false;
            hamburger.setAttribute('aria-expanded', !expanded);
            navigation.classList.toggle('active');
            hamburger.classList.toggle('active');

            if (isMobileViewport() && navigation.classList.contains('active')) {
                updateMenuPosition();
            }
        });

        window.addEventListener('resize', function() {
            if (isMobileViewport() && navigation.classList.contains('active')) {
                updateMenuPosition();
            }
        });

        if (isMobileViewport() && navigation.classList.contains('active')) {
            updateMenuPosition();
        }
    }
});
