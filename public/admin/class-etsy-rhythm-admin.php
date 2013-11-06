<?php
/**
 * Plugin Name.
 *
 * @package   Plugin_Name_Admin
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-plugin-name.php`
 *
 * TODO: Rename this class to a proper name for your plugin.
 *
 * @package Plugin_Name_Admin
 * @author  Your Name <email@example.com>
 */
class Etsy_Rhythm_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Call $plugin_slug from public plugin class.
		$plugin = Etsy_Rhythm::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		// Create Default Option Settings
		register_activation_hook(__FILE__, array( $this, 'add_option_defaults'));
		
		// Hook to delete all plugin information if deactivated and deleted.
		register_uninstall_hook(__FILE__, array( $this, 'delete_plugin_options'));
		
		// Admin Initialize
		add_action('admin_init', array( $this, 'admin_init') );

	}
	
	
	
	/**
	* Set default settings for our options
	*
	* @since 	1.0.0
	*/
	public function add_option_defaults() {
		$tmp = get_option('etsy_rhythm_settings');
		if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
			delete_option('etsy_rhythm_settings'); 
			$arr = array(	
							"api_key"		=>		"000",
							"target_blank"	=>		true,
							"quantity"		=>		25,
							"cache_life"	=>		26000,
							"reset_cache"	=>		false,
							"title_length"	=>		25,
							"language"		=>		"en",
							"user_rows"		=>		4,
							"materials"		=>		false,
							"who_made"		=>		false,
							"when_made"		=>		false,
							"image_size"	=>		"75x75",
							);
			update_option('etsy_rhythm_settings', $arr);
		}
	}

	
	public static function getOptions() {
		$options = get_option( 'etsy_rhythm_settings' );
		return $options;
	}
		
		
	
	/**
	* Register our options with WP
	*
	* @since	1.0.0
	*/
	public function admin_init(){
		register_setting( 'etsy_rhythm_plugin_options', 'etsy_rhythm_settings', array( $this, 'validate_options' ));
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
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Etsy_Rhythm::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Etsy_Rhythm::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Etsy Rhythm Options', $this->plugin_slug ),
			__( 'Settings', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'render_form' )
		);

	}

	
	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	
	/**
	* Render the options page
	*
	* @since	1.0.0
	*/
	public function render_form() {
		include_once( 'views/admin.php' );
	}


	/**
	* Sanitize and validate input
	*
	* @since	1.0.0
	* 
	* @param 	array		
	*
	* @return	array
	*/
	public function validate_options($input) {
		$input['api_key'] 		= 	wp_filter_nohtml_kses( preg_replace( '/[^a-z0-9]/', '', $input['api_key'] ) );
		$input['target_blank'] 	= 	wp_filter_nohtml_kses( $input['target_blank'] ); 
		$input['quantity'] 		= 	wp_filter_nohtml_kses( absint($input['quantity'] ) );
		$input['cache_life'] 	= 	wp_filter_nohtml_kses( absint($input['cache_life'] ) );
		$input['reset_cache'] 	= 	wp_filter_nohtml_kses( $input['reset_cache'] );
		$input['title_length'] 	= 	wp_filter_nohtml_kses( absint($input['title_length'] ) );
		$input['language'] 		= 	wp_filter_nohtml_kses( $input['language'] );
		$input['user_rows'] 	= 	wp_filter_nohtml_kses( $input['user_rows'] );
		$input['materials'] 	= 	wp_filter_nohtml_kses( $input['materials'] );
		$input['who_made'] 		= 	wp_filter_nohtml_kses( $input['who_made'] );
		$input['when_made'] 	= 	wp_filter_nohtml_kses( $input['when_made'] );
		$input['image_size'] 	= 	wp_filter_nohtml_kses( $input['image_size'] );
		return $input;
	}

}
