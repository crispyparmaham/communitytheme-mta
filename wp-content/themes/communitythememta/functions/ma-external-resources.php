<?php
/*
Plugin Name:  MA Content Consent
Description:  Shortcode for requesting user consent before loading external content.
Author:       <a href="https://www.altmann.de/">Matthias Altmann</a>
Project:      Code Snippet: MA Content Consent
Version:      1.3.0
Plugin URI:   https://www.altmann.de/en/blog-en/code-snippet-shortcode-content-consent/
Description:  en: https://www.altmann.de/en/blog-en/code-snippet-consent-for-external-content/
              de: https://www.altmann.de/blog/code-snippet-zustimmung-fuer-externe-inhalte/
Copyright:    © 2021-2024, Matthias Altmann



Version History:
Date		Version		Description
--------------------------------------------------------------------------------------------------------------
2024-01-19	1.3.0		Structural changes in V1.2.0 made styling the block, especially dimensions, way more 
						complicated or even impossible. A new structure with container and notice blocks, and 
						related attributes, now allows more specific styling. Also, the consented contents are 
						not embedded in an additional div anymore.
						Changes:
						- New Attributes container-class, container-style, notice-class, notice-style
						- Attributes block-class and block-style are now deprecated 
						  If still present, will replace container-class/style and notice-class/style 
						- Default styles are now assigned to classes ma-content-consent-container and 
						  ma-content-consent-notice instead of inline CSS
						- Replace contents of container directly with nodes, not nested in an additional div
						New Features:
						- Added global configuration via $GLOBALS['ma_content_consent']
2023-12-30	1.2.0		New Features:
						- Complete rebuild of JS
						- Added shortcode attributes alt, title
						- SVG backgrounds included: form, play, video, waveform, world. 
						  SVG symbols are only emitted to the page if used by a consent element.
						- Support for <script> tags embedded in hidden content
						- Support for dynamically embedded deferred content using AJAX calls
							- Removed init prevention for AJAX calls
							- Added PHP method MA_Content_Consent::enable_footercode() to trigger output of footer
							  code (styles, scripts, svg) for consent dialogs dynamically loaded by AJAX calls
							- Added JS function ma_content_consent_init_click_handler() to initialize click handlers 
							  for dynamically created consent dialogs.
						- Optimizations for accessibility 
2023-02-01	1.1.2		New Features:
						- Added Finnish translations for notice text and button label
						Changes:
						- Changed button tag from <a> to <span> to avoid Lighthouse SEO warning "Links not crawlable"
						Fixes:
						- Support for Oxygen Repeater (due to numbered IDs)
						  (Thanks to André Slotta for reporting and supporting with code adaptions)
2022-11-15	1.1.1		New Features:
						- Added Japanese translations for notice text and button label
						  (Thanks to Viorel-Cosmin Miron)
						- Added support for WPML
						  (Thanks to Viorel-Cosmin Miron)
						  Please note there's a WPML bug preventing the creation a policy link with the correct language
						  (https://wpml.org/forums/topic/get_the_privacy_policy_link-should-be-translated/#post-8153387) 
						Fixes:
						- Support for UTF-8 content in JS base64 decoding
						  (Thanks to Tobias Przybilla for reporting)
2022-10-21	1.1.0		New Features:
						- Added new shortcode parameters block-class, text-class, button-class
						Changes:
						- Migrated JavaScript from jQuery to vanilla JS (ES6) to eliminate jQuery dependency.
						  (Thanks to André Slotta for the provided code sample)
						Fixes:
						- Removed double '.' for German GDPR text.
						  (Thanks to Tobias Maximilian Hietsch for reporting)
2022-02-04	1.0.1		New Features:
						- Added Dansk translations for notice text and button label
						  (Thanks to Theis L. Soelberg)
						- Evaluating nested shortcodes
						  (Thanks to Anja Kretzer)
2022-02-03	1.0.0		Initial release as Code Snippet
2021-12-29	0.0.1		Initial version for client project
--------------------------------------------------------------------------------------------------------------
*/

if (!class_exists('MA_Content_Consent')) :

class MA_Content_Consent {
	const TITLE							= 'MA Content Consent';
	const SLUG							= 'ma-content-consent';
	const VERSION						= '1.3.0';


	// ===== CONFIGURATION ==============================================================================================
	public static $timing				= false; 	// Write timing info to wordpress debug.log if WP_DEBUG enabled		

	private static $default_consent_text = [ 		// consent text in different languages
		'da' => ['Når du har trykket, vil indholdet blive hentet fra eksterne servere. Se vores %s for flere informationer.','privatlivspolitik'],
		'de' => ['Bei Klick wird dieser Inhalt von externen Servern geladen. Details siehe %s.', 'Datenschutzerklärung'],
		'en' => ['When clicked, this content is loaded from external servers. See our %s for details.', 'privacy policy'],
		'es' => ['Al hacer clic, este contenido se carga desde servidores externos. Consulte la %s para más detalles.', 'política de privacidad'],
		'fi' => ['Kun tätä sisältöä napsautetaan, se ladataan ulkoisilta palvelimilta. Lisätietoja on %s.','tietosuojaselosteessa'],
		'fr' => ['En cliquant, ce contenu est chargé depuis des serveurs externes. Voir la %s.', 'politique de confidentialité'], 
		'hu' => ['Ha rákattint, ez a tartalom külső szerverekről töltődik be. A részletekért olvassa el az %s oldalt.', 'Adatkezelési Tájékoztatót'],
		'it' => ['Quando viene cliccato, questo contenuto viene caricato da server esterni. Vedere %s per i dettagli.', 'l\'informativa sulla privacy'],
		'ja' => ['クリックすると、以下のコンテンツが外部サーバーから読み込まれます。弊社のプライバシーポリシーの詳細は、%s', 'プライバシーポリシー'],
	]; 
	private static $default_button_text = [ 		// button text in different languages
		'da' => 'Indlæs eksternt indhold',
		'de' => 'Externen Inhalt laden',
		'en' => 'Load external content',
		'es' => 'Cargar contenido externo',
		'fi' => 'Lataa ulkoista sisältöä',
		'fr' => 'Charger le contenu externe', 
		'hu' => 'Külső tartalom betöltése',
		'it' => 'Caricare il contenuto esterno',
		'ja' => '外部のコンテンツを読み込ませます。',
	]; 

	// ===== INTERNAL ===================================================================================================
	private static $footercode_needed	= false;	// will be set to true if shortcode used on current page
	private static $footercode_svgs		= [];		// list of SVGs used and need to be embedded
	private static $available_svgs 		= ['form','play','video','waveform','world']; 
	private static $footercode_minimize = true;		// should we minimize all footer code (style, script, svg)?
	private static $total_runtime		= 0;

	

