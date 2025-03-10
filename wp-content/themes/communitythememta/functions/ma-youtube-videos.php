<?php
/*
Plugin Name:	MA GDPR YouTube
Description:	GDPR compliant YouTube video embedding
Author:			<a href="https://www.altmann.de/">Matthias Altmann</a>
Project:		Code Snippet: GDPR Compliant YouTube Embed
Version:		1.7.3
Plugin URI:		https://www.altmann.de/blog/code-snippet-gdpr-compliant-youtube-videos/
Description:	en: https://www.altmann.de/blog/code-snippet-gdpr-compliant-youtube-videos/
				de: https://www.altmann.de/blog/code-snippet-dsgvo-konforme-youtube-videos/
Copyright:		© 2021-2024, Matthias Altmann

NOTES
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

VERSION HISTORY
Date		Version		Description
--------------------------------------------------------------------------------------------------------------
2024-10-14	1.7.3		Changes:
						- Retrieval of video details now uses cURL for better handling of YouTube responses. 
						  (Huge thanks to Oscar Obianenue for reporting and supporting analysis and tests)
2024-10-11	1.7.2		Fixes:
						- Fixed missing styling of video preview (play button, GDPR text) in Bricks builder.
2024-07-03	1.7.1		Changes:
						- Added width and height attributes for thumbnail images
						- Player observer now handles fullscreen correctly 
						  (Thanks to Michael Herceg for reporting)
2024-04-28	1.7.0		Reorganization of code base.
						New Features:
						- For better accessibility, the Enter key can now be used to start and stop videos.
						  (Thanks to Stephan Koenigk for his feature request and pre-release tests)
						- alt, title and title-text now support placeholders @title@ and @description@, 
						  language specific, if available. The use of @ is deprecated.
						Changes:
						- For invalid IDs, don't create directory, store json, attempt to retrieve thumbnails
						- In Builders Bricks and Oxygen, click handler is deactivated to allow selecting element
						- Preparation for Bricks Element. Coming soon.
2023-12-30	1.6.0		New Features:
						- Complete rebuild of JS
						- Video player management via players registry and observer
							- Pause current video if another one is started
							- Pause video if modal/popup closed (evaluated by visibility of parent DOM element)
						- Support for dynamically embedded videos using AJAX calls:
							- Removed init prevention for AJAX calls
							- Added PHP method MA_GDPR_YouTube::enable_footercode() to trigger output of footer
							  code (styles, scripts, svg) for video embeds dynamically loaded by AJAX calls
							- Play click handler is now assigned as onclick event instead of collecting all 
							  videos after page load. This eliminates the need for an extra click handler 
							  initialization for players dynamically loaded after page load.  
						- Optimizations for accessibility 
						Fixes:
						- Prefixed wrapper/player IDs with snippet slug to prevent IDs starting with number
						- Fallback for aspect ratio via padding-top (CSS variable, @supports rule) 
						  for older browsers not supporting aspect-ratio like Safari < V15
						Changes:
						- Changed debug URL switch from "debug" to "ma-gdpr-youtube-debug" to avoid conflicts
						- Changed cache folder name from ma-gdpr-youtube-thumbnails to ma-gdpr-youtube incl. renaming
2023-02-26	1.5.0		New Features:
						- Title of the video is now automatically retrieved from YouTube and stored locally.
						  Request to YouTube is made using the current language to retrieve translated title if 
						  available. Titles are stored per language. So only one request per video and language. 
						  Title from YouTube can be displayed using parameter title-text="@". 
						  Title from YouTube can also be used as alt="@" and title="@" for the thumbnail.
						- Shortcode attributes title-text, gdpr-text now allow HTML (e.g. for bold, links, ...)
						Fixes:
						- If video is played in a modal (Oxygen, Bricks), don't restart video after closing modal.
						  Implemented via visibility check in onReady event.
						  (Requested by Manuel Mochkal)
						Changes:
						- Added compatibility check for allow_url_fopen. Must be On for the snippet to work.
						- Thumbnail size is now based on width and aspect-ratio instead of calculated height.
						- Changed JS function variable from ytVidPlay to ma_gdpr_youtube_ytVidPlay
2023-02-09	1.4.0		New Features:
						- Added parameter "video" with parsing of YouTube URL variants
						- Added global configuration via $GLOBALS['ma_gdpr_youtube']
						- Added Finnish translations for GDPR text
						  (Thanks to Thao Le)
2022-11-27	1.3.0		New Features:
						- Added Japanese translations for GDPR text
						  (Thanks to Viorel-Cosmin Miron)
						- Added support for WPML
						  (Thanks to Viorel-Cosmin Miron)
						  Please note there's a WPML bug preventing the creation a policy link with correct language.
						  (https://wpml.org/forums/topic/get_the_privacy_policy_link-should-be-translated/#post-8153387) 
						Changes:
						- Migrated JavaScript from jQuery to vanilla JS (ES6) to eliminate jQuery dependency.
						Fixes:
						- Changed init skip for JSON calls (introduced in 1.1.0) to allow rendering in Oxygen Builder 
2022-09-25	1.2.0		New Features:
						- Added new shortcode parameters title-text, title-class, title-style for title overlay
						- Added new shortcode parameters notice-class, notice-style for GDPR text banner
						- Added new shortcode parameters play-button, play-button-color, play-button-style for button variations
						Fixes:
						- Added original image size to source set for custom thumbnail by ID
						- Removed double '.' for German GDPR text.
						  (Thanks to Tobias Maximilian Hietsch for reporting)
						- Removed excess trailing comma at sprintf arguments
						  (Thanks to Nils Bäßler for reporting)
2022-02-07	1.1.0		New Features:
						- Support for webp thumbnail image format
						  (Requested by Artur Gilbert, Yan Kiara)
						- Added lazy loading for thumbnail images
						  (Requested by Yan Kiara)
						- Support for additional YouTube player parameters (e.g. modestbranding=1)
						  (Requested by Lau Fa)
						- Added Dansk translations for GDPR text
						  (Thanks to Theis L. Soelberg)
						- Added shortcode parameters alt and title for thumbnail image
						  (Requested by Yan Kiara)
						- Added shortcode parameters thumbnail (URL or media ID)
						  (Requested by Viorel-Cosmin Miron)
						- Added width/height attributes for thumbnail images
						  (Requested by Viorel-Cosmin Miron)
						- Added JS console debugging by URL parameter "debug"
						Fixes:
						- Optimization of SVG symbol minimizing
2021-08-05	1.0.6		New Features:
						- Using scheme-less URL to avoid issues with wrong WordPress URL configuration
						- Added parameter new-window to play video in a new window
						- Added "_" to valid character check on video id
						- Hide GDPR notice block if text is empty
						- Load and cache YouTube thumbnails only on very first appearance of a new video ID 
						  to improve performance, if specific YouTube thumbnail sizes are not available
						Fixes:
						- Check for availability of specific thumbnail sizes (might not be available from YouTube)
2021-06-17	1.0.5		Fix: Correction in Hungarian translation
2021-06-17	1.0.4		Features: 
						- Added "-" to valid character check on video id (thanks to Zoltán Kőrösi)
						- Added Hungarian GDPR text (thanks to Zoltán Kőrösi)
2021-06-17	1.0.3		Fix: Check GET parameter "ct_builder" before accessing it
2021-06-15	1.0.2		Feature: Add link to privacy policy to default gdpr text if configured in WordPress
2021-06-15	1.0.1		Fix: Allow same video embedded multiple times
2021-06-15	1.0.0		Initial Release
--------------------------------------------------------------------------------------------------------------
*/

