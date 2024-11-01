<?php
/**
 * The main class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class.
 */
final class WPDL {

	/**
	 * Version.
	 */
	public $version = '1.0.0';

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Auto-load in-accessible properties on demand.
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'mailer' ), true ) ) {
			return $this->$key();
		}
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'wpdl_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 */
	public function init_hooks() {
		register_activation_hook( WPDL_PLUGIN_FILE, array( 'WPDL_Install', 'install' ) );
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( 'WPDL_Shortcodes', 'init' ) );
	}

	/**
	 * Init when WordPress Initialises.
	 */
	public function init() {
		// Before init action.
		do_action( 'before_wpdl_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();

		// Init action.
		do_action( 'wpdl_init' );
	}

	/**
	 * Define Constants.
	 */
	public function define_constants() {
		$this->define( 'WPDL_ABSPATH', dirname( WPDL_PLUGIN_FILE ) . '/' );
		$this->define( 'WPDL_PLUGIN_BASENAME', plugin_basename( WPDL_PLUGIN_FILE ) );
		$this->define( 'WPDL_VERSION', $this->version );
		$this->define( 'WPDL_TEMPLATE_DEBUG_MODE', false );
	}

	/**
	 * Define constant if not already set.
	 */
	public function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		/**
		 * Class autoloader.
		 */
		include_once WPDL_ABSPATH . 'includes/class-wpdl-autoloader.php';

		/**
		 * Abstract classes.
		 */
		include_once WPDL_ABSPATH . 'includes/abstracts/abstract-wpdl-settings-api.php';

		/**
		 * Core classes.
		 */
		include_once WPDL_ABSPATH . 'includes/wpdl-core-functions.php';
		include_once WPDL_ABSPATH . 'includes/class-wpdl-ajax.php';
		include_once WPDL_ABSPATH . 'includes/class-wpdl-post-types.php';
		include_once WPDL_ABSPATH . 'includes/class-wpdl-install.php';
		include_once WPDL_ABSPATH . 'includes/class-wpdl-comments.php';
		include_once WPDL_ABSPATH . 'includes/class-wpdl-background-emailer.php';

		if ( $this->is_request( 'admin' ) ) {
			include_once WPDL_ABSPATH . 'includes/admin/class-wpdl-admin.php';
		}

		// Front-end use only.
		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}

	}

	/**
	 * Include required frontend files.
	 */
	public function frontend_includes() {
		include_once WPDL_ABSPATH . 'includes/wpdl-notice-functions.php';
		include_once WPDL_ABSPATH . 'includes/class-wpdl-frontend-scripts.php';
		include_once WPDL_ABSPATH . 'includes/class-wpdl-form-handler.php';
	}

	/**
	 * Function used to Init Template Functions
	 */
	public function include_template_functions() {

	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/wp-desklite/wp-desklite-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/wp-desklite-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'wp-desklite' );

		unload_textdomain( 'wp-desklite' );
		load_textdomain( 'wp-desklite', WP_LANG_DIR . '/wp-desklite/wp-desklite-' . $locale . '.mo' );
		load_plugin_textdomain( 'wp-desklite', false, plugin_basename( dirname( WPDL_PLUGIN_FILE ) ) . '/i18n/languages' );
	}

	/**
	 * Get the plugin url.
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WPDL_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WPDL_PLUGIN_FILE ) );
	}

	/**
	 * Get the template path.
	 */
	public function template_path() {
		return apply_filters( 'wpdl_template_path', 'wp-desklite/' );
	}

	/**
	 * Get Ajax URL.
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * Email class.
	 */
	public function mailer() {
		return WPDL_Emails::instance();
	}

}