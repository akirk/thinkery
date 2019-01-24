<?php
/**
 * Thinkery Frontend
 *
 * @package Thinkery
 */

/**
 * This is the class for the /thinkery/ part of the Thinkery Plugin.
 *
 * @since 0.6
 *
 * @package Thinkery
 * @author Alex Kirk
 */
class Thinkery_Frontend {
	/**
	 * Contains a reference to the Thinkery class.
	 *
	 * @var Thinkery
	 */
	private $thinkery;

	/**
	 * Whether we are on the /thinkery page.
	 *
	 * @var boolean
	 */
	private $on_thinkery_frontend = false;

	/**
	 * Whether an tag is being displayed
	 *
	 * @var string|false
	 */
	public $tag = false;

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
		add_filter( 'pre_get_posts', array( $this, 'thinkery_posts_query' ), 2 );
		add_filter( 'post_type_link', array( $this, 'thinkery_post_link' ), 10, 4 );
		add_filter( 'private_title_format', array( $this, 'private_title_format' ) );
		add_filter( 'template_include', array( $this, 'template_override' ) );
		add_filter( 'init', array( $this, 'register_thinkery_sidebar' ) );
		add_action( 'wp_ajax_thinkery_publish', array( $this, 'frontend_publish_post' ) );
		add_action( 'wp_ajax_trash_thinkery_post', array( $this, 'trash_thinkery_post' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 99 );
		add_filter( 'body_class', array( $this, 'add_body_class' ) );
	}

	/**
	 * Registers the sidebar for the /thinkery page.
	 */
	public function register_thinkery_sidebar() {
		register_sidebar(
			array(
				'name'          => 'Thinkery Topbar',
				'id'            => 'thinkery-topbar',
				'before_widget' => '<div class="thinkery-main-widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h1>',
				'after_title'   => '</h1>',
			)
		);
		register_sidebar(
			array(
				'name'          => 'Thinkery Sidebar',
				'id'            => 'thinkery-sidebar',
				'before_widget' => '<div class="thinkery-widget">',
				'after_widget'  => '</div>',
				'before_title'  => '<h5>',
				'after_title'   => '</h5>',
			)
		);
	}

