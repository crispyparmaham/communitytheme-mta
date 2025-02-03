document.addEventListener('DOMContentLoaded', function () {
  const buttons = document.querySelectorAll('.accordion-button');
  if (buttons) {
    buttons.forEach((button) => {
      button.addEventListener('click', function () {
        const accordionCollapse = this.closest('.accordion-item').querySelector('.accordion-collapse');
        const icon = this.querySelector('.accordion-icon');

        const allItems = document.querySelectorAll('.accordion-collapse');
        const allIcons = document.querySelectorAll('.accordion-icon');

        allItems.forEach((item) => {
          if (item !== accordionCollapse) {
            item.classList.remove('open');
          }
        });

        allIcons.forEach((i) => {
          i.textContent = '+'; // Setze alle Icons auf Plus
        });

        if (accordionCollapse.classList.contains('open')) {
          accordionCollapse.classList.remove('open');
          icon.textContent = '+'; // Setze aktuelles Icon auf Plus
        } else {
          accordionCollapse.classList.add('open');
          icon.textContent = '−'; // Setze aktuelles Icon auf Minus
        }
      });
    });
  }
});
