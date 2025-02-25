<?php
/**
 * Thinkery
 *
 * @package Thinkery
 */

/**
 * This is the class for the Thinkery Plugin.
 *
 * @package Thinkery
 * @author Alex Kirk
 */
class Thinkery {
	/**
	 * Initialize the plugin
	 */
	public static function init() {
		static::get_instance();
	}

	/**
	 * A reference to the Thinkery_Admin object.
	 *
	 * @var Thinkery_Admin
	 */
	public $admin;

	/**
	 * A reference to the Thinkery_Frontend object.
	 *
	 * @var Thinkery_Frontend
	 */
	public $frontend;

	/**
	 * A reference to the Thinkery_Things object.
	 *
	 * @var Thinkery_Things
	 */
	public $things;

	/**
	 * Get the class singleton
	 *
	 * @return Thinkery A class instance.
	 */
	public static function get_instance() {
		static $instance;
		if ( ! isset( $instance ) ) {
			$self     = get_called_class();
			$instance = new $self();
		}
		return $instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->admin = new Thinkery_Admin( $this );
		$this->frontend = new Thinkery_Frontend( $this );
		$this->importer = new Thinkery_Importer( $this );
		$this->rest = new Thinkery_Rest( $this );
		$this->things = new Thinkery_Things( $this );
		$this->register_hooks();
	}

	/**
	 * Register the WordPress hooks
	 */
	private function register_hooks() {
		add_filter( 'thinkery_template_path', array( $this, 'thinkery_template_path' ) );
	}

	/**
	 * Determine whether we are on the /thinkery/ page or a subpage.
	 *
	 * @return boolean Whether we are on a thinkery page URL.
	 */
	public static function on_frontend() {
		global $wp_query;

		if ( ! isset( $wp_query ) || ! isset( $wp_query->query['pagename'] ) ) {
			return false;
		}

		if ( isset( $_GET['public'] ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_posts' ) || ( is_multisite() && is_super_admin( get_current_user_id() ) ) ) {
			return false;
		}

		$pagename_parts = explode( '/', trim( $wp_query->query['pagename'], '/' ) );
		return count( $pagename_parts ) > 0 && 'thinkery' === $pagename_parts[0];
	}


	/**
	 * Add the default path to the template file.
	 *
	 * @param string $template_file The relative file path of the template to load.
	 */
	public static function thinkery_template_path( $template_file ) {
		return __DIR__ . '/templates/' . $template_file;
	}
}
