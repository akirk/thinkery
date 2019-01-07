<?php
/**
 * Thinkery Admin
 *
 * This contains the functions for the admin section.
 *
 * @package Thinkery
 */

/**
 * This is the class for the Thinkery Plugin Admin section.
 *
 *
 * @package Thinkery
 * @author Alex Kirk
 */
class Thinkery_Admin {
	/**
	 * Contains a reference to the Thinkery class.
	 *
	 * @var Thinkery
	 */
	private $thinkery;

	/**
	 * Constructor
	 *
	 * @param Thinkery $thinkery A reference to the Thinkery object.
	 */
	public function __construct( Thinkery $thinkery ) {
		$this->thinkery = $thinkery;
		$this->register_hooks();
	}

	/**
	 * Register the WordPress hooks
	 */
	private function register_hooks() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_thinkery_menu' ), 39 );
		add_action( 'tool_box', array( $this, 'toolbox_bookmarklets' ) );
	}

	/**
	 * Registers the admin menus
	 */
	public function register_admin_menu() {
		$menu_title = __( 'Thinkery', 'thinkery' );
		$page_type = sanitize_title( $menu_title );
		add_submenu_page( 'edit.php?post_type=' . Thinkery_Things::CPT, __( 'Settings' ), __( 'Settings' ), 'manage_options', 'thinkery-settings', array( $this, 'render_admin_settings' ) );
	}

	/**
	 * Check access for the Thinkery Admin settings page
	 */
	public function check_admin_settings() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to change the settings.', 'thinkery' ) );
		}
	}

	/**
	 * Process the Thinkery Admin settings page
	 */
	public function process_admin_settings() {
		$this->check_admin_settings();

		if ( empty( $_POST ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'thinkery-settings' ) ) {
			return;
		}

		if ( isset( $_GET['wp_http_referer'] ) ) {
			wp_safe_redirect( $_GET['wp_http_referer'] );
		} else {
			wp_safe_redirect( add_query_arg( 'updated', '1', remove_query_arg( array( 'wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		}
		exit;
	}

	/**
	 * Render the Thinkery Admin settings page
	 */
	public function render_admin_settings() {
		$this->check_admin_settings();

		$current_user = wp_get_current_user();

		?>
		<h1><?php esc_html_e( 'Thinkery Settings', 'thinkery' ); ?></h1>
		<?php
		if ( isset( $_GET['updated'] ) ) {
			?>
			<div id="message" class="updated notice is-dismissible"><p>
				<?php
				esc_html_e( 'Your settings were updated.', 'thinkery' );
				?>
			</p></div>
			<?php
		}

		include apply_filters( 'thinkery_template_path', 'admin/settings.php' );
	}

	/**
	 * Add a Thinkery menu to the admin bar
	 *
	 * @param  WP_Admin_Bar $wp_menu The admin bar to modify.
	 */
	public function admin_bar_thinkery_menu( WP_Admin_Bar $wp_menu ) {
		$thinkery_url   = site_url( '/thinkery/' );
		$thinkery_title = 'Thinkery';

		if ( current_user_can( 'administrator' ) ) {
			$wp_menu->add_node(
				array(
					'id'     => 'thinkery',
					'parent' => '',
					'title'  => '<span class="ab-icon dashicons dashicons-groups"></span> ' . esc_html( $thinkery_title ),
					'href'   => $thinkery_url,
				)
			);
		}
	}


	/**
	 * Display the Bookmarklets at the Tools section of wp-admin
	 */
	public function toolbox_bookmarklets() {
		?>
		<div class="card">
			<h2 class="title"><?php _e( 'Thinkery', 'thinkery' ); ?></h2>

			<p><?php _e( "Drag the bookmarklets to your bookmarks bar and click it when you're on a site around the web for the appropriate action.", 'thinkery' ); ?></p>
			<p>
				<a onclick="alert( '<?php echo esc_attr( __( 'Please drag this to your bookmarks bar.', 'thinkery' ) ); ?>' );return false" href="javascript:void(location.href='<?php echo esc_attr( self_admin_url( 'admin-ajax.php?action=thinkery_save_url&url=' ) ); ?>'+encodeURIComponent(location.href))" style="display: inline-block; padding: .5em; border: 1px solid #999; border-radius: 4px; background-color: #ddd; text-decoration: none; margin-right: 3em"><?php echo esc_html_e( 'Save Article', 'thinkery' ); ?></a>
			</p>
			</div>
		</div>
		<?php
	}
}
