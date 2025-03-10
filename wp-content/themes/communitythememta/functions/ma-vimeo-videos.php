<?php
/*
Plugin Name:	MA GDPR Vimeo
Description:	GDPR compliant Vimeo video embedding
Author:			<a href="https://www.altmann.de/">Matthias Altmann</a>
Project:		Code Snippet: GDPR Compliant Vimeo Embed
Version:		1.4.1
Plugin URI:		https://www.altmann.de/blog/code-snippet-gdpr-compliant-vimeo-videos/
Description:	en: https://www.altmann.de/blog/code-snippet-gdpr-compliant-vimeo-videos/
				de: https://www.altmann.de/blog/code-snippet-dsgvo-konforme-vimeo-videos/
Copyright:		© 2023-2024, Matthias Altmann


Notes:
Oxygen Builder
	Modals, on close, reset src attribute of iframes to stop running videos. 
	That is done by this.src = this.src, which also kills event handlers we assigned.
	If a modal is closed with a playing video, it's stopped, but we'll lose control 
	over it since we don't get any further events. 

TESTED WITH:
Product		Versions
--------------------------------------------------------------------------------------------------------------
PHP 		8.1, 8.2
WordPress	6.4.2 ... 6.6.2
Bricks		1.9.5 ... 1.10.3
Oxygen		4.8.1 ... 4.9
--------------------------------------------------------------------------------------------------------------

Version History:
Date		Version		Description
--------------------------------------------------------------------------------------------------------------
2024-10-11	1.7.2		Fixes:
						- Fixed missing styling of video preview (play button, GDPR text) in Bricks builder.
2024-07-04	1.4.0		New Features:
						- Support for unlisted Vimeo videos with hash parameter "h" in shortcode or URL
						- Loading more thumbnail sizes from Vimeo 
						Changes:
						- Replaced deprecated Vimeo SimpleAPI by oEmebd
						- Cache JSON format has changed due to the change from SimpleAPI to oEmbed
-			1.3.1		Changes
						- Added width and height attributes for thumbnail inages
2024-04-28	1.3.0		Reorganization of code base.
						New Features:
						- For better accessibility, the Enter key can now be used to start and stop videos.
						  (Thanks to Stephan Koenigk for his feature request and üre-release tests)
						Changes:
						- For invalid IDs, don't create directory, store json, attempt to retrieve thumbnails
						- In Builders Bricks and Oxygen, click handler is deactivated to allow selecting element
						- Preparation for Bricks Element. Coming soon.
2023-12-30	1.2.0		New Features:
						- Complete rebuild of JS
						- Now using Vimeo Player API to provide more control
						- Video player management via players registry and observer
							- Pause current video if another one is started
							- Pause video if modal/popup closed (evaluated by visibility of parent DOM element)
						- Support for dynamically embedded videos using AJAX calls:
							- Removed init prevention for AJAX calls
							- Added PHP method MA_GDPR_Vimeo::enable_footercode() to trigger output of footer
							  code (styles, scripts, svg) for video embeds dynamically loaded by AJAX calls
 							- Play click handler is now assigned as onclick event instead of collecting all 
							  videos after page load. This eliminates the need for an extra click handler 
							  initialization for players dynamically loaded after page load.  
						- Optimizations for accessibility 
						Fixes:
						- Prefixed wrapper/player IDs with snippet slug to prevent IDs starting with number
						- Fallback for aspect ratio via padding-top (CSS variable, @supports rule) 
						  for older browsers not supporting aspect-ratio like Safari < V15
2023-11-09	1.1.1		Fix: "Attempt to assign property 'notes' on null" error in get_video_details()
2023-04-04	1.1.0		New Feature: Pause handler for closed Oxygen Modals
2023-04-03	1.0.0		Initial Release
2023-04-02	0.0.0		Development start

Samples
https://vimeo.com/347119375	> Sample Video
https://vimeo.com/814361316 > Orbiting Earth

Documentation:
https://developer.vimeo.com/api/oembed/videos


*/


if (!class_exists('MA_GDPR_Vimeo')) :

class MA_GDPR_Vimeo {

	public const TITLE		= 'MA GDPR Vimeo';
	public const SLUG		= 'ma-gdpr-vimeo';
	public const VERSION	= '1.4.1';

	// ===== CONFIGURATION ==============================================================================================
	/** Default width of the video block. Can be specified in %, px */
	public $default_width			= '100%';
	/** Default aspect ratio of the video block. Syntax X:X or X/X */
	public $default_aspect_ratio	= '16/9';
	/** GDPR notice text in different languages. */
	private $default_gdpr_text 		= [  
		'da' => ['Når du har trykket, vil videoen blive indlæst fra Vimeo\'s servere. Se vores %s for flere informationer.','privatlivspolitik'],
		'de' => ['Bei Klick wird dieses Video von den Vimeo Servern geladen. Details siehe %s.', 'Datenschutzerklärung'],
		'en' => ['When clicked, this video is loaded from Vimeo servers. See our %s for details.', 'privacy policy'],
		'es' => ['Al hacer clic, este vídeo se carga desde los servidores de Vimeo. Consulte la %s para más detalles.', 'política de privacidad'],
		'fi' => ['Klikattuasi, tämä video ladataan Vimeon palvelimilta. Katso lisätietoja meidän %s.', 'tietosuojaselosteesta'],
		'fr' => ['En cliquant, cette vidéo est chargée depuis les serveurs de Vimeo. Voir la %s.', 'politique de confidentialité'], 
		'hu' => ['Kattintás után ez a videó a Vimeo szervereiről kerül lejátszásra. A részletekért olvassa el az %s oldalt.', 'Adatkezelési Tájékoztatót'],
		'it' => ['Quando si clicca, questo video viene caricato dai server di Vimeo. Vedere %s per i dettagli.', 'l\'informativa sulla privacy'],
		'ja' => ['クリックすると、この動画が Vimeo サーバーから読み込まれます。詳細については、%s をご覧ください。', 'プライバシー ポリシー'],
	]; 
	/** Default font size for GDPR text */
	public $default_gdpr_text_size	= '.7em';
	/** Default open video in new window */
	private $default_new_window		= false;
	/** thumbnail image sizes */
	private $thumbnail_image_sizes	= [1280,1024,960,768,640,480]; 
	/** Enable timing info to WordPress debug.log if WP_DEBUG also enabled. 
	 * - false/0:	Disabled 
	 * - true/1: 	Enabled 
	 * - 2: 		Extended 
	 */
	public $timing					= false;