	/**
	 * Reference our script for the /thinkery page
	 */
	public function enqueue_scripts() {
		if ( is_user_logged_in() ) {
			if ( $this->is_thinkery_frontend() ) {
				wp_enqueue_script(
					'thinkery',
					plugins_url( 'thinkery.js', __FILE__ ),
					array(
						'jquery',
						'jquery.cookie',
						'jquery.highlight',
						'jquery.tiptip',
						'jquery.ui-1.10.0.custom',
						'jquery.thinkeryAutocompleter',
					),
					'1.0',
					true
				);
				wp_enqueue_script( 'jquery.cookie', plugins_url( 'js/jquery.cookie.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_script( 'jquery.highlight', plugins_url( 'js/jquery.highlight.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_script( 'jquery.ui-1.10.0.custom', plugins_url( 'js/jquery.ui-1.10.0.custom.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_script( 'jquery.tiptip', plugins_url( 'js/jquery.tiptip.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_script( 'jquery.thinkeryAutocompleter', plugins_url( 'js/jquery.thinkeryAutocompleter.js', __FILE__ ), array( 'jquery' ) );
				$variables = array(
					'ajax_url'    => admin_url( 'admin-ajax.php' ),
					'spinner_url' => admin_url( 'images/wpspin_light.gif' ),
					'text_undo'   => __( 'Undo' ),
				);
				wp_localize_script( 'thinkery', 'wp_thinkery', $variables );
				wp_enqueue_style( 'thinkery', plugins_url( 'css/thinkery.css', __FILE__ ) );
			}
			wp_enqueue_style( 'thinkery-admin', plugins_url( 'css/thinkery-admin.css', __FILE__ ) );
		}
	}

	/**
	 * Add a CSS class to the body
	 *
	 * @param array $classes The existing CSS classes.
	 * @return array The modified CSS classes.
	 */
	public function add_body_class( $classes ) {
		if ( $this->on_thinkery_frontend ) {
			$classes[] = 'thinkery-page';
		}

		return $classes;
	}

	/**
	 * The Ajax function to be called upon posting from /thinkery
	 */
	public function frontend_publish_post() {
		if ( wp_verify_nonce( $_POST['_wpnonce'], 'thinkery_publish' ) ) {
			$post_id = wp_insert_post(
				array(
					'post_type'    => 'post',
					'post_title'   => $_POST['title'],
					'post_content' => $_POST['content'],
					'post_status'  => $_POST['status'],
				)
			);
			$result  = is_wp_error( $post_id ) ? 'error' : 'success';
			if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' ) {
				echo $result;
			} else {
				wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
				exit;
			}
		}
	}

	/**
	 * Load the template for /thinkery
	 *
	 * @param  string $template The original template intended to load.
	 * @return string The new template to be loaded.
	 */
	public function template_override( $template ) {
		if ( ! $this->is_thinkery_frontend() ) {
			return $template;
		}

		if ( current_user_can( 'edit_posts' ) ) {
			return apply_filters( 'thinkery_template_path', 'thinkery/things.php' );
		}

		return $template;
	}

	/**
	 * Link thinkery posts to the remote site.
	 *
	 * @param string  $post_link The post's permalink.
	 * @param WP_Post $post      The post in question.
	 * @param bool    $leavename Whether to keep the post name.
	 * @param bool    $sample    Is it a sample permalink.
	 * @reeturn string The overriden post link.
	 */
	public function thinkery_post_link( $post_link, WP_Post $post, $leavename, $sample ) {
		if ( Thinkery_Things::CPT === $post->post_type ) {
			return get_the_guid( $post );
		}
		return $post_link;
	}

	/**
	 * Remove the Private: when sending a private feed.
	 *
	 * @param  string $title_format The title format for a private post title.
	 * @return string The modified title format for a private post title.
	 */
	public function private_title_format( $title_format ) {
		if ( $this->is_thinkery_frontend() ) {
			return '%s';
		}
		return $title_format;
	}

	/**
	 * Determine whether we are on the /thinkery/ page or a subpage.
	 *
	 * @return boolean Whether we are on a thinkery page URL.
	 */
	protected function is_thinkery_frontend() {
		global $wp_query;

		if ( isset( $wp_query ) && ! empty( $wp_query->query['thinkery_tag'] ) ) {
			$wp_query->query['pagename'] = 'thinkery/' . $wp_query->query['thinkery_tag'];
		}

		if ( ! isset( $wp_query ) || ! isset( $wp_query->query['pagename'] ) ) {
			return false;
		}

		$pagename_parts = explode( '/', trim( $wp_query->query['pagename'], '/' ) );
		return count( $pagename_parts ) > 0 && 'thinkery' === $pagename_parts[0];
	}

	/**
	 * Modify the main query for the /thinkery page
	 *
	 * @param  WP_Query $query The main query.
	 * @return WP_Query The modified main query.
	 */
	public function thinkery_posts_query( $query ) {
		global $wp_query;
		if ( $wp_query !== $query || ! $this->is_thinkery_frontend() ) {
			return $query;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return $query;
		}
		$this->on_thinkery_frontend = true;

		$page_id = get_query_var( 'page' );

		$query->set( 'post_status', array( 'publish', 'private', 'draft' ) );
		$query->set( 'post_type', array( Thinkery_Things::CPT ) );
		$query->is_page = false;
		$query->set( 'page', null );
		$query->set( 'pagename', null );

		$pagename_parts = explode( '/', trim( $wp_query->query['pagename'], '/' ) );
		if ( isset( $pagename_parts[1] ) ) {
			$this->tag = $pagename_parts[1];
			$query->set(
				'tax_query', array(
					array(
						'taxonomy' => Thinkery_Things::TAG,
						'field' => 'slug',
						'terms' => $pagename_parts[1],
					),
				)
			);
			$query->is_singular = false;
		} elseif ( $page_id ) {
			$query->set( 'page_id', $page_id );
			$query->is_singular = true;
		} else {
			$query->is_singular = false;
		}

		return $query;
	}
}
