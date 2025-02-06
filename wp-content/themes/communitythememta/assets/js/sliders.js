document.addEventListener('DOMContentLoaded', () => {

    let topSwipers = document.querySelectorAll('.swiper-container.top-image-swiper');

    if(topSwipers.length > 0) {
        topSwipers.forEach(swiper => {
            const topSwiper = new Swiper(swiper, {
                loop: true,
                speed: 1000,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });
        })
    }

})