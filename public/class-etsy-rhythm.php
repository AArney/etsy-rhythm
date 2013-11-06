<?php
/**
 * Plugin Name.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * TODO: Rename this class to a proper name for your plugin.
 *
 * @package Plugin_Name
 * @author  Your Name <email@example.com>
 */
class Etsy_Rhythm {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * TODO - Rename "plugin-name" to the name your your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'etsy-rhythm';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	
	/**
	* Define logfile name
	*
	* @since 	1.0.1
	*/
	const DEFAULT_LOG = 'logs/error_log.txt';
	
	
	
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_shortcode( 'etsy-rhythm', array( $this, 'shortcode' ) );
		

	}

	
	/**
	* Sends arbitrary request to test validity of key
	*
	* @since	1.0.1
	*
	* @return 	Response
	*/
	private function testAPIKey() {
		$response = $this->api_request( 'listings/active', '&limit=1&offset=0', 1 );
		$data = json_decode( $response );
		return $data;
	}
	
	
	
	/**
	* Calls etsy API 
	*
	* @since	1.0.1
	*
	* @param string 
	* @param string				
	*/
	private function api_request( $etsy_request, $args = NULL, $noDebug = NULL ) {
	
		// Use the static method getOptions to import user settings
		$options = Etsy_Rhythm_Admin::getOptions();
		
		// Grab api key from the settings array
		$apiKey = $options['api_key'];
		
		// Create our JSON query string
		$url = "https://openapi.etsy.com/v2/$etsy_request?api_key=" . $apiKey . $args;
		
		// Request the body
		$request = wp_remote_request( $url );
		
		
		if ( !is_wp_error( $request ) ) {
			
			return $request['body'];
			
		} else {
			$this->logError( $request );
			exit;
		}
	}
	
	/**
	* Performs the shortcode function
	*
	* @since 	1.0.0
	* 
	* @return	All listing items
	*/
	public function shortcode( $atts ) {
		
		// Extract the attributes
		extract( shortcode_atts( array(
			'shop_id'		=>	'101010',
			'shop_section'	=>	'0',
			'quantity'		=>	'25' ), 
			$atts ) );
			
		// Pass the attributes to the postListing function and render the items
		return $this->postListings( $shop_id, $shop_section, $quantity );
	}
	
	
	
	
	/**
	* Grabs the active listings requested 
	*
	* @since	1.0.1
	*
	* @param	string		The shop id to be parsed
	* @param	string		The shop section 
	* @param	string		The number of items to return
	*
	* @return	string		Imploded array containing the markup
	*/
	public function postListings( $shop_id, $shop_section, $quantity) {    
		
		// Grab listings with shortcode atts as arguments
		$listings = $this->getActiveListings( $shop_id, $shop_section, $quantity );

		// If grabbing listings was successful
		if ( !is_wp_error( $listings ) ) {
			
			// Create an array to hold all of our markup
			$html[] = '<table class="etsy-shop-listing-table"><tr>';
		
			// Used to track row widths
			$itemCount = 1;
								
			// Attach new window code to link if selected
			if ( get_option( 'etsy_shop_target_blank' ) ) {
				$target = '_blank';
			} else {
				$target = '_self';
			}
								
				
			// Going to grab the specified number of items per row via the settings page
			$options = Etsy_Rhythm_Admin::getOptions();
			$userRows = $options['userRows'];
							
			// Loop through each listing and send results through the itemListing function
			foreach ( $listings->results as $result ) {

				$item_html = $this->generateItemListing( $result->listing_id, $result->title,  $result->state, $result->price, $result->currency_code, $result->quantity, $result->url, $result->Images[0]->url_170x135, $target );

					// A just in case measure to ensure we don't create blank tables		
					if ( $item_html !== false ) {
						
                    	$html[] = '<td class="etsy-shop-listing">'.$item_html.'</td>';
						
						// Increment item count
                   		$itemCount++;
						
						// Check against user requested row count and if so create a new row
						if ( $itemCount == $userRows ) {
							$html[] = '</tr><tr>';
							$itemCount = 1;
						}
					}
			}
		
		$html[] = '</tr></table>';
						  
		} else {
			$this->logError( $listings );
		}
	
		return implode("\n", $html);
	}

	
	public function getActiveListings( $shop_id, $shop_section, $quantity) {
		
		$options = Etsy_Rhythm_Admin::getOptions();

		$quantity = $options['quantity'];
		
			$etsy_cache_file = dirname( __FILE__ ).'/tmp/'.$shop_id.'-'.$shop_section.'_cache.json';
			
			// if no cache file exist
			if (!file_exists( $etsy_cache_file ) or ( time() - filemtime( $etsy_cache_file ) >= ETSY_SHOP_CACHE_LIFE ) ) {
				$reponse = $this->api_request( "shops/$shop_id/sections/$shop_section/listings/active", "&limit=$quantity&includes=Images" );
				if ( !is_wp_error( $reponse ) ) {
					// if request OK
					$tmp_file = $etsy_cache_file.rand().'.tmp';
					file_put_contents( $tmp_file, $reponse );
					rename( $tmp_file, $etsy_cache_file );
				} else {
					// return WP_Error
					return $reponse;
				}
			} else {
				// read cache file
				$reponse = file_get_contents( $etsy_cache_file );
			}
			
			$data = json_decode( $reponse );	
		
		return $data;
	}

	public function generateItemListing($listing_id, $title, $state, $price, $currency_code, $item_quantity, $url, $url_170x135, $target) {
		
		$options = Etsy_Rhythm_Admin::getOptions();
		$title_length = $options['title_length'];
		
		// Trim Title length based on user preference
		if ( strlen( $title ) > $title_length ) {
			$title = substr( $title, 0, $tile_length );
			$title .= "...";
		}
		
		// if the Shop Item is active
		if ( $state == 'active' ) {
			$state = __( 'Available', 'etsyshoprhythm' );
			
			$script_tags =  '
				<div class="etsy-shop-listing-card" id="' . $listing_id . '" style="text-align: center;">
					<a title="' . $title . '" href="' . $url . '" target="' . $target . '" class="etsy-shop-listing-thumb">
						<img alt="' . $title . '" src="' . $url_170x135 . '">          
					</a>
					<div class="etsy-shop-listing-detail">
						<p class="etsy-shop-listing-title">
							<a title="' . $title . '" href="' . $url . '" target="' . $target . '">'.$title.'</a>
						</p>
						<p class="etsy-shop-listing-availability">
							<a title="' . $title . '" href="' . $url . '" target="' . $target . '">'.$state.'</a>
						</p>
					</div>
					<p class="etsy-shop-listing-price">$'.$price.' <span class="etsy-shop-currency-code">'.$currency_code.'</span></p>
				</div>'; 
				
			return $script_tags;
		} else {
			return false;
		}
	}
	public function getShopSection( $shop_id, $shop_section) {
		$reponse = $this->api_request( "shops/$shop_id/sections/$shop_section", NULL , 1 );
		if ( !is_wp_error( $reponse ) ) {
			$data = json_decode( $reponse );
		} else {
			// return WP_Error
			return $reponse;
		}
		
		return $data;
	}
	
	
	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 *@return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}


	/**
	* Append error to a logfile 
	*
	* @since 	1.0.1
	*
	* @param	String		Body of error message
	* @param	optional string	
	*/
	public static function logError( $error, $logfile='' ) {
		
		if ( $logfile == '' ) {
			if ( defined( DEFAULT_LOG ) == TRUE ) {
				$logfile = DEFAULT_LOG;
			} else {
				$logfile = plugins_url('logs/error_log.txt');
			}
		}
		
		// Get time of error
		if ( ( $time = $_SERVER['REQUEST_TIME'] ) == '' ) {
			$time = time();
		}
		
		// Format day time
		$date = date('Y-m-d H:i:s', $time);
		
		// Append to log file
		if ( $fd = @fopen( $logfile, "a" ) ) {
			$result = fputcsv( $fd, array( $date, $message ) );
			fclose( $fd );
			
			if ( $result > 0 ) {
				return array( status => true );
			} else {
				return array( 	status	=> 	false,
								message	=>	"Unable to write to $logfile."
							);
			}
		} else {
			return array(	status	=>	false,
							message	=> "Unable to open log $logfile."
						);
		}
	}

}