if (!class_exists('MA_GDPR_YouTube')) :

class MA_GDPR_YouTube {

	const TITLE		= 'MA GDPR YouTube';
	const SLUG		= 'ma-gdpr-youtube';
	const VERSION	= '1.7.3';

	// ===== CONFIGURATION ==============================================================================================
	/** Default width of the video block. Can be specified in %, px */
	public $default_width			= '100%';
	/** Default aspect ratio of the video block. Syntax X:X or X/X */
	public $default_aspect_ratio	= '16/9';
	/** GDPR notice text in different languages. */
	private $default_gdpr_text 		= [
		'da' => ['Når du har trykket, vil videoen blive indlæst fra YouTube\'s servere. Se vores %s for flere informationer.','privatlivspolitik'],
		'de' => ['Bei Klick wird dieses Video von den YouTube Servern geladen. Details siehe %s.', 'Datenschutzerklärung'],
		'en' => ['When clicked, this video is loaded from YouTube servers. See our %s for details.', 'privacy policy'],
		'es' => ['Al hacer clic, este vídeo se carga desde los servidores de YouTube. Consulte la %s para más detalles.', 'política de privacidad'],
		'fi' => ['Klikattuasi, tämä video ladataan Youtuben palvelimilta. Katso lisätietoja meidän %s.', 'tietosuojaselosteesta'],
		'fr' => ['En cliquant, cette vidéo est chargée depuis les serveurs de YouTube. Voir la %s.', 'politique de confidentialité'], 
		'hu' => ['Kattintás után ez a videó a YouTube szervereiről kerül lejátszásra. A részletekért olvassa el az %s oldalt.', 'Adatkezelési Tájékoztatót'],
		'it' => ['Quando si clicca, questo video viene caricato dai server di YouTube. Vedere %s per i dettagli.', 'l\'informativa sulla privacy'],
		'ja' => ['クリックすると、この動画が YouTube サーバーから読み込まれます。詳細については、%s をご覧ください。', 'プライバシー ポリシー'],
	]; 
	/** Default font size for GDPR text */
	public $default_gdpr_text_size	= '.7em';
	/** Default open video in new window */
	private $default_new_window		= false;
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
	private $yt_urlformat_image 	= 'https://img.youtube.com/vi%s/%s/%s.%s';
	private $yt_urlformat_watch 	= 'https://www.youtube.com/watch?v=%s';

	private $yt_image_sizes		= [ // various resolutions, not all might be available!
	// see https://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api
	//	tag		 						  aspect ratio	availability	resolution	black bars
	//	'default' 			=> 120,		// 		4:3		guaranteed		120x90		
		'mqdefault' 		=> 320,		// 		16:9	guaranteed		320x180		no
		'hqdefault'			=> 480,		// 		4:3		guaranteed		480x360		yes
		'sddefault'			=> 640,		// 		4:3		optional		640x480		yes
		'hq720'				=> 1280,	// 		16:9	optional		1280x720	no
		'maxresdefault'		=> 1920,	// 		16:9	optional		(highest)	no
		// highest: depends on video, e.g. 1280x720, 1920x1080, ...
	]; 

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

		if (!defined('MA_GDPR_YouTube_Version')) define('MA_GDPR_YouTube_Version',self::VERSION);

		if ($this->is_incompatible()) return;

		// check content directory
		$this->content_base = $this->get_content_base();
		if (!$this->content_base) {return;}

