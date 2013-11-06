<?php
/**
 * Etsy Rhythm
 *
 * @package   Etsy_Rhythm
 * @author    Aaron Arney <aaron.arney@ocular-rhythm.com>
 * @license   GPL-2.0+
 * @link      http://www.ocular-rhythm.com
 * @copyright 2013 Aaron Arney
 */

/**
 * Etsy Rhythm Class
 *
 * @package Etsy Rhythm
 * @author  Aaron Arney <aaron.arney@ocular-rhythm.com>
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
	* @return	array	All listing items
	*/
	public function shortcode( $atts ) {
		
		// Extract the attributes
		extract( shortcode_atts( array(
			'shop_id'		=>	'',
			'shop_section'	=>	'',
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
			$user_rows = $options['user_rows'];
							
			// Loop through each listing and send results through the itemListing function
			foreach ( $listings->results as $result ) {

				$item_html = $this->generateItemListing( $result->listing_id, 
															$result->title, 			/* Title of Item */
															$result->description, 		/* Description of item */
															$result->state, 			/* Active or sold */
															$result->price, 			/* Price of item */
															$result->currency_code, 	/* Currency Code - USD */
															$result->quantity, 			/* Quantity of item in stock */
															$result->materials,			/* Materials used for item */
															$result->url, 				/* Url to the item */
															$result->who_made,			/* Who made the item */
															$result->when_made,			/* When the item was made */
															$result->Images[0]->url_170x135, /* Image of item */
															$target 					/* Target blank or self */			
														);

					// A just in case measure to ensure we don't create blank tables		
					if ( $item_html !== false ) {
						
                    	$html[] = '<td class="etsy-shop-listing">'.$item_html.'</td>';
						
						// Increment item count
                   		$itemCount++;
						
						// Check against user requested row count and if so create a new row
						if ( $itemCount == $user_rows ) {
							$html[] = '</tr><tr>';
							$itemCount = 1;
						}
					}
			}
		
		// Close the listings table
		$html[] = '</tr></table>';
						  
		} else {
			// If Error occured while retrieving listings
			$this->logError( $listings );
		}
	
		// Return array imploded into single string, delimited by newlines
		return implode("\n", $html);
	} 

	
	
	/*
	* Retrieves listings and caches them into a json file 
	*
	* @since	1.0.1
	*
	* @param	string		The shop id	
	* @param	string		The section
	* @param	string		The number of items to return
	*/
	public function getActiveListings( $shop_id, $shop_section, $quantity) {
		
		// Let's grab the number of items to return, by default it will return 25
		$options = Etsy_Rhythm_Admin::getOptions();
		$quantity = $options['quantity'];
		
		// I'm also going to grab the cache life setting
		$cache_life = $options['cache_life'];
		
		
			// Set up the cache file 
			$etsy_cache_file = dirname( __FILE__ ).'/tmp/'.$shop_id.'-'.$shop_section.'_cache.json';
			
			// This will check for an existing file, or if the cache file is older than the user set cache life
			if (!file_exists( $etsy_cache_file ) or ( time() - filemtime( $etsy_cache_file ) >= $cache_life ) ) {
				
				// This is the all important query string
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


	/**
	* Generates each item
	*
	* @since 	1.0.1
	*
	* @param	string		$listing_id		The id number of the item
	* @param	string		$title			The title of the item
	* @param	string		$description	A description of the item
	* @param	string		$state			Whether the item is active or inactive
	* @param	string		$price			The price of the item
	* @param	string		$currency_code	The currency code of the item
	* @param	string		$item_quantity	How many of the item is available
	* @param	string		$materials		The materials used to create the item
	* @param	string		$url			The url of the item
	* @param	string		$who_made		Who created the item
	* @param	string		$when_made		When the item was made
	* @param	string		$url_170x135	The thumbnail of the 
	* @param	string		$target			The target of the link - new window
	*
	* @return	string		$data			A string containing the markup for the item
	*/
	public function generateItemListing($listing_id, $title, $description, $state, $price, $currency_code, $item_quantity, $materials, $url, $who_made, $when_made, $url_170x135, $target) {
		
		// Grab the title length from the user options
		$options = Etsy_Rhythm_Admin::getOptions();
		$title_length = $options['title_length'];
		
		// Let's also grab the currency code
	
		// Trim Title length based on user preference
		if ( strlen( $title ) > $title_length ) {
			$title = substr( $title, 0, $tile_length );
			$title .= "...";
		}
		
		// if the Shop Item is active
		if ( $state == 'active' ) {
			$state = __( 'Available', 'etsyshoprhythm' );
			
			$script_tags =  '
				<div class="etsy-item-container" id="' . $listing_id . '">
					<a title="' . $title . '" href="' . $url . '" target="' . $target . '" class="etsy-item-thumbnail-link">
						<img alt="' . $title . '" src="' . $url_170x135 . '" class="etsy-item-thumbnail">          
					</a>
					
					<p class="etsy-item-title">
						<a title="' . $title . '" href="' . $url . '" target="' . $target . '">'.$title.'</a>
					</p>
					<p class="etsy-item-availability">
						<a title="' . $title . '" href="' . $url . '" target="' . $target . '">'.$state.'</a>
					</p>
					
					<p class="etsy-item-price">$'.$price.' <span class="etsy-item-currency-code">'.$currency_code.'</span></p>'; 
				
			// Let's check to see if the user wants to include materials
			if ( $options['materials'] ) {
				if ( $materials !== '' ) {
					$materials = implode(",", $materials);
					$script_tags .= '<p class="etsy-materials">'.$materials.'</p>';
				} else {
					$script_tags .= '<p class="etsy-materials">No materials mentioned</p>';
				}
			}
			
			// Now check for who made and when made
			if ( $options['who_made'] ) {
				if ( $who_made !== '' ) {
					if ( $who_made === 'i_did' ) {
						$who_made = "I did.";
					}
					$script_tags .= '<p class="etsy-who-made">'.$who_made.'</p>';
				} else {
					$script_tags .= '<p class="etsy-who-made">No creator specified</p>';
				}
			}

			if ( $options['when_made'] ) {
				if ( $when_made !== '' ) {
					$script_tags .= '<p class="etsy-when-made">'.$when_made.'</p>';
				} else {
					$script_tags .= '<p class="etsy-when-made">No date specified</p>';
				}
			}
			
			$script_tags .= '</div>';
				
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
	 * @return    Plugin slug variable.
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