class AccessibilityControls {
    constructor() {
        this.fontSizeStep = 1; // Step size for font size change
        this.minFontSize = 14; // Minimum font size
        this.maxFontSize = 24; // Maximum font size
        this.defaultFontSize = 18; // Default font size
        this.fontSizeVar = "--body-text-size";

        this.contrastClasses = ["contrast-bw", "contrast-wb"]; // Defined contrast classes

        this.buttons = [];
        this.toolOpen = false; 

        this.init();
    }

    init() {
        this.loadSettings();
        this.addEventListeners();
    }

    addEventListeners() {
        const elements = {
            openTools: document.querySelectorAll("[data-id='open-accessibility-tools']"),
            closeTools: document.querySelectorAll(".close-acc-dialog"),
            increaseFontsize: document.querySelectorAll("[data-id='increase-fontsize']"),
            decreaseFontsize: document.querySelectorAll("[data-id='decrease-fontsize']"),
            contrastBW: document.querySelectorAll("[data-id='contrast-bw']"),
            contrastWB: document.querySelectorAll("[data-id='contrast-wb']"),
            contrastReset: document.querySelectorAll("[data-id='contrast-reset']"),
            reset: document.querySelectorAll("[data-id='reset-all']"),
        };
        elements.openTools.forEach((button) => {
            button.addEventListener("click", () => this.openCloseTool());
        })
        elements.closeTools.forEach((button) => {
            button.addEventListener("click", () => this.openCloseTool());
        })

        elements.increaseFontsize.forEach((button) => {
            button.addEventListener("click", () => this.changeFontSize(1));
        })
        elements.decreaseFontsize.forEach((button) => {
            button.addEventListener("click", () => this.changeFontSize(-1));
        })
        elements.contrastBW.forEach((button) => {
            button.addEventListener("click", () => {
                this.addActiveClass(button)
                this.setContrast("contrast-bw")
            });
        })
        elements.contrastWB.forEach((button) => {
            button.addEventListener("click", () => {
                this.addActiveClass(button)
                this.setContrast("contrast-wb")
            });
        })
        elements.contrastReset.forEach((button) => {
            button.addEventListener("click", () => {
                this.addActiveClass(button)
                this.setContrast("contrast-reset");
            })
        })
        elements.reset.forEach((button) => {
            button.addEventListener("click", () => {
                this.addActiveClass(button)
                this.setContrast("contrast-reset");
                document.documentElement.style.setProperty(this.fontSizeVar, `${this.defaultFontSize}px`);
                localStorage.setItem("fontSize", this.defaultFontSize);
            })
        })
    }


    
    openTool(toolsDialog) {
        toolsDialog.style.display = 'block';
        toolsDialog.setAttribute("aria-hidden", "false");
    }
    closeTool(toolsDialog) {
        toolsDialog.style.display = 'none';
        toolsDialog.setAttribute("aria-hidden", "true");
    }
    openCloseTool() {
        let toolsDialog = document.getElementById("accessibility-tools-dialog");
        if(!this.toolOpen) {
           this.openTool(toolsDialog);
        } else {
            this.closeTool(toolsDialog);
        }
        this.toolOpen = !this.toolOpen; 
    }

    addActiveClass(element) {
        this.buttons.forEach((button) => {
            button.classList.remove("active");
        })
        this.buttons = [];
        this.buttons.push(element);
        this.buttons.forEach((button) => {
            button.classList.add("active");
        })
    }

    changeFontSize(direction) {
        console.log("change-font-size")
        let currentSize = parseFloat(getComputedStyle(document.documentElement).getPropertyValue(this.fontSizeVar));
        let newSize = currentSize + direction * this.fontSizeStep;

        if (newSize >= this.minFontSize && newSize <= this.maxFontSize) {
            document.documentElement.style.setProperty(this.fontSizeVar, `${newSize}px`);
            localStorage.setItem("fontSize", newSize);
        }
    }

    setContrast(mode) {
        document.documentElement.classList.remove(...this.contrastClasses); // Remove existing contrast classes

        if (mode !== "contrast-reset") {
            document.documentElement.classList.add(mode);
            localStorage.setItem("contrastMode", mode);
        } else {
            localStorage.removeItem("contrastMode"); // Reset contrast setting
        }
    }

    loadSettings() {
        const savedFontSize = localStorage.getItem("fontSize");
        if (savedFontSize) {
            document.documentElement.style.setProperty(this.fontSizeVar, `${savedFontSize}px`);
        }

        const savedContrastMode = localStorage.getItem("contrastMode");
        if (savedContrastMode && this.contrastClasses.includes(savedContrastMode)) {
            document.documentElement.classList.add(savedContrastMode);
        }
    }
}

// Initialize the accessibility controls
document.addEventListener("DOMContentLoaded", () => new AccessibilityControls());


