document.addEventListener("DOMContentLoaded", function() {
    const header = document.querySelector('.main-header');
    const logo = header?.querySelector('.logo-wrap img');
    const mainContainer = document.querySelector('.main-container');
    let lastScrollTop = 0;

    
    function updateHeaderState() {
        if (header) {
            const currentScrollTop = window.scrollY || window.pageYOffset;
            if (currentScrollTop > lastScrollTop) {
                // Scroll down
                header.classList.add('scrolled');
                if (logo) {
                    logo.classList.add('scrolled');
                }
            } else {
                // Scroll up
                header.classList.remove('scrolled');
                if (logo) {
                    logo.classList.remove('scrolled');
                }
            }
            lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop; // For Mobile or negative scrolling
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