	// ===== INTERNAL. DO NOT EDIT. =====================================================================================
	private $incompatibilities 		= [];		// incompatibilities detected before initialization
	private $content_base			= null;		// will be set to the content base folder dir and url
	private $footercode_needed		= false;	// will be set to true if shortcode used on current page
	private $footercode_minimize 	= true;		// should we minimize all footer code (style, script, svg)?
	private $timing_total_runtime	= 0;
	private $urlformat_oembed		= 'https://vimeo.com/api/oembed.json?url=https%%3A//vimeo.com/%s';
	private $urlformat_simpleapi	= 'http://vimeo.com/api/v2/video/%s.json';

	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Initialize Snippet: 
	 * - Add shortcode "ma-gdpr-youtube"
	 * - Register hook action for wp_footer to emit footer code (style, script)
	 * - Set flag to emit footer code (style, script) when in Oxygen Builder
	 */
	function __construct() {
		$st = microtime(true);
		$GLOBALS[__CLASS__] = $this;

		if (wp_doing_cron()) 		goto DONE;	// don't run for CRON requests

		if (WP_DEBUG && $this->timing>1) {error_log(sprintf('%s Initializing...',__CLASS__));}

		if (!defined('MA_GDPR_Vimeo_Version')) define('MA_GDPR_Vimeo_Version',self::VERSION);

		if ($this->is_incompatible()) return;

		// check content directory
		$this->content_base = $this->get_content_base();
		if (!$this->content_base) {return;}

		add_shortcode('ma-gdpr-vimeo', [$this, 'shortcode']);
		add_action('wp_footer',[$this,'footercode']);

		add_action('init', [$this, 'init_builder'], 50);

		DONE:
		// add a handler for logging total runtime
		add_action('shutdown', [$this, 'total_runtime']);
		
		$et = microtime(true);
		if (WP_DEBUG && $this->timing>1) {error_log(sprintf('  %s Initialization Timing %.5f s.',__CLASS__, $et-$st));}
		$this->timing_total_runtime += $et-$st;

		$this->timing_total_runtime += $et-$st;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Logs total timing for shortcodes on a page 
	 */
	public function total_runtime(){
		if (WP_DEBUG && $this->timing) {error_log(sprintf('%s Runtime: %.5f sec.', __CLASS__, $this->timing_total_runtime));}
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Checks for incompatibilities. Registers admin notice.
	 * @return bool 				`true` if any incompatibilities found
	 */
	private function is_incompatible(): bool{
		$incomp = [];
		if (!ini_get('allow_url_fopen')) {
			$incomp[] = 'PHP setting <code>allow_url_fopen</code> needs to be <b><On</b> for the '.$this->get_script_details()->type.' to work correctly.';
		}
		$this->incompatibilities = $incomp;
		if (count($incomp) && is_admin()) {
			add_action('admin_notices', function(){
				if (WP_DEBUG ) {error_log(self::TITLE.' Incompatibilities: '.implode(', ',$this->incompatibilities));}
				echo '<div class="notice notice-error is-dismissible">
						<p>The '.$this->get_script_details()->combined.' is skipped: '.implode(', ',$this->incompatibilities).'</p>
					</div>';
			});
		}	
		return count($incomp) ? true : false;	
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Init actions for Builder support.
	 */
	public function init_builder() {
		
		// OXYGEN
		if ( ($_GET['ct_builder']??null) == true) {
			// emit styles, script, svg when Oxygen Builder is active to support video preview
			$this->footercode_needed = true;
		}
		// BRICKS
		if (defined('BRICKS_VERSION')) {
			if ( ($_GET['bricks']??null) == 'run') {
				// emit styles, script, svg when Bricks Builder is active to support video preview
				$this->footercode_needed = true;
			}
			// load Bricks element add-on (not yet available)
			foreach([__DIR__,$this->content_base->dir] as $module_dir) {
				$module_filepath = $module_dir.'/'.self::SLUG.'-bricks-element.php';
				if (file_exists($module_filepath)) {
					call_user_func('\Bricks\Elements::register_element', $module_filepath);
					break;
				}
			}
		}
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Return base dir/url for video content. 
	 * Create directory /wp-content/uploads/ma-gdpr-vimeo/ if necessary
	 * Rename from old scheme ma-gdpr-vimeo-thumbnails if exists
	 * @return object|null	A dirinfo object (->dir, ->url)
	 */
	public function get_content_base(): ?object {
		$retval = (object)['dir'=>null,'url'=>''];
		$content_dir_info = wp_get_upload_dir();
		$retval->dir = $content_dir_info['basedir'].'/'.self::SLUG;
		$retval->url = $content_dir_info['baseurl'].'/'.self::SLUG;
		// rename folder from old to new scheme 
		if (file_exists($retval->dir.'-thumbnails')) {
			@rename($retval->dir.'-thumbnails',$retval->dir);
		}
		// create content folder if not exists
		if (!file_exists($retval->dir)) {
			if (!@mkdir($retval->dir)) {
				add_action('admin_notices', function(){
					echo '<div class="notice notice-error"><p>['.self::TITLE.'] Error creating content base folder <code>wp-content/uploads/'.self::SLUG.'</code>.</p></div>';
				});
				error_log(sprintf('%s Error creating content base folder.', __CLASS__)); 
				return null;
			}
		}
		if (!is_writable($retval->dir)) {
			add_action('admin_notices', function(){
				echo '<div class="notice notice-error"><p>['.self::TITLE.'] Folder <code>wp-content/uploads/'.self::SLUG.'</code> is not writable. Please correct folder permissions.</p></div>';
			});
		}
		// create scheme-less URL
		$retval->url = preg_replace('/^https?\:/','',$retval->url);
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Return a link to the privacy policy page (if configured in WordPress) or just the passed text
	 * @param string $text	The text to return if privacy policy page is not defined in WordPress
	 * @return string		The HTML link element for the privacy policy page or the initial text
	 */
	private function get_privacy_policy_link(string $text='privacy policy'): string {
		$pplink = get_the_privacy_policy_link();
		return  $pplink ? $pplink : $text; 
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Parses a Vimeo URL for a video ID.
	 * Handles numerous URL formats, or plain video ID.
	 * @param string $s		The URL/string to parse
	 * @param string $h 	The optional hash key for hidden videos
	 * @return array		Array containing key v (video ID), or empty
	 */
	private function parse_video_url(string $s='', $h=''): array {
		$st = microtime(true);
		$retval = ['v'=>null, 'h'=>$h];
		// regex for parsing vimeo url variants
		$re = '/^
			(?:https?\:)?\/{2}					# protocol http, https, or schemeless
			(?:www\.)?							# optional www
			(?:(?:player\.)?vimeo\.com)\/		# domain variants
			(?:(?:video)[\/]?) ?				# video term
			([A-Za-z0-9\-\_]+)					# THE ID
			(?:\/([A-Za-z0-9]+)) ?				# (optional) HASH key as path appending
			(?:[.+\?\&]h=([A-Za-z0-9]+)) ?		# (optional) HASH key as URL parameter
		/x';
		// parse url
		if (preg_match($re,$s,$matches)) {
			$retval['v'] = $matches[1]; 
			$retval['h'] = $matches[2] ? $matches[2] : $matches[3];
		}
		// id only?
		else if (preg_match('/^([A-Za-z0-9\-\_]+)$/',$s,$matches))	{$retval['v'] = $matches[1];}
	
		$et = microtime(true);
		if (WP_DEBUG && $this->timing>1) {error_log(sprintf('    %s("%s") => %s Timing: %.5f sec.', __METHOD__, $s, json_encode($retval), $et-$st));}
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/** 
	 * Get the image type as extension string from an integer value. Handles gif, jpg, pmg, webp.
	 * @param int $image_type 	The image type as int 
	 * @return string 			The image extension
	 */
	private function get_image_type_as_string(int $image_type): string {
		switch ($image_type) {
			case IMAGETYPE_GIF: 	return 'gif';
			case IMAGETYPE_JPEG:	return 'jpg';
			case IMAGETYPE_PNG:		return 'png';
			case IMAGETYPE_WEBP:	return 'webp';
			default:				return '';
		}
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Get type, width, height from an image file.
	 * @param string $filepath 	The image file path
	 * @return object 			The image info object: (bool) status, (string) type, (int) width, (int) height
	 */
	private function get_image_info(string $filepath): object {
		$retval = (object)[
			'status'	=> false,
			'type'		=> null,
			'width'		=> null,
			'height'	=> null,
		];
		if ($result = getimagesize($filepath)) {
			$retval->status = true;
			$retval->type 	= $this->get_image_type_as_string($result[2]);
			$retval->width	= $result[0];
			$retval->height	= $result[1];
		}
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Parses some info from an vimeo embed request result
	 * @param object $data	The embed request result
	 * @return object 		The info parsed 
	 */
	private function parse_embed_info(object $data): object {
		$retval = (object)[
			'hash' 			=> null,
			'app_id'		=> null,
			'title'			=> null,
		];
		$html = $data->html??'';
		/* Sample: 
		<iframe src="https://player.vimeo.com/video/347119375?h=1699409fe2&app_id=122963&autoplay=1&color=ef2200&byline=0&portrait=0" 
			width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="Sample Video"></iframe>
		*/
		if (preg_match('/[\?&;]h=([\da-f]+)/',$html,$matches)) 		{$retval->hash = $matches[1];}
		if (preg_match('/[\?&;]app_id=([\da-f]+)/',$html,$matches))	{$retval->app_id = $matches[1];}
		if (preg_match('/title="([^"]+)"/',$html,$matches)) 		{$retval->title = html_entity_decode($matches[1]);}

		if (!$retval->hash) {
			// if URL doesn't conatain the hash key, try to parse from uri (/videos/12345 or /videos/12345:67890)
			if (preg_match('/^\/videos\/[A-Za-z0-9]+\:([A-Za-z0-9]+)$/',$data->uri??'',$matches)) {
				$retval->hash = $matches[1];
			}
		}

		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Get details for video $id, containing hash, app_id, title, thumbnails, if already downloaded and cached. 
	 * Load from Vimeo if not yet cached or old version (pre 1.4.0).
	 * @param string $id	The video ID
	 * @param mixed  $hash 	The optional hash key for hidden videos
	 * @return object		Object with status, title, hash, app_id and thumbnails
	 */
	private function get_video_details(string $id, $hash=null): object {
		$st = microtime(true);
		$retval = (object)[
			'status' 		=> false,
			'status_code'	=> null,
			'status_text'	=> null,
			'status_errors'	=> [],
			'json_version'	=> 2, 
			'video_id'		=> $id,
			'hash'			=> null,
			'app_id'		=> null,
			'embed'			=> null,
			'thumbnails'	=> (object)[],
		];
		if (!$this->content_base) 	{goto DONE;}

		$vid_dir = $this->content_base->dir.'/'.$id;
		$info_filepath = $vid_dir.'/'.$id.'.json';

		// read from cache if available
		if (file_exists($info_filepath)) {
			// read from cache
			$result = json_decode(@file_get_contents($info_filepath));
			// json_version 2, status OK, and thumbnails available?
			if ($result && ($result->json_version??1>=2) && ($result->status??null) && ($result->thumbnails??[])) {
				$retval = $result;
				goto DONE;
			}
		}
		
		// not cached yet, or old cache format; load from vimeo
		LOAD_FROM_VIMEO:

		// prepare vimeo calls
		$referer = ($_SERVER['HTTPS']??'' == 'on' ? 'https:' : 'http').'//'.($_SERVER['HTTP_HOST']??'');
		$http_request_headers = [
			'Accept-language: '.$this->get_current_language(),
			'Referer: '.$referer,
		];
		$options = ['http'=>['method'=>'GET','header'=>implode("\r\n",$http_request_headers)]];
		$context = stream_context_create($options);

		$base_url = sprintf($this->urlformat_oembed, $id);
		if ($hash) $base_url .= '/'.$hash; // optional hash key

		// retrieve from vimeo in a loop: embed data, thumbnail urls
		foreach ($this->thumbnail_image_sizes as $width) {
			$st_thumbnail = microtime(true);
			$url = $base_url . '&width='.$width;
			$result = @file_get_contents($url, false, $context);
			
			// check status
			$http_status = $http_response_header[0];
			preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)\s(.*)#",$http_status, $matches);
			$retval->status_code = $matches[1];
			$retval->status_text = trim($matches[2]);
			if ($retval->status_code == 200) {
				$data = json_decode($result); 
				if (!$retval->embed) $retval->embed = $data;
				// parse embed info
				$parsed = $this->parse_embed_info($data);
				$retval->hash = $parsed->hash;
				$retval->app_id = $parsed->app_id;

				if (($data->domain_status_code??null) == 403) {
					$retval->status_code = 403;
					$retval->status_text = 'Forbidden';
				}
				if (($data->domain_status_code??null) == 404) {
					$retval->status_code = 404;
					$retval->status_text = 'Not Found';
				}
			}
			if ($retval->status_code != 200) {
				// cancel retrieval if video not found or not accessible
				$note = sprintf('Video %s: %s', $id, $retval->status_text);
				$retval->status_errors[] = $note;
				error_log('['.self::TITLE.'] '.$note); 
				goto DONE;
			}

			// video is accessible; check/create cache directory for this video
			if (!file_exists($vid_dir)) {
				if (!@mkdir($vid_dir)) {
					// cancel retrieval if cache directory can't be created
					$note = sprintf('Error creating cache folder for video %s.', $id);
					$retval->status_errors[] = $note;
					error_log('['.self::TITLE.'] '.$note); 
					goto DONE;
				}
			}

			// get thumbnail info
			$thumbnail = (object)[
				'url' => $data->thumbnail_url,
				'width' => $data->thumbnail_width,
				'height' => $data->thumbnail_height,
			];
			// load and store thumbnail
			if ($img_data = @file_get_contents($thumbnail->url)) {
				$status = $http_response_header[0];
				if (!preg_match('/200\sOK/',$status)) continue; // continue with next image size
				// store as tmp file to get image type from exif
				$img_tmp_path = $vid_dir.'/'.$id.'.tmp';
				file_put_contents($img_tmp_path, $img_data);
				$img_info = $this->get_image_info($img_tmp_path);
				if ($img_info->status) {
					$img_path = $vid_dir.'/'.$id.'_'.$img_info->width.'.'.$img_info->type;
					// store with correct filename
					if (@rename($img_tmp_path, $img_path)) {
						$retval->thumbnails->{$img_info->width} = (object)['name'=>basename($img_path), 'width'=>$img_info->width, 'height'=>$img_info->height];
					}
					if (WP_DEBUG && $this->timing>1) {error_log(sprintf('      Thumbnail %d px loaded, %d px received. Timing: %.5f sec.', $width, $img_info->width, microtime(true)-$st_thumbnail));}
				}
			}
		}
		$retval->status = true;

		DONE:
		// clean up embed info
		unset($retval->embed->thumbnail_url);
		unset($retval->embed->thumbnail_width);
		unset($retval->embed->thumbnail_height);
		unset($retval->embed->thumbnail_url_with_play_button);
		// store cache file
		@file_put_contents($info_filepath, json_encode($retval,JSON_PRETTY_PRINT));

		$et = microtime(true);
		if (WP_DEBUG && $this->timing>1) {error_log(sprintf('    %s("%s") Timing: %.5f sec.', __METHOD__, $id, $et-$st));}
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Get the current page/post get_current_language
	 * @return string		The language code as e.g. "de", "en"
	 */
	private function get_current_language(): string {
		$retval = get_locale();
		// Is a translation plugin active? Supporting Polylang, WPML 
		foreach (['pll_current_language','wpml_current_language'] as $func) {
			if (function_exists($func)) {$retval = $func(); break;}
		}
		$retval = str_replace('_','-',$retval);
		$retval = explode('-',$retval??'')[0];
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Handle the shortcode "ma-gdpr-vimeo". 
	 * @param array $atts		The shortcode attributes
	 * @param string $content	The content of the shortcode
	 * @return string			The output
	 */
	public function shortcode(array $shortcode_atts = [], string $content = ''): string {
		$st = microtime(true);
		$lang = $this->get_current_language();
		$retval = '';

		// get defaults for unspecified attributes
		$atts_default = [
			'slug'				=> self::SLUG,
			'video'				=> null,
			'h' 				=> null, // the hidden key
			'uniqid'			=> null,
			'width'				=> $this->default_width,
			'aspect-ratio'		=> $this->default_aspect_ratio,
			'notice-class'		=> null,
			'notice-style'		=> null,
			'gdpr-text'			=> isset($this->default_gdpr_text[$lang]) 
									? sprintf($this->default_gdpr_text[$lang][0],$this->get_privacy_policy_link($this->default_gdpr_text[$lang][1])) 
									: sprintf($this->default_gdpr_text['en'][0],$this->get_privacy_policy_link($this->default_gdpr_text['en'][1])),
			'gdpr-text-size'	=> null,
			'alt'				=> null,
			'title'				=> null,
			'thumbnail'			=> null,
			'title-text'		=> null,
			'title-class'		=> null,
			'title-style'		=> null,
			'play-button'		=> 'vimeo', // currently included: vimeo, circle, circle-o, play
			'play-button-style'	=> null,
			'play-button-color'	=> null,
			'new-window'		=> $this->default_new_window,
		];

		// merge global settings
		$atts = array_merge($atts_default, $GLOBALS['ma_gdpr_vimeo']??[]);
		// choose correct language for gdpr-text
		if ($GLOBALS['ma_gdpr_vimeo']['gdpr-text-'.strtolower($lang)]??'') {
			$atts['gdpr-text'] = $GLOBALS['ma_gdpr_vimeo']['gdpr-text-'.strtolower($lang)];
		}
		// allow html in shortcode attributes title-text, gdpr-text
		foreach (['title-text','gdpr-text'] as $att) {
			if ($shortcode_atts[$att]??'') {$shortcode_atts[$att] = html_entity_decode($shortcode_atts[$att]);}
		}
		// merge shortcode attributes
		$atts = (object)array_merge($atts, $shortcode_atts);
		if ($atts->video) {
			$video = $this->parse_video_url($atts->video, $atts->h);
			if (isset($video['v'])) {
				$atts->video = $video['v'];
				// use h key found in URL only if no manual h parameter specified
				if (!$atts->h) {$atts->h = $video['h']??null;}
			}
		}

		// any other parameter will be passed to vimeo directly
		$vimeo_parameters = [];
		foreach ($atts as $att_key => $att_val) {
			if (!in_array($att_key,array_keys($atts_default))) {
				$vimeo_parameters[$att_key] = $att_val;
			} 
		}

		if (!isset($atts->video) || ($atts->video == '' )) 	{$retval = sprintf('[%s] Missing video id.',self::TITLE); goto DONE;}
		if (preg_match('/[^A-Za-z0-9\-\_]/',$atts->video))	{$retval = sprintf('[%s] Invalid video id.',self::TITLE); goto DONE;}
		// generate an unique id (for the case a video is embedded multiple times)
		$atts->uniqid = $atts->slug.'-'.uniqid();

		if (!$this->content_base) {$retval = sprintf('[%s] Content directory is not available.',self::TITLE); goto DONE;}
		// check if we already have a thumbnail
		$video_details = $this->get_video_details($atts->video, $atts->h);
		if (!$video_details->status) {$retval = sprintf('[%s] %s',self::TITLE, implode(', ',$video_details->status_errors)); goto DONE;}

		// insert vimeo attributes
		if ($video_details->status) {
			foreach (['alt','title','title-text'] as $attr) {
				foreach (['title','author_name','description'] as $info) {
					$atts->{$attr} = str_replace('@'.$info.'@', $video_details->embed->{$info}??'', $atts->{$attr}??'');
				}
			}
		}

		$thumbnail = '';
		$sources = [];
		$click_handler = 'onclick="ma_gdpr_vimeo.click(this)"';

		if ($atts->thumbnail) {
			if (is_numeric($atts->thumbnail)) {
				// numeric value => media id
				// get available image sizes
				$metadata = wp_get_attachment_metadata($atts->thumbnail);
				$image_sizes = [];
				// add original size
				$img_src = wp_get_attachment_image_src($atts->thumbnail,'full');	// '0': url, '1': width, '2': height, '3': resized
				$mime_type = get_post_mime_type($atts->thumbnail);
				$image_sizes['original'] = [
					'file'		=> $metadata['file'],
					'width'		=> $metadata['width'],
					'height'	=> $metadata['height'],
					'mime-type'	=> $mime_type,
					'filesize'	=> $metadata['filesize']??null,
					'url'		=> $img_src[0],
					'key'		=> 'full',
				];
				// add resized sizes
				foreach ($metadata['sizes'] as $key => $data) {
					// retrieve url for specific size
					$img_src = wp_get_attachment_image_src($atts->thumbnail,$key);	// '0': url, '1': width, '2': height, '3': resized
					$data['url'] = $img_src['0']; 
					$data['key'] = $key;
					$image_sizes[$key] = $data;
				}
				// sort image sizes by width ascending
				uasort($image_sizes, function($a,$b){
					if ($a['width'] == $b['width']) {return 0;}
					return ($a['width'] < $b['width']) ? -1 : 1;
				});

				// get largest image 
				$largest = (object)end($image_sizes);

				foreach ($image_sizes as $key => $data) {
					// to improve thumbnail quality, use higher res image if we reach half its size 
					$sources[] = sprintf('<source media="(min-width:%dpx)" type="%s" srcset="%s" width="%d" height="%d">',
										$data['width']/2, $data['mime-type'], $data['url'], $data['width'], $data['height']);
				}
				// create thumbnail
				$thumbnail .= sprintf('<picture class="ma-gdpr-vimeo-thumbnail" '.$click_handler.'>%s <img loading="lazy" src="%s" width="%d" height="%d" alt="%s" title="%s"></picture>',
									implode('',array_reverse($sources)), $largest->url, $largest->width, $largest->height, $atts->alt??'', $atts->title??'');
			} else {
				// thumbnail URL
				// no width and height attributes because URL could be external, and retrieving img sizes would require extra request
				$sources = [];
				$thumbnail .= '<picture class="ma-gdpr-vimeo-thumbnail" '.$click_handler.'>';
				$thumbnail .= sprintf('<img loading="lazy" src="%s" alt="%s" title="%s">',$atts->thumbnail, $atts->alt, $atts->title);
				$thumbnail .= '</picture>';
			}
		} else {
			$thumbs = $video_details->thumbnails;
			foreach(get_object_vars($thumbs) as $size => $thumb) {
				$type = substr(strrchr($thumb->name,'.'),1);
				// to improve thumbnail quality, use higher res image if we reach half its size 
				$sources[] = sprintf('<source media="(min-width:%dpx)" type="image/%s" srcset="%s" width="%d" height="%d">',
			($size/2), $type, $this->content_base->url.'/'.$atts->video.'/'.$thumb->name, $thumb->width, $thumb->height);
			}
			// get smallest (last) thumbnail as img src
			$thumbs = (array)$thumbs;
			$smallest = array_pop($thumbs);
			$img_src = $this->content_base->url.'/'.$atts->video.'/'.$smallest->name;
			$thumbnail .= sprintf('<picture id="%s-thumbnail'.'" class="ma-gdpr-vimeo-thumbnail" %s> %s <img src="%s" alt="%s" title="%s" width="%d" height="%d"></picture>',
								$atts->uniqid, $click_handler, implode('',$sources), $img_src, $atts->alt, $atts->title, $smallest->width, $smallest->height);
		}

		// calculate dimensions of video block depending on width and aspect ratio
		list ($arw,$arh) = explode('/',str_replace(':','/',$atts->{'aspect-ratio'}),2); // aspect ratio elements
		list ($width_value, $width_unit) = ['100','%']; // default width value and unit
		// split width value and unit
		preg_match('/^(\d+)(.+)$/',$atts->width,$matches);
		if (count($matches) == 3) {array_shift($matches); list ($width_value, $width_unit) =  $matches;}
		// calculate block dimensions
		$block_width = $width_value.$width_unit;
		$block_height = ($width_value * (floatval($arh)/floatval($arw))) . $width_unit;

		// privacy policy url and link
		$atts->{'gdpr-text'} = str_replace('{privacy-policy-url}', get_privacy_policy_url(), $atts->{'gdpr-text'});
		$atts->{'gdpr-text'} = str_replace('{privacy-policy-link}', get_the_privacy_policy_link(), $atts->{'gdpr-text'});

		// title overlay
		$title_overlay = !empty($atts->{'title-text'})
			? sprintf('<div class="ma-gdpr-vimeo-title %1$s" %2$s>%3$s</div>',
				$atts->{'title-class'} ?? '',
				$atts->{'title-style'} ? 'style="'.$atts->{'title-style'}.'"' : '',
				$atts->{'title-text'}
				)
			: '';

		// play button style, color
		$play_button_style = '';
		if ($atts->{'play-button-style'}) {$play_button_style .= $atts->{'play-button-style'}.';';}
		if ($atts->{'play-button-color'}) {$play_button_style .= 'color:'.$atts->{'play-button-color'}.';';}
		if ($play_button_style) {$play_button_style = 'style="'.$play_button_style.'"';}

		// gdpr text size
		$gdpr_text_size = '';
		if ($atts->{'gdpr-text-size'}) {$gdpr_text_size = 'font-size:'.$atts->{'gdpr-text-size'}.';';}

		// vimeo iframe
		$content = <<<END_OF_CONTENT
		<iframe src="https://player.vimeo.com/video/{$video_details->video_id}?h={$video_details->hash}&app_id={$video_details->app_id}&autoplay=0&dnt=1" 
			style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
		<script src="https://player.vimeo.com/api/player.js"></script>
END_OF_CONTENT;


		$aspect_ratio		= str_replace(':','/',$atts->{'aspect-ratio'});
		$new_window			= $atts->{'new-window'};
		$vimeo_params		= count($vimeo_parameters) ? base64_encode(json_encode($vimeo_parameters)) : '';
		$play_button 		= $atts->{'play-button'} ?? '';
		$play_button_class	= $atts->{'play-button-class'} ?? '';
		$notice_class		= $atts->{'notice-class'} ?? '';
		$notice_style		= $atts->{'notice-style'} ?? '';
		$gdpr_text			= $atts->{'gdpr-text'};
		$content_b64		= base64_encode($content);

		// @since 1.2.0 width/height/aspect-ratio now set via CSS var for older browsers not supporting aspect-ratio, like e.g. Safari <V15
		$retval = <<<END_OF_HTML
		<div id="{$atts->uniqid}" data-video-id="{$atts->video}" class="ma-gdpr-vimeo-wrapper" style="--_width:{$block_width};--_height:{$block_height};--_aspect-ratio:{$aspect_ratio};" data-new-window="{$new_window}" data-parameters="{$vimeo_params}">
			{$thumbnail}
			<svg id="{$atts->uniqid}-button" class="ma-gdpr-vimeo-button button-{$play_button} {$play_button_class}" tabindex="0" role="button" aria-label="play video" {$play_button_style} {$click_handler}><use xlink:href="#ma-gdpr-vimeo-play-button-{$play_button}"></use></svg>
			{$title_overlay}
			<div class="ma-gdpr-vimeo-notice {$notice_class}" style="font-size:{$gdpr_text_size}; {$notice_style}">{$gdpr_text}</div>
			<div id="{$atts->uniqid}-content" class="ma-gdpr-vimeo-content" style="display:none">{$content_b64}</div>
		</div>
END_OF_HTML;
		$this->footercode_needed = true;

		DONE:
		$et = microtime(true);
		if (WP_DEBUG && $this->timing>1) {error_log(sprintf('  %s(%s) Timing: %.5f sec.', __METHOD__, json_encode($shortcode_atts), $et-$st));}
		$this->timing_total_runtime += $et-$st;
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Enables emission of footercode (styles, scripts, svg) for the video embed. 
	 * Can be used for pages where video is embedded dynamically by an AJAX call.
	 * On the parent page, call MA_GDPR_Vimeo::enable_footercode()
	 * @since 1.2.0
	 */
	public static function enable_footercode() {
		$GLOBALS[__CLASS__]->footercode_needed = true;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Emits the footer code (styles, script, svg) to handle the Vimeo embedding
	 */
	public function footercode() {
		$st = microtime(true);

		if (!$this->footercode_needed) {goto DONE;}

		// debugging info
		echo sprintf('<span id="%2$s-info" data-nosnippet style="display:none">%1$s %2$s %3$s</span>', $this->get_script_details()->type, self::SLUG, self::VERSION); 
		
		// emit style
		// @since 1.2.0: @supports rule provides wrapper height for older browsers not supporting aspect-ratio, like e.g. Safari <V15
		$style = <<<'END_OF_STYLE'
		<style id="ma-gdpr-vimeo-style">
			.ma-gdpr-vimeo-wrapper {position:relative; display:flex; isolation:isolate; width:var(--_width);aspect-ratio:var(--_aspect-ratio);}
			@supports not (aspect-ratio:1/1) {.ma-gdpr-vimeo-wrapper{height:var(--_height);padding-top:var(--_height);}}
			.ma-gdpr-vimeo-thumbnail {position:absolute; z-index:1; top:0; left:0; width:100%; height:100%; display:flex; cursor:pointer; }
			.ma-gdpr-vimeo-thumbnail img {width:100%; height:100%; object-fit:cover; object-position:50% 50%;}
			.ma-gdpr-vimeo-button {position:absolute; z-index:4; top:50%; left:50%; transform:translate(-50%,-50%); width:70px; height:70px; cursor:pointer; color:white;}
			.ma-gdpr-vimeo-button.button-vimeo {color:black; filter:drop-shadow(0px 0px 4px darkgray);}
			.ma-gdpr-vimeo-button.button-circle {filter:drop-shadow(0px 0px 4px darkgray);}
			.ma-gdpr-vimeo-button.button-circle-o {filter:drop-shadow(0px 0px 4px darkgray);}
			.ma-gdpr-vimeo-notice {position:absolute; z-index:2; width:100%; left:0; right:0; bottom:0; max-width:100%; text-align:center; font-size:.7em; background-color:rgba(255,255,255,.8); padding:.2em .5em;}
			.ma-gdpr-vimeo-notice:empty {display:none;}
			.ma-gdpr-vimeo-title {position:absolute; z-index:3; width:100%; top:1em; padding:0 1em; color:white; text-shadow: black 1px 1px 2px;}
		</style>
END_OF_STYLE;
		if ($this->footercode_minimize) { 
			$style = preg_replace('/\/\*.*?\*\//','',$style); 
			$style = preg_replace('/\r?\n */','',$style); 
			$style = preg_replace('/\t/','',$style); 
		}
		echo $style;

		// emit code
		// Vimeo Player API: https://developer.vimeo.com/player/sdk/embed
		$script = <<<'END_OF_SCRIPT'
		<script id="ma-gdpr-vimeo-script" type="text/javascript">
		"use strict";

		/* Check for Builder preview panes */
		if (
			(typeof window.parent?.bricksData?.wpEditor != 'undefined') /* Bricks*/
		|| 	(window.parent?.angular) /* Oxygen */
		) { 
			/* Dummy Object w/o functionality for Bricks Builder */ 
			window.ma_gdpr_vimeo = {
				init: function(){},
				click: function($target){},
			}
		}
		
		if ((typeof window.ma_gdpr_vimeo == 'undefined')){
			window.ma_gdpr_vimeo = {
				debug: (new URLSearchParams(window.location.search)).get('ma-gdpr-vimeo-debug')!=null,
				player_observer_timer: 1000,
				player_observer_interval: null,
				players: {}, /* list of active players */
				last_played: null,

				init: function(){
					this.debug && console.log('MA GDPR Vimeo initialized.');
				},

				player_observer_init: function(){
					if (this.player_observer_interval == null) {
						this.player_observer_interval = setInterval(ma_gdpr_vimeo.player_observer, this.player_observer_timer);
						this.debug && console.log('MA GDPR Vimeo Player Observer initialized.');
					}
				},

				player_observer: function() {
					/* pauses video if iframe is not visible anymore - e.g. closed modal */
					ma_gdpr_vimeo.debug && console.log('MA GDPR Vimeo Player Observer (entries: '+Object.values(ma_gdpr_vimeo.players).length+')');
					for (let $playerID in ma_gdpr_vimeo.players) {
						let $player = ma_gdpr_vimeo.players[$playerID];
						ma_gdpr_vimeo.debug && console.log('MA GDPR Vimeo Player Observer checking player '+$playerID+'...');
						
						$player.player.getPaused().then(function ($paused) {
							let $state = $paused ? 'STOP' : 'PLAY';
							ma_gdpr_vimeo.debug && console.log('MA GDPR Vimeo Player Observer got status of video '+$playerID+' as '+$state); 
							if ($state=='PLAY') {
								ma_gdpr_vimeo.last_played = $playerID;
								let $iframe = document.getElementById($playerID);
								if ($iframe) {
									ma_gdpr_vimeo.debug && console.log('MA GDPR Vimeo Player Observer checking iframe offsetParent '+$iframe.offsetParent);
									if (($iframe.offsetParent === null)) {
										ma_gdpr_vimeo.debug && console.log('MA GDPR Vimeo Player Observer pauses hidden video '+$playerID);
										$player.player.pauseVideo();
									}
								}
							}
						});
					}
				},
				player_stop_all_except: function($apiID) {
					/* loop through players and pause all except this one */
					ma_gdpr_vimeo.debug && console.log('Looping players', ma_gdpr_vimeo.players);
					for (let $playerID in ma_gdpr_vimeo.players) {
						ma_gdpr_vimeo.debug && console.log('Checking player '+$playerID);
						if ($playerID != $apiID) {
							ma_gdpr_vimeo.debug && console.log('Pausing player '+$playerID);
							ma_gdpr_vimeo.players[$playerID].player.pause().then(function() {
								ma_gdpr_vimeo.debug && console.log('Paused player '+$playerID);
							}).catch(function($error) {
								console.log('error: '+$error.name);
							});
						}
					}
				},

				click: function($target){
					let $wrapper = $target.closest('.ma-gdpr-vimeo-wrapper'); 
					if (!$wrapper) return;
					if ($wrapper.getAttribute('data-new-window') == '1') {
						/* get the video id from the wrapper */
						let $video_id = $wrapper.getAttribute('data-video-id');
						window.open('https://vimeo.com/'+$video_id);
						return;
					}
					/* initialize the player observer */
					ma_gdpr_vimeo.player_observer_init();

					/* replace wrapper content with video iframe */
					let b64 = $wrapper.querySelector('.ma-gdpr-vimeo-content').innerText;
					let content = decodeURIComponent(atob(b64).split('').map(function(c) {
						return '%'+('00'+c.charCodeAt(0).toString(16)).slice(-2);
					}).join(''));
					$wrapper.innerHTML = content;

					/* check if Vimeo API has already been loaded */
					if (document.querySelectorAll('#ma-gdpr-vimeo-player-api').length==0 ) {
						/* load the Vimeo API */
						ma_gdpr_vimeo.debug && console.log('Loading Vimeo API...');
						let $script = document.createElement('script');
						$script.id = 'ma-gdpr-vimeo-player-api';
						$script.src = 'https://player.vimeo.com/api/player.js';
						/* handler for Vimeo API loaded */
						$script.onload = function ($script) {
							ma_gdpr_vimeo.debug && console.log('Vimeo API loaded.');
							ma_gdpr_vimeo.video_start($wrapper);
						};
						document.body.appendChild($script);
					} else {
						/* Vimeo API is already loaded */
						ma_gdpr_vimeo.video_start($wrapper);
					}

				},

				video_start: function($wrapper) {
					/* get the video id from the parent div's id attribute */
					let $videoID = $wrapper.getAttribute('data-video-id');
					let $playerID = $wrapper.getAttribute('id');
					ma_gdpr_vimeo.debug && console.log('Starting video '+$videoID+' from wrapper '+$playerID);
					/* get the inner dimensions of the wrapper */
					const $wrapperWidth = window.getComputedStyle($wrapper).width;
					const $wrapperHeight = window.getComputedStyle($wrapper).height;
					ma_gdpr_vimeo.debug && console.log('Video WxH',[$wrapperWidth,$wrapperHeight]);

					/* remove styles from wrapper */
					$wrapper.style.height = $wrapperHeight;
					$wrapper.style.padding = 'unset';

					/* connect player */
					let $iframe = document.querySelector('#'+$playerID+' iframe');
					let $player = new Vimeo.Player($iframe);
					ma_gdpr_vimeo.players[$playerID] = {player:$player};

					/* handler to stop other videos */
					$player.on('play', function($data) {
						let $playerID = this.element.offsetParent.getAttribute('id');
						ma_gdpr_vimeo.debug && console.log('Event play', this, $playerID, $data);
						ma_gdpr_vimeo.player_stop_all_except($playerID);
					});
					/* now start video */
					$player.play();
					ma_gdpr_vimeo.last_played = $playerID;
				},
			};
			ma_gdpr_vimeo.init();
		}

		/* Accessibility: Handle Space or Enter as play click */
		document.querySelectorAll('.ma-gdpr-vimeo-button').forEach( ($elm) => {
			$elm.addEventListener('keyup', function($event) {
				if ($event.key==='Enter') {
					$event.preventDefault();
					$event.stopPropagation();
					$event.target.parentNode.querySelector('.ma-gdpr-vimeo-thumbnail').click();
				}
			});
		});
		/* Accessibility: Handle Space or Enter on BODY for playing or stopped video */
		document.addEventListener('keyup', function($event) {
			if (($event.key==='Enter') && ($event.target?.tagName==='BODY')) {
				ma_gdpr_vimeo.debug && console.log('Last played: '+ma_gdpr_vimeo.last_played);
				if (!ma_gdpr_vimeo.last_played) return;
				$event.preventDefault();
				$event.stopPropagation();
				ma_gdpr_vimeo.players[ma_gdpr_vimeo.last_played].player.getPaused().then(function($paused){
					if ($paused) {
						ma_gdpr_vimeo.debug && console.log('Start playing '+ma_gdpr_vimeo.last_played);
						let $player = ma_gdpr_vimeo.players[ma_gdpr_vimeo.last_played];
						$player && $player.player && (typeof $player.player.play!=='undefined') && $player.player.play();
					} else {
						ma_gdpr_vimeo.debug && console.log('Stopping all');
						ma_gdpr_vimeo.player_stop_all_except('');
					}
				});
			}
		});

		</script>
END_OF_SCRIPT;
		if ($this->footercode_minimize) { 
			$script = preg_replace('/\/\*.*?\*\//','',$script); 
			$script = preg_replace('/\r?\n */','',$script); 
			$script = preg_replace('/\t/','',$script); 
		}
		echo $script;
		

		// emit play button svg symbol
		$symbol = <<<'END_OF_SYMBOL'
		<svg id="ma-gdpr-vimeo-symbols" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
					aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;">
			<defs>
				<symbol id="ma-gdpr-vimeo-play-button-vimeo" viewBox="0 0 500 350" >
					<path fill="currentColor" d="M500,74.767C500,33.472,466.55,0,425.277,0 H74.722C33.45,0,0,33.472,0,74.767v200.467C0,316.527,33.45,350,74.722,350h350.555C466.55,350,500,316.527,500,275.233V74.767z  M200,259.578v-188.3l142.789,94.15L200,259.578z"/>
					<path fill="white" d="M199.928,71.057l0.074,188.537l142.98-94.182 L199.928,71.057z"/>
				</symbol>
				<symbol id="ma-gdpr-vimeo-play-button-circle" viewBox="0 0 24 28" >
					<path fill="currentColor" d="M12 2c6.625 0 12 5.375 12 12s-5.375 12-12 12-12-5.375-12-12 5.375-12 12-12zM18 14.859c0.313-0.172 0.5-0.5 0.5-0.859s-0.187-0.688-0.5-0.859l-8.5-5c-0.297-0.187-0.688-0.187-1-0.016-0.313 0.187-0.5 0.516-0.5 0.875v10c0 0.359 0.187 0.688 0.5 0.875 0.156 0.078 0.328 0.125 0.5 0.125s0.344-0.047 0.5-0.141z"/>
				</symbol>
				<symbol id="ma-gdpr-vimeo-play-button-circle-o" viewBox="0 0 24 28" >
					<path fill="currentColor" d="M18.5 14c0 0.359-0.187 0.688-0.5 0.859l-8.5 5c-0.156 0.094-0.328 0.141-0.5 0.141s-0.344-0.047-0.5-0.125c-0.313-0.187-0.5-0.516-0.5-0.875v-10c0-0.359 0.187-0.688 0.5-0.875 0.313-0.172 0.703-0.172 1 0.016l8.5 5c0.313 0.172 0.5 0.5 0.5 0.859zM20.5 14c0-4.688-3.813-8.5-8.5-8.5s-8.5 3.813-8.5 8.5 3.813 8.5 8.5 8.5 8.5-3.813 8.5-8.5zM24 14c0 6.625-5.375 12-12 12s-12-5.375-12-12 5.375-12 12-12 12 5.375 12 12z"/>
				</symbol>
				<symbol id="ma-gdpr-vimeo-play-button-play" viewBox="0 0 24 28" >
					<path fill="currentColor" d="M21.625 14.484l-20.75 11.531c-0.484 0.266-0.875 0.031-0.875-0.516v-23c0-0.547 0.391-0.781 0.875-0.516l20.75 11.531c0.484 0.266 0.484 0.703 0 0.969z"/>
				</symbol>
			</defs>
		</svg>
END_OF_SYMBOL;
		if ($this->footercode_minimize) { $symbol = preg_replace('/\r?\n[\t ]*/','',$symbol); }
		echo $symbol;

		DONE:
		$et = microtime(true);
		if (WP_DEBUG && $this->timing>1) {error_log(sprintf('%s() Timing: %.5f sec.', __METHOD__, $et-$st));}
		$this->timing_total_runtime += $et-$st;
	}

	//===================================================================================================================
	// UTILS
	//===================================================================================================================
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Returns the script details as type, version, combined.
	 * @return object	The script  type and version:
	 * - `type`: The type as 'Plugin' or 'Code Snippet'.
	 * - `version`: The version.
	 * - `combined`: The combination of type and version.
	 * - `full`: The combination of type, title and version
	 */
	public function get_script_details() {
		$type = basename(__FILE__) == 'ma-gdpr-vimeo.php' ? 'Plugin' : 'Code Snippet';
		$retval =(object)[
			'type'		=> $type,
			'version'	=> self::VERSION,
			'combined'	=> sprintf('%s %s', $type, self::VERSION),
			'full'		=> sprintf('%s "%s" %s', $type, self::TITLE, self::VERSION),
		];
		return $retval;;
	}


}


//===================================================================================================================
// Initialize
$GLOBALS['MA_GDPR_Vimeo'] = new MA_GDPR_Vimeo();
	
endif;
