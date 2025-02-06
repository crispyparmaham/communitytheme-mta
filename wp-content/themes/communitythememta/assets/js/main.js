
function calcVariables() {
    let vh = window.innerHeight * 0.01;
    let headerHeight = document.querySelector('.main-header').offsetHeight;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
    document.documentElement.style.setProperty('--headerHeight', `${headerHeight}px`);
}

window.addEventListener('resize', calcVariables);
document.addEventListener('DOMContentLoaded', calcVariables);