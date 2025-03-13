class AccessibilityControls {
    constructor() {
        this.fontSizeStep = 1; // Step size for font size change
        this.minFontSize = 14; // Minimum font size
        this.maxFontSize = 24; // Maximum font size
        this.defaultFontSize = 18; // Default font size
        this.fontSizeVar = "--body-text-size";

        this.contrastModes = {
            "contrast-bw": {
                "--color-background": "hsl(0, 0%, 0%)",
                "--color-foreground": "hsl(0, 0%, 100%)"
            },
            "contrast-wb": {
                "--color-background": "hsl(0, 0%, 100%)",
                "--color-foreground": "hsl(0, 0%, 0%)"
            },
            "contrast-reset": {
                "--color-background": "hsl(0, 0%, 60.6%)",
                "--color-foreground": "hsl(0, 0%, 0%)"
            }
        };

        this.init();
    }

    init() {
        this.loadSettings();
        this.addEventListeners();
    }

    addEventListeners() {
        document.getElementById("increase-fontsize").addEventListener("click", () => this.changeFontSize(1));
        document.getElementById("decrease-fontsize").addEventListener("click", () => this.changeFontSize(-1));

        document.getElementById("contrast-bw").addEventListener("click", () => this.setContrast("contrast-bw"));
        document.getElementById("contrast-wb").addEventListener("click", () => this.setContrast("contrast-wb"));
        document.getElementById("contrast-reset").addEventListener("click", () => this.setContrast("contrast-reset"));
    }

    changeFontSize(direction) {
        let currentSize = parseFloat(getComputedStyle(document.documentElement).getPropertyValue(this.fontSizeVar));
        let newSize = currentSize + direction * this.fontSizeStep;

        if (newSize >= this.minFontSize && newSize <= this.maxFontSize) {
            document.documentElement.style.setProperty(this.fontSizeVar, `${newSize}px`);
            localStorage.setItem("fontSize", newSize);
        }
    }

    setContrast(mode) {
        if (this.contrastModes[mode]) {
            for (let [key, value] of Object.entries(this.contrastModes[mode])) {
                document.documentElement.style.setProperty(key, value);
            }
            localStorage.setItem("contrastMode", mode);
        }
    }

    loadSettings() {
        const savedFontSize = localStorage.getItem("fontSize");
        if (savedFontSize) {
            document.documentElement.style.setProperty(this.fontSizeVar, `${savedFontSize}px`);
        }

        const savedContrastMode = localStorage.getItem("contrastMode");
        if (savedContrastMode && this.contrastModes[savedContrastMode]) {
            this.setContrast(savedContrastMode);
        }
    }
}

// Initialize the accessibility controls
document.addEventListener("DOMContentLoaded", () => new AccessibilityControls());
