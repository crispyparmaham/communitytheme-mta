class Accordion {
  constructor() {
    this.init();
  }

  init() {
    document.addEventListener('DOMContentLoaded', () => {
      this.handleHash();
      this.attachEventListeners();
    });
  }

  handleHash() {
    const currentUrlHash = window.location.hash;
    if (currentUrlHash) {
      const accordionItem = document.querySelector(currentUrlHash);
      if (accordionItem) {
        this.toggleAccordion(accordionItem);
      }
    }
  }

  attachEventListeners() {
    const buttons = document.querySelectorAll('.accordion-button');
    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        const accordionItem = button.closest('.accordion-item');
        this.toggleAccordion(accordionItem);
      });
    });
  }

  toggleAccordion(accordionItem) {
    const accordionCollapse = accordionItem.querySelector('.accordion-collapse');
    const icon = accordionItem.querySelector('.accordion-icon');

    this.closeAllAccordions(accordionCollapse);
    
    if (accordionCollapse.classList.contains('open')) {
      this.closeAccordion(accordionCollapse, icon);
    } else {
      this.openAccordion(accordionCollapse, icon);
    }
  }

  closeAllAccordions(except = null) {
    document.querySelectorAll('.accordion-collapse').forEach((item) => {
      if (item !== except) {
        this.closeAccordion(item, item.closest('.accordion-item').querySelector('.accordion-icon'));
      }
    });
  }

  openAccordion(element, icon) {
    element.style.height = element.scrollHeight + 'px';
    element.classList.add('open');
    icon.textContent = '−';
  }

  closeAccordion(element, icon) {
    element.style.height = '0';
    element.classList.remove('open');
    icon.textContent = '+';
  }
}

// Initialize the accordion functionality
new Accordion();
