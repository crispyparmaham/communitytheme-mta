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
                item.style.height = '0';
            }
          });
  
          allIcons.forEach((i) => {
            i.textContent = '+'; // Set all icons to plus
          });
  
          if (accordionCollapse.classList.contains('open')) {
            accordionCollapse.style.height = '0';
            accordionCollapse.classList.remove('open');
            icon.textContent = '+'; // Set current icon to plus
          } else {
            accordionCollapse.style.height = accordionCollapse.scrollHeight + 'px';
            accordionCollapse.classList.add('open');
            icon.textContent = '−'; // Set current icon to minus
          }
        });
      });
    }
  });
  