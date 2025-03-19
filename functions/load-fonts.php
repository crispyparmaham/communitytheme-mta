<?php
/**
 * MTA Font Manager
 * 
 * Automatisch Schriften aus dem /wp-content/uploads/fonts/ Verzeichnis laden
 * und als @font-face CSS einbinden.
 */

if (!class_exists('MTA_Font_Manager')) :
class MTA_Font_Manager {

    const TITLE         = 'MTA Font Manager';
    const SLUG          = 'mta-font-manager';
    const VERSION       = '1.0.0';
    
    // Konfiguration
    private $font_display = 'swap'; // CSS font-display: 'auto', 'block', 'swap', 'fallback', 'optional' oder '' (deaktivieren)
    private $css_output = 'file';   // 'file' (verlinkte CSS Datei) oder 'html' (Inline CSS im head)
    private $css_minimize = true;   // CSS minimieren (true) oder nicht (false)
    private $sample_text = 'The quick brown fox jumps over the lazy dog.'; // Beispieltext für die Admin-Seite
    
    // Interne Variablen
    private $fonts_base = null;     // Wird automatisch initialisiert
    private $supported_formats = ['woff2', 'woff', 'ttf', 'otf']; // Unterstützte Formate in Prioritätsreihenfolge
    private $fonts = [];            // Liste der Schriften und zugehörige Dateien
    private $font_families = [];    // Liste der Schriftfamiliennamen
    private $font_files_count = 0;  // Anzahl der gescannten Schriftdateien
    private $font_css = '';         // Temporärer Speicher für CSS
    private $fonts_details_cache = []; // Cache für bereits geparste Schriftdetails
    
    /**
     * Konstruktor
     */
    function __construct() {
        // Globalen Zugriff ermöglichen
        $GLOBALS['MTA_Font_Manager'] = $this;
        
        // Nicht für AJAX, CRON oder JSON-Anfragen ausführen
        if (wp_doing_ajax() || wp_doing_cron() || wp_is_json_request()) {
            return;
        }
        
        // Version als Konstante definieren
        if (!defined('MTA_Font_Manager_Version')) {
            define('MTA_Font_Manager_Version', self::VERSION);
        }
        
        // Basisverzeichnis und URL initialisieren
        $this->fonts_base = $this->get_fonts_base();
        if (!$this->fonts_base) {
            return;
        }
        
        // Schriften sammeln und CSS generieren
        $this->collect_fonts();
        $this->font_css = $this->generate_font_css();
        
        // Backend: Admin-Menü hinzufügen
        add_action('admin_menu', [$this, 'admin_init_menu']);
        add_action('admin_init', [$this, 'admin_init']);
        
        // Frontend: CSS einbinden
        add_action('wp_head', [$this, 'frontend_css'], 5);
        
        // Shortcode für Testzwecke
        add_shortcode('mta-fonts-test', [$this, 'shortcode_font_samples']);
    }
    