		add_shortcode('ma-gdpr-youtube', [$this, 'shortcode']);
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
		if (!ini_get('allow_url_fopen')) {$incomp[] = 'PHP setting <code>allow_url_fopen</code> needs to be <b>On</b>';}
		if (!extension_loaded('curl')) {$incomp[] = 'PHP extension <code>curl</code> needs to be <b>enabled</b>';}
		$this->incompatibilities = $incomp;
		if (count($incomp) && is_admin()) {
			add_action('admin_notices', function(){
				if (WP_DEBUG ) {error_log(self::TITLE.' Incompatibilities: '.implode(', ',$this->incompatibilities));}
				echo '<div class="notice notice-error is-dismissible">
						<p>The '.$this->get_script_details()->combined.' is skipped: '.implode(', ',$this->incompatibilities).'.</p>
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
		if ( ($_GET['bricks']??null) == 'run') {
			// emit styles, script, svg when Bricks Builder is active to support video preview
			$this->footercode_needed = true;
		}
		if (defined('BRICKS_VERSION')) {
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
	 * Create directory /wp-content/uploads/ma-gdpr-youtube/ if necessary
	 * Rename from old scheme ma-gdpr-youtube-thumbnails if exists
	 * @return object|null	A dirinfo object (->dir, ->url)
	 */
	private function get_content_base(): ?object {
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
	 * Parses a YouTube URL for a video ID.
	 * Handles numerous URL formats, or plain video ID.
	 * @param string $s	The URL/string to parse
	 * @return array	Array containing keys v (video ID), optional t (start time), rel (related switch), or empty
	 */
	private function parse_yt_url(string $s=''): array {
		$st = microtime(true);
		$retval = [];
		// regex for parsing youtube url variants
		$re = '/^
			(?:https?\:)?\/{2}														# protocol http, https, or schemeless
			(?:www\.)?																# optional www
			(?:youtu\.be|youtube\.com|youtube-nocookie\.com)\/						# domain variants
			(?:(?:embed|shorts|user\/\w+\#(?:\w+\/)+|watch|ytscreeningroom)[\/]?) ?	# optional specs
			(?:[\?\&]? (?:v|vi)[\/=])?												# optional v, vi parameter
			([A-Za-z0-9\-\_]+)														# THE ID
		/x';
		// parse url
		if (preg_match($re,$s,$matches)) 							{$retval['v'] = $matches[1];}
		// id only?
		else if (preg_match('/^([A-Za-z0-9\-\_]+)$/',$s,$matches))	{$retval['v'] = $matches[1];}
	
		if (isset($retval['v'])) { // found video id? try to find more parameters
			// timecode
			if (preg_match('/(?:[&\?])(?:t|start)=(\d+)/',$s,$matches)) {$retval['t'] = $matches[1];}
			// rel
			if (preg_match('/(?:[&\?])rel=(\d+)/',$s,$matches)) {$retval['rel'] = $matches[1];}
		}
		$et = microtime(true);
		if (WP_DEBUG && $this->timing>1) {error_log(sprintf('    %s("%s") => %s Timing: %.5f sec.', __METHOD__, $s, json_encode($retval), $et-$st));}
		return $retval;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/** 
	 * Retrieve video info from YouTube, for current language
	 * @param string $url	The video id
	 * @param string $url	The watch URL, containing the video id
	 * @param string $lang	The current language
	 * @param object $info	The info object to fill
	 * @return object|null	The info object
	 */
	private function retrieve_video_info(string $id, string $url, string $lang, object $info): ?object {
		// retrieve from YouTube using current language
		/* OLD retrieval using fopen with cURL wrapper
		$options = ['http'=>['method'=>'GET','header'=>"Accept-language: {$lang}\r\n"]];
		$context = stream_context_create($options);
		$html = @file_get_contents($url,false,$context);
		*/
		/* @since 1.7.3 NEW retrieval usíng cURL */
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_CUSTOMREQUEST 	=> 'GET',
			CURLOPT_HTTPHEADER		=> [
				'Accept-Language: '.$lang,
				'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
			],
			CURLOPT_FOLLOWLOCATION	=> true,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_HEADER			=> true,
			CURLINFO_HEADER_OUT		=> true,
			CURLOPT_SSL_VERIFYPEER	=> true,
		]);
		$curl_result = curl_exec($ch);
		$curl_info = curl_getinfo($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		/* optional: write results to debug.log
		if (WP_DEBUG) {
			error_log('['.__METHOD__.'] curl info '.print_r($curl_info,true));
			error_log('['.__METHOD__.'] curl result '.$curl_result);
			error_log('['.__METHOD__.'] curl error '.$curl_error);
		}
		*/

		if (!$curl_result) {
			// return error details
			$info->error = sprintf('%s %s',$curl_info['http_code'], $curl_error);
			return $info;
		}
		$curl_result = explode("\r\n\r\n",$curl_result,2);
		$html = count($curl_result)==2 ? $curl_result[1] : '';

		if ($html) {
			// optional: save as HTML - for debugging
			//@file_put_contents(sprintf('%s/%s-%s.html',$this->content_base->dir, date('YmdHis').'.html',$html);

			// try to find translated title from the script
			preg_match('/{"playerOverlayVideoDetailsRenderer":{"title":{"simpleText":"(.+?)"}/',$html,$matches) && $info->title->$lang = $matches[1];
			// or get title from title or meta title
			if (!($info->title->$lang??null))	
				preg_match('/<title>(.+?)<\/title>/s',$html,$matches) && $info->title->$lang = preg_replace('/- YouTube$/','',$matches[1]);
			if (!($info->title->$lang??null))	
				preg_match('/<meta name="title" content="(.+?)">/',$html,$matches) && $info->title->$lang = $matches[1];
			// get some other info for maybe later use
			preg_match('/<meta name="description" content="(.+?)">/',$html,$matches)			&& $info->description->$lang = $matches[1];
			preg_match('/<meta name="keywords" content="(.+?)">/',$html,$matches)				&& $info->keywords->$lang = $matches[1];
			preg_match('/<meta itemprop="genre" content="(.+?)">/',$html,$matches)				&& $info->genre->$lang = $matches[1];
			preg_match('/<link rel="shortlinkUrl" href="(.+?)">/',$html,$matches)				&& $info->shortlinkurl = $matches[1];
			preg_match('/<meta itemprop="isFamilyFriendly" content="(.+?)">/',$html,$matches)	&& $info->familyfriendly = $matches[1];
			preg_match('/<meta itemprop="datePublished" content="(.+?)">/',$html,$matches)		&& $info->datepublished = $matches[1];
			preg_match('/<meta itemprop="uploadDate" content="(.+?)">/',$html,$matches)			&& $info->dateuploaded = $matches[1];
			// look for background image (= response validity check)
			$bgimage_found = preg_match('/background\-image: url\(\'https:\/\/i\.ytimg\.com\/vi\/'.$id.'\//',$html);

			DONE:
			if ( (!$info->dateuploaded??'') && (!$bgimage_found)) { 
				// invalid video details, because neither meta uploadDate nor CSS background-image $id found
				$info->error = 'Invalid video details received from YouTube.';
				return $info;
			}
			// save 
			$vid_dir = $this->content_base->dir.'/'.$id;
			if (!file_exists($vid_dir)) {
				if (!@mkdir($vid_dir)) {
					error_log(sprintf('[%s] Error creating thumbnail cache folder for video %s.', self::TITLE, $id));
					return null;
				}
			}
			$info_filepath = $vid_dir.'/'.$id.'.json';
			@file_put_contents($info_filepath, json_encode($info,JSON_PRETTY_PRINT));
		}
		return $info;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Get the info for a video, for the current language, either from cache or from YouTube
	 * @param string $id 	The video id
	 * @param string $lang	The currnet language
	 * @return object|null	The info object
	 */
	private function get_video_info(string $id, string $lang): ?object {
		$st = microtime(true);
		$info = null;
		$url = sprintf($this->yt_urlformat_watch,$id);

		// create an empty info object. Some fields may be available in different languages
		$info = (object)[
			'title'			=> (object)[],
			'description'	=> (object)[],
			'keywords'		=> (object)[],
			'genre'			=> (object)[],
			'shortlinkurl'	=> '',
			'familyfriendly'=> '',
			'datepublished'	=> '',
			'dateuploaded'	=> '',
			'thumbsloaded' 	=> false, // will be set by check_thumbnails
		];
		$info_filepath = $this->content_base->dir.'/'.$id.'/'.$id.'.json';
		if (file_exists($info_filepath)) {
			// read from cache
			if ($info = json_decode(@file_get_contents($info_filepath))) {
				// current language already available?
				if (property_exists($info->title,$lang)) {
					goto DONE;
				}
			}
		}
		// either file doesn't exist yet, or info or language missing. Retrieve from YouTube.
		$info = $this->retrieve_video_info($id, $url, $lang, $info);

		DONE:
		$et = microtime(true);
		if (WP_DEBUG && $this->timing>1) {error_log(sprintf('    %s("%s","%s") Timing: %.5f sec.', __METHOD__, $id, $lang, $et-$st));}
		return $info;
	}

	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Check if thumbnails for video $id have been downloaded. Load if not yet available.
	 * @param string $id	The YouTube video ID
	 * @param object $info	The video info object
	 * @return	bool		True if downloaded, false if not downloaded
	 */
	private function check_thumbnails(string $id, object $info): bool {
		$st = microtime(true);
		$retval = false;
		if (!$this->content_base) 	{goto DONE;}
		// check if directory for this video exists

		if (!$info->thumbsloaded??false) {
			$vid_dir = $this->content_base->dir.'/'.$id;
			// retrieve thumbnails
			foreach ($this->yt_image_sizes as $size_tag => $size) {
				foreach(['jpg'=>'','webp'=>'_webp'] as $ext => $url_appendix) {
					$img_path = $vid_dir.'/'.$id.'_'.$size_tag.'.'.$ext;
					if (!file_exists($img_path)) {
						$img_url = sprintf($this->yt_urlformat_image, $url_appendix, $id, $size_tag, $ext);
						// load and cache thumbnail
						if ($img_data = @file_get_contents($img_url)) {
							file_put_contents($img_path, $img_data);
						}
					}
				}
			}
			$info->thumbsloaded = wp_date('Y-m-d H:i:s');
			$info_filepath = $vid_dir.'/'.$id.'.json';
			@file_put_contents($info_filepath, json_encode($info,JSON_PRETTY_PRINT));
		}
		
		$retval = true;
		DONE:
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
		$retval = explode('-',@$retval)[0];
		return $retval;
	}

	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Handle the shortcode "ma-gdpr-youtube". 
	 * @param array $atts		The shortcode attributes
	 * @param string $content	The content of the shortcode
	 * @return string			The output
	 */
	public function shortcode(array $shortcode_atts = [], string $content = '') {
		$st = microtime(true);
		$lang = $this->get_current_language();
		$retval = '';

		// get defaults for unspecified attributes
		$atts_default = [
			'id'				=> null, // deprecated
			'video'				=> null,
			'uniqid'			=> null,
			'width'				=> $this->default_width,
			'aspect-ratio'		=> $this->default_aspect_ratio,
			'notice-class'		=> null,
			'notice-style'		=> null,
			'gdpr-text'			=> isset($this->default_gdpr_text[$lang]) 
									? sprintf($this->default_gdpr_text[$lang][0],self::get_privacy_policy_link($this->default_gdpr_text[$lang][1])) 
									: sprintf($this->default_gdpr_text['en'][0],self::get_privacy_policy_link($this->default_gdpr_text['en'][1])),
			'gdpr-text-size'	=> null,
			'alt'				=> null,
			'title'				=> null,
			'thumbnail'			=> null,
			'title-text'		=> null,
			'title-class'		=> null,
			'title-style'		=> null,
			'play-button'		=> 'youtube', // currently included: youtube, circle, circle-o, play
			'play-button-style'	=> null,
			'play-button-color'	=> null,
			'new-window'		=> $this->default_new_window,
		];
		// merge global settings
		$atts = array_merge($atts_default, $GLOBALS['ma_gdpr_youtube']??[]);
		// choose correct language for gdpr-text
		if ($GLOBALS['ma_gdpr_youtube']['gdpr-text-'.strtolower($lang)]??'') {
			$atts['gdpr-text'] = $GLOBALS['ma_gdpr_youtube']['gdpr-text-'.strtolower($lang)];
		}
		// allow html in shortcode attributes title-text, gdpr-text
		foreach (['title-text','gdpr-text'] as $att) {
			if ($shortcode_atts[$att]??'') {$shortcode_atts[$att] = html_entity_decode($shortcode_atts[$att]);}
		}
		// merge shortcode attributes
		$atts = (object)array_merge($atts, $shortcode_atts);

		if ($atts->video) {
			$video = $this->parse_yt_url($atts->video);
			if (isset($video['v'])) {
				$atts->id = $video['v'];
				if (isset($video['t'])) 	{$atts->t = $video['t'];}
				if (isset($video['rel'])) 	{$atts->rel = $video['rel'];}
			}
		}

		// any other parameter will be passed to youtube directly
		// see https://developers.google.com/youtube/player_parameters?hl=de#Parameters for a list of parameters
		// Note! rel=0 not supported anymore since September 2019 - without hacks which we won't support
		// See https://www.amblemedia.com/disable-suggested-videos-on-youtube-embeds/
		$yt_parameters = [];
		foreach ($atts as $att_key => $att_val) {
			if (!in_array($att_key,array_keys($atts_default))) {
				$yt_parameters[$att_key] = $att_val;
			} 
		}
		$yt_parameters_json = json_encode($yt_parameters);

		if (!isset($atts->id) || ($atts->id == '' )) 	{$retval = sprintf('[%s] Missing video id.',self::TITLE); goto DONE;}
		if (preg_match('/[^A-Za-z0-9\-\_]/',$atts->id))	{$retval = sprintf('[%s] Invalid video id.',self::TITLE); goto DONE;}
		// generate an unique id (for the case a video is embedded multiple times)
		$atts->uniqid = self::SLUG.'-'.$atts->id.'-'.uniqid();

		if (!$this->content_base) {$retval = sprintf('[%s] Content directory is not available.',self::TITLE); goto DONE;}

		// load youtube attributes
		if ($video_info = $this->get_video_info($atts->id,$lang)) {
			// @since 1.7.3 Check for errors during retrieval of video details from YT
			if ($video_info->error??null) {$retval = sprintf('[%s] Error loading video: %s',self::TITLE,$video_info->error); goto DONE;}
			// legacy, deprecated
			if ($atts->alt == '@') 				$atts->alt = esc_html($video_info->title->$lang??$video_info->title->en??'');
			if ($atts->title == '@') 			$atts->title = esc_html($video_info->title->$lang??$video_info->title->en??'');
			if ($atts->{'title-text'} == '@') 	$atts->{'title-text'} = esc_html($video_info->title->$lang??$video_info->title->en??'');
			// @since 1.7.0 insert youtube meta data
			foreach (['alt','title','title-text'] as $attr) {
				foreach (['title','description'] as $meta) {
					$atts->{$attr} = str_replace('@'.$meta.'@', $video_info->$meta->$lang??$video_info->$meta->en??'', $atts->{$attr}??'');
				}
			}
		} else {$retval = sprintf('[%s] Invalid video id.',self::TITLE); goto DONE;}
		
		// check if we already have a thumbnail
		if (!$this->check_thumbnails($atts->id, $video_info)) {$retval = sprintf('[%s] Error retrieving thumbnails.',self::TITLE); goto DONE;}

		$thumbnail = '';
		$sources = [];
		$click_handler = 'onclick="ma_gdpr_youtube.click(this)"';

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
				$thumbnail .= sprintf('<picture class="ma-gdpr-youtube-thumbnail" '.$click_handler.'>%s <img loading="lazy" src="%s" width="%d" height="%d" alt="%s" title="%s"></picture>',
									implode('',array_reverse($sources)), $largest->url, $largest->width, $largest->height, $atts->alt??'', $atts->title??'');
			} else {
				// thumbnail URL
				// no width and height attributes because URL could be external, and retrieving img sizes would require extra request
				$sources = [];
				$thumbnail .= '<picture class="ma-gdpr-youtube-thumbnail" '.$click_handler.'>';
				$thumbnail .= sprintf('<img loading="lazy" src="%s" alt="%s" title="%s">',$atts->thumbnail, $atts->alt, $atts->title);
				$thumbnail .= '</picture>';
			}
		} else {
			$source_list = [];
			$sizes = [];
			// get sources
			foreach ($this->yt_image_sizes as $size_tag => $size) {
				$source_list[] = [$size_tag,$size];
			}
			// get smallest thumbnail first
			list ($size_tag,$size) = array_shift($source_list);
			$img_src = $this->content_base->url.'/'.$atts->id.'/'.$atts->id.'_'.$size_tag.'.jpg';
			$img_path = $this->content_base->dir.'/'.$atts->id.'/'.$atts->id.'_'.$size_tag.'.jpg';
			$img_info = getimagesize($img_path);
			$img_width = $img_info[0];
			$img_height = $img_info[1];
			$tmp_thumbnail = sprintf('<picture class="ma-gdpr-youtube-thumbnail" %s> %s <img src="%s" alt="%s" title="%s" width="%d" height="%d"></picture>',
									$click_handler, '%s' /* later */, $img_src, $atts->alt, $atts->title, $img_width, $img_height
								);
			// get larger thumbnails
			while (count($source_list)) {
				list ($size_tag,$size) = array_shift($source_list);
				foreach(['jpg'=>'jpeg','webp'=>'webp',] as $ext => $mime) { // will be reversed! so webp before jpg to have jpg before webp in final output
					$img_path = $this->content_base->dir.'/'.$atts->id.'/'.$atts->id.'_'.$size_tag.'.'.$ext;
					if (file_exists($img_path)) {
						// get real image size
						$img_info = getimagesize($img_path);
						if ($img_info) {
							$img_width = $img_info[0];
							$img_height = $img_info[1];
							// skip if we already have this size/format combo (maxres might be same as hq720)
							if (in_array($img_width.'_'.$ext,$sizes)) {continue;}
							$sizes[] = $img_width.'_'.$ext;
							// to improve thumbnail quality, use higher res image if we reach half its size 
							$sources[] = sprintf('<source media="(min-width:%dpx)" type="image/%s" srcset="%s" width="%d" height="%d">',
											$img_width/2, $mime, 
											$this->content_base->url.'/'.$atts->id.'/'.$atts->id.'_'.$size_tag.'.'.$ext, 
											$img_width, $img_height,
										);
						}
					}
				}
			}
			$thumbnail .= sprintf($tmp_thumbnail, implode('',array_reverse($sources)));
		}

		// calculate dimensions of video block depending on width and aspect ratio
		list ($arw,$arh) = explode('/',str_replace(':','/',$atts->{'aspect-ratio'}),2); // aspect ratio elements
		list ($width_value, $width_unit) = ['100','%']; // default width value and unit
		// split width value and unit
		preg_match('/^(\d+)(.+)$/',$atts->width,$matches);
		if (count($matches) == 3) {array_shift($matches); list ($width_value, $width_unit) =  $matches;}
		// calculate block dimensions
		$block_width = $width_value.$width_unit;
		$block_height = ($width_value * ($arh/$arw)) . $width_unit;

		// privacy policy url and link
		$atts->{'gdpr-text'} = str_replace('{privacy-policy-url}', get_privacy_policy_url(), $atts->{'gdpr-text'});
		$atts->{'gdpr-text'} = str_replace('{privacy-policy-link}', get_the_privacy_policy_link(), $atts->{'gdpr-text'});

		// title overlay
		$title_overlay = !empty($atts->{'title-text'})
			? sprintf('<div class="ma-gdpr-youtube-title %1$s" %2$s>%3$s</div>',
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

		// @since 1.5.0 player size based on aspect-ratio
		// @since 1.6.0 width/height/aspect-ratio now set via CSS var for older browsers not supporting aspect-ratio, like e.g. Safari <V15
		$retval = sprintf(	'<div id="%7$s" data-video-id="%2$s" class="ma-gdpr-youtube-wrapper" style="--_width:%3$s;--_height:%4$s;--_aspect-ratio:%16$s;" data-new-window="%8$s" data-yt-parameters="%9$s">'.
								$thumbnail.
								'<svg class="ma-gdpr-youtube-button button-%13$s %14$s" %15$s tabindex="0" role="button" aria-label="play video" '.$click_handler.'><use xlink:href="#ma-gdpr-youtube-play-button-%13$s"></use></svg>'.
								'%10$s'.
								'<div class="ma-gdpr-youtube-notice %11$s" style="%6$s %12$s">%5$s</div>'.
							'</div>',
					/*1*/	$this->content_base->url, 
					/*2*/	$atts->id,
					/*3*/	$block_width,
					/*4*/	$block_height,
					/*5*/	$atts->{'gdpr-text'},
					/*6*/	$gdpr_text_size,
					/*7*/	$atts->uniqid,
					/*8*/	$atts->{'new-window'},
					/*9*/	count($yt_parameters) ? base64_encode($yt_parameters_json) : '',
					/*10*/	$title_overlay,
					/*11*/	$atts->{'notice-class'} ?? '',
					/*12*/	$atts->{'notice-style'} ?? '',
					/*13*/	$atts->{'play-button'} ?? '',
					/*14*/	$atts->{'play-button-class'} ?? '',
					/*15*/	$play_button_style,
					/*16*/	str_replace(':','/',$atts->{'aspect-ratio'})
				);
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
	 * On the parent page, call MA_GDPR_YouTube::enable_footercode()
	 * @since 1.6.0
	 */
	public static function enable_footercode() {
		$GLOBALS[__CLASS__]->footercode_needed = true;
	}
	//-------------------------------------------------------------------------------------------------------------------
	/**
	 * Emits the footer code (styles, script, svg) to handle the YouTube embedding
	 */
	public function footercode() {
		$st = microtime(true);

		if (!$this->footercode_needed) {goto DONE;}

		/** @since 1.4.0 debugging info */ 
		echo sprintf('<span id="%2$s-info" data-nosnippet style="display:none">%1$s %2$s %3$s</span>', $this->get_script_details()->type, self::SLUG, self::VERSION); 

		// emit style
		// @since 1.6.0: @supports rule provides wrapper height for older browsers not supporting aspect-ratio, like e.g. Safari <V15
		$style = <<<'END_OF_STYLE'
		<style id="ma-gdpr-youtube-style">
			.ma-gdpr-youtube-wrapper {position:relative; display:flex; isolation:isolate; width:var(--_width);aspect-ratio:var(--_aspect-ratio);}
			@supports not (aspect-ratio:1/1) {.ma-gdpr-youtube-wrapper{height:var(--_height); padding-top:var(--_height);}}
			.ma-gdpr-youtube-thumbnail {position:absolute; z-index:1; top:0; left:0; width:100%; height:100%; display:flex; cursor:pointer; }
			.ma-gdpr-youtube-thumbnail img {width:100%; height:100%; object-fit:cover; object-position:50% 50%;}
			.ma-gdpr-youtube-button {position:absolute; z-index:4; top:50%; left:50%; transform:translate(-50%,-50%); width:70px; height:70px; cursor:pointer; color:white;}
			.ma-gdpr-youtube-button.button-youtube {color:#f61c0d;}
			.ma-gdpr-youtube-button.button-circle {filter:drop-shadow(0px 0px 4px darkgray);}
			.ma-gdpr-youtube-button.button-circle-o {filter:drop-shadow(0px 0px 4px darkgray);}
			.ma-gdpr-youtube-notice {position:absolute; z-index:2; width:100%; left:0; right:0; bottom:0; max-width:100%; text-align:center; font-size:.7em; background-color:rgba(255,255,255,.8); padding:.2em .5em;}
			.ma-gdpr-youtube-notice:empty {display:none;}
			.ma-gdpr-youtube-title {position:absolute; z-index:3; width:100%; top:1em; padding:0 1em; color:white; text-shadow: black 1px 1px 2px;}
		</style>
END_OF_STYLE;
		if ($this->footercode_minimize) { 
			$style = preg_replace('/\/\*.*?\*\//','',$style); 
			$style = preg_replace('/\r?\n */','',$style); 
			$style = preg_replace('/\t/','',$style); 
		}
		echo $style;

		// emit code
		$script = <<<'END_OF_SCRIPT'
		<script id="ma-gdpr-youtube-script" type="text/javascript">
		"use strict";

		/* Check for Builder preview panes */
		if (
			(typeof window.parent?.bricksData?.wpEditor != 'undefined') /* Bricks*/
		|| 	(window.parent?.angular) /* Oxygen */
		) { 
			/* Dummy Object w/o functionality for Bricks Builder */ 
			window.ma_gdpr_youtube = {
				init: function(){},
				click: function($target){},
			}
		}

		if ((typeof window.ma_gdpr_youtube == 'undefined')){
			window.ma_gdpr_youtube = {
				debug: (new URLSearchParams(window.location.search)).get('ma-gdpr-youtube-debug')!=null,
				player_observer_timer: 1000,
				player_observer_interval: null,
				players: {}, /* list of active players */
				fullscreen: null,
				last_played: null,

				init: function(){
					this.debug && console.log('MA GDPR YouTube initialized.');
					window.YT = null; /* prevent JS errors in Oxygen Builder */
				},

				get_player_state: function($input,$simple=false) {
					/* helper to get player status. $input can be a state (integer), player apiID (string) or a real player object */
					let $state = null;
					if (typeof $input == 'number') {$state = $input;} 
					else if (typeof $input == 'string') {$input = this.players[$input].player;}
					if (typeof $input == 'object') {
						if ($input && typeof $input.getPlayerState !== 'undefined') {$state = $input.getPlayerState();}
					}
					/* if $simple is true, return 'PLAY' or 'STOP' */
					if ($simple) {
						if ((window.YT == null) || (window.YT.PlayerState == null)) return $state;
						switch ($state) {
							case YT.PlayerState.UNSTARTED:	$state = 'STOP'; break;
							case YT.PlayerState.ENDED:		$state = 'STOP'; break;
							case YT.PlayerState.PLAYING:	$state = 'PLAY'; break;
							case YT.PlayerState.PAUSED:		$state = 'STOP'; break;
							case YT.PlayerState.BUFFERING:	$state = 'PLAY'; break;
							case YT.PlayerState.CUED:		$state = 'STOP'; break;
							default: $state = 'STOP';
						}
					}
					return $state;
				},

				player_observer_init: function(){
					if (this.player_observer_interval == null) {
						this.player_observer_interval = setInterval(this.player_observer, this.player_observer_timer);
						this.debug && console.log('MA GDPR YouTube Player Observer initialized.');
						/* observe fullscreen change */
						document.addEventListener('fullscreenchange', function($event){
							ma_gdpr_youtube.debug && console.log('MA GDPR YouTube Player Observer FullscreenChange',$event);
							let $element_id = document.fullscreenElement?.id??null;
							if ($element_id) { /* fullscreen started */
								ma_gdpr_youtube.fullscreen = $element_id;
							} else { /* fullscreen ended */
								ma_gdpr_youtube.fullscreen = null;
							}
							ma_gdpr_youtube.debug && console.log('MA GDPR YouTube Player Observer has fullscreen '+ma_gdpr_youtube.fullscreen);
						});
					}
				},

				player_observer: function() {
					/* pauses video if iframe is not visible anymore - e.g. closed modal - but also not fullscreen */
					ma_gdpr_youtube.debug && console.log('MA GDPR YouTube Player Observer (entries: '+Object.values(ma_gdpr_youtube.players).length+')');
					for (let $playerID in ma_gdpr_youtube.players) {
						let $player = ma_gdpr_youtube.players[$playerID];
						let $playerState = ma_gdpr_youtube.get_player_state($player.player);
						let $playerStateSimple = ma_gdpr_youtube.get_player_state($playerState,true);
						ma_gdpr_youtube.debug && console.log('MA GDPR YouTube Player Observer checking player '+$playerID+', state '+$playerState+' '+$playerStateSimple);
						if ($playerStateSimple=='PLAY') {
							let $iframe = document.getElementById($playerID);
							if ($iframe) {
								if ($iframe.id == ma_gdpr_youtube.fullscreen) {
									ma_gdpr_youtube.debug && console.log('MA GDPR YouTube Player Observer iframe is fullscreen, skipping.');
								} else {
									ma_gdpr_youtube.debug && console.log('MA GDPR YouTube Player Observer checking iframe offsetParent '+$iframe.offsetParent);
									ma_gdpr_youtube.debug && console.log('MA GDPR YouTube Player Observer player details ',$player);
									if ($iframe.offsetParent === null) {
										ma_gdpr_youtube.debug && console.log('MA GDPR YouTube Player Observer pauses hidden video '+$playerID);
										$player.player.pauseVideo();
									}
								}
							}
						}
					}
				},
				player_stop_all_except: function($apiID) {
					/* loop through players and pause all except this one */
					this.last_played = null;
					for (let $playerID in ma_gdpr_youtube.players) {
						ma_gdpr_youtube.debug && console.log('Checking existing player '+$playerID);
						if ($playerID != $apiID) {
							ma_gdpr_youtube.debug && console.log('Pausing existing player '+$playerID);
							let $player = ma_gdpr_youtube.players[$playerID];
							if (ma_gdpr_youtube.get_player_state($playerID,true)==='PLAY') {this.last_played = $playerID;}
							$player.player.pauseVideo();
						}
					}
				},
				player_currently_playing: function() {
					ma_gdpr_youtube.debug && console.log('player_currently_playing');
					for (let $playerID in this.players) {
						ma_gdpr_youtube.debug && console.log('  checking '+$playerID);
						if (this.get_player_state($playerID,true)==='PLAY') {
							ma_gdpr_youtube.debug && console.log(' -> currently playing '+$playerID);
							return $playerID; 
						}
					}
					ma_gdpr_youtube.debug && console.log(' -> none playing');
					return null;
				},

				click: function($target){
					/* get closest wrapper */
					let $wrapper = $target.closest('.ma-gdpr-youtube-wrapper'); 
					if (!$wrapper) return;
					if ($wrapper.getAttribute('data-new-window') == '1') {
						/* get the video id from the parent div's id attribute */
						let $videoID = $wrapper.getAttribute('data-video-id');
						/* check if additional yt parameters have been specified */
						let $yt_parameters = ma_gdpr_youtube.get_parameters_from_wrapper($wrapper);
						let $ytp = $yt_parameters ? '&' + (new URLSearchParams($yt_parameters).toString()) : '';
						window.open('https://www.youtube.com/watch?v='+$videoID+$ytp,'video-'+$videoID);
						return;
					}
					/* initialize the player observer */
					ma_gdpr_youtube.player_observer_init();
					/* Instantiate a new YT player (replacing wrapper), and play it when ready */
					/* check if YouTube API has already been loaded */
					if ( document.querySelectorAll('#ma-gdpr-youtube-player-api').length==0 ) {
						/* handler for YT API loaded */
						window.onYouTubeIframeAPIReady = function() {
							ma_gdpr_youtube.debug && console.log('YouTube API ready.');
							ma_gdpr_youtube.video_start($wrapper);
						};
						/* load the YouTube API */
						ma_gdpr_youtube.debug && console.log('Loading YouTube API...');
						const $script = document.createElement('script');
						$script.id = 'ma-gdpr-youtube-player-api';
						$script.src = 'https://www.youtube.com/iframe_api';
						document.body.appendChild($script);
					} else {
						/* YouTube API is already loaded */
						ma_gdpr_youtube.video_start($wrapper);
					}
				},

				get_parameters_from_wrapper: function($wrapper) {
					let $retval = null;
					let $yt_parameters = $wrapper.getAttribute('data-yt-parameters');
					if ($yt_parameters) {
						this.debug && console.log('yt-parameters RAW',$yt_parameters);
						$yt_parameters = atob($yt_parameters);
						this.debug && console.log('yt-parameters JSON',$yt_parameters);
						$yt_parameters = JSON.parse($yt_parameters);
						this.debug && console.log('yt-parameters Object',$yt_parameters);
						if ($yt_parameters.hasOwnProperty) {
							for (let $key in $yt_parameters) {
								if (!isNaN($yt_parameters[$key])) {$yt_parameters[$key] = parseInt($yt_parameters[$key]);}
							}
							$yt_parameters.enablejsapi = 1;
							$retval = $yt_parameters;
						}
					}
					return $retval;
				},

				video_start: function($wrapper) {
					/* get the video id from the parent div's id attribute */
					const $videoID = $wrapper.getAttribute('data-video-id');
					const $apiID = $wrapper.getAttribute('id');
					this.debug && console.log('Starting video '+$videoID+' from wrapper '+$apiID);
					/* get the inner dimensions of the wrapper */
					const $wrapperWidth = window.getComputedStyle($wrapper).width;
					const $wrapperHeight = window.getComputedStyle($wrapper).height;
					this.debug && console.log('Video WxH',[$wrapperWidth,$wrapperHeight]);
		
					/* remove styles from wrapper */
					$wrapper.style.height = $wrapperHeight;
					$wrapper.style.padding = 'unset';
		
					if (!YT) return; /* prevent JS errors in Oxygen Builder */
		
					const $apiConfig = {
						width: parseInt($wrapperWidth),
						height: parseInt($wrapperHeight),
						videoId: $videoID,
						host: 'https://www.youtube-nocookie.com',
						enablejsapi: 1,
						playerapiid: $apiID,
						rel: 0,
						events: {
							'onReady': function($event) { 
								ma_gdpr_youtube.debug && console.log('YouTube onReady.',{apiID:$apiID, event:$event});
								/* check if video is visible. The ready event is also fired if closing a modal on e.g. Oxygen, Bricks. 
								This prevents playing the video again in background */
								if ($event.target && $event.target.getIframe() && ($event.target.getIframe().offsetParent === null)) {return;}
								ma_gdpr_youtube.debug && console.log('Starting video.',{apiID:$apiID, event:$event});
								let $player = ma_gdpr_youtube.players[$apiID];
								$player && $player.player && (typeof $player.player.playVideo!=='undefined') && $player.player.playVideo();
							},
							'onStateChange': function($event) {
								ma_gdpr_youtube.debug && console.log('YouTube onStateChange '+$apiID+', '+$event.data);
								if ((YT == null) || (YT.PlayerState == null)) return;
								if ($event.data == YT.PlayerState.UNSTARTED) { 
									ma_gdpr_youtube.debug && console.log('YouTube video unstarted '+$apiID);
								}
								if ($event.data == YT.PlayerState.ENDED) { 
									ma_gdpr_youtube.debug && console.log('YouTube video ended '+$apiID);
								}
								if ($event.data == YT.PlayerState.PLAYING) { 
									ma_gdpr_youtube.debug && console.log('YouTube video playing '+$apiID);
									ma_gdpr_youtube.player_stop_all_except($apiID);
								}
								if ($event.data == YT.PlayerState.PAUSED) { 
									ma_gdpr_youtube.debug && console.log('YouTube video paused '+$apiID);
								}
								if ($event.data == YT.PlayerState.BUFFERING) { 
									ma_gdpr_youtube.debug && console.log('YouTube video buffering '+$apiID);
								}
								if ($event.data == YT.PlayerState.CUED) { 
									ma_gdpr_youtube.debug && console.log('YouTube video cued '+$apiID);
								}
							}
						}
					};
		
					/* check if additional yt parameters have been specified */
					let $yt_parameters = ma_gdpr_youtube.get_parameters_from_wrapper($wrapper);
					if ($yt_parameters) {
						ma_gdpr_youtube.debug && console.log('YouTube API custom parameters',{apiID:$apiID,parameters:$yt_parameters});
						$apiConfig.playerVars = $yt_parameters;
					}
					ma_gdpr_youtube.debug && console.log('apiConfig',$apiConfig);
					ma_gdpr_youtube.players[$apiID] = {player:new YT.Player($apiID, $apiConfig)};
				},

			};
			ma_gdpr_youtube.init();
		}

		/* Accessibility: Handle Space or Enter as play click */
		document.querySelectorAll('.ma-gdpr-youtube-button').forEach( ($elm) => {
			$elm.addEventListener('keyup', function($event) {
				if ($event.key==='Enter') {
					$event.preventDefault();
					$event.stopPropagation();
					$event.target.parentNode.querySelector('.ma-gdpr-youtube-thumbnail').click();
				}
			});
		});
		/* Accessibility: Handle Space or Enter on BODY for playing or stopped video */
		document.addEventListener('keyup', function($event) {
			if (($event.key==='Enter') && ($event.target?.tagName==='BODY')) {
				ma_gdpr_youtube.debug && console.log('Currently playing: '+ma_gdpr_youtube.player_currently_playing());
				ma_gdpr_youtube.debug && console.log('Last played: '+ma_gdpr_youtube.last_played);
				$event.preventDefault();
				$event.stopPropagation();
				if (ma_gdpr_youtube.player_currently_playing()) {
					ma_gdpr_youtube.debug && console.log('Stopping all');
					ma_gdpr_youtube.player_stop_all_except('');
				} else {
					ma_gdpr_youtube.debug && console.log('Start playing '+ma_gdpr_youtube.last_played);
					let $player = ma_gdpr_youtube.players[ma_gdpr_youtube.last_played];
					$player && $player.player && (typeof $player.player.playVideo!=='undefined') && $player.player.playVideo();
				}
			}
		});
		</script>
END_OF_SCRIPT;
		if ($this->footercode_minimize) { 
			$script = preg_replace('/\/\*.*?\*\//s','',$script); 
			$script = preg_replace('/\r?\n */','',$script); 
			$script = preg_replace('/\t/','',$script); 
		}
		echo $script;

		// emit play button svg symbol
		$symbol = <<<'END_OF_SYMBOL'
			<svg id="ma-gdpr-youtube-symbols" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
						aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;">
				<defs>
					<symbol id="ma-gdpr-youtube-play-button-youtube" viewBox="0 0 500 350" >
						<path fill="currentColor" d="M500,74.767C500,33.472,466.55,0,425.277,0 H74.722C33.45,0,0,33.472,0,74.767v200.467C0,316.527,33.45,350,74.722,350h350.555C466.55,350,500,316.527,500,275.233V74.767z  M200,259.578v-188.3l142.789,94.15L200,259.578z"/>
						<path fill="white" d="M199.928,71.057l0.074,188.537l142.98-94.182 L199.928,71.057z"/>
					</symbol>
					<symbol id="ma-gdpr-youtube-play-button-circle" viewBox="0 0 24 28" >
						<path fill="currentColor" d="M12 2c6.625 0 12 5.375 12 12s-5.375 12-12 12-12-5.375-12-12 5.375-12 12-12zM18 14.859c0.313-0.172 0.5-0.5 0.5-0.859s-0.187-0.688-0.5-0.859l-8.5-5c-0.297-0.187-0.688-0.187-1-0.016-0.313 0.187-0.5 0.516-0.5 0.875v10c0 0.359 0.187 0.688 0.5 0.875 0.156 0.078 0.328 0.125 0.5 0.125s0.344-0.047 0.5-0.141z"/>
					</symbol>
					<symbol id="ma-gdpr-youtube-play-button-circle-o" viewBox="0 0 24 28" >
						<path fill="currentColor" d="M18.5 14c0 0.359-0.187 0.688-0.5 0.859l-8.5 5c-0.156 0.094-0.328 0.141-0.5 0.141s-0.344-0.047-0.5-0.125c-0.313-0.187-0.5-0.516-0.5-0.875v-10c0-0.359 0.187-0.688 0.5-0.875 0.313-0.172 0.703-0.172 1 0.016l8.5 5c0.313 0.172 0.5 0.5 0.5 0.859zM20.5 14c0-4.688-3.813-8.5-8.5-8.5s-8.5 3.813-8.5 8.5 3.813 8.5 8.5 8.5 8.5-3.813 8.5-8.5zM24 14c0 6.625-5.375 12-12 12s-12-5.375-12-12 5.375-12 12-12 12 5.375 12 12z"/>
					</symbol>
					<symbol id="ma-gdpr-youtube-play-button-play" viewBox="0 0 24 28" >
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
		$type = basename(__FILE__) == 'ma-gdpr-youtube.php' ? 'Plugin' : 'Code Snippet';
		$retval =(object)[
			'type'		=> $type,
			'name'		=> self::TITLE,
			'version'	=> self::VERSION,
			'combined'	=> sprintf('%s "%s" %s', $type, self::TITLE, self::VERSION),
		];
		return $retval;;
	}


}

//===================================================================================================================
// Initialize
new MA_GDPR_YouTube();
	
endif;