	//-------------------------------------------------------------------------------------------------------------------
	public static function init() {
		$st = microtime(true);

		add_shortcode('ma-content-consent', [__CLASS__, 'shortcode']);
		add_action('wp_footer',[__CLASS__,'footercode']);


		// for timing
		add_action('shutdown', [__CLASS__,'total_runtime']);

		$et = microtime(true);
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s%s::%s() Timing: %.5f sec.', '', __CLASS__, __FUNCTION__, $et-$st));}
		self::$total_runtime += $et-$st;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Logs total timing for shortcodes on a page 
	 */
	public static function total_runtime(){
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s%s::%s() Timing: %.5f sec.', '', __CLASS__, __FUNCTION__,self::$total_runtime));}
	}
	//-------------------------------------------------------------------------------------------------------------------
	private static function get_current_language(){
		$retval = get_locale();
		// Is a translation plugin active? Supporting Polylang, WPML 
		foreach (['pll_current_language','wpml_current_language'] as $func) {
			if (function_exists($func)) {$retval = $func(); break;}
		}
		$retval = str_replace('_','-',$retval);
		$retval = explode('-',@$retval)[0];
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	private static function get_privacy_policy_link($text) {
		// return a link to the privacy policy (if configured) or just the passed text
		$pplink = get_the_privacy_policy_link();
		return  $pplink ? $pplink : $text; 
	}

	//-------------------------------------------------------------------------------------------------------------------
	public static function shortcode($shortcode_atts = [], string $content = '') {
		$st = microtime(true);
		$lang = self::get_current_language();
		if (!is_array($shortcode_atts)) $shortcode_atts = [];
		
		// fix Gutenberg content: remove </p> ... <p>
		$content = preg_replace('/^<\/p>[ \r\n]*/','',$content); // remove leading </p> tag, spaces, line breaks from shortcode start tag
		$content = preg_replace('/[ \r\n]*<p>$/','',$content); // remove trailing line breaks, spaces, <p> tag from shortcode end tag
		// evaluate nested shortcodes
		$content = do_shortcode($content);

		// get defaults for unspecified atts
		$atts_default = [
			'slug'				=> self::SLUG,
			'id'				=> uniqid(self::SLUG . '-'),
			'alt'				=> null,
			'title'				=> null,
			'block-style'		=> '', /* deprecated */
			'block-class'		=> '', /* deprecated */
			'container-style'		=> '',
			'container-class'		=> '',
			'notice-style'		=> '',
			'notice-class'		=> '',
			'text'				=> isset(self::$default_consent_text[$lang]) 
										? sprintf(self::$default_consent_text[$lang][0],self::get_privacy_policy_link(self::$default_consent_text[$lang][1])) 
										: sprintf(self::$default_consent_text['en'][0],self::get_privacy_policy_link(self::$default_consent_text['en'][1])),
			'text-style'		=> '',
			'text-class'		=> '',
			'button-text'		=> self::$default_button_text[$lang] ?? self::$default_button_text['en'],
			'button-style'		=> '',
			'button-class'		=> '',
			'background-image'	=> null,
			'contentb64'		=> base64_encode($content),
		];
		// merge global settings
		$atts = array_merge($atts_default, $GLOBALS['ma_content_consent']??[]);
		// choose correct language for text
		if ($GLOBALS['ma_content_consent']['text-'.strtolower($lang)]??'') {
			$atts['text'] = $GLOBALS['ma_content_consent']['text-'.strtolower($lang)];
		}
		// choose correct language for button-text
		if ($GLOBALS['ma_content_consent']['button-text-'.strtolower($lang)]??'') {
			$atts['button-text'] = $GLOBALS['ma_content_consent']['button-text-'.strtolower($lang)];
		}
		// merge shortcode attributes
		$atts = (object)array_merge($atts, $shortcode_atts);

		$background_svg = '';
		$background_image_style = '';
		if ($atts->{'background-image'}) {
			if (strpos($atts->{'background-image'},'@')===0) {
				$svg = str_replace('@','',$atts->{'background-image'});
				if (in_array($svg,self::$available_svgs)) {
					self::$footercode_svgs[] = str_replace('@','',$atts->{'background-image'});
					$background_svg = '<svg style="position:absolute; z-index:-1; inset:1em; width:calc(100% - 2em); height:calc(100% - 2em); opacity:.1;">'. 
										'<use xlink:href="#ma-content-consent-background-'.str_replace('@','',$atts->{'background-image'}).'"></use>'.
									'</svg>';
				}
			} else {
				$background_image_style = 'background-image:url('.$atts->{'background-image'}.'); background-size:cover; background-position:center center;';
			}
		};
		$atts->{'text'} = str_replace('{privacy-policy-url}', get_privacy_policy_url(), $atts->{'text'});
		$atts->{'text'} = str_replace('{privacy-policy-link}', get_the_privacy_policy_link(), $atts->{'text'});

		foreach (['alt','title'] as $attr) {
			$atts->{$attr} = $atts->{$attr}??'';
		}

		self::$footercode_needed = true;

		// deprecated attributes block-class/style override container/notice-class/style
		if ($atts->{'block-class'}) {$atts->{'container-class'} = $atts->{'notice-class'} = $atts->{'block-class'};}
		if ($atts->{'block-style'}) {$atts->{'container-style'} = $atts->{'notice-style'} = $atts->{'block-style'};}

		// base64 decode supporting UTF-8 from https://stackoverflow.com/questions/30106476/using-javascripts-atob-to-decode-base64-doesnt-properly-decode-utf-8-strings#answer-30106551
		$html = <<<END_OF_HTML
		<div id="{$atts->id}" class="{$atts->slug}-container {$atts->{'container-class'}}" style="{$atts->{'container-style'}}" alt="{$atts->alt}" title="{$atts->title}">
			<div class="{$atts->slug}-notice {$atts->{'notice-class'}}" style="{$background_image_style} {$atts->{'notice-style'}}">
				{$background_svg}
				<div style="text-align:center">
					<p class="{$atts->slug}__text {$atts->{'text-class'}}" style="font-size:.7em; {$atts->{'text-style'}}">{$atts->{'text'}}</p>
					<button id="{$atts->id}__button" class="ma-content-consent__button wp-block-button__link {$atts->slug}__button {$atts->{'button-class'}}" tabindex="0" role="button" aria-label="{$atts->{'button-text'}}" style="display:inline-block; {$atts->{'button-style'}}">{$atts->{'button-text'}}</button>
				</div>
				<div id="{$atts->id}__content" class="{$atts->slug}__content" style="display:none">{$atts->contentb64}</div>
			</div>
		</div>
END_OF_HTML;
		


		$et = microtime(true);
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s%s::%s() Timing: %.5f sec.', '-> ', __CLASS__, __FUNCTION__, $et-$st));}
		self::$total_runtime += $et-$st;
		return $html;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Enables emission of footercode (styles, scripts, svg) for the deferred content embed. 
	 * Can be used for pages where deferred content is embedded dynamically by an AJAX call.
	 * On the parent page, call MA_Content_Consent::enable_footercode()
	 * @since 1.2.0
	 */
	public static function enable_footercode() {
		// add all svgs if footercode is enabled for AJAX
		self::$footercode_svgs = self::$available_svgs;
		self::$footercode_needed = true;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Emits the footer code: Script, SVG definitions for backgrounds, debug info)
	 */

	/**
	 * Notes about <script> tags in deferred content:
	 * Scripts added from content string are getting marked as non-executable. 
	 * Note from DOMParser documentation: Any <script> element gets marked non-executable, and the contents of <noscript> are parsed as markup.
	 * Instead, a DOM Parser is used to find <script> tags and convert them to executable script elements.
	 * To also find <script> tags nested deeper in the content structure the DOM Parser is executed recursively. 
	 */
	public static function footercode() {
		$st = microtime(true);
		if (!self::$footercode_needed) {goto DONE;}

		/** @since 1.2.0 debugging info */ 
		$codebase = basename(__FILE__) == 'ma-content-consent.php' ? 'Plugin' : 'Code Snippet';
		echo sprintf('<span id="ma-content-consent-info" data-nosnippet style="display:none">%s %s %s</span>', $codebase, self::SLUG, self::VERSION); 
		
		$style = <<<'END_OF_STYLE'
		<style id="ma-content-consent-style">
			.ma-content-consent-container {
				width:100%; 
				display:flex; 
				flex-direction:column; 
				justify-content:center; 
				align-content:center;
			}
			.ma-content-consent-notice {
				position:relative; 
				isolation:isolate; 
				width:100%; 
				height:100%; 
				display:flex; 
				flex-direction:column; 
				justify-content:center; 
				align-content:center; 
				background-color:#efefef; 
				border:1px solid lightgray; 
				padding:1em;
			}
		</style>
END_OF_STYLE;
		if (self::$footercode_minimize) { 
			$style = preg_replace('/\/\*.*?\*\//','',$style); 
			$style = preg_replace('/\r?\n */','',$style); 
			$style = preg_replace('/\t/','',$style); 
		}
		echo $style;

		$script = <<<'END_OF_SCRIPT'
		<script id="ma-content-consent-script" type="text/javascript">
		var $macc_debug = /[?&]ma\-content\-consent\-debug/.test(location.search);
		/* recursive DOMTree translator */
		window.ma_content_consent_DOMTree_translator = function($newContent,$nodes) {
			$macc_debug && console.log('DOMTree translator Nodes',$nodes);
			$nodes.forEach(function($node) {
				if ($node.nodeName == 'SCRIPT') {
					/* create a new script node to get rid of the non-execution flag */
					let $script = document.createElement('script');
					/* transfer script attributes */
					$macc_debug && console.log('DOMTree script attributes',$node.attributes);
					for (let $nodeAttr of $node.attributes) {
						$macc_debug && console.log('DOMTree script processing attribute',{name:$nodeAttr.name,value:$nodeAttr.value});
						/*$script.[$nodeAttr.name] = $nodeAttr.value||true;*/
						$script.setAttribute($nodeAttr.name,$nodeAttr.value);
					}
					/* transfer script content */
					$script.text = $node.text;
					/* append the new script node */
					$macc_debug && console.log('DOMTree processed script',$script);
					$newContent.append($script);
				} else {
					/* create a plain clone of the node */
					let $clone = $node.cloneNode(false);
					/* traverse child nodes of the original node */
					window.ma_content_consent_DOMTree_translator($clone,$node.childNodes);
					$newContent.append($clone);
				}
			});
			$macc_debug && console.log('new content',$newContent);

		};
		window.ma_content_consent_click_handler = function($trigger) {
			$trigger.addEventListener('click',function(){
				let $container = this.closest('div.ma-content-consent-container');
				let $container_id = $container.getAttribute('id');

				let $b64 = $container.querySelector('.ma-content-consent__content').innerText;
				let $content = decodeURIComponent(atob($b64).split('').map(function($c) {
					return '%'+('00'+$c.charCodeAt(0).toString(16)).slice(-2);
				}).join(''));
				$macc_debug && console.log('Hidden content: '+$content);
				
				/* run DOMParser for script detection */
				let $parser = new DOMParser();
				let $doc = $parser.parseFromString($content, 'text/html');
				let $newContent = document.createElement('div');
				window.ma_content_consent_DOMTree_translator($newContent,$doc.body.childNodes);
				$macc_debug && console.log('processed content',$newContent);
				/* replace container content with new content */
				$container.replaceChildren(...$newContent.childNodes);
			});
		};
		/* can be called by AJAX requests building dynamic content embeds */
		window.ma_content_consent_init_click_handler = function() {
			document.querySelectorAll('.ma-content-consent-container .ma-content-consent__button').forEach(($trigger) => {ma_content_consent_click_handler($trigger)});
		};
		ma_content_consent_init_click_handler();
		</script>
END_OF_SCRIPT;
		if (self::$footercode_minimize) { 
			$script = preg_replace('/\/\*.*?\*\//','',$script); 
			$script = preg_replace('/\r?\n */','',$script); 
			$script = preg_replace('/\t/','',$script); 
		}
		echo $script;

		// emit background svg symbol
		// source form: https://uxwing.com/form-icon/
		// source video: https://uxwing.com/video-icon/
		// source waveform: https://uxwing.com/waveform-icon/
		// source world: https://freesvg.org/silhouette-vector-graphics-of-political-world-map
		$symbols = '';
		if (in_array('form',self::$footercode_svgs)) {
			$symbols .= <<<'END_OF_SYMBOL_FORM'
			<symbol id="ma-content-consent-background-form" viewBox="0 0 122.88 120.45">
				<path fill="currentColor" d="M8.89,0h105.1c4.9,0,8.89,4,8.89,8.89v102.68c0,4.88-4,8.89-8.89,8.89H8.89c-4.88,0-8.89-4-8.89-8.89V8.89 C-0.01,4,3.99,0,8.89,0L8.89,0L8.89,0z M66.17,91.29h30.49c1.95,0,3.54,1.61,3.54,3.54v6.69c0,1.94-1.61,3.54-3.54,3.54H66.17 c-1.93,0-3.54-1.59-3.54-3.54v-6.69C62.63,92.88,64.22,91.29,66.17,91.29L66.17,91.29z M26.23,60.79h70.42 c1.71,0,3.27,0.7,4.41,1.84l0.01,0.01c1.13,1.13,1.83,2.69,1.83,4.41v8.85c0,1.71-0.7,3.28-1.83,4.41l-0.01,0.01 c-1.13,1.13-2.69,1.83-4.41,1.83H26.23c-1.71,0-3.27-0.7-4.41-1.83l-0.01-0.01c-1.13-1.13-1.83-2.69-1.83-4.41v-8.85 c0-1.71,0.7-3.27,1.83-4.41l0.01-0.01C22.96,61.49,24.52,60.79,26.23,60.79L26.23,60.79z M96.65,66.21H26.23 c-0.23,0-0.44,0.09-0.59,0.24c-0.15,0.15-0.24,0.36-0.24,0.59v8.85c0,0.23,0.09,0.44,0.24,0.59c0.15,0.15,0.36,0.24,0.59,0.24 h70.42c0.23,0,0.44-0.09,0.59-0.24c0.15-0.15,0.24-0.36,0.24-0.59v-8.85c0-0.23-0.09-0.44-0.24-0.59 C97.09,66.3,96.88,66.21,96.65,66.21L96.65,66.21z M26.23,33h70.42c1.71,0,3.27,0.7,4.41,1.83l0.01,0.01 c1.13,1.13,1.83,2.69,1.83,4.41v8.85c0,1.71-0.7,3.28-1.83,4.41l-0.01,0.01c-1.13,1.13-2.69,1.83-4.41,1.83H26.23 c-1.71,0-3.27-0.7-4.41-1.83l-0.01-0.01c-1.13-1.13-1.83-2.69-1.83-4.41v-8.85c0-1.71,0.7-3.27,1.83-4.41l0.01-0.01 C22.96,33.7,24.52,33,26.23,33L26.23,33z M96.65,38.42H26.23c-0.23,0-0.44,0.09-0.59,0.24c-0.15,0.15-0.24,0.36-0.24,0.59v8.85 c0,0.23,0.09,0.44,0.24,0.59c0.15,0.15,0.36,0.24,0.59,0.24h70.42c0.23,0,0.44-0.09,0.59-0.24c0.15-0.15,0.24-0.36,0.24-0.59v-8.85 c0-0.23-0.09-0.44-0.24-0.59C97.09,38.51,96.88,38.42,96.65,38.42L96.65,38.42z M114.8,27.01H8.46v82.6c0,0.68,0.27,1.28,0.72,1.74 c0.46,0.46,1.06,0.72,1.74,0.72H112.3c0.68,0,1.28-0.27,1.74-0.72c0.46-0.46,0.72-1.06,0.72-1.74v-82.6H114.8L114.8,27.01 L114.8,27.01z M105.53,9.64c2.42,0,4.39,1.97,4.39,4.39c0,2.42-1.96,4.39-4.39,4.39c-2.43,0-4.39-1.97-4.39-4.39 C101.13,11.6,103.09,9.64,105.53,9.64L105.53,9.64L105.53,9.64z M75.76,9.64c2.42,0,4.39,1.97,4.39,4.39 c0,2.42-1.97,4.39-4.39,4.39s-4.39-1.97-4.39-4.39C71.36,11.6,73.32,9.64,75.76,9.64L75.76,9.64L75.76,9.64z M90.64,9.64 c2.42,0,4.39,1.97,4.39,4.39c0,2.42-1.97,4.39-4.39,4.39s-4.39-1.97-4.39-4.39C86.25,11.6,88.21,9.64,90.64,9.64L90.64,9.64 L90.64,9.64z"/>
			</symbol>
END_OF_SYMBOL_FORM;
		}
		if (in_array('play',self::$footercode_svgs)) {
			$symbols .= <<<'END_OF_SYMBOL_PLAY'
			<symbol id="ma-content-consent-background-play" viewBox="0 0 500 350" >
				<path fill="currentColor" d="M500,74.767C500,33.472,466.55,0,425.277,0 H74.722C33.45,0,0,33.472,0,74.767v200.467C0,316.527,33.45,350,74.722,350h350.555C466.55,350,500,316.527,500,275.233V74.767z  M200,259.578v-188.3l142.789,94.15L200,259.578z"/>
				<path fill="white" d="M199.928,71.057l0.074,188.537l142.98-94.182 L199.928,71.057z"/>
			</symbol>
END_OF_SYMBOL_PLAY;
		}
		if (in_array('video',self::$footercode_svgs)) {
			$symbols .= <<<'END_OF_SYMBOL_VIDEO'
			<symbol id="ma-content-consent-background-video" viewBox="0 0 122.88 111.34">
				<path fill="currentColor" d="M23.59,0h75.7a23.68,23.68,0,0,1,23.59,23.59V87.75A23.56,23.56,0,0,1,116,104.41l-.22.2a23.53,23.53,0,0,1-16.44,6.73H23.59a23.53,23.53,0,0,1-16.66-6.93l-.2-.22A23.46,23.46,0,0,1,0,87.75V23.59A23.66,23.66,0,0,1,23.59,0ZM54,47.73,79.25,65.36a3.79,3.79,0,0,1,.14,6.3L54.22,89.05a3.75,3.75,0,0,1-2.4.87A3.79,3.79,0,0,1,48,86.13V50.82h0A3.77,3.77,0,0,1,54,47.73ZM7.35,26.47h14L30.41,7.35H23.59A16.29,16.29,0,0,0,7.35,23.59v2.88ZM37.05,7.35,28,26.47H53.36L62.43,7.38v0Zm32,0L59.92,26.47h24.7L93.7,7.35Zm31.32,0L91.26,26.47h24.27V23.59a16.32,16.32,0,0,0-15.2-16.21Zm15.2,26.68H7.35V87.75A16.21,16.21,0,0,0,12,99.05l.17.16A16.19,16.19,0,0,0,23.59,104h75.7a16.21,16.21,0,0,0,11.3-4.6l.16-.18a16.17,16.17,0,0,0,4.78-11.46V34.06Z"/>
			</symbol>
END_OF_SYMBOL_VIDEO;
		}
		if (in_array('waveform',self::$footercode_svgs)) {
			$symbols .= <<<'END_OF_SYMBOL_WAVEFORM'
			<symbol id="ma-content-consent-background-waveform" viewBox="0 0 122.88 61.34">
				<path fill="currentColor" d="M49.05,15.88c0-1.42,1.15-2.57,2.57-2.57s2.57,1.15,2.57,2.57v29.6c0,1.42-1.15,2.57-2.57,2.57s-2.57-1.15-2.57-2.57V15.88 L49.05,15.88L49.05,15.88z M73.83,15.88c0-1.42-1.15-2.57-2.57-2.57c-1.42,0-2.57,1.15-2.57,2.57v29.6c0,1.42,1.15,2.57,2.57,2.57 c1.42,0,2.57-1.15,2.57-2.57V15.88L73.83,15.88L73.83,15.88z M122.88,9.46c0-1.42-1.14-2.56-2.53-2.56c-1.4,0-2.53,1.14-2.53,2.56 v42.43c0,1.42,1.14,2.56,2.53,2.56c1.4,0,2.53-1.14,2.53-2.56V9.46L122.88,9.46L122.88,9.46z M113.11,2.57 c0-1.42-1.15-2.57-2.57-2.57s-2.57,1.15-2.57,2.57v56.2c0,1.42,1.15,2.57,2.57,2.57s2.57-1.15,2.57-2.57V2.57L113.11,2.57 L113.11,2.57z M83.65,21.66c0-1.42-1.15-2.57-2.57-2.57c-1.42,0-2.57,1.15-2.57,2.57v18.02c0,1.42,1.15,2.57,2.57,2.57 c1.42,0,2.57-1.15,2.57-2.57V21.66L83.65,21.66L83.65,21.66z M93.46,15.88c0-1.42-1.15-2.57-2.57-2.57c-1.42,0-2.57,1.15-2.57,2.57 v29.6c0,1.42,1.15,2.57,2.57,2.57c1.42,0,2.57-1.15,2.57-2.57V15.88L93.46,15.88L93.46,15.88z M103.25,9.46 c0-1.42-1.14-2.56-2.53-2.56c-1.4,0-2.53,1.14-2.53,2.56v42.43c0,1.42,1.14,2.56,2.53,2.56c1.4,0,2.53-1.14,2.53-2.56V9.46 L103.25,9.46L103.25,9.46z M0,9.46C0,8.05,1.14,6.9,2.53,6.9c1.4,0,2.53,1.14,2.53,2.56v42.43c0,1.42-1.14,2.56-2.53,2.56 C1.13,54.45,0,53.3,0,51.89V9.46L0,9.46L0,9.46z M9.78,2.57C9.78,1.15,10.93,0,12.35,0c1.42,0,2.57,1.15,2.57,2.57v56.2 c0,1.42-1.15,2.57-2.57,2.57c-1.42,0-2.57-1.15-2.57-2.57V2.57L9.78,2.57L9.78,2.57z M39.23,21.66c0-1.42,1.15-2.57,2.57-2.57 c1.42,0,2.57,1.15,2.57,2.57v18.02c0,1.42-1.15,2.57-2.57,2.57c-1.42,0-2.57-1.15-2.57-2.57V21.66L39.23,21.66L39.23,21.66z M29.42,15.88c0-1.42,1.15-2.57,2.57-2.57c1.42,0,2.57,1.15,2.57,2.57v29.6c0,1.42-1.15,2.57-2.57,2.57 c-1.42,0-2.57-1.15-2.57-2.57V15.88L29.42,15.88L29.42,15.88z M19.63,9.46c0-1.42,1.14-2.56,2.53-2.56c1.4,0,2.53,1.14,2.53,2.56 v42.43c0,1.42-1.14,2.56-2.53,2.56c-1.4,0-2.53-1.14-2.53-2.56V9.46L19.63,9.46L19.63,9.46z M58.9,9.46c0-1.42,1.14-2.56,2.53-2.56 c1.4,0,2.53,1.14,2.53,2.56v42.43c0,1.42-1.14,2.56-2.53,2.56c-1.4,0-2.53-1.14-2.53-2.56V9.46L58.9,9.46L58.9,9.46z"/>
			</symbol>
END_OF_SYMBOL_WAVEFORM;
		}
		if (in_array('world',self::$footercode_svgs)) {
			$symbols .= <<<'END_OF_SYMBOL_WORLD'
			<symbol id="ma-content-consent-background-world" viewBox="0 0 783.086 400.649" >
				<g fill="currentColor">
					<path d="m346.72 131.38l-3.377 4.501-2.25 2.251-4.501 2.475-0.676 2.026s-1.801 1.801-1.575 2.927 0.226 3.151 0.226 3.151-3.151 2.926-4.502 3.826c-1.351 0.9-3.152 2.926-3.152 2.926s-1.125 1.351-1.8 1.801c-0.675 0.449-1.8 1.8-2.701 3.151s-1.801 2.25-2.251 2.925-1.801 2.251-2.476 4.727c-0.675 2.477-2.476 5.402-2.476 5.402l-0.451 1.125c0.226 3.827 0.225 4.502 0.676 5.853 0.45 1.35 0.901 0.899 0.45 3.826-0.45 2.925-0.45 4.051-0.9 5.402-0.45 1.349-1.801 3.825-2.25 4.952-0.451 1.125 0.9 3.15 0.9 3.15s0.676 2.927 0.225 3.602c-0.45 0.676 0.225 2.026 0.225 2.026s2.701 1.575 3.602 2.476c0.9 0.9 2.476 2.251 2.701 2.926 0.226 0.676 2.026 3.827 2.251 4.502s2.026 3.15 2.476 3.826c0.45 0.676 2.701 3.152 2.701 3.152s2.926 2.249 3.602 3.15c0.675 0.9 2.701 1.35 3.375 2.025 0.676 0.675 2.251 0.449 3.151 1.126 0.901 0.675 2.701-0.451 3.602 0 0.9 0.45 3.377-0.45 4.052-0.9s2.25-2.025 3.376-1.351c1.125 0.674 1.8 0 2.701 0.225 0.899 0.225 2.25-0.899 3.15-0.899 0.901 0 3.151-0.901 3.826-0.901 0.676 0 2.927-1.352 3.828-1.352 0.899 0 2.025-0.898 3.15-0.449s0.675-0.899 2.701-0.225 2.701 0 3.376 0.674c0.676 0.676 1.577 0.002 2.252 0.902 0.675 0.899 1.8 1.574 2.025 2.475 0.224 0.901 1.575 1.802 1.575 1.802l1.351 0.224s1.802-1.35 2.251-0.449c0.45 0.9 2.476 0.676 2.476 0.676l2.025 2.25 0.676 0.9s1.351 3.602 1.125 4.952c-0.225 1.35-0.225 4.276-0.9 4.951-0.675 0.676-1.125 3.602-1.125 3.602s-0.901 0.9 1.125 2.701c2.026 1.802 3.377 3.376 4.052 4.275 0.676 0.901 2.477 3.378 2.7 4.953 0.226 1.575 0.676 2.475 1.577 4.501 0.9 2.026 2.025 2.251 2.25 4.503 0.226 2.25 0.675 2.251 0.45 3.825-0.225 1.576-0.9 1.801-0.225 2.926 0.675 1.126 1.574 3.152 1.351 3.827-0.225 0.675-0.676 3.601-0.676 3.601l-0.45 1.351-2.025 3.828-1.351 2.024c-0.45 1.575-1.35 3.151-1.125 5.402 0.224 2.251-1.125 2.25 0.675 4.5 1.801 2.252 2.7 4.728 3.826 6.754 1.126 2.025 1.801 4.051 2.025 5.401 0.226 1.351 1.126 1.802 1.576 3.602 0.449 1.801 0.675 2.7 0.675 3.826 0 1.125-0.226 4.052 0.226 4.727 0.449 0.675 0.675 2.702 0.9 3.602 0.225 0.901-0.676 1.126 0.675 2.702 1.351 1.575 2.251 2.475 3.151 3.601s0.675 0.675 1.351 2.251c0.675 1.575 1.575 2.477 1.801 3.826 0.225 1.35 0.449 4.726 1.125 6.302 0.675 1.576 1.575 3.152 1.575 3.152s2.477 1.575 3.377 1.35c0.899-0.225 2.701-2.249 4.052-1.575 1.351 0.675 2.476 0 3.601 0s2.251-1.351 3.377-1.351c1.125 0 2.7-1.801 3.602-1.35 0.9 0.449 2.024-0.452 3.15-1.577 1.126-1.124 1.801-2.024 3.826-3.825 2.026-1.801 1.801-1.802 3.151-2.927 1.351-1.126 2.702-3.602 4.277-4.052s2.025-2.024 2.475-3.15c0.451-1.126 0.675-1.801 1.351-2.702 0.676-0.899 0.225 0.228 1.351-2.7 1.126-2.927 2.478-4.051 3.603-4.727 1.125-0.675 1.8-2.025 2.475-2.251s1.801 0.675 1.351-1.351c-0.449-2.025 0-4.951-0.449-5.626-0.451-0.677-1.127 1.574-0.901-1.803 0.227-3.375 2.701-6.752 2.701-6.752s3.376-4.276 4.502-4.951 1.35-1.35 2.927-2.251c1.574-0.899 4.951-3.15 4.951-4.276s1.351-4.052 1.351-4.728c0-0.675 0-1.35-0.449-5.177-0.451-3.825 0.449-2.926-0.901-6.527s-1.577-8.103-2.476-9.228c-0.901-1.126-0.451-4.053-0.451-4.952 0-0.9 1.577-3.151 2.252-4.051 0.675-0.901 3.601-4.503 3.601-4.503s4.501-5.176 6.077-7.651c1.576-2.477 3.151-4.727 4.276-5.627 1.126-0.9 3.827-2.928 5.402-5.178 1.576-2.25 4.727-6.526 4.953-7.203 0.224-0.674 2.024-2.925 2.024-2.925s2.476-2.927 2.927-4.502c0.449-1.576 2.699-10.354 2.249-9.904-0.448 0.451-3.15 0.9-4.051 1.125-0.899 0.226 1.352 1.125-5.177 0.676-6.526-0.451-9.902 0.675-9.902 0.675-2.027-0.451-3.602 1.8-4.278-1.351-0.675-3.15-1.126-4.951-2.475-7.202-1.351-2.251-2.252-3.151-4.276-6.078-2.025-2.925-1.802-1.576-3.603-3.825-1.8-2.251-2.251-1.577-3.376-4.502-1.125-2.926-2.025-1.8-3.376-5.852-1.351-4.053-2.251-4.503-2.926-6.753-0.676-2.25-1.575-2.927-2.026-4.952-0.45-2.026-5.626-12.379-6.302-13.055-0.676-0.674-1.801-4.277-1.576-3.601 0.226 0.675 3.377 2.926 3.377 2.926s1.8-0.676 2.476-0.676c0.675 0 3.376 2.477 3.376 3.376 0 0.901 4.729 5.178 5.853 8.329 1.125 3.151 2.478 5.402 2.701 6.302s2.476 4.502 3.377 5.853c0.899 1.351 3.825 4.051 4.275 5.402s4.728 5.852 4.728 8.778c0 2.926 2.024 6.752 2.25 7.652s4.276 2.027 4.276 2.027l5.403-1.351s8.102-5.178 9.003-5.178c0.899 0 8.104-3.376 8.104-3.376s2.925-2.7 4.95-4.275c2.026-1.576 4.277-4.501 4.728-5.853 0.449-1.351 1.35-1.575 3.15-3.602 1.801-2.026 4.277-4.051 2.702-5.402-1.576-1.35-2.477-0.9-4.727-3.151-2.251-2.25-2.026-1.8-3.602-4.727-1.576-2.925 0.225-6.302-3.152-2.925-3.375 3.376-2.025 4.276-5.402 4.953-3.375 0.674-4.727 2.925-6.302 0.674s-3.15-0.9-4.052-4.277c-0.9-3.376-1.576-3.602-1.8-4.951-0.224-1.351 0-1.351-1.801-4.728-1.8-3.376-0.45-8.553 0.675-5.627 1.126 2.926 2.926 3.376 5.853 5.402 2.925 2.025 4.277 3.602 6.977 3.827 2.701 0.224 4.952 0 5.402 0.449 0.45 0.451 0.9-0.224 2.026 0.225 1.126 0.451 1.574-0.674 2.025 1.125 0.45 1.801 2.926 2.026 4.051 3.151 1.126 1.125 1.126 0.676 4.053 1.125 2.927 0.451 6.979 0.451 6.979 0.451l4.275-0.226 4.952 0.449s3.603 2.026 4.502 2.026c0.9 0 1.125 2.477 3.376 3.827 2.251 1.351 2.701 1.575 4.051 3.151 1.353 1.576 1.126 2.251 2.926 3.826 1.802 1.577 4.729-0.224 5.179-0.898 0.448-0.676 2.699-1.801 2.251 0-0.451 1.8 2.249 1.124 1.574 5.402-0.675 4.275-1.126 3.15 0 6.077 1.125 2.926 6.077 11.929 6.303 14.63s1.575 1.801 1.8 4.278c0.226 2.475 1.575 3.375 1.575 5.176s1.577 2.25 1.801 3.602c0.227 1.351 2.702 0.224 3.377 0 0.676-0.226 3.826-3.152 3.826-3.152s1.353-0.449 1.126-2.701c-0.226-2.249 0-1.8 0-5.176s-1.35-3.376-0.675-6.751c0.675-3.376 2.475-5.403 2.926-6.978 0.45-1.575 2.251-4.276 3.15-4.502 0.9-0.225 5.853-2.7 6.979-4.277 1.125-1.575 2.926-3.376 4.727-5.176 1.8-1.8 4.951-4.051 5.627-3.376 0.675 0.675 0.45 0.45 1.576 0.45 1.125 0 3.151 0.901 4.726 1.126 1.576 0.225 4.728 4.051 5.403 6.077s0.225 1.575 2.701 4.502c2.476 2.925 4.727 1.124 4.727 4.726s0 3.376 1.126 4.727c1.125 1.351 2.475-2.476 3.825-2.025s1.575-0.45 2.7 3.151c1.125 3.602 1.8 3.602 2.027 7.203 0.224 3.601-0.226 0.225-0.226 3.15 0 2.927-0.226 3.152 0.226 5.852 0.449 2.703 2.023 3.603 1.8 4.053 0 0 1.35-3.826 0.899-6.528-0.448-2.7 0-6.526 1.126-7.651 1.125-1.127 2.252-2.251 3.151 0.675 0.901 2.925 1.351 0.675 3.376 4.051 2.027 3.377 3.827 2.7 4.052 4.276s1.353 1.802 2.926 3.151c1.576 1.351 2.926-0.224 4.727-1.575 1.802-1.351 6.752-6.303 6.979-8.329 0.224-2.025 2.25-1.576-1.352-6.751-3.602-5.177-3.602-6.978-7.428-10.129-3.824-3.151-6.076-3.376-3.149-7.428 2.926-4.052 4.277-6.752 6.077-5.626 1.801 1.125 4.95 0.899 4.95 5.402 0 4.501-0.9 9.453 2.251 4.051s4.728-8.553 3.826-7.651c-0.899 0.9 2.026-1.801 2.026-1.801s0.226-2.026 4.053-2.926c3.825-0.902 4.95-1.351 6.302-2.026 1.35-0.676 5.177-5.178 5.852-7.203 0.675-2.026 2.476-5.627 2.026-7.202-0.45-1.577-2.702-9.228-3.152-9.004-0.449 0.226-0.224 0.226-3.149-2.925-2.928-3.151-4.953-5.402-5.403-6.753s-1.576-1.576-0.226-3.376c1.352-1.8 2.027-1.575 2.252-3.151 0.224-1.575 2.024-2.926-1.352-1.8-3.375 1.125-0.9 0.451-3.826 0.226-2.926-0.226-10.578 1.575-6.751-3.151 3.825-4.727 4.949-7.652 5.625-7.652 0.677 0 4.053-3.827 4.277-0.226 0.226 3.601 1.575 2.7 1.801 4.951s4.727-2.475 4.727-2.475 1.575 0.45 2.7 1.575c1.126 1.125 1.126 4.727 1.126 4.727s-0.9-1.126 1.576 0.675c2.477 1.802 5.176 0.9 5.176 4.727 0 3.826 0 4.052 1.576 4.952 1.577 0.9 3.15 0.451 4.503-0.675 1.35-1.125 3.6-5.177 3.6-5.177s0.225-1.576-3.376-3.827c-3.601-2.25-5.852-3.825-7.201-4.951-1.352-1.125-0.901-4.501 0.448-5.853 1.353-1.351 2.026-2.026 1.576-3.826-0.451-1.802 0.676-3.152 4.052-2.927 3.376 0.226 4.051-0.224 4.051-0.224s0.677 0.449 0.677-0.901c0-1.35 4.275-7.653 3.149-7.653-1.125 0 0.228-1.575-0.45-4.051-0.675-2.477 2.251-6.302-0.224-8.103-2.476-1.801-3.152-2.702-4.727-5.402-1.575-2.702-1.352-3.151-7.203-3.827-5.852-0.675-8.553 1.351-9.904-0.449-1.351-1.801-2.025 2.7-2.925-2.702s1.351-9.004 1.351-9.004 3.601-2.25 4.5-3.826c0.9-1.576 4.501-2.251 7.43-1.125 2.926 1.125 7.201 0.9 10.804 1.577 3.601 0.674 3.376 2.925 5.177 0 1.8-2.928 0-4.277 1.35-5.853 1.352-1.576 2.926-4.052 4.051-2.477 1.125 1.577 3.379 3.151 5.18 1.125 1.8-2.025 2.475-2.25 2.475-2.25l0.898 0.901s2.026-1.127 2.026 0.898c0 2.027 2.027 4.278 0.901 5.853-1.127 1.577-2.7 1.351-2.7 5.177 0 3.828 0 3.152 2.474 6.304 2.477 3.15 4.052 0.449 4.502 3.826 0.451 3.376 0.899 2.702 3.377 4.952 2.476 2.25 2.024 2.25 3.826 3.601 1.801 1.351 2.702 2.702 2.702 0.451s0.225-3.376 0-5.402c-0.227-2.025 1.801-3.602 0.448-6.978-1.35-3.376-0.224-3.151-2.925-5.627-2.702-2.475-4.503 0.451-5.401-3.151-0.901-3.601 4.275-5.177 5.627-5.627 1.35-0.449 3.374-3.151 3.824-1.35 0.451 1.8-3.375 8.328 3.153 1.35 6.526-6.977 3.601-4.051 3.601-4.051s3.376-0.45 4.051 0c0.674 0.451 2.478-2.026 1.8-1.8-0.675 0.226-7.2-3.602-7.2-3.602l-4.729-2.7s1.577-1.126 2.702-0.9c1.126 0.225 1.352-2.251 2.478-2.477 1.125-0.225 8.777 1.577 10.354 2.477 1.575 0.9 7.201 1.125 6.525 0.675-0.675-0.451-4.275-3.151-4.501-4.052-0.224-0.9-8.103-2.701-10.578-2.476-2.477 0.224-4.726 0.899-6.752 0.224-2.027-0.675-4.952-2.476-6.528-2.476-1.575 0-21.382-3.826-22.282-3.601s-2.252 4.501-2.927 3.826c-0.675-0.675-1.8-0.676-4.953-1.576-3.149-0.9-7.427-1.576-13.055-2.025-5.626-0.45-9.451 0.45-11.027-1.351-1.576-1.8-4.951-1.125-8.778-1.351-3.826-0.224-8.328 0-10.354 0s-6.751-0.224-7.877-0.45-2.476-1.801-3.826-2.025c-1.352-0.226-1.127-0.675-3.827-1.125-2.701-0.451-2.476-3.376-3.376-0.901-0.9 2.477-0.451 1.801-1.801 2.701-1.351 0.901-2.701 2.701-3.602 2.701-0.899 0-4.5 0.225-5.177-0.225-0.676-0.45-1.801-0.9-4.052-0.9s-2.251 0.224-5.177-0.226-5.177-0.226-7.878-1.575c-2.701-1.351-2.701-2.251-4.952-2.026-2.249 0.226-2.249-0.45-5.402-0.45-3.15 0-6.976 1.801-9.003 2.026-2.024 0.225-6.526-0.226-8.102-0.901-1.577-0.675-2.026-2.25-7.879-1.576-5.852 0.676-9.228 0.676-8.103-1.125 1.126-1.8 2.476-3.601 3.151-3.826 0.676-0.225 2.25-1.35 1.124-1.8-1.124-0.451-2.25-1.125-4.95-1.351-2.702-0.225-2.478 0.9-6.528 0.45-4.051-0.45 2.251-1.575-6.076-1.35-8.328 0.226-10.354 0.9-11.254 0.9-0.901 0-2.027-1.8-4.277 0.9-2.251 2.701-5.404 4.276-9.904 4.501-4.501 0.226-4.727-0.45-9.002 1.125-4.277 1.575-6.979-0.45-6.078 3.151 0.899 3.601 2.026 4.952 2.026 5.627s-2.026 5.177-4.501 2.476c-2.478-2.701-6.979-13.28-4.953-6.077 2.025 7.202 6.526 9.679 2.251 10.804-4.276 1.125-3.828-0.45-4.503-0.901-0.674-0.45 0-3.601-0.225-5.402-0.226-1.8-0.675-2.926-1.575-3.826-0.899-0.901-0.676-2.251-2.476-2.701-1.801-0.45-0.225-1.351-3.151-1.351s-5.401-1.801-4.726 2.251c0.674 4.051 1.349 1.351 3.149 5.177s5.177 1.125 2.702 3.826c-2.476 2.701-9.003 1.801-10.579 0.676-1.576-1.126-1.125 0.45-4.053-1.576-2.926-2.026-2.024-3.602-5.4-2.026s-4.502 4.502-5.853 3.376-4.501 1.575-5.627 0.226c-1.126-1.351-5.628-1.351-6.527 0-0.901 1.35-3.602 1.125-7.428 2.926-3.826 1.8-2.702 0.224-7.428 4.051-4.728 3.826-7.429 6.977-9.003 4.501-1.576-2.475-10.13-6.752-6.753-7.428 3.377-0.675 4.952-2.025 6.753-0.45s4.728 1.126 5.176 0c0.45-1.126 2.477-2.251-0.225-3.601-2.701-1.351-2.926-0.451-7.202-2.026-4.277-1.576 0-1.125-5.853-2.026-5.852-0.9-8.329-1.35-9.904-1.8-1.574-0.45-3.826 0-6.076-0.225-2.252-0.225-3.826-0.675-5.853-0.45-2.025 0.225-3.15-0.225-5.852 1.801s-4.728 2.926-6.528 4.501-6.526 4.727-7.877 5.853c-1.351 1.125-6.078 4.726-6.528 6.077s0.45 2.476-1.351 3.376c-1.801 0.9-3.601 0.9-4.952 1.125s0-2.026-1.351 0.225c-1.35 2.251-0.9 10.354 0.225 9.228s0.45-4.726 5.402-1.349c4.952 3.375 8.104 2.925 7.878 6.076-0.225 3.151 3.376 2.926 3.826 3.602 0.45 0.674 2.701-0.901 2.701-0.901s4.952-3.151 4.952-4.726c0-1.576 3.151-5.177 3.151-5.177s-2.252-1.125-2.252-3.376c0-2.25 0.677-2.927 1.126-5.178 0.45-2.25-2.476 0.001 4.277-4.277 6.751-4.276 7.202-1.349 7.202-1.349s0.899 0.9-0.226 2.25c-1.125 1.351-3.151 4.953-3.601 5.402-0.451 0.451 2.026 4.052 3.15 4.052 1.126 0 3.377-0.901 4.728-0.676 1.351 0.226 3.601-0.674 4.276-0.224 0.676 0.45 2.251 1.8 1.125 2.926-1.125 1.125-3.602 2.475-5.401 2.026-1.802-0.449-4.503-3.602-2.927 0.9s-0.225 5.627-1.576 5.402c-1.349-0.225-4.276-2.475-3.826 0.901 0.451 3.375-2.925 5.626-3.601 5.626s-2.025-0.225-2.025-0.225-2.027-0.226-3.151-0.226c-1.126 0-3.377 1.125-4.052 2.026-0.676 0.9-1.576 0.226-2.701 0.226s0-1.351-2.251-1.801c-2.25-0.45-3.601-0.9-4.951 0-1.351 0.9-3.376 1.35-4.277 0.675-0.9-0.675 0.451-2.252-1.351-1.35-1.8 0.9-3.151 3.376-3.826 3.602-0.675 0.224 2.026 0-0.675 0.224-2.701 0.226-3.826 0.902-5.177 1.351-1.351 0.451 0.45 3.151-2.026 4.051s-0.674-1.125-3.151 0.9c-2.476 2.027-2.927 3.827-3.826 4.052-0.901 0.226-0.675 0.675-2.701 0.9s-1.8 0.45-2.927 0.675c-1.125 0.225 0.226 0.676-2.25 0.225-2.477-0.45-4.277 0.451-3.827 1.351s8.778 3.376 2.476 3.151-4.276 0.9-2.926 1.8c1.351 0.9 2.025 1.8 2.701 2.026 0.676 0.225 1.8 2.927 1.8 2.927s1.801-0.226 0.676 1.35c-1.125 1.576 0.45 2.026-0.676 4.052-1.125 2.026-1.125 0.675-2.025 1.125-0.9 0.449-2.251 2.026-4.728 0.675-2.476-1.351-4.275-2.026-7.202-2.25-2.926-0.226-0.901-0.675-2.926 0.899-2.026 1.577-2.476 3.377-2.25 5.402 0.225 2.025 0.45 4.727-0.226 6.078-0.675 1.35-0.675 2.701-0.226 4.727 0.451 2.025 1.802 2.927 1.802 2.927s3.151 0.449 3.826 0.675c0.675 0.225 2.026-0.45 3.826 0.674 1.801 1.126 3.602 1.577 4.727 0.226 1.125-1.35 5.627-2.926 6.979-4.276 1.35-1.351 2.25-1.8 1.575-3.151s1.351-4.727 2.476-5.402 3.601-1.351 3.601-1.351l2.701-4.051s2.251-2.477 3.376-2.251c1.125 0.225 7.427 2.477 6.978-0.449-0.45-2.927 0.45-3.827 1.125-2.702s2.476 0.675 2.476 0.675 0.9-0.449 1.575 0.226c0.675 0.674 1.575 3.602 2.926 4.276 1.351 0.676 2.476 1.351 3.377 1.8 0.9 0.451 1.801 0 3.602 1.576 1.8 1.576 3.601 2.477 3.825 3.151 0.227 0.675 1.802 2.251 1.802 2.251s1.575 0.45 1.125 1.351c-0.45 0.9-2.7 3.602-3.826 4.051-1.126 0.45-4.051-0.451-2.926 0.45 1.124 0.9 2.701 2.026 3.376 1.35 0.676-0.675 4.951-5.177 4.951-5.177s-0.225 0.225 0-1.126c0.226-1.349 0-2.925 1.126-2.25 1.125 0.676 1.801 1.576 2.251 0.676 0.45-0.901 0.9-0.45-0.226-1.125-1.126-0.676-3.826-4.502-5.177-4.727-1.351-0.226-4.728-2.477-4.728-2.477s-0.674-0.675-2.25-2.475c-1.576-1.801-2.025-1.351-2.477-2.927-0.449-1.575-1.801-3.601 0.676-3.151s4.051 2.025 6.302 3.376 1.802 1.8 3.827 3.151 2.252 2.702 3.826 2.477c1.575-0.225 3.377 0.9 3.377 2.926s0.449 4.276 0.899 4.727c0.451 0.451 1.8 1.351 2.251 2.251 0.45 0.9 1.351 3.151 2.025 4.051 0.676 0.9 1.351 1.576 2.026 2.702 0.675 1.125 0.9-0.226 1.575 0.225 0.676 0.45 1.576 0.224 1.576-0.676s0.224-2.476 0.224-2.476 3.153-0.9 2.253-1.351c-0.901-0.45-3.603-3.151-3.377-4.051 0.225-0.9 0-2.7 1.124-2.475 1.126 0.224 2.252 2.25 4.728 0.449s5.402-2.701 4.501 0c-0.899 2.701 0.227 3.15 0 6.077-0.224 2.927 0.902 2.026 2.478 3.602 1.575 1.576 2.7 1.802 3.825 2.251 1.126 0.451 2.926 0.226 4.728 0.451 1.801 0.225 2.926-0.676 4.502-0.225 1.574 0.45 5.176-0.226 5.852 0.225 0.676 0.45 3.377-0.9 3.601 0.675 0.225 1.576 1.126-0.225 0.451 3.376s-1.576 6.077-1.576 6.077c-2.476 4.727-3.376 5.853-4.502 6.078-1.124 0.225-1.8 0.9-3.825 0.675-2.026-0.226-4.502 0-7.428-0.675-2.926-0.676-8.329 0.675-10.579-0.676-2.251-1.35-5.402-0.225-6.754-1.8-1.35-1.576-3.6-1.351-4.501-2.251-0.9-0.9-1.35-3.375-1.35 0.226 0 3.602-2.026 3.826-2.476 5.627-0.451 1.8 0.674 0.9-1.802 0.675-2.476-0.226-3.151 1.125-6.752-1.575-3.602-2.701-5.402-2.701-7.202-3.827-1.802-1.126-3.602 2.025-5.402-1.575-1.801-3.602-4.277-0.676-4.502-3.602-0.225-2.927 0-2.7 0.225-4.501 0.227-1.801 2.701-3.376 1.576-4.051-1.126-0.675-0.226-1.801-1.801-1.125-1.576 0.675-3.826 0.9-5.627 1.575-1.801 0.676 4.051 0-4.727 0.676-8.778 0.675-11.255 1.575-11.255 1.575s-0.675-0.45-1.801 0.45c-1.125 0.9-2.701 2.251-4.276 1.576-1.575-0.676-2.476-0.451-3.826-0.225-1.351 0.225-2.25-1.125-3.826 0-1.576 1.124-3.376 1.575-4.052 1.124-0.7-0.46-1.83-2.26-1.83-2.26z"/>
					<path d="m497.3 279.48c-0.675 2.477-3.151 6.077-3.151 6.077l-4.503 2.251s-1.573 1.351-2.475 1.576c-0.9 0.224-3.151 1.351-3.151 1.351s-2.025-0.451-1.351 1.574c0.675 2.026 0.901 4.503 0.675 5.177-0.224 0.676-0.224 2.027-0.224 3.828 0 1.8-0.675 4.05-1.801 4.5-1.125 0.451-2.251 0.901-2.026 2.477 0.226 1.576 0.226 6.302 0.226 6.302l2.927 4.502s0.9-0.675 2.701-0.675c1.799 0 3.601-1.574 3.601-1.574s2.027 0.448 1.8-2.026c-0.225-2.477 0.902-5.402 0.675-6.078-0.224-0.675 2.027-3.602 2.027-4.276 0-0.676 2.251-3.825 2.251-4.952 0-1.125 1.575-2.926 1.8-4.275 0.225-1.352 0.45-4.502 0.9-5.402 0.451-0.9 0.676-1.801 0.676-2.701s0-2.701 0-2.701l-1.6-4.96z"/>
					<path d="m673.54 309.87c-0.675 0.899-1.125 10.579-1.575 11.704s-1.351 6.978-0.899 8.327c0.449 1.352 1.124 6.303 0.675 7.879-0.451 1.575 0.225 6.527-0.902 6.302-1.124-0.226-1.573 0.226-1.573 0.9 0 0.676 1.573 2.025 1.573 2.025s2.703 0.001 3.828-0.225c1.125-0.225 3.376-1.351 5.177-1.351s2.927-1.351 4.501-1.351c1.576 0 4.953-1.124 5.853-0.675s2.25-2.701 3.826-2.025c1.576 0.675 4.953-1.576 6.528-1.576 1.574 0 2.251-2.477 4.052-2.025 1.8 0.45 5.177-0.9 6.753-0.227 1.573 0.677 4.051-2.25 5.176 0 1.126 2.252 4.503 3.152 4.503 4.277s-0.228 3.602-0.228 3.602l2.702-0.226s4.275-2.701 4.952-2.701c0.675 0 2.251-2.25 2.251-1.124 0 1.124-1.352 3.602-1.801 4.726-0.45 1.126 0.449 2.477 0.449 2.477s2.253 1.125 1.352 3.15c-0.901 2.026 0.224 1.577 0 3.151-0.226 1.576 0 0.9 0.898 1.8 0.902 0.901 2.479 2.479 3.828 2.252 1.352-0.226 1.8-1.575 2.926-0.9 1.125 0.675 3.375 0.225 4.05 1.126 0.678 0.899-4.275 3.15 4.278-0.675 8.553-3.828 9.451-4.728 10.577-6.079 1.129-1.35 2.479-2.7 3.377-3.601 0.901-0.9 0.227-0.675 2.926-2.701 2.701-2.025 2.701-1.125 5.179-4.952 2.476-3.825 4.275-5.176 4.727-5.852 0.45-0.676 1.35-0.9 1.575-3.603 0.225-2.7 2.025-5.176 2.025-6.076 0-0.899 0.675-2.249 0.45-3.826-0.225-1.576 0.901-3.825-0.45-6.076-1.351-2.252 0.675-3.15-1.351-4.053-2.026-0.9-3.376-0.449-3.826-2.025-0.451-1.576-2.252-1.351-2.927-5.627s-3.376-4.728-3.376-4.728-2.701-1.125-2.026-1.574c0.677-0.451 0.901-3.603 0.677-4.503-0.226-0.9-0.226-2.926-0.9-4.275-0.675-1.352-1.126-3.151-1.801-4.728-0.675-1.575-1.8-2.026-2.251-3.826s-1.126-2.477-1.35-2.926c-0.227-0.45-0.678-2.7-1.126-0.226-0.45 2.476-1.352 7.202-1.352 7.202s-1.801 3.828-2.476 4.953c-0.675 1.124-1.35 4.051-2.475 3.825-1.126-0.225-1.803 1.127-2.701 0.9-0.901-0.226-2.251 0.226-2.926-0.449-0.675-0.676-2.476-2.476-3.377-2.702-0.9-0.224-1.575-2.024-2.251-2.024-0.677 0-1.801-1.577-1.575-2.477 0.224-0.9 0.449-2.925 0.675-3.826 0.224-0.9 1.576-2.927 1.576-2.927s0-1.35-1.126-1.35c-1.125 0-1.351 0-3.827-0.226-2.475-0.226-3.149-0.45-4.052-0.45-0.898 0 0.902-0.45-1.575 0.225-2.475 0.677-3.601 2.477-5.177 4.053-1.573 1.575-0.224 1.124-1.573 1.575-1.353 0.451-1.126-1.126-2.027 1.351-0.901 2.476 0.451 3.15-0.227 3.601-0.675 0.451-5.4-1.575-5.4-1.575s1.125-0.226 0.224-1.351c-0.899-1.126-1.8-2.7-3.149-2.025-1.353 0.675-2.479 1.126-3.377 2.251-0.901 1.125-0.901 2.025-1.576 2.925s-2.475 0.9-2.701 1.801c-0.225 0.901-0.225 3.151-0.899 3.376-0.675 0.226-0.9-0.898-1.575-1.124-0.675-0.225-0.451-2.026-0.9-0.9-0.45 1.125-0.9 0.899-1.575 2.7-0.677 1.801-0.901 3.377-1.576 3.602-0.675 0.226 0 0.45-2.027 1.576-2.024 1.125-2.475 1.125-5.4 2.476s-2.25 1.351-4.276 1.575c-2.025 0.226-2.477 0.901-3.827 1.126-1.352 0.225-2.25 0.449-3.602 1.8-1.35 1.351-2.475 1.8-3.375 2.026s-1.76 1.11-1.76 1.11z"/>
					<path d="m686.82 220.96c-0.899 1.352-3.825 5.402-4.95 5.628-1.126 0.224-2.252 1.351-3.152 1.125s-2.926 0-3.601 2.926-1.801 4.502-2.478 4.276c-0.675-0.225 1.125 4.276-2.7 2.477-3.825-1.801-2.475 1.576-2.926 2.477-0.45 0.899 0.675 3.376 0.9 4.276 0.227 0.9 0.9 2.476 1.801 3.825 0.9 1.351 1.575 2.926 3.151 2.478 1.576-0.452 6.526 0 6.526 0s1.803 0.675 2.927 0.675c1.125 0 1.351-0.45 2.025-0.45s2.025-2.252 1.801-2.476c-0.225-0.227-0.899-0.901 0.45-2.252 1.351-1.351 2.926-1.8 3.152-3.376 0.225-1.575-0.45-1.575 0.225-2.477 0.675-0.9 2.25-0.673 1.801-1.575-0.451-0.9 0.449-0.675-0.451-2.477-0.899-1.8-2.026-2.476-1.126-4.727 0.901-2.25 1.803-2.701 1.577-4.051-0.226-1.352 1.125-1.801-0.451-3.377-1.574-1.574-2.25-2.025-3.376-2.477l-1.11-0.46z"/>
					<path d="m730.94 243.47c0.902-0.225 4.052-2.476 4.278 1.576 0.225 4.051 0.448 2.925 1.801 5.176 1.35 2.251 2.248 2.928 3.824 1.802 1.575-1.127 3.376-1.127 3.826-3.151 0.451-2.026 0.451-2.026 1.801-2.026 1.353 0 1.801-0.225 2.479 0.677 0.674 0.898 1.122-0.002 4.05 1.574 2.926 1.576 5.401 1.124 7.204 2.701 1.801 1.575 2.701 1.352 5.176 2.251 2.476 0.9 1.35-0.45 3.601 2.026 2.252 2.475 1.353 3.15 2.476 4.276 1.125 1.125 2.027-0.901 1.576 1.8s-3.601 0 1.126 3.825c4.726 3.828 5.402 4.504 6.976 5.628 1.577 1.126 2.702 2.477 1.353 2.477-1.353 0-5.628 0-6.077-0.676-0.451-0.675-2.476-1.126-3.828-2.701-1.349-1.574-4.275-2.701-4.949-4.051-0.677-1.352-1.577-1.801-2.702-1.801s-2.25 1.35-3.827 2.926c-1.576 1.575-0.898-0.899-2.926-0.225-2.027 0.676-4.275-0.226-5.402-1.126-1.126-0.9-0.899-2.25-2.699-1.575-1.803 0.675-2.926 0.225-2.251-1.125 0.675-1.352 0.224-3.152 0.224-3.152s0.677-2.251-0.898-2.7c-1.576-0.449-1.576-2.025-3.152-2.477-1.577-0.449-4.051-1.575-4.051-1.575s-0.225 0-0.901-0.225c-0.675-0.226-0.675-0.226-2.476-0.675-1.8-0.451-3.152-0.002-4.051-1.126-0.902-1.125-0.451-2.701-0.451-2.701s3.601 0.45 0-2.251l-3.601-2.7s0.448-0.226 0.224-0.676c-0.23-0.46 2.25-0.01 2.25-0.01z"/>
					<path d="m629.65 224.78s1.125 1.127 2.926 0.901c1.8-0.226 3.149 3.601 4.277 4.951 1.124 1.351 1.801 2.026 4.276 3.602 2.475 1.575 2.25-1.351 5.627 3.376 3.376 4.726 1.8 1.801 3.376 4.726 1.575 2.929 2.476 4.503 3.376 5.18 0.899 0.675 1.576 2.474 1.802 3.15 0.224 0.675 1.799 2.7 1.799 3.376 0 0.675 0.451 2.476-0.899 4.051-1.351 1.576-2.477 1.801-2.477 1.801s-2.701-1.351-3.601-2.701c-0.901-1.35-2.025-2.476-3.602-5.176-1.575-2.702-3.376-5.628-4.276-6.978-0.9-1.352-2.251-2.703-2.926-4.052-0.675-1.352-2.476-5.402-3.376-5.853s-1.126-1.8-2.702-3.376c-1.574-1.576-1.35-2.926-2.025-3.826s-2.25-2.928-2.25-2.928l0.66-0.22z"/>
					<path d="m191.87 400.35c-1.576-0.9-4.952-2.476-6.077-3.376s-4.501-5.854-4.501-5.854l-2.477-4.727s-2.925-3.601-2.25-4.951c0.675-1.351 2.026-2.251 2.026-2.926s1.35-2.477 0.224-3.151c-1.125-0.676-1.125-0.9-1.801-2.926-0.675-2.025-0.675-3.828-1.575-4.277-0.901-0.449-1.576-3.376-2.476-3.601-0.9-0.226-1.801-1.801-1.801-1.801l-1.351-18.232s-0.451-8.327-0.9-10.129c-0.451-1.8 0.675-5.626-0.451-9.003-1.125-3.376 0.226-7.427-0.45-8.778-0.675-1.351 0.45-5.176-0.225-8.777s2.701-8.103 0-9.453c-2.701-1.352-0.675-3.377-2.926-3.377-2.25 0-3.826-1.126-5.852-2.7-2.026-1.576-5.853-4.503-6.978-5.629-1.125-1.124-4.727-4.051-5.852-4.951s-2.251-4.727-3.151-5.853c-0.9-1.124-1.35-4.5-2.701-6.526-1.351-2.025-3.151-5.401-3.602-6.077-0.45-0.675-5.402-6.527-5.402-6.527l0.45-3.601s3.376-2.026 2.926-2.701c-0.45-0.676 0.226-1.352-1.351-2.701-1.576-1.352-1.351-3.828 0-4.952 1.351-1.127 3.376-4.728 3.376-4.728l3.376-5.626s2.702-2.928 2.702-4.953 3.826-3.375 1.575-5.176c-2.251-1.802-1.125-3.377-1.801-4.728-0.676-1.35-0.225-2.25 1.801-3.601s5.627-6.302 5.627-6.302 2.701-0.676 3.376-0.676 1.801-7.204 3.827-2.477c2.026 4.727 2.701 2.251 2.026 5.627-0.676 3.376 0.9 2.702 1.8 1.802 0.9-0.902-0.451 0.675 1.35-3.378 1.8-4.051 1.126-5.401 2.251-4.275s1.575 1.351 3.601 2.026c2.025 0.675 2.701 0.449 3.375 1.8 0.675 1.351 0.451-0.45 4.277 0.226s9.228-1.351 8.553 0.899c-0.675 2.251-2.026-0.225 1.125 2.251 3.151 2.477 3.602 2.926 5.402 3.377 1.8 0.45 3.376 0.225 6.078 2.926 2.701 2.7 3.376 2.926 4.051 4.275 0.675 1.352 1.8 0.451 2.926 1.801 1.125 1.352 2.701 1.352 4.276 1.352s0-1.352 2.476 0.45c2.477 1.8 4.277 2.024 5.402 5.626 1.125 3.604 3.376 3.377 1.575 6.979-1.8 3.602-5.401 5.177-3.826 4.952 1.575-0.226 4.502-1.577 5.402-1.351 0.9 0.224 1.35-0.675 2.701 0s1.576-0.9 1.351 0.675c-0.226 1.576-0.226 0-0.226 1.576 0 1.575-0.45-1.576 2.251-0.9 2.701 0.675 1.125-1.352 4.276 1.575 3.152 2.927 3.152 2.027 4.276 3.602 1.126 1.574 2.251-0.899 2.927-0.225s2.701 0.224 4.727 0.675c2.026 0.449 0.675-1.35 3.827 0.675 3.15 2.025 6.302 1.35 6.977 3.151 0.675 1.8 2.477 1.125 3.376 2.251s1.35-0.451 2.251 1.126c0.9 1.574 3.602 1.124 3.151 2.699-0.451 1.577 0.674 0.676 0.224 2.926-0.449 2.253-2.026 5.854-3.375 6.753-1.351 0.901-1.35 2.026-2.251 3.151-0.9 1.127-0.675-1.125-1.801 2.026-1.125 3.152-0.9 3.602-1.8 6.527s-0.225 0-0.9 2.926c-0.675 2.926-0.225 4.727-0.9 8.104-0.675 3.375-0.45 3.376-0.9 5.852s1.125-1.124-0.901 4.051c-2.025 5.178-2.25 4.951-2.925 7.203-0.676 2.252 0.225 0.45-2.251 1.801-2.476 1.352-1.125 0.9-4.052 1.801-2.925 0.9-6.527 1.802-7.652 2.026s-5.627 1.575-4.276 4.275c1.351 2.702 1.351 3.152 0 6.529-1.351 3.375-3.376 4.502-3.376 6.076 0 1.575-0.901 3.15-1.125 4.276-0.226 1.126 2.025-1.126-0.675 2.251-2.701 3.376-2.926 2.476-3.827 4.502-0.9 2.025 0 0.449-0.9 2.025-0.9 1.575-3.151 2.25-3.151 2.25s0.224 1.126-0.676 0.676c-0.901-0.449-0.675 0.225-4.052-0.676-3.376-0.9-3.826-3.149-4.276-1.575-0.45 1.575 0.225 2.477 2.251 4.052 2.025 1.576 5.402 2.025 4.501 3.602-0.9 1.576-0.9 1.576-1.575 2.476-0.676 0.901 0.9 0.225-0.676 0.901-1.575 0.675-2.476 3.602-3.151 3.602s-1.126 1.125-2.026 0.675c-0.9-0.451-3.15-2.026-3.601-1.126-0.45 0.899-1.351-1.126-1.126 2.025 0.226 3.151 0.676 4.952-0.45 5.628-1.125 0.675-3.827 0-3.827 0l-0.45-1.126-1.575 0.45s-0.675 2.701 0.45 3.151 0.9 0.899 2.025 2.25c1.126 1.351 0.451 3.827 0.451 3.827s0 0.676-1.125 1.801c-1.125 1.125-1.576 2.026-1.801 2.701-0.226 0.675-1.576 3.602 0.45 3.376 2.025-0.226 3.602-1.351 3.602 2.251 0 3.601 0.9 2.702-0.225 5.177-1.126 2.476-2.026 2.701-2.251 4.951-0.224 2.251 0.226 2.476 0.676 3.827s1.576 0.675 0.45 1.351c-1.12 0.64-4.49-0.03-4.49-0.03z"/>
					<path d="m133.8 217.13l-1.575-2.25s-0.675-1.352-1.801-1.576-3.602-3.151-3.602-3.151-1.125-0.9-1.125-2.475c0-1.576-0.901-4.051-0.226-6.752 0.676-2.7 0.901-6.077 0.676-6.978-0.226-0.9 1.125-1.125-0.45-2.026-1.576-0.9-4.727-1.125-6.753-1.125-2.025 0-4.502 0.676-4.952 0.9-0.45 0.225 2.476-6.752 2.926-7.877s2.476-6.078 2.476-6.752c0-0.676 0.675-0.901-0.675-1.8-1.35-0.901-3.601-1.351-4.952-0.676-1.35 0.676-1.125-0.9-2.25 1.8-1.125 2.702-2.251 5.628-3.602 6.078-1.35 0.451-4.051 1.801-4.051 1.801s-3.376-0.225-4.502-0.45c-1.125-0.225-5.852-2.701-6.752-4.276-0.9-1.576-1.35-4.953-1.35-6.527 0-1.576 2.475-6.302 2.25-7.428-0.225-1.125 2.477-5.627 2.251-6.303-0.226-0.675 0.45-2.926 1.125-3.826s4.052-2.026 4.952-2.926c0.9-0.901 2.476-2.025 4.051-2.476s2.927-0.9 4.052-0.9 4.051-2.476 4.501 0c0.451 2.475 2.477 1.575 3.602 1.8 1.125 0.226 1.351 1.351 3.151-1.575 1.801-2.927 3.601-3.827 3.601-3.152 0 0.676 2.926-0.224 3.376 0.451s1.351-0.451 2.926 0.9c1.576 1.351 3.826 1.576 3.601 2.25-0.225 0.675 0.9 0.675 0.9 1.351 0 0.675 0.901-1.576 0.901 2.475 0 4.052 1.125 6.528 1.125 6.528s1.575 3.377 2.25 2.701c0.675-0.675 2.025-2.475 2.025-2.475s2.251-1.351 0.676-6.078c-1.576-4.726-0.45-6.077-0.9-7.653-0.451-1.576-0.451-2.477 2.25-4.502s9.228-6.527 10.804-7.427c1.575-0.9 5.176-3.376 5.176-3.376s1.35-0.675 1.576-2.476c0.225-1.8 4.501-4.952 4.727-5.852s3.152-3.15 3.152-3.15 3.15-1.802 2.925-2.926c-0.225-1.125 4.052-3.15 4.727-3.15s4.277 1.124 4.277-0.902c0-2.025 0-2.476 2.251-4.276 2.25-1.8 5.176-4.502 6.302-4.502 1.125 0 4.952-2.024 6.527-2.475 1.576-0.451 6.528-2.702 2.026 0-4.502 2.7-1.576 1.125-2.476 3.376-0.899 2.25-1.576 5.177 2.701 1.8 4.277-3.376 4.051-4.052 5.627-4.276 1.576-0.225 0.45 0.675 3.826-0.451 3.377-1.125 3.602-1.575 3.602-1.575s1.576-1.351 0.675-2.025c-0.9-0.676 0.901-1.576-2.476-0.226-3.376 1.351-3.151 1.576-5.402 0.9-2.25-0.675-5.627-0.451-3.151-2.702 2.476-2.25 5.627-2.926 3.601-3.826-2.025-0.9-7.202 0.226-7.877 0.45-0.676 0.225-2.251-0.225-3.602 0-1.35 0.225-0.9-1.799 0-2.25 0.901-0.451 3.827-1.802 7.428-2.702s12.38-1.125 14.405-1.125 4.726 0.451 6.302-0.675c1.575-1.125 4.727-2.925 5.177-3.827 0.451-0.9 2.251-2.25 2.251-2.25s2.025-2.026 0.9-2.926c-1.125-0.9-2.251-2.926-3.151-2.476s-0.224 3.152-2.025 0.225c-1.801-2.925-1.576-3.376-2.476-4.051s-1.801-0.226-1.125-3.376c0.675-3.151 0.45-4.502 0.45-4.502s0.675-2.7 0-2.925c-0.676-0.226-1.125-0.9-2.25-0.226-1.126 0.675-7.428 3.827-8.778 4.502-1.351 0.675-3.152-2.701-2.477-4.276 0.676-1.577 0.225-0.676-0.675-2.926-0.9-2.251-4.501-2.702-5.852-2.702s-4.501-2.026-5.402-0.675c-0.9 1.35-5.626 5.626-6.528 6.077-0.9 0.451-2.25-3.826-2.925 3.151-0.676 6.979 2.476 9.004-1.125 9.454s-6.753 2.475-6.753 2.475-2.925 0.226-2.925 2.026c0 1.802-1.125 6.077-2.477 6.527-1.35 0.451-4.727-0.224-5.852-1.575s0.225-6.302 0.225-6.302l2.026-2.026s-14.405-2.25-14.181-3.151c0.226-0.9-4.276-2.475-4.726-3.826s-0.676-4.277-0.225-5.402c0.45-1.125 6.527-6.527 9.678-7.652 3.151-1.126 8.553-3.601 10.129-4.052 1.576-0.45 4.276-0.225 5.177-1.35 0.9-1.126 1.125-3.151 1.125-3.151s-0.45-3.152 0.45-2.702c0.9 0.451 2.476 1.126 2.701 1.801 0.226 0.675 0.226 2.251 1.801 0.449 1.576-1.8 3.602-2.476 5.402-3.375 1.801-0.901 4.501-0.675 6.078-1.575 1.575-0.9 4.276-1.801 4.501-4.052 0.225-2.25-0.675-1.575-1.801-1.801-1.125-0.224 0.45 0.226-3.376 2.026-3.827 1.8-2.701 0.45-5.402 1.8-2.701 1.351-2.927 1.576-3.151 0.451-0.226-1.126 0.45-2.926 0.45-2.926s0.226-0.226-1.35-0.675c-1.576-0.45-1.576 0.225-1.801-1.8-0.225-2.026 1.125-4.952-1.125-4.052-2.251 0.9-4.276-0.675-5.852 3.826s-6.978 9.229-8.329 8.103c-1.35-1.125-1.575-1.351-2.701-1.351s-2.701-1.35-4.727-1.575-8.104-0.225-9.678 0c-1.576 0.226-3.376-0.9-4.728 0.451-1.35 1.35-3.375 3.151-3.375 3.827 0 0.675-2.026-2.477-2.701-2.251-0.675 0.225-2.477-0.9-4.501-0.9-2.026 0-3.377-1.126-4.051-1.126-0.675 0 0.674-1.801 0-2.026-0.675-0.224-15.307-0.675-15.307-0.675s-4.726-1.8-6.526-1.575c-1.801 0.225-6.752 0.449-10.58 1.125-3.827 0.675-9.454 2.926-11.254 3.151-1.801 0.226-5.852 0.226-6.527-0.9-0.676-1.125 0.225-0.9-0.676-1.125-0.9-0.224-0.224-0.675-2.25-2.25-2.026-1.576 0.901-2.926-3.151-2.926-4.051 0-6.527-0.226-10.128 0-3.602 0.225-11.029 2.025-11.929 1.8s-8.103 1.126-9.229 0.451c-1.125-0.676-6.753 2.475-7.877 2.25-1.125-0.225-5.402 0.226-6.527 0.901-1.126 0.676-6.528 2.025-4.727 3.151 1.8 1.125 1.575 2.25 1.8 2.926 0.226 0.675-1.8 2.25-1.8 2.25s-0.451 0-2.926-0.9c-2.476-0.901-5.177-1.35-6.303-0.675-1.125 0.675-3.151 2.027-2.701 2.927 0.451 0.9 3.601 2.926 4.052 2.25 0.45-0.675 1.576-2.025 2.926-1.8 1.351 0.226 3.377-0.9 1.576 1.351-1.801 2.25-3.377 3.376-4.502 3.602-1.125 0.224-3.826 0.449-6.302 0.675-2.477 0.226-2.252-0.9-4.502 1.351-2.25 2.25-3.151 0.9-3.601 3.376-0.45 2.475-0.9 2.475 0 3.151 0.9 0.675-1.801 0.9 0.225 1.575 2.025 0.676 4.501 1.351 4.276 2.025-0.226 0.676 2.251-0.899 0.9 0.9-1.351 1.802-2.25 3.376 0.45 1.576 2.701-1.8 4.276-0.451 6.527-1.576s4.502-1.35 5.852-2.25c1.351-0.901 2.026-2.25 4.052-2.927 2.026-0.675 0.675 0.226 0.9 1.351s0 1.35 1.351 0.449c1.35-0.9 1.35-0.224 3.151-1.35 1.8-1.125 3.826-2.251 5.177-1.576 1.35 0.676 1.8 0.676 2.701 0.676 0.9 0-1.125-0.226 0.9 0 2.026 0.224-0.9-0.676 5.852 0.9 6.753 1.575 8.554 0.45 9.003 2.926 0.451 2.476 1.351-0.226 2.476 1.35 1.125 1.575 1.801 0.226 2.476 2.926 0.675 2.702 0 10.129-0.45 11.029s-0.675 0.9-0.45 2.926c0.225 2.025 2.926 11.705 2.026 12.379s-2.926 0.9-2.926 0.9l-1.125 4.051s-1.8 3.376-3.376 5.177c-1.576 1.8-3.601 4.052-4.727 5.626-1.125 1.576-2.026 4.278-3.601 5.853s-2.926 0.9-2.476 4.276 0 6.977 0.45 9.454c0.451 2.476 1.576 5.852 2.476 7.203 0.9 1.35 1.576 1.35 3.151 2.475 1.576 1.125 3.602 2.477 3.376 3.602-0.225 1.125 0.225 8.104 0.225 8.104s1.351 2.25 1.575 4.726c0.226 2.477 1.801 4.952 1.801 4.952l1.351 2.927s0.675 0.226 1.576 2.025c0.9 1.801 0.9 1.801 1.125 2.7 0.226 0.902 0.226 0.676 0.226 0.902 0 0.224-0.226-3.827-0.676-4.953-0.45-1.125-0.45-2.475-0.9-3.826s0.225-2.927-1.125-4.952-1.35-2.251-2.025-4.277c-0.675-2.025-1.576-4.276-0.675-4.727 0.9-0.449 1.575-3.375 3.376 0 1.8 3.376 2.026 2.477 2.251 5.178 0.224 2.701 0.9 3.602 1.575 4.726 0.675 1.125 2.25 4.953 3.151 6.527 0.9 1.576 0-0.9 2.025 2.251 2.026 3.151 2.252 3.826 3.377 6.302s1.801 2.476 2.476 4.276c0.675 1.802 0.225 1.351-0.226 3.376-0.45 2.025 0.45 4.277 0.45 4.277s3.151 1.125 3.376 1.8c0.225 0.675 3.376 1.8 3.827 2.701 0.45 0.901 3.376 2.025 3.376 2.025s2.476 1.576 3.375 2.026c0.901 0.45 3.603 0.901 3.603 0.901s1.8 0.451 2.475 0.675c0.675 0.225 2.701 0.225 2.701 0.225s1.801-1.35 2.476-0.9c0.675 0.449 2.251-0.901 2.926 0.224 0.675 1.126 2.026 1.126 2.701 2.027 0.675 0.9 1.35 0.9 2.476 2.025s2.251 0.9 3.376 1.8c1.125 0.9 2.025 0.9 3.376 0.9 1.35 0 1.35 0 2.251 0.226 0.899 0.225 1.35-0.676 2.25 0.451 0.9 1.125 1.575 1.8 2.25 2.925s2.251 1.125 2.476 2.926c0.225 1.8 0.675 1.577 0.675 3.151 0 1.576 0.9 2.251 1.576 2.702 0.674 0.449 3.15 2.699 3.15 2.699l3.376 0.676 2.701 2.026 2.85 0.9z"/>
					<path d="m205.94 8.03l13.505-1.688s4.726 0 7.09-1.688c2.363-1.688 0-1.688 7.09-3.038 7.09-1.351 5.065-2.026 8.779-1.351 3.713 0.676 4.726 0.676 8.778 1.351 4.051 0.675 5.064-0.337 8.777 0.675 3.714 1.013 5.402 0.675 8.441 0.675h9.453c4.727 0 9.116-2.701 10.804-1.013s2.701 1.013 8.441 1.688c5.739 0.675 8.103-0.676 10.466 0 2.364 0.675 6.416-0.337 11.142 0s7.09-0.337 8.778-0.337 3.375-2.364 5.739-0.338c2.364 2.026 5.402-0.675 5.739 2.026 0.338 2.701 4.727 0.675 0.338 2.701s-3.714 1.688-6.753 4.389c-3.038 2.701 2.026 5.064-4.389 7.428-6.415 2.363-7.428-1.351-9.116 2.701-1.688 4.051 0 5.064-0.337 7.09-0.338 2.025-1.013 2.363-3.039 4.051-2.025 1.688-2.025 0-6.752 1.013s-8.103 2.026-10.128 2.026c-2.026 0-6.415 1.688-7.766 2.363-1.351 0.676 2.364 1.688-4.727 3.714-7.09 2.026-8.779 1.689-10.804 4.389-2.026 2.702-1.351 3.04-2.701 4.728-1.351 1.688-5.402 8.441-7.428 6.753-2.025-1.688-3.039-1.351-4.727-3.039s-2.701-4.051-3.714-5.402-1.351-4.727-1.688-6.753-3.377-6.752 0-6.077c3.376 0.675 5.402 0.338 6.415-0.337 1.013-0.676 2.026-3.039 2.026-3.039s-0.337-3.714-1.351-5.74c-1.013-2.026-0.337-3.714-2.025-5.739-1.688-2.026-1.688-1.688-4.052-3.714-2.363-2.026-1.688-3.039-5.402-3.376s-4.727-1.351-6.078-1.013c-1.35 0.337-5.739-2.364-1.013-2.701 4.727-0.338 9.116 0.337 9.116 0.337s6.077-2.701 4.052-3.038c-2.025-0.338 3.038-3.377-1.688-2.364-4.727 1.013-10.128 2.364-11.479 2.701-1.35 0.337-4.727 0.675-8.103 1.688-3.375 1.013-7.428 0.676-9.453 3.039-2.026 2.363 0 2.701-2.026 2.363-2.025-0.337 1.688-2.363-3.038 0-4.727 2.364-3.376 2.026-5.739 1.351-2.364-0.675-4.052-1.688-4.052-1.688s3.04-0.338-1.35-0.675c-4.389-0.337-4.389-0.337-6.077-2.364-1.688-2.026-0.675-2.026 0.337-2.701 1-0.676 1.68-2.027 1.68-2.027z"/>
					<path d="m198.51 23.223c-1.688 1.688-5.74 4.052-4.726 5.065 1.013 1.013 1.688 4.727 3.714 3.714 2.025-1.013-0.338-4.389 7.427-1.688 7.766 2.701 7.09-0.337 8.778 3.714s3.038 7.089 2.025 8.103c-1.012 1.013-1.35 2.026-4.727 3.039-3.376 1.013-4.727 2.364-2.363 4.051 2.363 1.688 6.753 3.376 8.778 4.39 2.026 1.012 8.103 1.351 5.064-2.025s0.675-4.39 0.675-4.39 5.74 4.39 3.715-0.675c-2.026-5.064-3.377-5.064-2.026-5.064s4.051 1.688 4.051 1.688l1.351-2.701-1.351-4.389-4.389-4.389s3.713-3.376-1.351-4.051-4.727-0.675-7.428-2.364c-2.701-1.688-3.038-2.363-8.103-1.35-5.064 1.013-6.752 1.688-6.752 1.688l-2.35-2.366z"/>
					<path d="m123.22 27.275c2.026-2.026 7.428-6.752 9.454-5.402 2.025 1.35 5.063-1.013 7.427 1.013 2.363 2.026 5.064 4.389 7.09 5.064s4.389-1.688 6.415-0.675c2.025 1.013 9.115-5.064 7.765-0.337-1.351 4.727 1.688 6.077-2.026 6.415s-7.765 3.039-9.454 2.026c-1.688-1.013-3.039-2.026-5.74-0.675-2.701 1.35-1.35 1.35-4.389 0.675-3.038-0.675-3.713 0.675-4.388-0.338-0.676-1.012-2.701-0.675-2.364-3.714 0.337-3.038 1.35-2.701-0.676-2.701-2.025 0-3.713 0.338-5.064-0.675-1.34-1.013-4.04-0.676-4.04-0.676z"/>
				</g>
			</symbol>
END_OF_SYMBOL_WORLD;
		}
		if ($symbols) {
			$svg_defs = <<<END_OF_SVG_DEFS
			<svg id="ma-content-consent-symbols" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
			aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;">
				<defs>
					{$symbols}
				</defs>
			</svg>
END_OF_SVG_DEFS;
			
			if (self::$footercode_minimize) { $svg_defs = preg_replace('/\r?\n[\t ]*/','',$svg_defs); }
			echo $svg_defs;
		}

		DONE:
		$et = microtime(true);
		if (WP_DEBUG && self::$timing) {error_log(sprintf('%s%s::%s() Timing: %.5f sec.', '-> ', __CLASS__, __FUNCTION__, $et-$st));}
		self::$total_runtime += $et-$st;
	}

}

//===================================================================================================================
// Initialize

// Warn about incompatibilities (currently none)
add_action('wp_loaded',function(){
	if (is_admin()) {
		if (!isset($GLOBALS['MA_Content_Consent_Incompatibilities'])) {$GLOBALS['MA_Content_Consent_Incompatibilities'] = [];}
		if (count($GLOBALS['MA_Content_Consent_Incompatibilities'])) {
			if (WP_DEBUG && MA_Content_Consent::$timing) 
				{error_log('MA_Content_Consent / Incompatibilities: '.print_r($GLOBALS['MA_Content_Consent_Incompatibilities'],true));}
			add_action('admin_notices', function(){
				if (WP_DEBUG ) {error_log('MA_Content_Consent / Incompatibilities: '.print_r($GLOBALS['MA_Content_Consent_Incompatibilities'],true));}
				$implementation = basename(__FILE__) == 'ma-content-consent.php' ? 'Plugin' : 'Code Snippet';
				echo '<div class="notice notice-warning xis-dismissible">
						<p>The '.$implementation.'  "MA Content Consent" is skipped: '.implode(' or ',$GLOBALS['MA_Content_Consent_Incompatibilities']).'</p>
					</div>';
			});
		}
	}
}, 1000); 

add_action('wp_loaded',function(){
	if (count($GLOBALS['MA_Content_Consent_Incompatibilities']??[])) return;
	if (wp_doing_cron()) 		return; 	// don't run for CRON requests
	if (is_favicon()) 			return; 	// don't run for favicon request
	if (($_SERVER['QUERY_STRING']??'') == 'service-worker')			return;	// don't run for service-worker
	if (($_SERVER['REQUEST_URI']??'') == '/favicon.ico')			return;	// don't run for favicon
	if ((strpos($_SERVER['REQUEST_URI']??'','/wp-content/') === 0))	return;	// don't run for dynamic wp-content file
	
	$codebase = basename(__FILE__) == 'ma-content-consent.php' ? 'Plugin' : 'Code Snippet';
	if (WP_DEBUG && MA_Content_Consent::$timing) 
		{error_log(sprintf('MA_Content_Consent: Initializing %s for request URI="%s" action="%s"', $codebase, $_SERVER['REQUEST_URI']??'', $_REQUEST['action']??''));}

	MA_Content_Consent::init();

}, 1200); 
	
endif;