    /**
     * Basisverzeichnis und URL für Schriften ermitteln.
     * Erstellt das Verzeichnis /wp-content/uploads/fonts/ falls notwendig.
     * 
     * @return object|null Objekt mit den Eigenschaften `dir` und `url` oder `null` bei Fehler
     */
    private function get_fonts_base() {
        $result = (object)[
            'dir' => null,
            'url' => ''
        ];
        
        // Upload-Verzeichnis ermitteln
        $upload_dir_info = wp_get_upload_dir();
        $result->dir = $upload_dir_info['basedir'] . '/fonts';
        $result->url = $upload_dir_info['baseurl'] . '/fonts';
        
        // Schriftverzeichnis erstellen, falls es nicht existiert
        if (!file_exists($result->dir)) {
            if (!@mkdir($result->dir, 0755, true)) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error"><p>[' . self::TITLE . '] Fehler beim Erstellen des Schriftordners <code>wp-content/uploads/fonts</code>.</p></div>';
                });
                error_log(sprintf('%s::%s() Fehler beim Erstellen des Schriftordners.', __CLASS__, __FUNCTION__));
                return null;
            }
        }
        
        // Schreibrechte prüfen
        if (!is_writable($result->dir)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>[' . self::TITLE . '] Ordner <code>wp-content/uploads/fonts</code> ist nicht beschreibbar. Bitte korrigieren Sie die Ordnerberechtigungen.</p></div>';
            });
        }
        
        // Schemeless URL erstellen (funktioniert sowohl mit HTTP als auch HTTPS)
        $result->url = preg_replace('/^https?\:/', '', $result->url);
        
        return $result;
    }
    
    /**
     * Sammelt alle Schriftdateien aus dem Schriftverzeichnis
     */
    private function collect_fonts() {
        // Wenn bereits Schriften gesammelt wurden, nichts tun
        if (!empty($this->fonts)) {
            return;
        }
        
        $this->fonts = [];
        
        // Rekursiver Scan nach Schriftdateien (inkl. Unterverzeichnisse)
        $directory_iterator = new RecursiveDirectoryIterator(
            $this->fonts_base->dir,
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS
        );
        $file_iterator = new RecursiveIteratorIterator($directory_iterator);
        
        // Alle Schriftdateien sammeln
        $font_files = [];
        foreach ($file_iterator as $file) {
            if (in_array(strtolower($file->getExtension()), $this->supported_formats)) {
                $font_files[] = $file;
                $this->font_files_count++;
            }
        }
        
        // Schriftdateien verarbeiten und in die Datenstruktur eintragen
        foreach ($font_files as $font_file) {
            $font_ext = strtolower($font_file->getExtension());
            $font_details = $this->parse_font_name($font_file->getBasename('.' . $font_ext));
            $font_name = $font_details->name;
            $font_weight = $font_details->weight;
            $font_style = $font_details->style;
            
            // Relativer Pfad vom Basisordner
            $font_path = str_replace($this->fonts_base->dir, '', $font_file->getPath());
            
            // URL-Pfadelemente codieren (für Leerzeichen oder Sonderzeichen)
            $font_path_url = implode('/', array_map('rawurlencode', explode('/', $font_path)));
            
            // Eintrag für diese Schriftfamilie erstellen
            if (!array_key_exists($font_name, $this->fonts)) {
                $this->fonts[$font_name] = [];
            }
            
            // Eintrag für dieses Schriftgewicht und -stil
            $weight_style_key = $font_weight . '/' . $font_style;
            if (!array_key_exists($weight_style_key, $this->fonts[$font_name])) {
                $this->fonts[$font_name][$weight_style_key] = [];
            }
            
            // Schriftdetails für diese Datei speichern
            $this->fonts[$font_name][$weight_style_key][$font_ext] = 
                $this->fonts_base->url . 
                $font_path_url . '/' . 
                rawurlencode($font_file->getBasename());
        }
        
        // Nach Schriftfamilie sortieren
        ksort($this->fonts, SORT_NATURAL | SORT_FLAG_CASE);
        
        // Schriftfamiliennamen speichern
        $this->font_families = array_keys($this->fonts);
    }
    
    /**
     * Parst Schriftgewicht und -stil aus einem Schriftdateinamen
     * 
     * @param string $name Der Schriftdateiname
     * @return object Objekt mit `name`, `weight`, `style`
     */
    private function parse_font_name(string $name): object {
        // Bereits im Cache?
        if (array_key_exists($name, $this->fonts_details_cache)) {
            return $this->fonts_details_cache[$name];
        }
        
        $result = (object)[
            'name' => $name,
            'weight' => 400,
            'style' => 'normal'
        ];
        
        // Gewichtspattern (von spezifisch zu weniger spezifisch)
        $weights = [
            // Spezifische Matches
            200 => '/[ \-]?(200|((extra|ultra)\-?light))/i',
            800 => '/[ \-]?(800|((extra|ultra)\-?bold))/i',
            600 => '/[ \-]?(600|([ds]emi(\-?bold)?))/i',
            // Weniger spezifische Matches
            100 => '/[ \-]?(100|thin)/i',
            300 => '/[ \-]?(300|light)/i',
            400 => '/[ \-]?(400|normal|regular|book)/i',
            500 => '/[ \-]?(500|medium)/i',
            700 => '/[ \-]?(700|bold)/i',
            900 => '/[ \-]?(900|black|heavy)/i',
            'var' => '/[ \-]?(VariableFont|\[wght\])/i',
        ];
        
        $count = 0;
        
        // Stil erkennen & entfernen
        $new_name = preg_replace('/[ \-]?(italic|oblique)/i', '', $result->name, -1, $count);
        if ($new_name && $count) {
            $result->name = $new_name;
            $result->style = 'italic';
        }
        
        // Gewicht erkennen & entfernen
        foreach ($weights as $weight => $pattern) {
            $new_name = preg_replace($pattern, '', $result->name, -1, $count);
            if ($new_name && $count) {
                $result->name = $new_name;
                $result->weight = $weight;
                break;
            }
        }
        
        // Webfont-Suffix entfernen
        $result->name = preg_replace('/[ \-]?webfont$/i', '', $result->name);
        
        // Variable Font: Spezifika erkennen & entfernen
        if ($result->weight == 'var') {
            $result->name = preg_replace('/_(opsz,wght|opsz|wght)$/i', '', $result->name);
        }
        
        // Im Cache speichern
        $this->fonts_details_cache[$name] = $result;
        
        return $result;
    }
    
    /**
     * Generiert CSS für benutzerdefinierte Schriften
     * 
     * @param bool $plain_css Nur reines CSS zurückgeben (ohne link oder style Tags)
     * @return string Das CSS als String
     */
    private function generate_font_css($plain_css = false): string {
        $style = '';
        
        // CSS für jede Schriftfamilie erstellen
        foreach ($this->fonts as $font_name => $font_details) {
            ksort($font_details); // Nach Gewicht/Stil sortieren
            
            foreach ($font_details as $weight_style => $file_list) {
                list($font_weight, $font_style) = explode('/', $weight_style);
                
                // CSS-Block für diese Schriftvariante erstellen
                $style .= '@font-face{' . PHP_EOL;
                $style .= '  font-family:"' . $font_name . '";' . PHP_EOL;
                
                // Gewicht ausgeben (variable Schriften anders behandeln)
                if ($font_weight == 'var') {
                    $style .= '  font-weight:1 1000;' . PHP_EOL;
                } else {
                    $style .= '  font-weight:' . $font_weight . ';' . PHP_EOL;
                }
                
                $style .= '  font-style:' . $font_style . ';' . PHP_EOL;
                
                // Schriftquellen in Prioritätsreihenfolge ausgeben
                $urls = [];
                foreach ($this->supported_formats as $format) {
                    if (array_key_exists($format, $file_list)) {
                        $font_url = $file_list[$format];
                        
                        // Format für CSS bestimmen
                        $css_format = '';
                        switch ($format) {
                            case 'otf': 
                                $css_format = 'opentype'; 
                                break;
                            case 'ttf': 
                                $css_format = 'truetype'; 
                                break;
                            default:
                                $css_format = $format;
                        }
                        
                        $urls[] = 'url("' . $font_url . '") format("' . $css_format . '")';
                    }
                }
                
                $style .= '  src:' . implode(',' . PHP_EOL . '      ', $urls) . ';' . PHP_EOL;
                
                // Font-Display-Eigenschaft hinzufügen, wenn konfiguriert
                if ($this->font_display) {
                    $style .= '  font-display:' . $this->font_display . ';' . PHP_EOL;
                }
                
                $style .= '}' . PHP_EOL;
            }
        }
        
        // CSS minimieren, wenn konfiguriert
        if ($this->css_minimize) {
            $style = preg_replace('/\r?\n *(?![\@\.])/', '', $style); // Zeilenumbrüche entfernen außer vor @, .
            $style = preg_replace('/\t/', '', $style); // Tabs entfernen
        }
        
        // CSS-Datei erstellen oder aktualisieren
        $css_path = $this->fonts_base->dir . '/' . self::SLUG . '.css';
        $css_code = '/* Version: ' . self::VERSION . ' */' . PHP_EOL . $style;
        $css_hash_code = hash('CRC32', $css_code, false);
        $css_hash_file = file_exists($css_path) ? hash_file('CRC32', $css_path, false) : 0;
        
        // Nur schreiben, wenn sich der Inhalt geändert hat
        if ($css_hash_code !== $css_hash_file) {
            $status = file_put_contents($css_path, $css_code);
            if ($status === false) {
                error_log(sprintf('%s::%s() Fehler beim Schreiben der CSS-Datei "%s"', __CLASS__, __FUNCTION__, $css_path));
            }
            $css_hash_file = file_exists($css_path) ? hash_file('CRC32', $css_path, false) : 0;
        }
        
        // Rückgabe je nach Konfiguration und Parameter
        if ($plain_css) {
            return $style;
        } elseif ($this->css_output == 'file') {
            $css_url = str_replace($this->fonts_base->dir, $this->fonts_base->url, $css_path);
            return sprintf(
                '<link id="%s" href="%s?ver=%s" rel="stylesheet" type="text/css"/>',
                self::SLUG,
                $css_url,
                $css_hash_file
            );
        } else { // html
            return sprintf(
                '<style id="%s">/* Version: %s */%s</style>',
                self::SLUG,
                self::VERSION,
                PHP_EOL . $style
            );
        }
    }
    
    /**
     * Führt Aktionen aus, die für die Admin-Oberfläche benötigt werden
     */
    public function admin_init() {
        global $pagenow;
        
        // CSS für die Font Manager-Seite laden
        if (($pagenow === 'themes.php') && ($_REQUEST['page'] ?? null) === self::SLUG) {
            if ($this->css_output == 'file') {
                $css_path = $this->fonts_base->dir . '/' . self::SLUG . '.css';
                $css_hash_file = file_exists($css_path) ? hash_file('CRC32', $css_path, false) : 0;
                wp_enqueue_style(
                    self::SLUG,
                    $this->fonts_base->url . '/' . self::SLUG . '.css',
                    [],
                    $css_hash_file
                );
            } else {
                add_action('admin_head', [$this, 'frontend_css'], 5);
            }
        }
    }
    
    /**
     * Gibt das CSS im Frontend aus
     */
    public function frontend_css() {
        echo $this->font_css;
    }
    
    /**
     * Registriert das Admin-Menü unter Appearance > MTA Font Manager
     */
    public function admin_init_menu() {
        add_submenu_page(
            'themes.php',                                   // Parent-Slug (Appearance)
            _x(self::TITLE, 'page title', 'mta-font-manager'), // Seitentitel
            _x(self::TITLE, 'menu title', 'mta-font-manager'), // Menütitel
            'manage_options',                               // Erforderliche Berechtigung
            self::SLUG,                                    // Menü-Slug
            [$this, 'admin_page']                           // Callback-Funktion
        );
    }
    
    /**
     * Gibt den Inhalt der Admin-Seite aus
     */
    public function admin_page() {
        $output = '<div class="wrap">' .
            '<h1>' . esc_html(get_admin_page_title()) . '</h1>' .
            $this->get_font_samples('admin') .
        '</div>';
        
        echo $output;
    }
    
    /**
     * Shortcode-Funktion für das Anzeigen von Schriftproben
     */
    public function shortcode_font_samples($atts) {
        return $this->get_font_samples('shortcode');
    }
    
    /**
     * Generiert HTML für die Anzeige von Schriftproben
     *
     * @param string $mode 'admin' für Admin-Seite, 'shortcode' für Frontend
     * @return string HTML-Code mit Schriftproben
     */
    private function get_font_samples($mode = 'admin'): string {
        $sample_text = $this->sample_text;
        
        // Styles für die Schriftproben
        $output_style = <<<'END_OF_STYLE'
        <style>
        .mta-fonts-wrap {font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Ubuntu,Cantarell,Helvetica,sans-serif;font-size:13px;margin:20px 0;}
        .mta-fonts-wrap h3 {margin-top:20px;padding-bottom:0;font-weight:bold;}
        
        .mta-fonts-header {border:1px solid #ccc;border-radius:5px;padding:10px;padding-top:0;background-color:#f8f8f8;margin-bottom:20px;}
        .mta-fonts-header > div {display:flex;flex-direction:row;margin-top:.5em;}
        .mta-fonts-label {flex-shrink:0;display:inline-block;width:110px;font-weight:bold;}
        
        #mta-fonts-input-font-size {width:60px;text-align:center;min-height:1.5em;line-height:1em;padding:0;}
        #mta-fonts-input-sample-text {width:100%;max-width:400px;text-align:left;}
        
        .mta-fonts-legend {margin-top:2em;border:1px solid #ccc;border-radius:5px;padding:10px;padding-top:0;background-color:#f8f8f8;}
        .mta-fonts-legend > div {display:flex;flex-direction:row;margin-top:.5em;}
        .mta-fonts-legend > div > span:first-child {width:15ch;flex-shrink:0;font-weight:bold;}
        
        .mta-fonts-family {margin-bottom:2em;padding:15px;border:1px solid #eee;border-radius:5px;background-color:#fff;}
        .mta-fonts-family h3 {margin-top:0!important;border-bottom:1px solid #eee;padding-bottom:8px;}
        
        .mta-fonts-font-row {display:flex;flex-direction:row;justify-content:space-between;align-items:center;padding:10px;border-bottom:1px solid #f0f0f0;margin:0;}
        .mta-fonts-font-row:hover {background-color:#f8f8f8;}
        .mta-fonts-font-info {font-size:12px;line-height:1em;width:100px;}
        .mta-fonts-font-sample {font-size:16px;line-height:1.3em;flex-grow:1;margin:0 15px;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}
        .mta-fonts-format-info {font-size:11px;margin-left:1em;color:#555;}
        .mta-fonts-format-info .woff2 {color:green;font-weight:bold;}
        .mta-fonts-format-info .woff {color:#ff8c00;}
        .mta-fonts-format-info .ttf, .mta-fonts-format-info .otf {color:#a0522d;}
        
        @media (max-width:700px) {
            .mta-fonts-font-row {flex-wrap:wrap;}
            .mta-fonts-font-info {order:1;margin-bottom:5px;}
            .mta-fonts-font-sample {order:3;width:100%;margin:10px 0 5px;}
            .mta-fonts-format-info {order:2;}
        }
        </style>
END_OF_STYLE;
        
        // Container-Stil basierend auf dem Modus
        $container_style = $mode == 'shortcode' ? 'style="border:1px solid #eee;padding:15px;border-radius:5px;"' : '';
        $title = $mode == 'shortcode' ? '<h2>' . esc_html(self::TITLE) . '</h2>' : '';
        
        // Header mit Informationen und Kontrollen
        $output_header = <<<END_OF_HEADER
        <div class="mta-fonts-wrap" {$container_style}>
            {$title}
            <div class="mta-fonts-header">
                <div>
                    <span class="mta-fonts-label">Version:</span>
                    <span>{$this->get_version_info()}</span>
                </div>
                <div>
                    <span class="mta-fonts-label">Schriftfamilien:</span>
                    <span>{$this->count_font_families()}</span>
                </div>
                <div>
                    <span class="mta-fonts-label">Schriftdateien:</span>
                    <span>{$this->font_files_count}</span>
                </div>
                <div>
                    <span class="mta-fonts-label">Schriftgröße:</span>
                    <span><input id="mta-fonts-input-font-size" type="number" value="16" onchange="mta_fonts_change_font_size();"> px</span>
                </div>
                <div>
                    <span class="mta-fonts-label">Beispieltext:</span>
                    <input id="mta-fonts-input-sample-text" value="{$sample_text}" onkeyup="mta_fonts_change_sample_text();">
                </div>
            </div>
END_OF_HEADER;

        // Legende für die Schriftformate
        $output_legend = <<<END_OF_LEGEND
        <div class="mta-fonts-legend">
            <h3>Schriftformate</h3>
            <div><span style="color:green">WOFF2</span><span>Modernes Format, optimal für das Web (kleine Dateigröße).</span></div>
            <div><span style="color:#ff8c00">WOFF</span><span>Älteres Webformat mit größerer Dateigröße.</span></div>
            <div><span style="color:#a0522d">TTF, OTF</span><span>Desktop-Formate, können für das Web verwendet werden, haben aber größere Dateigrößen.</span></div>
        </div>
END_OF_LEGEND;

        // JavaScript für interaktive Steuerelemente
        $output_script = <<<'END_OF_SCRIPT'
        <script>
        function mta_fonts_change_font_size() {
            let size = document.querySelector('#mta-fonts-input-font-size').value;
            document.querySelectorAll('.mta-fonts-font-sample').forEach(el => {
                el.style.fontSize = size + 'px';
            });
        }
        
        function mta_fonts_change_sample_text() {
            let text = document.querySelector('#mta-fonts-input-sample-text').value;
            document.querySelectorAll('.mta-fonts-font-sample').forEach(el => {
                el.textContent = text;
            });
        }
        </script>
END_OF_SCRIPT;

        // Schriftproben für jede Schriftfamilie generieren
        $output_samples = '';
        foreach ($this->fonts as $font_name => $font_details) {
            $output_samples .= '<div class="mta-fonts-family">';
            $output_samples .= sprintf('<h3>%s</h3>', esc_html($font_name));
            
            // Nach Gewicht/Stil sortieren
            ksort($font_details);
            
            foreach ($font_details as $weight_style => $formats) {
                list($weight, $style) = explode('/', $weight_style);
                
                // Formatinformationen anzeigen
                $format_info = [];
                foreach ($formats as $format => $url) {
                    $format_info[] = sprintf('<span class="%2$s">%1$s</span>', strtoupper($format), $format);
                }
                
                $format_display = '<span class="mta-fonts-format-info">(' . implode(', ', $format_info) . ')</span>';
                
                // Schriftstilbezeichnung
                $style_display = $weight;
                if ($weight == 'var') {
                    $style_display = 'Variable';
                    $weight = '400'; // Standardgewicht für die Anzeige
                }
                if ($style == 'italic') {
                    $style_display .= ' Italic';
                }
                
                // Schriftprobe erstellen
                $output_samples .= sprintf(
                    '<div class="mta-fonts-font-row">
                        <span class="mta-fonts-font-info">%2$s</span>
                        <span class="mta-fonts-font-sample" style="font-family:\'%1$s\';font-weight:%3$s;font-style:%4$s">%5$s</span>
                        %6$s
                    </div>',
                    esc_attr($font_name),
                    esc_html($style_display),
                    esc_attr($weight),
                    esc_attr($style),
                    esc_html($sample_text),
                    $format_display
                );
            }
            
            $output_samples .= '</div>';
        }
        
        // Keine Schriften gefunden
        if (empty($this->fonts)) {
            $output_samples = '<p>Keine Schriftdateien gefunden. Bitte laden Sie Schriftdateien in das Verzeichnis <code>/wp-content/uploads/fonts/</code> hoch.</p>';
        }
        
        // Vollständige Ausgabe zusammenbauen
        $output = $output_style . $output_header . $output_samples . $output_legend . $output_script . '</div>';
        
        return $output;
    }
    
    /**
     * Zählt die Anzahl der Schriftfamilien
     * 
     * @return int Anzahl der Schriftfamilien
     */
    private function count_font_families(): int {
        return count($this->font_families);
    }
    
    /**
     * Gibt Versionsinformationen zurück
     * 
     * @return string Versionsinformation
     */
    private function get_version_info(): string {
        return self::VERSION;
    }
}

// Initialisierung der Klasse
new MTA_Font_Manager();
endif;